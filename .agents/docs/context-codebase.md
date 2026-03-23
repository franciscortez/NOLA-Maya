# Context Codebase

## Project Overview

This project integrates **Maya** directly into **GoHighLevel (GHL)** as a custom payment provider. It allows GHL users to process payments through cards, GCash, GrabPay, Maya, and QRPH using a centralized Laravel application.

### Core Idea

```
GHL CRM ←→ This Laravel App ←→ Maya API
```

- **GHL**: Business CRM for managing contacts, sales, and invoices.
- **This App**: A bridge that registers as a custom payment provider in GHL.
- **Maya**: Handles secure payment processing for various local and international methods (formerly Paymaya).

## Tech Stack

| Layer           | Technology                                     |
| --------------- | ---------------------------------------------- |
| Framework       | Laravel 12 (PHP 8.2+)                          |
| Database        | MySQL                                          |
| Frontend        | Blade templates, vanilla JS, TailwindCSS (CDN) |
| Payment Gateway | Maya API (v2)                                  |
| CRM Platform    | GoHighLevel API v2021-07-28                    |
| Tunneling       | ngrok (for HTTPS in local development)         |
| Hosting         | Google Cloud Run (Target Environment)          |

---

## 🏢 GHL (GoHighLevel)

### Overview

GHL acts as the CRM and the initiator of the payment process. We integrate by building a "Custom Payment Provider" app that handles OAuth, presents an iFrame for checkout (`paymentsUrl`), and provides a backend webhook for payment verification and management (`queryUrl`). 

**Dynamic Multi-Account Support**: This integration is designed for multi-tenancy. Each GHL sub-account (location) can be linked to its own unique Maya account via a dedicated configuration page. Credentials are securely stored at the location level in the database.

### OAuth Scopes

The integration requires the following scopes for full functionality:

- `payments/orders.readonly`, `payments/orders.write`, `payments/subscriptions.readonly`, `payments/transactions.readonly`, `payment/orders.collectPayment`
- `payments/custom-provider.readonly`, `payments/custom-provider.write`
- `products.readonly`, `products/prices.readonly`
- `contact.readonly`
- `invoice.readonly`, `invoice.write`
- `location.readonly`

### 1. Custom Payments Integration Details

HighLevel's Custom Payment Provider API allows managing checkouts, subscriptions, saved methods, and refunds.

#### 1.1 Provider Registration & Config

- **Create Public Provider Config API**: `POST /payments/custom-provider/provider`
  Requires an OAuth `access_token` and registers:
    - `queryUrl`: Backend API endpoint for background validation (`/api/ghl/query`).
    - `paymentsUrl`: Frontend URL loaded in an iFrame for checkout (`/checkout`).
- **Connect Config API**: Associates API keys (`apiKey` for backend, `publishableKey` for frontend) at the location level.

#### 1.2 Checkout iFrame Sequence

1.  **Ready State**: iFrame tells GHL: `{ "type": "custom_provider_ready", "loaded": true, "addCardOnFileSupported": true }`.
2.  **Prop Initiation**: GHL responds with `payment_initiate_props` (one-time/subscription) or `setup_initiate_props` (vaulting).
3.  **Payment Processing**: Iframe handles the internal merchant flow (e.g., calling Maya for a checkout URL and redirecting, or creating a vaulting session).
4.  **Outcome Responses**: Iframe posts back one of:
    - `{ "type": "custom_element_success_response", "chargeId": "GATEWAY_CHARGE_ID" }`
    - `{ "type": "custom_element_error_response", "error": { "description": "Reason for failure" } }`
    - `{ "type": "custom_element_close_response" }` (User canceled).

#### 1.3 `queryUrl` Handlers

The `<APP_URL>/api/ghl/query` endpoint handles backend requests from GHL:

- **`verify`**: GHL confirms payment status after Iframe success.
- **`list_payment_methods`**: Returns saved card details for a contact.
- **`charge_payment`**: Processes off-session or invoice charges.
- **`create_subscription`**: STARTS a manual subscription schedule.
- **`cancel_subscription`**: REQUESTS a subscription cancellation.
- **`refund`**: Processes full or partial refunds via Maya API.

#### 1.4 Webhook Synchronization

Sync updates back to GHL using `POST https://backend.leadconnectorhq.com/payments/custom-provider/webhook`.

- **Supported Events**: `payment.captured`, `subscription.trialing`, `subscription.active`, `subscription.updated`, `subscription.charged`.
- **Authentication**: Requires `apiKey` and `marketplaceAppId`.

#### 1.5 Saved Methods (Vaulting)

To support "Card on File", the app must:

1.  Extend `custom_provider_ready` with `"addCardOnFileSupported": true`.
2.  Handle `setup_initiate_props` in the Iframe.
3.  Store the `customerId` and `paymentMethodId` mapping.
4.  Respond to `list_payment_methods` and `charge_payment` on the `queryUrl`.

#### 1.6 Subscriptions & Schedules

- **Capabilities API**: App must declare support for subscription schedules (Agency/Location level).
- **Manual Schedules**: GHL can trigger `create_subscription` directly via `queryUrl`.
- **Updates**: Recurring charge successes must be reported via `subscription.charged` webhooks.

### Database Schema

#### `location_tokens`

Stores GHL OAuth tokens and localized Maya credentials.

| Column               | Type            | Description                                       |
| -------------------- | --------------- | ------------------------------------------------- |
| location_id          | string (unique) | GHL sub-account location ID                       |
| location_name        | string          | GHL sub-account human-readable name               |
| access_token         | text            | GHL OAuth access token (Encrypted)                |
| refresh_token        | text            | GHL OAuth refresh token (Encrypted)               |
| expires_at           | timestamp       | Token expiry (Auto-refreshed via middleware)      |
| maya_live_public_key | text            | Per-location Maya Live Public Key                 |
| maya_live_secret_key | text            | Per-location Maya Live Secret Key (Encrypted)     |
| maya_test_public_key | text            | Per-location Maya Test Public Key                 |
| maya_test_secret_key | text            | Per-location Maya Test Secret Key (Encrypted)     |
| live_webhook_id      | string          | Provisioned Maya Live Webhook ID                  |
| test_webhook_id      | string          | Provisioned Maya Test Webhook ID                  |
| live_webhook_secret  | text            | Per-location Maya Live Webhook Secret (Encrypted) |
| test_webhook_secret  | text            | Per-location Maya Test Webhook Secret (Encrypted) |
| user_type            | string          | Usually "Location"                                |

---

## 💳 MAYA

### Overview

Maya handles secure payment capture via three primary products: **Checkout**, **Pay with Maya**, and **Vault**. Integration is built on Maya API v2.

### 1. Maya Checkout

- **Ready-built Payment Form**: Provides a secure checkout page for Card Acceptance (Visa, Mastercard, JCB) and E-Wallets.
- **3DS Authentication**: Automatically handled for cards depending on the issuing bank.
- **Customizable**: Supports merchant branding (logo/name) in the hosted UI.

### 2. Pay with Maya (Express)

- **Maya Wallet Login**: Customers pay by logging into their Maya account.
- **QRPh (Scan-to-Pay)**: Generates a QRPh code for payment via Maya or other participating e-wallets/banks.
- **Wallet Linking**: Allows customers to link their Maya account for faster recurring payments.

### 3. Maya Vault (Tokenization)

- **Card Tokenization**: Replaces sensitive card data with a unique `paymentToken`.
- **PCI DSS Compliance**: Maya handles the secure storage of card information.
- **Recurring Payments**: Used for off-session charges. **Note**: Maya does NOT provide a built-in scheduler; the application must implement its own cron/scheduler and call the Vault API.

### Webhook & Security

- **No Signature Header**: Security is maintained via IP Whitelisting (per environment) and manual Status Verification.
- **`requestReferenceNumber`**: A mandatory unique identifier for every transaction attempt, used for reconciliation.

### Database Schema

#### `transactions`

Bridging table linking GHL and Maya transactions.

| Column             | Type            | Description                                  |
| ------------------ | --------------- | -------------------------------------------- |
| checkout_id        | string (unique) | Maya checkout ID                             |
| payment_id         | string          | Maya payment ID                              |
| ghl_transaction_id | string          | GHL transaction reference                    |
| ghl_order_id       | string          | GHL order reference                          |
| ghl_invoice_id     | string          | GHL invoice reference                        |
| ghl_location_id    | string          | GHL location ID                              |
| amount             | integer         | Amount in cents (centavos)                   |
| currency           | string(3)       | Default: PHP                                 |
| description        | string          | Payment description                          |
| status             | string          | pending / paid / failed / refunded / expired |
| payment_method     | string          | card / qrph / gcash / grab_pay / paymaya     |
| customer_name      | string          | Customer name                                |
| customer_email     | string          | Customer email                               |
| metadata           | json            | Raw webhook data                             |
| paid_at            | timestamp       | When payment was confirmed                   |

#### `webhook_logs`

Audit table for incoming Maya Webhook events.

| Column        | Type            | Description                            |
| ------------- | --------------- | -------------------------------------- |
| event_id      | string (unique) | Maya unique event ID                   |
| event_type    | string          | Type of webhook event                  |
| payload       | json            | Full raw payload                       |
| status        | string          | pending / processed / failed / skipped |
| error_message | text            | If failed, why                         |

---

## 🔄 FLOW

### 1. Installation & Config Flow

1.  **OAuth Redirect**: HighLevel open redirect URL with `code`.
2.  **Token Exchange**: Exchange `code` for `access_token` and `refresh_token`.
3.  **Config Page**: User configures Maya Sandbox/Live keys on the Custom Page.
4.  **Register Provider**: Backend calls `Create Public Provider Config` with `queryUrl` and `paymentsUrl`.
5.  **Connect Keys**: Backend calls `Connect Config API` to store `apiKey` and `publishableKey` in GHL.

### 2. One-Time Checkout Flow

1.  **Handshake**: `custom_provider_ready` → `payment_initiate_props` (with `amount`, `currency`).
2.  **Maya Session**: Backend calls Maya Checkout API; returns `redirectUrl`.
3.  **Handoff**: User pays on Maya hosted page; redirects back to Iframe success page.
4.  **Success**: Iframe emits `custom_element_success_response`.
5.  **Verification**: GHL calls `verify` on `queryUrl`. Backend checks Maya payment status; Returns `{ "success": true }`.

### 3. Vaulting (Save Card) Flow

1.  **Initiation**: User clicks "Add Card" in GHL; Iframe loads.
2.  **Handshake**: `custom_provider_ready` → `setup_initiate_props`.
3.  **Maya Vault**: Iframe collects card info; Creates a Token; Backend creates a Maya Customer/Payment Method.
4.  **Completion**: Iframe emits success response.
5.  **Persistence**: Saved method is now available via `list_payment_methods`.

### 4. Recurring / Off-Session Flow

1.  **Trigger**: Subscription due or Invoice payment.
2.  **GHL Request**: GHL calls `charge_payment` on `queryUrl` with `paymentMethodId`.
3.  **Maya Action**: Backend calls Maya Payment API using the saved method.
4.  **Response**: Return `chargeSnapshot` to GHL.

### 5. Webhook & Sync Flow

1.  **Maya Webhook**: Maya sends `PAYMENT_SUCCESS` or `SUBSCRIPTION_SUCCESS`.
2.  **App Update**: Backend updates local `transactions` table.
3.  **GHL Forward**: App sends `payment.captured` or `subscription.charged` to GHL Webhook endpoint.

---

## 🏛️ ARCHITECTURE (MVC + Service + Request)

The project follows a structured **MVC + Service Layer** pattern to ensure separation of concerns, scalability, and testability.

### 1. Request Flow

1.  **Request (`Http/Requests`)**: Handles incoming data validation and authorization logic. Ensures controllers only receive clean, valid data.
2.  **Controller (`Http/Controllers`)**: Acts as the orchestrator. It receives the validated request, extracts necessary data, and delegates business logic to the appropriate Service.
3.  **Service (`Services`)**: Contains the core business logic and external API integrations (Maya & GHL). Services often return **DTOs** (Data Transfer Objects) to the controller.
4.  **Model (`Models`)**: Represents the data layer and handles database interactions.
5.  **Response/View**: The controller returns a Blade view (for the iFrame) or a JSON response (for GHL/Maya).

### 2. Key Benefits

-   **Modular**: Maya and GHL logic are isolated in their respective services.
-   **Testable**: Services can be unit-tested independently of the HTTP layer.
-   **Clean Controllers**: Controllers remain "thin" and focused only on request/response handling.

---

## 📂 Folder Structure Map

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── GhlOAuthController.php       — OAuth callback & token management
│   │   ├── ProviderConfigController.php — GHL provider registration
│   │   ├── CheckoutController.php       — Frontend iFrame & session creation
│   │   ├── QueryController.php          — GHL queryUrl/backend handlers
│   │   └── MayaWebhookController.php    — Handling Maya status updates
│   ├── Requests/
│   │   ├── ProviderConfigRequest.php    — Validating GHL keys/tokens
│   │   └── CheckoutRequest.php          — Validating checkout properties
│   └── Middleware/
│       ├── AllowIframeEmbedding.php     — X-Frame-Options management
│       ├── CheckGhlToken.php            — OAuth token validation & refresh
│       └── VerifyMayaSignature.php      — Webhook security (IP-based)
├── Services/
│   ├── CheckoutService.php              — Maya session orchestration
│   ├── GhlQueryService.php              — Handling verify/charge/list
│   ├── GhlService.php                   — GHL API communication (OAuth/Config)
│   ├── MayaService.php                  — Maya API communication (Checkout/Vault)
│   └── WebhookProcessingService.php     — Processing & forwarding payloads
├── DTOs/                                — Strongly typed data objects
└── Models/                              — Transaction & Token models
```
