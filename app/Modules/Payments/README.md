# Payments

Модуль хранит платежи и содержит существующую интеграцию checkout/webhook для LiqPay.
Основной CRUD проходит через [Http/Controllers/PaymentController.php](Http/Controllers/PaymentController.php), процессоры, сервис и репозиторий.
LiqPay entry points находятся в [Http/Controllers/PaymentCheckoutController.php](Http/Controllers/PaymentCheckoutController.php) и [Http/Controllers/PaymentWebhookController.php](Http/Controllers/PaymentWebhookController.php).
Контракт платёжного шлюза расположен в `app/Infrastructure/Services/Contracts`, а его текущая реализация — в `app/Infrastructure/Services/LiqPayGateway.php`.
MVP placeholder checkout реализован модулем Subscriptions и сохраняет здесь запись с provider `placeholder` и status `simulated`, не вызывая реальный шлюз.
Схема и permissions модуля находятся в [Database](Database) и [Enums/Permission.php](Enums/Permission.php).
