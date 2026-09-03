# Business Stories

This directory is the product-level source of truth for business behaviour. A story describes the value delivered to an actor and the observable acceptance criteria. Implementation details, API contracts, database schemas, and deployment instructions belong in the other documentation sections.

## Story format

Each story should contain:

- status: `planned`, `in-progress`, `implemented`, or `deferred`;
- actor and desired outcome;
- business rules and boundaries;
- acceptance criteria written as observable scenarios;
- explicit out-of-scope items;
- links to related stories or technical documentation when useful.

Use [the story template](_template.md) when adding a story. Story filenames use a stable numeric prefix and a short kebab-case name. Existing numbers must not be reused after a story is removed.

## Story index

| ID | Story | Status |
| --- | --- | --- |
| BS-001 | [Subscription-based Telegram access](001-subscription-telegram-access.md) | Implemented (MVP) |
| BS-002 | [Admin manages subscriptions](002-admin-manages-subscriptions.md) | Implemented |
| BS-003 | [Payment placeholder](003-payment-placeholder.md) | Implemented |
| BS-004 | [Channel registration and self-service subscriptions](004-channel-registration-and-self-service.md) | Implemented |

## Current product boundary

The product manages Telegram channels, channel-specific subscription types, users, subscriptions, and placeholder payment records. Subscription state is the source of truth for Telegram channel access. The real Telegram adapter and scheduled expiration workflow are not implemented yet.

Real payment-provider behaviour is intentionally deferred. The subscriber checkout uses a clearly identified placeholder, rejects payment credentials, and never implies that money was charged.
