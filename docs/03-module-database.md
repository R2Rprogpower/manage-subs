# Module-local Database Structure

Each module can define its own database artifacts directly inside the module:

- `app/Modules/<Module>/Database/Migrations/*.php`
- `app/Modules/<Module>/Database/Seeders/*Seeder.php`
- `app/Modules/<Module>/Database/Factories/*Factory.php`

## How it works

- Module migrations are auto-loaded by `App\Providers\ModuleDatabaseServiceProvider`.
- Module seeders are auto-discovered by `database/seeders/DatabaseSeeder.php`.
- Factories can live in the same module and be referenced from module models via `newFactory()`.

## Example commands

```bash
docker compose exec -T app php artisan migrate
docker compose exec -T app php artisan db:seed
docker compose exec -T app php artisan migrate:fresh --seed
```
