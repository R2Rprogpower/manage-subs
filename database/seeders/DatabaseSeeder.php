<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $moduleSeeders = [];

        foreach (glob(app_path('Modules/*/Database/Seeders/*Seeder.php')) ?: [] as $path) {
            if (! preg_match('#Modules/([^/]+)/Database/Seeders/([^/]+)\.php$#', str_replace('\\', '/', $path), $matches)) {
                continue;
            }

            $moduleSeeders[] = sprintf(
                'App\\Modules\\%s\\Database\\Seeders\\%s',
                $matches[1],
                $matches[2]
            );
        }

        sort($moduleSeeders);

        if ($moduleSeeders !== []) {
            $this->call($moduleSeeders);
        }
    }
}
