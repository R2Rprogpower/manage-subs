# BS-002: Admin manages subscriptions

**Status:** Implemented

## Story

As an **authorized administrator**, I want to inspect and manage users, plans, and subscriptions, so that I can support customers and correct subscription state.

## Business rules

- Only an administrator with the required permission may perform a management action.
- An administrator may move a draft to pending, activate, suspend, renew, cancel,
  or grant a free subscription.
- Lifecycle states are draft, pending, active, suspended, cancelled, and expired;
  only active grants access.
- A subscription lifecycle change must trigger access reconciliation for its user.
- Administrative actions must be auditable and identify the actor.
- Invalid lifecycle transitions must be rejected without partially changing state.

## Acceptance criteria

### Scenario: Authorized administrator activates a subscription

**Given** an administrator has permission to manage subscriptions  
**And** the subscription can be activated  
**When** the administrator activates it  
**Then** its status and validity period are updated consistently  
**And** the user's Telegram access is reconciled  
**And** the action is recorded in the audit log

### Scenario: Administrator cancels a subscription

**Given** an administrator has permission to manage subscriptions  
**When** the administrator cancels an active subscription  
**Then** the subscription no longer grants access  
**And** the user's Telegram access is reconciled against any other subscriptions  
**And** the action is recorded in the audit log

### Scenario: Unauthorized management attempt

**Given** a signed-in user lacks the required permission  
**When** the user attempts a subscription management action  
**Then** the action is rejected  
**And** no subscription or access state changes

## Out of scope

- The visual design of admin pages.
- Bulk import or bulk lifecycle operations.
- Payment-provider reconciliation.

## Related documentation

- [API overview](../02-api-overview.md)
- [Subscription-based Telegram access](001-subscription-telegram-access.md)
