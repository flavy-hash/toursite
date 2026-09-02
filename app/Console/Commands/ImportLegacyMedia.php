<?php

namespace App\Console\Commands;

use App\Models\NavItem;
use App\Models\Tour;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Moves artwork that lives under /public onto the public disk.
 *
 * Content seeded from config files references images committed in the repo.
 * The admin panel's file upload only reads the public disk, so those fields
 * show as empty and cannot be replaced. Copying them across makes every image
 * editable the same way.
 *
 * Idempotent: values already on the disk are skipped, and the originals under
 * /public are copied rather than moved, so nothing else that points at them
 * breaks.
 */
class ImportLegacyMedia extends Command
{
    protected $signature = 'media:import-legacy {--dry-run : List what would change without writing}';

    protected $description = 'Copy images committed under /public onto the public storage disk';

    /**
     * Model class => [single-image fields, list-of-images fields, target directory]
     */
    private const SOURCES = [
        Tour::class => [['image'], ['gallery'], 'tours'],
        NavItem::class => [['panel_image'], [], 'nav'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $changed = 0;
        $missing = [];

        foreach (self::SOURCES as $model => [$single, $lists, $directory]) {
            foreach ($model::all() as $record) {
                $changes = [];

                foreach ($single as $field) {
                    if ($new = $this->relocate($record->{$field}, $directory, $disk, $dryRun, $missing)) {
                        $changes[$field] = $new;
                    }
                }

                foreach ($lists as $field) {
                    $current = $record->{$field} ?? [];

                    $moved = collect($current)
                        ->map(fn (string $path) => $this->relocate($path, "{$directory}/gallery", $disk, $dryRun, $missing) ?? $path)
                        ->all();

                    if ($moved !== $current) {
                        $changes[$field] = $moved;
                    }
                }

                if ($changes === []) {
                    continue;
                }

                $changed += count($changes);
                $this->line('  ' . class_basename($model) . " #{$record->getKey()}: " . implode(', ', array_keys($changes)));

                if (! $dryRun) {
                    $this->save($record, $changes);
                }
            }
        }

        foreach (array_unique($missing) as $path) {
            $this->warn("  missing on disk, left as-is: {$path}");
        }

        $this->info($dryRun
            ? "Dry run — {$changed} field(s) would be updated."
            : "Done — {$changed} field(s) updated.");

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $changes */
    private function save(Model $record, array $changes): void
    {
        $record->forceFill($changes)->save();
    }

    /**
     * @param  array<int, string>  $missing
     * @return string|null The new disk key, or null if there is nothing to move.
     */
    private function relocate(?string $path, string $directory, Filesystem $disk, bool $dryRun, array &$missing): ?string
    {
        // Already a disk key, an external URL, or empty — nothing to move.
        if (blank($path) || Str::startsWith($path, ['http://', 'https://']) || ! Str::startsWith($path, '/')) {
            return null;
        }

        $source = public_path(ltrim($path, '/'));

        if (! is_file($source)) {
            $missing[] = $path;

            return null;
        }

        $key = $directory . '/' . Str::slug(pathinfo($path, PATHINFO_FILENAME))
            . '-' . substr(md5($path), 0, 8) . '.' . pathinfo($path, PATHINFO_EXTENSION);

        if (! $dryRun && ! $disk->exists($key)) {
            $disk->put($key, (string) file_get_contents($source));
        }

        return $key;
    }
}
