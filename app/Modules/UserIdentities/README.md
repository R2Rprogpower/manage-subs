# UserIdentities

Модуль хранит внешние идентичности пользователя, включая связь аккаунта приложения с внешним provider ID.
Схема данных находится в [Database/Migrations](Database/Migrations), а модель — в `app/Models/UserIdentity.php`.
CRUD API описан в [api.php](api.php) и проходит через requests, processors, service и repository модуля.
Доступ к операциям ограничивают [Policies/UserIdentityPolicy.php](Policies/UserIdentityPolicy.php) и permissions из [Enums/Permission.php](Enums/Permission.php).
Начальные permissions создаёт [Database/Seeders/UserIdentitiesSeeder.php](Database/Seeders/UserIdentitiesSeeder.php).
Telegram-доставка доступа не реализована в этом модуле и должна подключаться через событие изменения subscription state.
