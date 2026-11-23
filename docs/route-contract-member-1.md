# Route contract for orders, returns, reviews, admin, and chat

This handoff aligns the assigned feature routes with the storefront/admin shell and existing route names from Member 1. It defines the URL/name/action interface only. The order feature owner should provide controller actions and views; the shared route owner should add the declarations once to `routes/web.php`. Do not add competing route declarations while that file is being integrated.

## Member 1 layout handoff

### Layouts and navigation

- Customer pages extend `layouts.storefront`. Main content is yielded inside `#store-main-content`; the layout supplies storefront header/footer, role banner, cart drawer, login/register modals, flash messages, and toast container.
- Admin pages extend `layouts.admin`. Main content is yielded inside `#admin-content`; the shell provides the sidebar, topbar, flash messages, and toast container.
- The account sidebar styling already exists in `resources/css/storefront.css`: `.account-layout`, `.account-sidebar`, `.account-user-card`, `.account-nav-list`, `.account-nav-item`. The customer account link is `orders.index` and the admin sidebar links to `admin.orders.index` and `admin.users.index`.
- Existing navigation IDs include `store-lookup-order-link`, `store-main-header`, `store-main-content`, `admin-sidebar`, `admin-sidebar-menu`, `admin-sidebar-footer`, `admin-nav-orders`, and `admin-nav-users`.
- Order list styling is present in `resources/css/storefront.css` through `.order-tabs-nav`, `.order-tab-btn`, `.order-card`, `.order-card-header`, `.order-meta-info`, `.order-items-body`, `.order-item-row`, `.order-card-footer`, and `.order-actions-wrap`.

### Tokens and shared components

`resources/css/app.css` imports Bootstrap 5.3, Font Awesome 6.4, then the design tokens, base styles, storefront, admin, and chatbot styles. Preserve that order and reuse the existing tokens:

- Walnut: `--primary: #1c1917`, `--primary-light: #2d2621`, `--primary-dark: #12100e`.
- Champagne: `--accent: #c89b53`, `--accent-hover: #b3843b`, `--accent-light: #fbf5e8`.
- Surfaces/text/status: `--bg-canvas`, `--bg-surface`, `--bg-subtle`, `--text-primary`, `--text-secondary`, `--text-muted`, `--status-success`, `--status-warning`, `--status-danger`, `--status-info` and matching `*-bg` variables.
- Fonts: `--font-heading` (Outfit with Playfair Display fallback), `--font-serif` (Playfair Display), `--font-body` (Plus Jakarta Sans).
- Shape/elevation/effects: `--radius-xs/sm/md/lg/full`, `--shadow-sm/md/lg/glass`, `--transition-fast/normal/slow`.

Reusable Blade components include `<x-button>`, `<x-badge>`, `<x-flash-message>`, `<x-toast>`, `<x-modal>`, `<x-form.input>`, `<x-form.select>`, and `<x-form.textarea>`. Continue to use Laravel forms with `@csrf`, server validation, and flashed success/error states. Keep feature root IDs within the agreed prefixes: `orders-`, `tracking-`, `cancel-`, `return-`, `review-`, `chat-`, `admin-order-`, and `admin-user-`.

## Existing shared route names to preserve

Current `routes/web.php` already declares:

| Method | URL | Route name | Current role |
| --- | --- | --- | --- |
| GET | `/orders` | `orders.index` | Authenticated customer account placeholder |
| GET | `/orders-lookup` | `orders.lookup` | Guest order lookup placeholder |
| GET | `/admin/orders` | `admin.orders.index` | Admin orders placeholder |
| GET | `/admin/users` | `admin.users.index` | Admin users placeholder |
| GET | `/product/{slug}` | `product.detail` | Product detail URL used by chatbot cards |

Keep these names and parent URLs. The current admin route group does not yet enforce authentication/admin-role access; add the agreed middleware/policy when the admin feature routes are integrated. Do not change common route definitions from this feature branch while the route owner is editing them.

## Feature route contract

New actions should follow the current English route path style and preserve existing list names. Customer routes require `auth` (guest order lookup remains a separate agreed feature). Admin routes require `auth` and database-backed admin authorization.

| Method | URL | Route name | Controller action | Purpose |
| --- | --- | --- | --- | --- |
| GET | `/orders/{order}` | `orders.show` | `OrderController@show` | Customer order detail, item price snapshots, address, payment, tracking, status history |
| POST | `/orders/{order}/cancel` | `orders.cancel` | `OrderController@cancel` | Required non-empty customer reason; delegate to `CancellationService` |
| GET | `/orders/{order}/returns` | `returns.index` | `ReturnRequestController@index` | List return requests belonging to the order and customer |
| POST | `/orders/{order}/returns` | `returns.store` | `ReturnRequestController@store` | Create a return/exchange/refund request; multiple requests per order allowed |
| GET | `/returns/{returnRequest}` | `returns.show` | `ReturnRequestController@show` | Request status, items, evidence, admin response, refund result |
| POST | `/orders/{order}/items/{orderItem}/reviews` | `reviews.store` | `ReviewController@store` | Review only after validating customer/order/item/product ownership and delivered state |
| GET | `/admin/orders/{order}` | `admin.orders.show` | `Admin\OrderController@show` | Admin order detail and history |
| PATCH | `/admin/orders/{order}/status` | `admin.orders.status` | `Admin\OrderController@updateStatus` | Validate allowed transition; record admin actor in status history |
| PATCH | `/admin/orders/{order}/tracking` | `admin.orders.tracking` | `Admin\OrderController@updateTracking` | Update tracking/carrier fields supported by existing schema |
| POST | `/admin/orders/{order}/cancel` | `admin.orders.cancel` | `Admin\OrderController@cancel` | Required non-empty reason; delegate to `CancellationService` |
| PATCH | `/admin/users/{user}` | `admin.users.update` | `Admin\UserController@update` | Update only database-backed role/status with authorization checks |
| POST | `/chat/webhook` | `chat.webhook` | `ChatController@webhook` | Receive a chat turn and return JSON with optional real product cards/questions |

`orders.index`, `admin.orders.index`, and `admin.users.index` remain the existing list route names. List actions should be moved from placeholder closures to controllers by the shared route owner when the corresponding controllers are ready; do not introduce alternate list route names.

## Chat webhook response

`POST /chat/webhook` returns this JSON structure. `products` and `suggestedQuestions` are optional. Generate every product URL server-side with the existing `product.detail` route, and return a relative same-site URL. The widget persists product cards alongside chat messages.

```json
{
  "response": "Mình tìm được một số sản phẩm phù hợp với phòng khách của bạn.",
  "products": [
    {
      "id": 12,
      "name": "Sofa gỗ sồi",
      "imageUrl": "/storage/products/sofa.jpg",
      "price": 12500000,
      "compareAtPrice": 14900000,
      "slug": "sofa-go-soi",
      "url": "/product/sofa-go-soi"
    }
  ],
  "suggestedQuestions": ["Có màu khác không?", "Kích thước sản phẩm là bao nhiêu?"]
}
```

## Service and database contracts

- Customer cancellation validates a required reason and calls `App\Services\CancellationService::cancel(Order $order, string $reason, User $actor, string $actorType)`. The service owns eligibility, status history, payment handling, and stock effects; the controller does not update stock. System timeout continues to use the service reason `Quá thời gian thanh toán`. The service is not present in this checkout yet, so the action returns a user-facing integration message until Member 2's service is available.
- Orders use existing `orders`, `order_items`, `payments`, and `order_status_histories`. Display only `preparing`, `awaiting_shipment`, `shipping`, `delivered`, and `cancelled` as the agreed labels. Never advance a cancelled order or invent a status.
- Returns/reviews use existing `return_requests`, `return_request_items`, and `reviews`. Keep multiple requests per order; do not add a unique constraint on `return_requests.order_id`. No stock movement occurs on request submission or approval. Only the agreed received/inspected return flow may trigger inventory work.
- Review validation requires the same authenticated user, delivered order, product, and order item to be linked.
- Chat reads real product records through `ChatController`/`ChatbotService`; no echo mock server or invented facts. When no AI provider is configured, use database-backed catalogue matching and do not imply an AI connection. Persist chat messages in `chat_conversations`/`chat_messages`; coordinate any new metadata migration with Member 2 and do not edit migrations that have run.
- Payment countdown uses the stored `payment_expires_at`; the UI never cancels expired payments itself.
- The current checked-in migration does not define `payment_expires_at` on `orders` or `payments`. The order detail view renders the stored timestamp if either model exposes it; it does not invent an expiry value. The owner of the payment schema needs to confirm/add that field through a coordinated new migration before an expiry time can appear from current database data.

## Handoff status

The shared storefront/admin layouts, design tokens, sidebar, common Blade components, and baseline route names are present in the project. This document records the feature route contract for the shared route owner. The customer order list/detail/cancel routes now use the agreed existing `orders.*` names; return/review/admin/chat additions remain for their assigned stages.
