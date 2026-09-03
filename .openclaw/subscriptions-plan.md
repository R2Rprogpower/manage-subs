# Telegram channel subscriptions — implementation plan

## Architecture found

- Laravel modular monolith: domain modules live in `app/Modules`, shared Eloquent models in `app/Models`, and each module follows Request → Processor → Service → Repository → Presentation.
- API authentication uses Sanctum; authorization uses Form Request checks and Spatie permissions.
- The desktop UI is the existing Blade/Bootstrap theme with vanilla JavaScript in `resources/js/pages/subscription-admin.init.js`.
- Existing `Plans` are the project's subscription-type entity, so C-002 extends `plans` rather than introducing a competing model/table.
- `Subscriptions` are the only source of channel access; `Payments` only records the placeholder step.

## Stories and dependencies

1. C-001 — owned Telegram channel registration.
2. C-002 — money/achievement subscription types; depends on C-001.
3. Subscription lifecycle — draft, pending, active, suspended, cancelled, expired.
4. U-001 — subscriber checkout; depends on C-001 and C-002.
5. C-003 — payment placeholder for money types; depends on C-002.
6. U-002 — private list of the authenticated user's subscriptions; depends on U-001.
7. Access authorization — only a non-expired active subscription grants channel access.
8. Frontend integration and cross-story tests.
9. U-003 and C-004 remain deferred except for an access-change event and achievement-compatible type/configuration fields.

## Decisions

- Keep the public/API name `plans` for backward compatibility while presenting plans as subscription types in the UI.
- Add a `kind` enum (`money`, `achievement`) and a nullable JSON `configuration` payload; existing price/currency/duration columns remain the concrete money configuration.
- Achievement checkout is unavailable in MVP; it can be configured and displayed but needs future automation before activation.
- Lifecycle transitions are performed by `SubscriptionService`; generic update endpoints may not mutate status.
- Checkout records a pending subscription, creates a simulated placeholder payment, then activates through the centralized transition method in one transaction.
- No card data, payment provider, Telegram automation, moderation, or generic rules engine is introduced.

## Relevant files

- `app/Modules/Channels`, `app/Models/TelegramChannel.php`
- `app/Modules/Plans`, `app/Models/Plan.php`
- `app/Modules/Subscriptions`, `app/Models/Subscription.php`
- `app/Modules/Payments`, `app/Models/Payment.php`
- `resources/views/admin-dashboard.blade.php`
- `resources/js/pages/subscription-admin.init.js`
- `tests/Feature/ChannelFeatureTest.php`, `tests/Feature/PlanFeatureTest.php`, `tests/Feature/SubscriberFlowFeatureTest.php`, `tests/Feature/SubscriptionFeatureTest.php`

## Validation constraints

- Docker is forbidden for this task.
- Host PHP is 8.3.6 while the lock file requires PHP >= 8.4.1, so Artisan, PHPUnit, and PHPStan require manual validation after the user's rebuild.

