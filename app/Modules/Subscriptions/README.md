# Subscriptions

Модуль является источником истины для доступа пользователя к каждому Telegram-каналу.
Статусы и источники описаны в [Enums/SubscriptionStatus.php](Enums/SubscriptionStatus.php) и [Enums/SubscriptionSource.php](Enums/SubscriptionSource.php).
Self-service endpoints позволяют оформить подписку через безопасный placeholder и получить только собственные подписки; маршруты находятся в [api.php](api.php).
[Services/SubscriptionService.php](Services/SubscriptionService.php) проверяет доступность канала и типа, блокирует дубли активного доступа и управляет lifecycle.
Администратор может activate, suspend, cancel, renew и grant free access при наличии permissions из [Enums/Permission.php](Enums/Permission.php).
Каждое существенное изменение создаёт audit log и событие [Events/SubscriptionAccessChanged.php](Events/SubscriptionAccessChanged.php) для будущей Telegram-синхронизации.
Lifecycle включает draft, pending, active, suspended, cancelled и expired, а переходы централизованы в сервисе.
Полноценная Telegram automation остаётся вне MVP, а доступ предоставляется только статусом active с непросроченным `ends_at`.
