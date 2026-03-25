# NOLA Maya × GoHighLevel — Feature TODO

> **Feature-chunked task list.** Each section is a feature area with sub-tasks.
>
> - `[ ]` = Not started
> - `[/]` = In progress
> - `[x]` = Done

---

## 1. 🔐 OAuth & Authentication [x]

> GHL OAuth flow for connecting sub-accounts to this app.

- [x] OAuth callback endpoint (`/oauth/callback`)
- [x] Exchange authorization code for access/refresh tokens
- [x] Save tokens to `location_tokens` table
- [x] Redirect to provider config page after OAuth
- [x] Auto-refresh expired tokens — `GhlService::refreshToken()` should exist and be called automatically via middleware
- [x] Token expiry check middleware — Validate token freshness before any GHL API call

### [ ] 1️⃣ Priority: Multi-location & Error Handling

> Ensure the app scales and handles errors gracefully.

- [ ] Multi-location support — Test and verify multiple GHL locations can connect simultaneously
- [ ] OAuth error handling UI — Show user-friendly errors on callback failures
- [ ] Provider config error detail display — Show GHL API error details on failure
- [ ] Maya credential state per location — Ensure each GHL location resolves the correct Maya mode/config cleanly
- [ ] Test/live mode separation — Prevent sandbox credentials from being used in live mode accidentally

---

## 2. 🔧 Provider Configuration [ ]

> Registering/removing NOLA Maya as a custom payment provider in GHL.
> This supports multiple Maya accounts by allowing locations to provision their own credentials.

- [x] Register custom provider in GHL (`ProviderConfigService::registerCustomProvider`)
- [x] Provider config UI (`/provider/config`) — Direct page for users to input Maya API keys
- [ ] Push Maya API keys via Connect Config API
- [ ] Map Maya credentials properly into GHL config fields (secret key / public key for test and live)
- [ ] Delete provider from GHL

### [ ] 1️⃣ Priority: Invoice Payments

> Handle payments for GHL Invoices via Maya.

**Prerequisites (GHL Marketplace Setup):**

- [ ] Update App OAuth scopes to include `invoices.readonly` and `invoices.write`

**Development Tasks:**

- [ ] Investigate whether GHL invoices can use the same `paymentsUrl` Maya Checkout flow or need separate Maya Invoice API logic
- [ ] Map GHL Invoice payload to Maya Checkout request structure
- [ ] Decide whether to support Maya Invoice API later as a separate feature
- [ ] Handle invoice status updates via GoHighLevel API when payment succeeds
- [ ] Verify invoice payment success flow in GHL sandbox and Maya sandbox

### [ ] 1️⃣ Priority: Provider Config Resilience (Multi-Tenant Integration)

> The integration MUST be dynamic. Each GHL sub-account MUST be able to use its own Maya keys.

- [ ] Display current provider connection status — Show whether provider is already connected before offering connect/disconnect
- [ ] Fetch existing provider config from GHL — Use `GET /payments/custom-provider/provider` to check registration state
- [ ] Provider config validation — Verify Maya credentials are valid before pushing to GHL
- [ ] Verify product activation — Confirm the account actually has Maya Checkout enabled for the given credentials
- [ ] Dynamic Key Injection — Ensure the app resolves the correct Maya keys from `location_tokens` for every API call
- [ ] Provider config error detail display — Show GHL API error details on failure, not just generic messages
- [ ] Surface sandbox vs production credential mismatch clearly in UI/logs

---

## 3. 💳 Checkout & Payment Flow [ ]

> The iFrame checkout experience inside GHL and Maya session management.

- [ ] Checkout iFrame page (`/checkout`) with GHL postMessage handshake
- [ ] `custom_provider_ready` heartbeat to GHL
- [ ] Listen for `payment_initiate_props` from GHL
- [ ] Create Maya Checkout transaction with total amount, buyer, items, redirect URLs, and `requestReferenceNumber`
- [ ] Generate and store a unique `requestReferenceNumber` for every attempt
- [ ] Open Maya hosted checkout URL in popup window or top-level redirect
- [ ] Popup fallback if popup blocker is activated
- [ ] Retrieve payment status after popup closes using DB-first logic and Maya API fallback
- [ ] Notify GHL with `custom_element_success_response`
- [ ] Notify GHL with `custom_element_error_response`
- [ ] Success and cancel callback pages
- [ ] Support multiple line items — Parse `productDetails` array from GHL into Maya item structure
- [ ] Pass buyer/contact/billing details from GHL contact into Maya payload
- [ ] Handle expired or abandoned checkout attempts gracefully
- [ ] Prevent duplicate checkout creation when user retries rapidly
- [ ] Support Maya payment methods enabled for the merchant account
- [ ] Validate currency support and fail fast for unsupported location/payment combinations

### [ ] Recurring Payments (Funnels)

> Support recurring subscriptions initiated from GHL funnels.

- [ ] Investigate how GHL sends `mode: "subscription"` in `payment_initiate_props`
- [ ] Decide recurring architecture using Maya Vault for stored cards and an app-side recurring scheduler (Required: Maya Vault provides tokenization only, not scheduling)
- [ ] Investigate whether any Maya recurring capability beyond Vault is available/enabled for your merchant setup
- [ ] Implement “pay and save card” / “save card only” flow using Maya Vault
- [ ] Store customer-to-vault token mapping securely
- [ ] Store customer consent / mandate evidence for future off-session charges
- [ ] Map GHL subscription details (recurring amount, interval, retries, cancellation rules) to your internal subscription model
- [ ] Implement `create_subscription` handler for `queryUrl`
- [ ] Implement recurring charge job using vaulted cards
- [ ] Handle `subscription.created` lifecycle sync back to GHL
- [ ] Handle recurring payment success/failure updates back to GHL
- [ ] Handle subscription cancellations (`cancel_subscription` queryUrl)
- [ ] Implement retry/dunning rules for failed recurring charges

---

## 4. ✅ Payment Verification (queryUrl) [ ]

> GHL's `queryUrl` handler for verifying, refunding, and managing payments.

- [ ] Verify payment status (`type: verify`) — DB-first with Maya API fallback
- [ ] Build `chargeSnapshot` response for GHL
- [ ] Resolve test vs live Maya credentials from GHL config
- [ ] Improve verify resilience — Handle race conditions where webhook has not arrived yet (add retry/wait logic)
- [ ] Verify by multiple ID types — Support lookup by `payment_id`, `requestReferenceNumber`, or `ghl_transaction_id`
- [ ] Add fallback retrieval by Maya payment ID when present
- [ ] Add fallback retrieval by Maya `requestReferenceNumber` when webhook delivery is delayed
- [ ] Normalize Maya statuses into GHL-compatible payment states

---

## 5. 💸 Refunds / Voids [ ]

> Processing refunds initiated from GHL back through Maya.

- [ ] Implement void flow for same-day eligible payments
- [ ] Implement refund flow for settled/next-day eligible payments
- [ ] Update transaction status to `voided` / `refunded` in DB
- [ ] Return refund/void result to GHL
- [ ] Partial refund support — Verify partial refund amounts work correctly end-to-end
- [ ] Refund status tracking — Store refund/void reference IDs and amount in transaction metadata
- [ ] Refund/void webhook handling — Process Maya payment status updates related to reversals
- [ ] Refund failure handling — Better error messages for failed refunds/voids (already reversed, invalid state, cutoff exceeded, insufficient context)
- [ ] Enforce Maya business rules around same-day void vs next-day refund
- [ ] Reconcile refund/void status if API response and webhook timing differ

---

## 6. 🔔 Webhooks [ ]

> Bidirectional webhook handling: Maya → App → GHL.

- [ ] Maya webhook endpoint (`/api/webhook/maya`)
- [ ] Register webhook URL/events in Maya
- [ ] Handle payment success events
- [ ] Handle payment failure events
- [ ] Handle payment expiration / cancellation events
- [ ] Handle authorized / captured events if auth-capture is enabled
- [ ] Handle refund / void related payment updates
- [ ] Forward successful payment event to GHL webhook
- [ ] Webhook source hardening — Restrict by Maya environment IP allowlist and HTTPS-only endpoint
- [ ] Webhook retry/idempotency — Prevent duplicate processing of the same webhook event
- [ ] Webhook event logging table — Store raw webhook payloads for debugging/audit trail
- [ ] Fast ACK pattern — Return success quickly and process heavy work asynchronously in app logic
- [ ] Webhook observability — Log delivery result when forwarding updates to GHL

### [ ] 1️⃣ Priority: Transaction Expiration Handling

> Handle stale transactions and expiration events.

- [ ] Handle Maya expiration / abandoned-payment events
- [ ] Mark transactions as `expired` when checkout is not completed in time
- [ ] Stale transaction cleanup — Mark transactions as expired if pending beyond your allowed window
- [ ] Ensure GHL verify/query reflects expired state correctly

---

## 7. 💾 Transaction Management [ ]

> Database-backed transaction tracking and reporting.

- [ ] Transaction model with scopes (`paid`, `pending`, `failed`, `expired`, `byLocation`)
- [ ] Transaction creation on Maya checkout creation
- [ ] Store Maya identifiers (`checkout_id`, `payment_id`, `requestReferenceNumber`, etc.)
- [ ] Status updates from webhooks and retrieval APIs
- [ ] Audit timeline for each transaction attempt
- [ ] Track mode (`test` / `live`) per transaction
- [ ] Track refund / void history in related metadata tables
- [ ] Stale transaction cleanup — Mark transactions as `expired` if pending beyond configured threshold

---

## 8. 📦 Maya Credentials & Merchant Setup [ ]

> Maya-specific operational preresquisites required before development and production launch.

- [ ] Complete Maya Checkout onboarding
- [ ] Confirm access to Maya Business Manager or Maya Manager 1.0
- [ ] Generate and store Sandbox Public/Secret API keys
- [ ] Generate and store Production Public/Secret API keys
- [ ] Confirm which Maya products are enabled for this merchant account (Checkout, Vault, Auth/Capture, etc.)
- [ ] Confirm which payment methods are enabled for the merchant account
- [ ] Confirm webhook registration process for sandbox and production
- [ ] Document who owns Maya account operations, key rotation, and go-live coordination

---

## 9. 🛡️ Security & Reliability [ ]

> Hardening the integration for production use.

- [ ] HTTPS enforcement — Ensure all endpoints require HTTPS
- [ ] Rate limiting — Add rate limits on checkout/session creation
- [ ] Exclude Maya/GHL webhooks from generic throttling that may break delivery
- [ ] Input sanitization — Validate and sanitize all incoming data from GHL and Maya
- [ ] Webhook source hardening — Apply Maya IP allowlisting per environment
- [ ] Encrypt stored GHL tokens at rest
- [ ] Encrypt stored Maya credentials / secrets at rest
- [ ] CSRF protection on app routes; explicitly exempt only the endpoints that must accept cross-origin/server-to-server calls
- [ ] Logging & monitoring — Structured logging for checkout, verify, refund, webhooks, and GHL delivery
- [ ] Replay protection / idempotency keys where applicable
- [ ] Secret rotation playbook for Maya and GHL credentials
- [ ] Alerting for webhook failures, payment verification mismatches, and refund/void errors

---

## 10. 🚀 Deployment & DevOps [ ]

> Production readiness and deployment pipeline.

- [ ] Production deployment guide — Document Google Cloud Run requirements, env setup, secret management, and deployment steps
- [ ] Dockerization — Create a `Dockerfile` optimized for Laravel on Google Cloud Run
- [ ] Cloud SQL setup — Configure managed MySQL for transactions, tokens, and webhook logs
- [ ] Secret Manager integration — Store Maya and GHL secrets outside plain runtime env files where possible
- [ ] Health check endpoint — Dedicated `/api/health` checking DB status
- [ ] Queue worker / job strategy for webhook processing and recurring billing jobs
- [ ] Public domain + TLS setup for Maya redirects and webhooks
- [ ] Production webhook IP/network validation test
- [ ] Runbook for sandbox-to-production cutover

---

## 11. 📄 Documentation & Testing [ ]

> Project documentation and test coverage.

- [ ] `README.md` — Setup instructions, environment requirements, architecture overview
- [ ] API documentation — Document all endpoints (Postman collection or OpenAPI spec)
- [ ] Maya integration flow diagram — OAuth → provider config → checkout → webhook → verify → refund/void
- [ ] Unit tests — Test services (`MayaService`, `GhlService`, `ProviderConfigService`)
- [ ] Integration tests — End-to-end test for OAuth → checkout → payment → verify flow
- [ ] Webhook testing — Mock Maya webhook events for automated testing
- [ ] Refund/void automated tests — Cover same-day void, next-day refund, partial refund, invalid-state failures
- [ ] Recurring billing tests — Cover vault token creation, saved card charge, retry logic, cancellation sync
- [ ] GHL sandbox testing guide — Step-by-step guide for testing the full flow on GHL sandbox
- [ ] Maya sandbox testing guide — Document sandbox credentials, test cards, redirect flows, and webhook testing steps
- [ ] Production go-live checklist — Final verification before enabling live mode

---

## 12. 🎯 Recommended Build Order [ ]

> Suggested implementation order so NOLA Maya reaches a usable MVP quickly.

- [ ] Phase 1 — GHL OAuth + provider registration + Maya credential validation
- [ ] Phase 2 — One-time Maya Checkout flow for funnels/order forms
- [ ] Phase 3 — Verify/queryUrl + transaction persistence
- [ ] Phase 4 — Maya webhooks + GHL status sync
- [ ] Phase 5 — Refunds/voids
- [ ] Phase 6 — Invoice support
- [ ] Phase 7 — Maya Vault + saved cards
- [ ] Phase 8 — Recurring subscriptions and off-session charging
- [ ] Phase 9 — Production hardening, observability, and go-live
