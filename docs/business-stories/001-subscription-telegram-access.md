# BS-001: Subscription-based Telegram access

**Status:** In progress

## Story

As a **subscriber**, I want my Telegram channel access to follow my subscription state, so that I can use the channel for exactly as long as I am entitled to it.

## Business rules

- A user is entitled to channel access when at least one subscription is active and has not expired.
- An active subscription with no end date grants access until its status changes.
- Expired or cancelled subscriptions do not grant access.
- A user must not lose access while another qualifying subscription remains active.
- The internal subscription state is the source of truth; Telegram membership is a synchronized projection of it.
- Repeating the same synchronization operation must not create a different result.
- Failed Telegram operations must be recorded and retried without changing the subscription state.

## Acceptance criteria

### Scenario: Active subscription grants access

**Given** a user has an active, non-expired subscription  
**When** channel access is synchronized  
**Then** the user is granted access to the Telegram channel

### Scenario: Unlimited subscription grants access

**Given** a user has an active subscription without an end date  
**When** channel access is evaluated  
**Then** the user remains entitled to channel access

### Scenario: Last qualifying subscription expires

**Given** a user has no other qualifying subscription  
**When** the user's active subscription reaches its end date  
**Then** the subscription becomes expired  
**And** the user's Telegram channel access is revoked

### Scenario: One of several subscriptions expires

**Given** a user has more than one subscription that grants access  
**When** one subscription expires or is cancelled  
**Then** the user keeps Telegram channel access

### Scenario: Telegram synchronization fails

**Given** the user's entitlement has changed  
**When** Telegram cannot apply the corresponding access change  
**Then** the failure is logged for retry  
**And** the authoritative subscription state remains unchanged

## Out of scope

- Choosing a Telegram Bot API library or transport.
- Defining retry intervals and infrastructure.
- Payment-provider behaviour.

## Related documentation

- [Module database structure](../03-module-database.md)
