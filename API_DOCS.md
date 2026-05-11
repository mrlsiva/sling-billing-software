# Sling Billing Software — REST API Documentation

## Base URL
```
http://yourdomain.com/api
```

## Authentication
All endpoints (except login) require a **Bearer Token** via Laravel Sanctum.

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## Standard Response Format
```json
{
  "code": 200,
  "message": "Success message",
  "success": true,
  "data": { ... }
}
```
**Error response:**
```json
{
  "code": 400,
  "message": "Error message",
  "success": false,
  "data": null,
  "errors": ["Validation error 1", "..."]
}
```

---

## User Roles
| role_id | Role | Description |
|---------|------|-------------|
| 2 | HO (Head Office) | Full access — manages products, vendors, inventory, reports |
| 3 | Branch | POS billing, branch stock, branch reports |

---

# ─────────────────────────────────────────
# 1. AUTHENTICATION
# ─────────────────────────────────────────

## POST /api/login
```json
Body:
{
  "user_name": "admin",
  "password": "password",
  "slug_name": "company-slug"
}

Response:
{
  "data": {
    "token": "1|abc123...",
    "user": { "id": 1, "name": "...", "role_id": 2 }
  }
}
```

## POST /api/logout
> Requires Auth

Invalidates the current token. No body required.

---

# ─────────────────────────────────────────
# 2. PROFILE
# ─────────────────────────────────────────

## GET /api/profile
Returns the logged-in user's full profile with user_detail and bank_detail.

---

# ─────────────────────────────────────────
# 3. DASHBOARD
# ─────────────────────────────────────────

## GET /api/dashboard
> Role: HO

Query params: `from_date` (date), `to_date` (date)

Returns per-branch order counts, sales totals, date-range totals.

```json
Response data:
{
  "auth": { "id": 1, "name": "...", "user_detail": {}, "bank_detail": {} },
  "branches": [
    {
      "id": 5,
      "name": "Branch A",
      "date_orders": 12,
      "date_sales": 45000,
      "total_orders": 300,
      "total_sales": 1200000
    }
  ],
  "date_orders": 12,
  "date_sales": 45000,
  "total_orders": 300,
  "total_sales": 1200000,
  "from_date": "2025-01-01",
  "to_date": "2025-01-01"
}
```

## GET /api/branch/dashboard
> Role: Branch

Query params: `from_date`, `to_date`

```json
Response data:
{
  "auth": { ... },
  "date_orders": 5,
  "date_order_amount": 15000,
  "total_orders": 120,
  "total_order_amount": 360000,
  "from_date": "2025-01-01",
  "to_date": "2025-01-01"
}
```

---

# ─────────────────────────────────────────
# 4. CATEGORIES
# ─────────────────────────────────────────

## GET /api/categories/list
Query: `?category=name_search`  Paginated (10/page)

## POST /api/categories/store
```json
{ "name": "Electronics" }
```

## GET /api/categories/{id}/view
## GET /api/categories/{id}/status  — Toggle active/inactive
## POST /api/categories/update
```json
{ "category_id": 1, "name": "Updated Name" }
```

---

# ─────────────────────────────────────────
# 5. SUB CATEGORIES
# ─────────────────────────────────────────

## GET /api/sub_categories/list
Query: `?sub_category=`

## POST /api/sub_categories/store
```json
{ "category_id": 1, "name": "Mobiles" }
```

## GET /api/sub_categories/{id}/view
## GET /api/sub_categories/{id}/status
## POST /api/sub_categories/update
```json
{ "sub_category_id": 1, "category_id": 1, "name": "Updated" }
```

---

# ─────────────────────────────────────────
# 6. PRODUCTS
# ─────────────────────────────────────────

## GET /api/products/list
Query: `?product=`

## POST /api/products/store
```json
{
  "category_id": 1, "sub_category_id": 2,
  "name": "iPhone 15", "code": "IP15",
  "price": 75000, "tax_id": 1,
  "metric_id": 1, "hsn_code": "8517"
}
```

## GET /api/products/{id}/view
## GET /api/products/{id}/status
## POST /api/products/update

---

# ─────────────────────────────────────────
# 7. SIZES
# ─────────────────────────────────────────

## GET /api/sizes/list          — Query: `?size=`
## POST /api/sizes/store        — Body: `{ "name": "XL" }`
## GET /api/sizes/{id}/view
## GET /api/sizes/{id}/status
## POST /api/sizes/update       — Body: `{ "size_id": 1, "size": "XXL" }`

---

# ─────────────────────────────────────────
# 8. COLOURS
# ─────────────────────────────────────────

## GET /api/colours/list        — Query: `?colour=`
## POST /api/colours/store      — Body: `{ "name": "Red" }`
## GET /api/colours/{id}/view
## GET /api/colours/{id}/status
## POST /api/colours/update     — Body: `{ "colour_id": 1, "colour": "Dark Red" }`

---

# ─────────────────────────────────────────
# 9. TAXES
# ─────────────────────────────────────────

## GET /api/taxes/list
## POST /api/taxes/store        — Body: `{ "name": "GST 18%", "percent": 18 }`
## GET /api/taxes/{id}/view
## GET /api/taxes/{id}/status
## POST /api/taxes/update

---

# ─────────────────────────────────────────
# 10. METRICS (Units)
# ─────────────────────────────────────────

## GET /api/metrics/list
## POST /api/metrics/store      — Body: `{ "name": "PCS" }`
## GET /api/metrics/{id}/view
## GET /api/metrics/{id}/status
## POST /api/metrics/update

---

# ─────────────────────────────────────────
# 11. FINANCES
# ─────────────────────────────────────────

## GET /api/finances/list
## POST /api/finances/store
## GET /api/finances/{id}/view
## GET /api/finances/{id}/status
## POST /api/finances/update

---

# ─────────────────────────────────────────
# 12. PAYMENTS (Settings)
# ─────────────────────────────────────────

## GET /api/payments/list
## GET /api/payments/{id}/update  — Toggle payment mode status

---

# ─────────────────────────────────────────
# 13. STAFFS
# ─────────────────────────────────────────

## GET /api/staffs/list
## POST /api/staffs/store
## GET /api/staffs/{id}/view
## GET /api/staffs/{id}/status
## POST /api/staffs/update

---

# ─────────────────────────────────────────
# 14. VENDORS
# ─────────────────────────────────────────

## GET /api/vendors/list
Query: `?vendor=name_or_phone`

## POST /api/vendors/store
```json
{
  "name": "ABC Traders", "phone": "9876543210",
  "email": "abc@mail.com", "address": "...",
  "state": "TN", "city": "Chennai", "gst": "33ABCDE1234F1Z5"
}
```

## GET /api/vendors/{id}/view
## GET /api/vendors/{id}/status
## POST /api/vendors/update

---

# ─────────────────────────────────────────
# 15. VENDOR LEDGER & PAYMENTS
# ─────────────────────────────────────────

## GET /api/vendors/{id}/ledger
Query: `?from_date=&to_date=&search=`

```json
Response data:
{
  "vendor": { ... },
  "purchase_orders": { "data": [...], "total": 10, ... },
  "payments": [...],
  "payment_methods": [...],
  "total_gross": 500000,
  "total_paid": 300000,
  "balance": 200000,
  "remaining_opening_balance": 0
}
```

## GET /api/vendors/{id}/payments
Returns all payment history for a vendor with payment mode details.

## POST /api/vendors/payments/store
```json
{
  "vendor_id": 1,
  "payment": 1,
  "payment_amount": 50000,
  "comment": "Cheque payment"
}
```
> Auto-allocates payment to opening balance → unpaid POs → prepaid balance.

---

# ─────────────────────────────────────────
# 16. PURCHASE ORDERS
# ─────────────────────────────────────────

## GET /api/purchase_orders
Query: `?vendor=name`  — Paginated with computed status (paid/unpaid/partial/overdue)

## GET /api/purchase_orders/create_data
Returns vendors, payments, categories, taxes needed for the create form.

## GET /api/purchase_orders/get_categories
## GET /api/purchase_orders/get_product?category=1&sub_category=2
## GET /api/purchase_orders/get_product_detail?product=1
## GET /api/purchase_orders/get_stock_variations?product_id=1
## GET /api/purchase_orders/get_product_stock?product=1

## POST /api/purchase_orders/store
```json
{
  "vendor": 1,
  "payment": 1,
  "invoice": "INV-001",
  "invoice_date": "2025-01-01",
  "due_date": "2025-02-01",
  "products": [
    {
      "category": 1,
      "sub_category": 2,
      "product": 3,
      "unit": 1,
      "quantity": 10,
      "price_per_unit": 5000,
      "tax": 18,
      "discount": 0,
      "net_cost": 50000,
      "gross_cost": 59000,
      "imei": ["123456789012345"],
      "variation": [
        { "stock_id": 1, "size_id": 2, "colour_id": 3, "qty": 5 }
      ]
    }
  ]
}
```

## GET /api/purchase_orders/{id}/detail
View all line items for a purchase order by invoice number.

## POST /api/purchase_orders/update
```json
{
  "purchase_order_id": 1,
  "old_amount": 59000,
  "new_amount": 55000,
  "reason": "Price correction"
}
```

## POST /api/purchase_orders/refund
```json
{
  "purchase_id": 1,
  "vendor": 1,
  "purchase_amount": 59000,
  "refund_quantity": 2,
  "refund_amount": 11800,
  "comment": "Damaged goods"
}
```

---

# ─────────────────────────────────────────
# 17. INVENTORY — HO Stock & Transfer
# ─────────────────────────────────────────

## GET /api/inventory/stock
Query: `?shop=&branch=0&product=&stock_in=1`
> branch=0 means HO stock. Use branch user_id for branch stock.

## GET /api/inventory/stock/{stock_id}/variations
Returns size/colour variations for a stock row.

## GET /api/inventory/get_sub_category?id=1
## GET /api/inventory/get_product?category=1&sub_category=2
## GET /api/inventory/get_product_detail?product=1
Returns stock quantity, IMEI list, variations.

## GET /api/inventory/transfer
Query: `?product=&branch=`  — Paginated grouped by invoice.

## GET /api/inventory/transfer/{id}/bill
Returns transfer header + all products in that invoice.

## POST /api/inventory/transfer/store
```json
{
  "branch": 5,
  "category": 1,
  "sub_category": 2,
  "product": 3,
  "quantity": 5,
  "imeis": ["123456789012345"],
  "variation_qty": { "12": 3, "13": 2 }
}
```
> variation_qty keys are StockVariation IDs, values are quantities.

---

# ─────────────────────────────────────────
# 18. BRANCH STOCK & TRANSFER
# ─────────────────────────────────────────

## GET /api/branch/stock
Query: `?product=&stock_in=1`

## GET /api/branch/stock/{stock_id}/variations

## GET /api/branch/get_sub_category?id=1
## GET /api/branch/get_product?category=1&sub_category=2
## GET /api/branch/get_product_detail?product=1

## GET /api/branch/transfer
Query: `?product=&branch=`

## GET /api/branch/transfer/{id}/bill

## POST /api/branch/transfer/store
```json
{
  "category": 1,
  "sub_category": 2,
  "product": 3,
  "quantity": 5,
  "transfer_to": 2,
  "imeis": [],
  "variation_qty": {}
}
```
> transfer_to: `1` = other branch, `2` = HO

---

# ─────────────────────────────────────────
# 19. CUSTOMERS
# ─────────────────────────────────────────

## GET /api/customers
Query: `?customer=name_or_phone`

## POST /api/customers/store
```json
{
  "name": "Ravi Kumar", "phone": "9876543210",
  "alt_phone": "", "address": "Chennai",
  "pincode": "600001", "gender": 1,
  "dob": "1990-01-01", "gst": ""
}
```

## GET /api/customers/{id}/view
## POST /api/customers/update
## GET /api/customers/{id}/order  — Customer's order history

---

# ─────────────────────────────────────────
# 20. ORDERS (HO View)
# ─────────────────────────────────────────

## GET /api/orders
Query: `?branch=0&order=search_term`

## GET /api/orders/{id}/view
```json
Response data:
{
  "order": { "id": 1, "bill_id": "B001", "bill_amount": 5000, ... },
  "order_details": [...],
  "order_payment_details": [...]
}
```

---

# ─────────────────────────────────────────
# 21. BRANCH BILLING (POS)
# ─────────────────────────────────────────

## GET /api/branch/billing
Query: `?category=&sub_category=&product=&filter=1`
Returns stocks, categories, genders, payments, finances, staffs.

## GET /api/branch/billing/get_sub_category?id=1
## GET /api/branch/billing/get_product?category=&sub_category=&product=&filter=1
## GET /api/branch/billing/get_product_detail?id=1
## GET /api/branch/billing/get_variation_detail?id=1
## GET /api/branch/billing/get_imei_product?product=1
## GET /api/branch/billing/suggest_phone?phone=98
## GET /api/branch/billing/get_customer_detail?phone=9876543210

## POST /api/branch/billing/customer_store
```json
{
  "name": "Ravi", "phone": "9876543210",
  "address": "Chennai", "gender": 1
}
```

## POST /api/branch/billing/store
```json
{
  "billed_by": 3,
  "discount": 0,
  "customer": {
    "name": "Ravi", "phone": "9876543210",
    "address": "Chennai", "gender": 1
  },
  "cart": [
    {
      "product_id": 5,
      "qty": 2,
      "price": 5000,
      "tax_amount": 900,
      "variation_id": 12,
      "imeis": ["123456789012345"]
    }
  ],
  "payments": [
    {
      "method": "Cash",
      "amount": 10900,
      "extra": {}
    }
  ]
}
```

```json
Response:
{ "order_id": 42, "bill_id": "BR01" }
```

## GET /api/branch/billing/{id}/get_bill
Returns order + order_details + order_payment_details.

---

# ─────────────────────────────────────────
# 22. BRANCH ORDERS & REFUNDS
# ─────────────────────────────────────────

## GET /api/branch/orders
Query: `?order=search_term`

## GET /api/branch/orders/{id}/refund_data
Returns order, order_details, payment_details, payment methods, staffs.

## POST /api/branch/orders/refund
```json
{
  "order_id": 42,
  "refunded_by": 5,
  "amount": 5000,
  "reason": "Customer returned",
  "payment": 1,
  "detail": "",
  "orders_details": [15, 16],
  "quantity": { "15": 1, "16": 2 },
  "imeis": { "15": ["123456789012345"] }
}
```

---

# ─────────────────────────────────────────
# 23. GST BILLING — HO
# ─────────────────────────────────────────

## GET /api/gst_bills?branch=0
## GET /api/gst_bills/create_data
## GET /api/gst_bills/get_sub_category?id=1
## GET /api/gst_bills/get_product?category=1&sub_category=2

## POST /api/gst_bills/store
```json
{
  "branch": 5,
  "order_id": "ORD-001",
  "reference_no": "REF-001",
  "date_time": "2025-01-01",
  "issued_by": "Manager",
  "sold_by": "Staff A",
  "customer_name": "Ravi",
  "customer_phone": "9876543210",
  "customer_address": "Chennai",
  "category": 1, "sub_category": 2, "product": 3,
  "imie": "123456789012345",
  "item_code": "IP15-001",
  "quantity": 1,
  "gross": 75000
}
```

## GET /api/gst_bills/{id}/view
## POST /api/gst_bills/bulk_upload  — multipart/form-data, field: `file` (.xlsx)

---

# ─────────────────────────────────────────
# 24. GST BILLING — Branch
# ─────────────────────────────────────────

## GET /api/branch/gst_bills
## GET /api/branch/gst_bills/create_data
## GET /api/branch/gst_bills/get_sub_category?id=1
## GET /api/branch/gst_bills/get_product?category=1&sub_category=2
## POST /api/branch/gst_bills/store  (same body as HO, no branch field needed)
## GET /api/branch/gst_bills/{id}/view
## POST /api/branch/gst_bills/bulk_upload

---

# ─────────────────────────────────────────
# 25. CREDITS — HO
# ─────────────────────────────────────────

## GET /api/ho/credits?date=2025-01-01&customer=name_or_phone
Returns paginated credits with payment history and order/customer details.

## GET /api/ho/credits/{id}/payments
Returns payment history for a credit entry.

## POST /api/ho/credits/payments/store
```json
{
  "credit_id": 1,
  "payment_id": 2,
  "amount": 5000
}
```

---

# ─────────────────────────────────────────
# 26. CREDITS — Branch
# ─────────────────────────────────────────

## GET /api/branch/credits?date=2025-01-01&customer=
## GET /api/branch/credits/{id}/payments
## POST /api/branch/credits/payments/store

---

# ─────────────────────────────────────────
# 27. BILL SETTINGS
# ─────────────────────────────────────────

## GET /api/bills/{branch}/list  — branch = 0 for HO
## POST /api/bills/store

---

# ─────────────────────────────────────────
# 28. NOTIFICATIONS
# ─────────────────────────────────────────

## GET /api/notifications
## GET /api/notifications/{type}

---

# ─────────────────────────────────────────
# 29. REPORTS — HO
# ─────────────────────────────────────────

## GET /api/reports/daily
Query: `?branch=0&date=2025-01-01`
> branch=0 = all HO data. Pass branch user_id for specific branch.

Returns:
- orders (with customer, payments, refunds)
- paymentSummary (grouped by payment mode)
- productIn / productOut (transfers)
- purchases, vendor payments, purchase refunds (HO only, branch=0)
- totalSales, totalPurchase, totalVendorPaid, profit, credit_amount

## GET /api/reports/orders
Query: `?branch=0&from_date=&to_date=&search=`

## GET /api/reports/sales
Query: `?branch=0&from_date=&to_date=`

## GET /api/reports/purchase
Query: `?from_date=&to_date=&vendor=`

## GET /api/reports/transfer
Query: `?branch=0&from_date=&to_date=&product=`

---

# ─────────────────────────────────────────
# 30. REPORTS — Branch
# ─────────────────────────────────────────

## GET /api/branch/reports/daily?date=2025-01-01
## GET /api/branch/reports/orders?from_date=&to_date=&search=
## GET /api/branch/reports/sales?from_date=&to_date=
## GET /api/branch/reports/transfer?from_date=&to_date=&product=

---

# ─────────────────────────────────────────
# 31. GENERAL DROPDOWNS
# ─────────────────────────────────────────

## GET /api/genders
## GET /api/payment_list
## GET /api/finances
## GET /api/categories
## GET /api/{category}/sub_categories
## GET /api/staffs
## GET /api/branches   — HO only: list of all branches

---

# ─────────────────────────────────────────
# PAGINATION FORMAT
# ─────────────────────────────────────────

All paginated responses follow Laravel's standard format:
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 10,
  "total": 48,
  "from": 1,
  "to": 10,
  "next_page_url": "http://domain.com/api/endpoint?page=2",
  "prev_page_url": null
}
```

---

# ─────────────────────────────────────────
# ERROR CODES
# ─────────────────────────────────────────

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Bad Request / Validation Failed |
| 401 | Unauthenticated (invalid/missing token) |
| 404 | Resource Not Found |
| 422 | Unprocessable — business logic error (e.g. stock=0, amount exceeds balance) |
| 500 | Server Error |