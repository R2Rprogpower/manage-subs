# BS-003: Payment placeholder

**Status:** Planned

## Story

As a **prospective subscriber**, I want to complete the subscription flow without a real payment, so that the product experience can be developed and tested before payment processing is enabled.

## Business rules

- The placeholder must clearly state that no real payment is being processed.
- It must not collect, store, or transmit card or bank details.
- It must not call a real payment provider.
- A placeholder result must be distinguishable from a real provider transaction in stored data and audit records.
- Enabling a real payment provider later requires a separate business story and security review.

## Acceptance criteria

### Scenario: User reaches the payment step

**Given** a user has selected a paid plan  
**When** the user continues to payment  
**Then** the product shows a payment placeholder  
**And** explicitly states that no money will be charged

### Scenario: User confirms the placeholder flow

**Given** the payment placeholder is displayed  
**When** the user confirms it  
**Then** the configured non-production subscription outcome is applied  
**And** the result is recorded as a placeholder rather than a real payment

### Scenario: Sensitive payment data is submitted

**Given** the placeholder flow is active  
**When** a request includes card or bank credentials  
**Then** the application rejects the data  
**And** does not persist or forward it

## Out of scope

- LiqPay or any other payment-provider integration.
- Webhooks, refunds, chargebacks, and financial reconciliation.
- Production charging.

## Related documentation

- [Admin manages subscriptions](002-admin-manages-subscriptions.md)
