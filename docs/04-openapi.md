# OpenAPI / API Docs

This project uses Scramble for OpenAPI generation.

- Docs UI: http://localhost:8080/docs/api
- OpenAPI JSON: http://localhost:8080/docs/api.json

Docs protection is controlled in `config/scramble.php` via `SCRAMBLE_PROTECT_DOCS`.
By default in this project (`false`), docs are publicly accessible.

## Generate OpenAPI file

```bash
# Export OpenAPI JSON to default path (configured in config/scramble.php)
docker compose exec -T app php artisan scramble:export

# Export OpenAPI JSON to a custom file path
docker compose exec -T app php artisan scramble:export --path=openapi.json

# Analyze documentation generation issues
docker compose exec -T app php artisan scramble:analyze
```
