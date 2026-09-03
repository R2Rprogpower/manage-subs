# Permissions

Модуль управляет ролями и разрешениями на базе `spatie/laravel-permission`.
Список системных ролей находится в [Enums/Role.php](Enums/Role.php), а базовые права пользователей и RBAC — в [Enums/Permission.php](Enums/Permission.php).
Операции назначения ролей и permissions доступны через маршруты из [api.php](api.php).
Авторизация CRUD-операций определяется политиками из [Policies](Policies), а ответы формируются ресурсами и presentations.
[Database/Seeders/PermissionsSeeder.php](Database/Seeders/PermissionsSeeder.php) создаёт базовые роли и права; итоговая синхронизация всех модульных permissions с admin/super-admin выполняется корневым `DatabaseSeeder`.
Изменения ролей и прав записываются через общий `AuditLogService`.
