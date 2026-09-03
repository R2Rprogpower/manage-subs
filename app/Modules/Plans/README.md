# Plans

Модуль управляет типами подписки, которые привязаны к конкретному Telegram-каналу через `telegram_channel_id`.
Тип содержит код, название, цену в minor units, валюту, длительность и признак активности; модель находится в `app/Models/Plan.php`.
Владелец может изменять типы только своих каналов, а администратор — при наличии permissions из [Enums/Permission.php](Enums/Permission.php).
Validation и owner checks реализованы в [Http/Requests](Http/Requests), а бизнес-операции проходят через [Services/PlanService.php](Services/PlanService.php).
API-маршруты находятся в [api.php](api.php), миграции и локальные demo types — в [Database](Database).
Только активные типы активного канала участвуют в публичном каталоге и self-service checkout.
