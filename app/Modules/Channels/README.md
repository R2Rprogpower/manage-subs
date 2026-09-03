# Channels

Модуль регистрирует Telegram-каналы, закрепляет владельца и контролирует уникальность Telegram chat ID и username.
Доступные статусы описаны в [Enums/ChannelStatus.php](Enums/ChannelStatus.php), а права — в [Enums/Permission.php](Enums/Permission.php).
Owner-aware доступ реализован в [Policies/ChannelPolicy.php](Policies/ChannelPolicy.php) и request-классах из [Http/Requests](Http/Requests).
Каталог возвращает только активные каналы, у которых есть хотя бы один активный тип подписки; запросы находятся в [Repositories/ChannelRepository.php](Repositories/ChannelRepository.php).
API-маршруты описаны в [api.php](api.php), схема таблицы — в [Database/Migrations](Database/Migrations), а начальные permissions — в [Database/Seeders](Database/Seeders).
