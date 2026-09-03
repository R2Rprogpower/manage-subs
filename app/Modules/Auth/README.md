# Auth

Модуль отвечает за регистрацию, вход, выход, MFA и управление API-токенами Sanctum.
HTTP-маршруты находятся в [api.php](api.php), а входящие данные проверяются классами из [Http/Requests](Http/Requests).
Контроллер передаёт выполнение процессорам из [Processors](Processors), которые вызывают сервисы из [Services](Services).
Формат успешных ответов определяют классы из [Presentations](Presentations).
Контракты репозиториев и сервисов находятся в [Contracts](Contracts), а локальные демонстрационные данные — в [Database/Seeders](Database/Seeders).
