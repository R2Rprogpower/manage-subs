# Users

Модуль предоставляет административный CRUD пользователей и чтение их ролей и permissions.
Модель и authentication state находятся в `app/Models/User.php`, а API-маршруты — в [api.php](api.php).
Входные данные валидируются в [Http/Requests](Http/Requests), включая отдельную проверку права на просмотр пользователей.
Операции выполняются через [Processors](Processors), [Services/UserService.php](Services/UserService.php) и [Repositories/UserRepository.php](Repositories/UserRepository.php).
Формат API-ответов определяют classes из [Presentations](Presentations) и [Resources](Resources).
Локальные demo users создаёт [Database/Seeders/UsersSeeder.php](Database/Seeders/UsersSeeder.php), а в production этот seeder их пропускает.
Изменения пользователей записываются в общий audit log.
