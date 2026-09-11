<#
    Builds an incremental update for a site already deployed by
    scripts/build-deploy-package.ps1.

    The full package is ~37 MB, almost all of it vendor/ and uploaded photos
    that do not change between releases. This ships only what does: application
    code, views, and the compiled front-end assets.

    The live layout splits the application from the web root (see DEPLOY.md),
    so the archive carries two folders that land in different places:

        app/  ->  ~/twinsafrican_app/
        web/  ->  ~/twinsafricantravel.com/

    Deliberately NOT included, because overwriting any of them breaks the site:
      .env                    server-specific, holds the passwords
      vendor/                 unchanged unless composer.lock changes
      storage/app/public/     the uploaded photos
      public/index.php        rewritten on the server for the split layout
      public/.htaccess        may have been edited for the host

    IMPORTANT: if the release adds any new class, the server must regenerate
    its autoloader before the new code will load at all. vendor/ is installed
    with --classmap-authoritative, which disables PSR-4 lookup entirely, so a
    copied-in model or controller stays invisible and every page touching it
    returns 500:

        cd ~/twinsafrican_app
        composer dump-autoload --optimize --classmap-authoritative --no-dev

    See "Shipping an update" in DEPLOY.md for the full order of operations.

    Usage:
        powershell -ExecutionPolicy Bypass -File scripts\build-update-package.ps1
#>

param(
    [string] $OutputDir = "$env:TEMP\twins-update"
)

$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$stage = Join-Path $OutputDir 'stage'
$appDir = Join-Path $stage 'app'
$webDir = Join-Path $stage 'web'
$zip = Join-Path $OutputDir 'twins-african-update.zip'

function Size($path) {
    if (-not (Test-Path $path)) { return 0 }
    (Get-ChildItem $path -Recurse -File -Force -ErrorAction SilentlyContinue |
        Measure-Object Length -Sum).Sum
}

Write-Host "`nBuilding update package" -ForegroundColor Cyan
Write-Host ("-" * 56)

# ---------------------------------------------------------------------------
# 0. Stop if the front-end has not been rebuilt
# ---------------------------------------------------------------------------

$manifest = Join-Path $root 'public\build\manifest.json'
if (-not (Test-Path $manifest)) {
    throw "public/build/manifest.json is missing - run 'npm run build' first."
}

# A manifest older than the newest source file means the CSS or JS changes
# would not actually reach the server.
$newestSource = Get-ChildItem (Join-Path $root 'resources') -Recurse -File -Force |
    Where-Object { $_.Extension -in '.css', '.js' } |
    Sort-Object LastWriteTime -Descending |
    Select-Object -First 1

if ($newestSource -and $newestSource.LastWriteTime -gt (Get-Item $manifest).LastWriteTime) {
    throw "public/build is older than $($newestSource.Name) - run 'npm run build' first."
}

if (Test-Path $OutputDir) { Remove-Item $OutputDir -Recurse -Force }
New-Item $appDir -ItemType Directory -Force | Out-Null
New-Item $webDir -ItemType Directory -Force | Out-Null

# ---------------------------------------------------------------------------
# 1. Application code
# ---------------------------------------------------------------------------

foreach ($dir in @('app', 'config', 'database', 'resources', 'routes')) {
    $args = @(
        (Join-Path $root $dir), (Join-Path $appDir $dir),
        '/E', '/NFL', '/NDL', '/NJH', '/NJS', '/NP', '/R:1', '/W:1'
    )
    if ($dir -eq 'resources') { $args += @('/XD', 'node_modules') }
    robocopy @args | Out-Null
}

# bootstrap/app.php and providers.php only: bootstrap/cache holds the server's
# own compiled files and must not be overwritten by ours.
New-Item (Join-Path $appDir 'bootstrap') -ItemType Directory -Force | Out-Null
foreach ($file in @('app.php', 'providers.php')) {
    $source = Join-Path $root "bootstrap\$file"
    if (Test-Path $source) {
        Copy-Item $source (Join-Path $appDir "bootstrap\$file") -Force
    }
}

# ---------------------------------------------------------------------------
# 2. Compiled front-end and any new public assets
# ---------------------------------------------------------------------------

robocopy (Join-Path $root 'public\build') (Join-Path $webDir 'build') `
    /E /NFL /NDL /NJH /NJS /NP /R:1 /W:1 | Out-Null

# The logo files, which are new since the last deployment. Everything else
# under public/assets is unchanged and already on the server.
$logos = Get-ChildItem (Join-Path $root 'public\assets\images') -Filter 'logo-*.png' -File
if ($logos) {
    $target = Join-Path $webDir 'assets\images'
    New-Item $target -ItemType Directory -Force | Out-Null
    $logos | ForEach-Object { Copy-Item $_.FullName (Join-Path $target $_.Name) -Force }
}

# ---------------------------------------------------------------------------
# 3. Zip, with forward slashes so Linux unpacks a tree rather than one flat
#    folder of backslashed filenames. See DEPLOY.md.
# ---------------------------------------------------------------------------

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

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
Write-Host ("{0,-42} {1,8:N1} MB" -f 'app/  (to twinsafrican_app)', ((Size $appDir) / 1MB))
Write-Host ("{0,-42} {1,8:N1} MB" -f 'web/  (to twinsafricantravel.com)', ((Size $webDir) / 1MB))
Write-Host ("{0,-42} {1,8:N1} MB" -f 'ZIP TO UPLOAD', ((Get-Item $zip).Length / 1MB))
Write-Host "`n  $zip`n" -ForegroundColor Green
