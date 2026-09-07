<#
    Builds a deployment archive for shared hosting (Namecheap cPanel).

    The working copy is ~1 GB, almost all of which is irrelevant to a server:
    git history, node_modules, dev-only Composer packages, and multi-megabyte
    test fixtures inside otherwise-needed packages.

    This copies only what runs, installs production dependencies fresh, strips
    package tests and docs, and zips the result. Nothing in the project is
    modified - the staging copy is built in a temp directory.

    Usage:
        pwsh scripts/build-deploy-package.ps1
        pwsh scripts/build-deploy-package.ps1 -SkipUploads   # code only
#>

param(
    [string] $OutputDir = "$env:TEMP\twins-deploy",
    # Uploaded photos are the bulk of the remaining size. Skip them if you would
    # rather move storage/app/public over FTP separately.
    [switch] $SkipUploads,

    # Downscale uploaded photos in the staged copy. Admin uploads arrive at
    # camera resolution; capping them cuts the package and speeds up the site.
    # Your originals are never touched.
    [switch] $OptimiseImages,

    [int] $MaxEdge = 1920
)

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$stage = Join-Path $OutputDir 'app'
$zip = Join-Path $OutputDir 'twins-african-deploy.zip'

function Size($path) {
    if (-not (Test-Path $path)) { return 0 }
    (Get-ChildItem $path -Recurse -File -Force -ErrorAction SilentlyContinue |
        Measure-Object Length -Sum).Sum
}

function Report($label, $bytes) {
    "{0,-42} {1,8:N1} MB" -f $label, ($bytes / 1MB)
}

Write-Host "`nBuilding deployment package" -ForegroundColor Cyan
Write-Host ("-" * 56)

if (Test-Path $OutputDir) { Remove-Item $OutputDir -Recurse -Force }
New-Item $stage -ItemType Directory -Force | Out-Null

# ---------------------------------------------------------------------------
# 1. Copy what actually runs
# ---------------------------------------------------------------------------

# Excluded wholesale: git history, node sources, the test suite, and any local
# caches or logs the server will regenerate itself.
$excludeDirs = @('.git', '.github', 'node_modules', 'tests', 'scripts', '.idea', '.vscode')

$copy = @(
    'app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage'
)

foreach ($dir in $copy) {
    $args = @(
        (Join-Path $root $dir), (Join-Path $stage $dir),
        '/E', '/NFL', '/NDL', '/NJH', '/NJS', '/NP', '/R:1', '/W:1'
    )
    if ($dir -eq 'resources') { $args += @('/XD', 'node_modules') }
    robocopy @args | Out-Null
}

foreach ($file in @('artisan', 'composer.json', 'composer.lock', '.env.example')) {
    Copy-Item (Join-Path $root $file) (Join-Path $stage $file) -Force
}

Report 'application code' (Size $stage) | Write-Host

# ---------------------------------------------------------------------------
# 2. Clear runtime junk the server regenerates
# ---------------------------------------------------------------------------

# Logs and compiled caches from local development have no business shipping,
# and a stale config cache on a new host causes very confusing failures.
# The .gitignore stays: it is the only file in the folder once the logs are
# gone, and a zip cannot carry an empty directory. Without it storage/logs
# would not exist on the server and Laravel could not write its log at all.
foreach ($log in @(Get-ChildItem (Join-Path $stage 'storage\logs') -File -Force -ErrorAction SilentlyContinue)) {
    if ($log.Name -ne '.gitignore') {
        Remove-Item -LiteralPath $log.FullName -Force -ErrorAction SilentlyContinue
    }
}

# Cleared recursively, keeping each .gitignore so the folder still exists:
#   framework/*        - compiled views, sessions and cache from local browsing
#   framework/testing  - files the test suite wrote to its fake disks
#   livewire-tmp       - half-finished admin uploads, kept only until a form saves
foreach ($cache in @('storage\framework\cache\data', 'storage\framework\sessions',
                     'storage\framework\views', 'storage\framework\testing',
                     'storage\app\private\livewire-tmp', 'bootstrap\cache')) {
    $path = Join-Path $stage $cache
    if (-not (Test-Path $path)) { continue }

    foreach ($file in @(Get-ChildItem $path -Recurse -File -Force -ErrorAction SilentlyContinue)) {
        if ($file.Name -ne '.gitignore') {
            Remove-Item -LiteralPath $file.FullName -Force -ErrorAction SilentlyContinue
        }
    }

    # Deepest first, so a folder is empty by the time it is considered.
    $dirs = @(Get-ChildItem $path -Recurse -Directory -Force -ErrorAction SilentlyContinue) |
        Sort-Object { $_.FullName.Length } -Descending
    foreach ($dir in $dirs) {
        if (-not @(Get-ChildItem $dir.FullName -Force -ErrorAction SilentlyContinue)) {
            Remove-Item -LiteralPath $dir.FullName -Force -ErrorAction SilentlyContinue
        }
    }
}

# public/storage is a symlink to storage/app/public; it must be recreated on
# the server, and a copied symlink would duplicate every upload.
$link = Join-Path $stage 'public\storage'
if (Test-Path $link) { Remove-Item $link -Recurse -Force -ErrorAction SilentlyContinue }

if ($SkipUploads) {
    $uploads = Join-Path $stage 'storage\app\public'
    if (Test-Path $uploads) { Remove-Item $uploads -Recurse -Force }
    Write-Host '  uploads skipped (-SkipUploads)' -ForegroundColor Yellow
}

if ($OptimiseImages -and -not $SkipUploads) {
    $uploads = Join-Path $stage 'storage/app/public'
    if (Test-Path $uploads) {
        php (Join-Path $root 'scripts\optimise-images.php') $uploads $MaxEdge 82
    }
}

Report 'after clearing caches and logs' (Size $stage) | Write-Host

# ---------------------------------------------------------------------------
# 3. Production dependencies only
# ---------------------------------------------------------------------------

Push-Location $stage
$previousHome = $env:COMPOSER_HOME
try {
    <#
        Run against a throwaway Composer home.

        A stale github-oauth token in the global auth.json makes dist downloads
        fail with a 401. Composer then falls back to installing from git source,
        which drags each package's whole history along - vendor comes out
        larger than it started - and on Windows it dies outright on
        vlucas/phpdotenv, whose test fixtures contain a file named "nul.env"
        (NUL is a reserved device name).

        An isolated home sidesteps the bad credential without touching it.
    #>
    $env:COMPOSER_HOME = Join-Path $OutputDir 'composer-home'
    New-Item $env:COMPOSER_HOME -ItemType Directory -Force | Out-Null

    # --no-dev drops phpunit, mockery, pint, faker and collision, which together
    # are the largest thing in vendor. The optimised autoloader also saves the
    # server a filesystem scan on every request.
    composer install --no-dev --prefer-dist --optimize-autoloader `
        --classmap-authoritative --no-interaction --no-progress

    if ($LASTEXITCODE -ne 0) {
        throw "composer install failed (exit $LASTEXITCODE) - vendor would be incomplete, stopping."
    }
} finally {
    $env:COMPOSER_HOME = $previousHome
    Pop-Location
}

Report 'with production vendor' (Size $stage) | Write-Host

# ---------------------------------------------------------------------------
# 4. Strip test fixtures and docs from the packages that remain
# ---------------------------------------------------------------------------

# openspout and league/csv each ship a 37.8 MB million-row CSV used only by
# their own test suites. Removing package tests is standard for deployment.
$before = Size (Join-Path $stage 'vendor')
$pruned = 0

Get-ChildItem (Join-Path $stage 'vendor') -Directory -Recurse -Depth 2 -ErrorAction SilentlyContinue |
    Where-Object { $_.Name -in @('tests', 'test', 'Tests', 'docs', 'doc', 'examples', 'test_files', '.github') } |
    ForEach-Object {
        $pruned += (Size $_.FullName)
        Remove-Item $_.FullName -Recurse -Force -ErrorAction SilentlyContinue
    }

Report "vendor after pruning (freed $([math]::Round($pruned/1MB,1)) MB)" (Size (Join-Path $stage 'vendor')) | Write-Host

# ---------------------------------------------------------------------------
# 5. Zip
# ---------------------------------------------------------------------------

<#
    Written entry by entry rather than with Compress-Archive.

    Compress-Archive on Windows PowerShell 5.1 stores paths with backslashes
    ("app\Console\Kernel.php"). The ZIP format requires forward slashes, and
    Linux extractors - including the one behind cPanel's File Manager - read a
    backslash as part of the file name, so the whole archive lands as thousands
    of files dumped in one folder. Naming the entries ourselves avoids that.
#>

Write-Host "`n  compressing..." -ForegroundColor DarkGray

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

if (Test-Path $zip) { Remove-Item $zip -Force }

$archive = [System.IO.Compression.ZipFile]::Open($zip, 'Create')
try {
    $prefix = (Resolve-Path $stage).Path.TrimEnd('\') + '\'

    foreach ($file in Get-ChildItem $stage -Recurse -File -Force) {
        $name = $file.FullName.Substring($prefix.Length).Replace('\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
            $archive, $file.FullName, $name, 'Optimal') | Out-Null
    }
} finally {
    $archive.Dispose()
}

Write-Host ("-" * 56)
Report 'staged folder' (Size $stage) | Write-Host
Report 'ZIP TO UPLOAD' ((Get-Item $zip).Length) | Write-Host
Write-Host "`n  $zip`n" -ForegroundColor Green
