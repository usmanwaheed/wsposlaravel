# REST API Reference

All endpoints are prefixed with `/api` and protected with Laravel Sanctum. Include the `Authorization: Bearer {token}` header when consuming the API from external systems.

## Authentication

Use the API login endpoint to obtain a bearer token for subsequent requests.

### Obtain token
`POST /api/login`

Payload:
```json
{
  "email": "admin@garment-pos.test",
  "password": "password",
  "device_name": "pos-terminal-1"
}
```

The response contains a `token` and `token_type`. Include `Authorization: Bearer {token}` on protected API calls.

### Revoke current token
`POST /api/logout`

Requires the bearer token in the request headers and revokes the current access token.

## Products

### List products
`GET /api/products`

Query parameters:
- `search` – fuzzy search by name or SKU.
- `page` – page number (defaults to `1`).
- `per_page` – pagination size (defaults to `25`).

Returns a paginated response with product and variation information.

### Create product
`POST /api/products`

Payload:
```json
{
  "name": "Classic Cotton Shirt",
  "category": "Shirts",
  "brand": "GarmentCo",
  "description": "Optional description",
  "base_price": 25,
  "tax_rate": 8,
  "variations": [
    {
      "color": "Red",
      "size": "M",
      "sku": "SHIRT-RED-M",
      "barcode": "123456789012",
      "price": 25,
      "stock": 10
    }
  ]
}
```

Creates the product and its variations in a single transaction and returns the saved resource.

### Update product
`PUT /api/products/{product}`

Accepts the same payload as creation. Existing variations are replaced with the provided array.

### Delete product
`DELETE /api/products/{product}`

Deletes the product and associated variations.

### Sync single product to WooCommerce
`POST /api/products/{product}/sync`

Pushes the specified product to the configured WooCommerce site and returns the remote response.

## Inventory

### Bulk stock update
`POST /api/inventory/update`

Payload:
```json
{
  "updates": [
    { "product_variation_id": 1, "stock": 12 },
    { "product_variation_id": 2, "delta": -1 }
  ]
}
```

Supports absolute stock values (`stock`) or relative adjustments (`delta`).

## Orders

### List orders
`GET /api/orders`

Supports `status`, `from`, `to`, and pagination parameters. Returns orders with items, customer, and payment information.

### Create order
`POST /api/orders`

Payload:
```json
{
  "customer": { "email": "jane@example.com" },
  "items": [
    { "product_variation_id": 12, "quantity": 2, "discount": 0 }
  ],
  "payment": { "method": "cash", "amount": 54.0 },
  "sync_to_woocommerce": true,
  "notes": "Optional note"
}
```

Creates the order, reduces inventory, records payment, and optionally pushes the sale to WooCommerce.

### Import WooCommerce orders
`POST /api/orders/import`

Pulls recent WooCommerce orders using the configured credentials and stores them locally. Returns an array of imported order IDs.

## Reports

### Daily sales summary
`GET /api/reports/sales/daily`

Returns totals for the current day: sales, discounts, tax, and order count.

### Monthly sales trend
`GET /api/reports/sales/monthly`

Returns total sales grouped by day for the current month.

### Product performance
`GET /api/reports/products`

Returns top-selling SKUs with quantities and revenue totals.

### Category performance
`GET /api/reports/categories`

Aggregates revenue by product category.

### Low stock
`GET /api/reports/low-stock`

Returns variations with fewer than five units in stock.

### Payment summary
`GET /api/reports/payments`

Aggregates payments by method (cash, card, credit).

## WooCommerce Sync

### Bulk sync products
`POST /api/woocommerce/sync/products`

Fetches WooCommerce products and merges them into the POS database.

### Bulk sync inventory
`POST /api/woocommerce/sync/inventory`

Pushes current POS stock levels to WooCommerce.

### Bulk sync orders
`POST /api/woocommerce/sync/orders`

Imports completed/processing WooCommerce orders into the POS system.

---

For additional integration requirements (e.g., scheduled syncs or webhook processing), extend the `App\Services\WooCommerceService` class or add dedicated controllers/jobs.
