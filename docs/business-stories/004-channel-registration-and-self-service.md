# BS-004: Channel registration and self-service subscriptions

**Status:** Implemented

## Story

As a **Telegram channel owner**, I want to register my channel and configure its
subscription types so that subscribers can discover it and obtain access.

As a **subscriber**, I want to choose an available channel and subscription type
and see my own subscriptions.

## Business rules

- A Telegram chat ID and public username are unique when present.
- The authenticated creator becomes the channel owner.
- Owners may update only their own channels and subscription types unless they
  have an administrative permission.
- A channel appears in the catalog only when it is active and has at least one
  active subscription type.
- A subscriber cannot hold two simultaneously active subscriptions for one
  channel.
- Checkout is a payment placeholder and activates the subscription immediately.
- Subscription state and validity dates determine channel access.
- A subscriber can list only their own subscriptions through the self-service
  endpoint.

## API

- `GET /api/channels/available`
- `GET|POST|PATCH|DELETE /api/channels`
- `POST /api/subscriptions/checkout`
- `GET /api/subscriptions/mine`

## Out of scope

- Telegram Bot API delivery or membership reconciliation.
- Channel moderation and manual review.
- Real payments.
