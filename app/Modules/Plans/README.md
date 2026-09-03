# Plans

Модуль управляет типами подписки, которые привязаны к конкретному Telegram-каналу через `telegram_channel_id`.
Тип различается полем `kind` (`money` или `achievement`), содержит название, флаг активности и подходящую конфигурацию; модель находится в `app/Models/Plan.php`.
Денежный тип использует цену, валюту и длительность, а achievement-конфигурация хранит минимальный ключ будущего достижения без rules engine.
Владелец может изменять типы только своих каналов, а администратор — при наличии permissions из [Enums/Permission.php](Enums/Permission.php).
Validation и owner checks реализованы в [Http/Requests](Http/Requests), а бизнес-операции проходят через [Services/PlanService.php](Services/PlanService.php).
API-маршруты находятся в [api.php](api.php), миграции и локальные demo types — в [Database](Database).
Только активные типы активного канала участвуют в публичном каталоге, а MVP checkout разрешён только для `money`.
