<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleDatabaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $moduleMigrationPaths = array_values(array_filter(
            glob(app_path('Modules/*/Database/Migrations')) ?: [],
            static fn (string $path): bool => is_dir($path)
        ));

        if ($moduleMigrationPaths !== []) {
            $this->loadMigrationsFrom($moduleMigrationPaths);
        }
    }
}
