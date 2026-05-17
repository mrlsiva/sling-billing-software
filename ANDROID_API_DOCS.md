# Sling Billing Software — Android API Integration Guide

## Table of Contents
1. [Project Setup](#1-project-setup)
2. [Base Configuration](#2-base-configuration)
3. [Authentication](#3-authentication)
4. [Standard Response Format](#4-standard-response-format)
5. [Pagination](#5-pagination)
6. [Error Handling](#6-error-handling)
7. [API Reference](#7-api-reference)
   - [Dashboard](#71-dashboard)
   - [Categories](#72-categories)
   - [Sub Categories](#73-sub-categories)
   - [Products](#74-products)
   - [Sizes & Colours](#75-sizes--colours)
   - [Taxes & Metrics](#76-taxes--metrics)
   - [Vendors](#77-vendors)
   - [Purchase Orders](#78-purchase-orders)
   - [Inventory — HO](#79-inventory--ho)
   - [Branch Stock & Transfer](#710-branch-stock--transfer)
   - [Customers](#711-customers)
   - [Branch Billing (POS)](#712-branch-billing-pos)
   - [Branch Orders & Refunds](#713-branch-orders--refunds)
   - [GST Billing](#714-gst-billing)
   - [Credits](#715-credits)
   - [Reports](#716-reports)
   - [Notifications](#717-notifications)
   - [General Dropdowns](#718-general-dropdowns)
8. [User Roles](#8-user-roles)

---

## 1. Project Setup

### Gradle Dependencies
Add to your `build.gradle (app)`:

```gradle
dependencies {
    // Retrofit for HTTP calls
    implementation 'com.squareup.retrofit2:retrofit:2.9.0'
    implementation 'com.squareup.retrofit2:converter-gson:2.9.0'

    // OkHttp for logging/interceptor
    implementation 'com.squareup.okhttp3:okhttp:4.12.0'
    implementation 'com.squareup.okhttp3:logging-interceptor:4.12.0'

    // Coroutines (optional but recommended)
    implementation 'org.jetbrains.kotlinx:kotlinx-coroutines-android:1.7.3'
}
```

---

## 2. Base Configuration

### Base URL
```
http://yourdomain.com/api/
```
> Replace `yourdomain.com` with the actual server domain or IP address.

### Required Headers for Every Request
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```
> The `Authorization` header is **not required** only for the login endpoints.

### Retrofit Setup (Kotlin)

```kotlin
object RetrofitClient {

    private const val BASE_URL = "http://yourdomain.com/api/"
    private var token: String = ""

    private val okHttpClient = OkHttpClient.Builder()
        .addInterceptor { chain ->
            val request = chain.request().newBuilder()
                .addHeader("Accept", "application/json")
                .addHeader("Content-Type", "application/json")
                .apply {
                    if (token.isNotEmpty()) {
                        addHeader("Authorization", "Bearer $token")
                    }
                }
                .build()
            chain.proceed(request)
        }
        .addInterceptor(HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        })
        .build()

    val instance: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }

    fun setToken(newToken: String) {
        token = newToken
    }
}
```

---

## 3. Authentication

### POST `/api/login`
Login with company slug + password. **No auth token required.**

**Request Body:**
```json
{
  "slug_name": "your-company-slug",
  "password": "your-password"
}
```

**Success Response `(200)`:**
```json
{
  "code": 200,
  "message": "Successfully Logged in",
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "slug_name": "your-company-slug",
    "role_id": 2,
    "auth_token": "1|abc123xyz...",
    "branch": [...],
    "user_detail": {
      "address": "...",
      "phone": "..."
    },
    "bank_detail": { ... }
  }
}
```

**Error Response `(400)`:**
```json
{
  "code": 400,
  "message": "Failed to Login",
  "success": false,
  "data": null,
  "errors": ["Invalid Login Credentials"]
}
```

> After login, save `data.auth_token` and `data.role_id`.  
> Call `RetrofitClient.setToken(data.auth_token)` before any subsequent API calls.

---

### POST `/api/logout`
Invalidates all tokens for the user. Requires `Authorization` header.

**Request Body:** _(none)_

**Response `(200)`:**
```json
{
  "code": 200,
  "message": "Logged out from all devices successfully",
  "success": true,
  "data": "Success"
}
```

---

### POST `/api/admin/login`
For super admin only. Uses `user_name` instead of `slug_name`.

**Request Body:**
```json
{
  "user_name": "admin",
  "password": "adminpass"
}
```

---

## 4. Standard Response Format

Every API response follows this structure:

### Success
```json
{
  "code": 200,
  "message": "Success message",
  "success": true,
  "data": { ... }
}
```

### Error / Validation Failed
```json
{
  "code": 400,
  "message": "Error message",
  "success": false,
  "data": null,
  "errors": [
    "Validation error 1",
    "Validation error 2"
  ]
}
```

### Kotlin Data Classes

```kotlin
data class ApiResponse<T>(
    val code: Int,
    val message: String?,
    val success: Boolean,
    val data: T?,
    val errors: List<String>?
)

data class PaginatedData<T>(
    val data: List<T>,
    val current_page: Int,
    val last_page: Int,
    val per_page: Int,
    val total: Int,
    val from: Int?,
    val to: Int?,
    val next_page_url: String?,
    val prev_page_url: String?
)
```

---

## 5. Pagination

All list endpoints that return many records are paginated (**10 items per page** by default).

**Request:** append `?page=2` to any list endpoint.

**Response structure:**
```json
{
  "code": 200,
  "success": true,
  "data": {
    "data": [ ... ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 48,
    "from": 1,
    "to": 10,
    "next_page_url": "http://domain.com/api/endpoint?page=2",
    "prev_page_url": null
  }
}
```

> Check `next_page_url != null` to know if more pages exist.

---

## 6. Error Handling

| HTTP Code | Meaning |
|-----------|---------|
| `200` | Success |
| `400` | Bad Request / Validation Failed |
| `401` | Unauthenticated — missing or expired token |
| `404` | Resource Not Found |
| `422` | Business logic error (e.g. insufficient stock, amount exceeds balance) |
| `500` | Server Error |

### Kotlin Error Handling Pattern

```kotlin
suspend fun <T> safeApiCall(call: suspend () -> Response<ApiResponse<T>>): Result<T> {
    return try {
        val response = call()
        if (response.isSuccessful && response.body()?.success == true) {
            Result.success(response.body()!!.data!!)
        } else if (response.code() == 401) {
            // Clear token, redirect to login
            Result.failure(Exception("Session expired. Please login again."))
        } else {
            val errors = response.body()?.errors?.joinToString(", ")
                ?: response.body()?.message
                ?: "Unknown error"
            Result.failure(Exception(errors))
        }
    } catch (e: IOException) {
        Result.failure(Exception("Network error. Check your connection."))
    } catch (e: Exception) {
        Result.failure(e)
    }
}
```

---

## 7. API Reference

---

### 7.1 Dashboard

#### GET `/api/dashboard`
> **Role:** HO (Head Office)

**Query Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `from_date` | date `YYYY-MM-DD` | Start date filter |
| `to_date` | date `YYYY-MM-DD` | End date filter |

**Response `data`:**
```json
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

---

#### GET `/api/branch/dashboard`
> **Role:** Branch

**Query Parameters:** Same as HO dashboard (`from_date`, `to_date`)

**Response `data`:**
```json
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

### 7.2 Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/categories/list` | List categories (paginated) |
| `POST` | `/api/categories/store` | Create category |
| `GET` | `/api/categories/{id}/view` | View category |
| `GET` | `/api/categories/{id}/status` | Toggle active/inactive |
| `POST` | `/api/categories/update` | Update category |

**GET `/api/categories/list`**
Query: `?category=search_term&page=1`

**POST `/api/categories/store`**
```json
{ "name": "Electronics" }
```

**POST `/api/categories/update`**
```json
{ "category_id": 1, "name": "Updated Name" }
```

---

### 7.3 Sub Categories

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/sub_categories/list` | List sub categories |
| `POST` | `/api/sub_categories/store` | Create sub category |
| `GET` | `/api/sub_categories/{id}/view` | View sub category |
| `GET` | `/api/sub_categories/{id}/status` | Toggle status |
| `POST` | `/api/sub_categories/update` | Update sub category |

**POST `/api/sub_categories/store`**
```json
{ "category_id": 1, "name": "Mobiles" }
```

**POST `/api/sub_categories/update`**
```json
{ "sub_category_id": 1, "category_id": 1, "name": "Smartphones" }
```

---

### 7.4 Products

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/products/list` | List products |
| `POST` | `/api/products/store` | Create product |
| `GET` | `/api/products/{id}/view` | View product |
| `GET` | `/api/products/{id}/status` | Toggle status |
| `POST` | `/api/products/update` | Update product |

**GET `/api/products/list`**
Query: `?product=search_term&page=1`

**POST `/api/products/store`**
```json
{
  "category_id": 1,
  "sub_category_id": 2,
  "name": "iPhone 15",
  "code": "IP15",
  "price": 75000,
  "tax_id": 1,
  "metric_id": 1,
  "hsn_code": "8517"
}
```

---

### 7.5 Sizes & Colours

**Sizes:**

| Method | Endpoint | Body |
|--------|----------|------|
| `GET` | `/api/sizes/list` | Query: `?size=` |
| `POST` | `/api/sizes/store` | `{ "name": "XL" }` |
| `GET` | `/api/sizes/{id}/view` | — |
| `GET` | `/api/sizes/{id}/status` | — |
| `POST` | `/api/sizes/update` | `{ "size_id": 1, "size": "XXL" }` |

**Colours:**

| Method | Endpoint | Body |
|--------|----------|------|
| `GET` | `/api/colours/list` | Query: `?colour=` |
| `POST` | `/api/colours/store` | `{ "name": "Red" }` |
| `GET` | `/api/colours/{id}/view` | — |
| `GET` | `/api/colours/{id}/status` | — |
| `POST` | `/api/colours/update` | `{ "colour_id": 1, "colour": "Dark Red" }` |

---

### 7.6 Taxes & Metrics

**Taxes:**

| Method | Endpoint | Body |
|--------|----------|------|
| `GET` | `/api/taxes/list` | — |
| `POST` | `/api/taxes/store` | `{ "name": "GST 18%", "percent": 18 }` |
| `GET` | `/api/taxes/{id}/view` | — |
| `GET` | `/api/taxes/{id}/status` | — |
| `POST` | `/api/taxes/update` | `{ "tax_id": 1, "name": "GST 12%", "percent": 12 }` |

**Metrics (Units of Measurement):**

| Method | Endpoint | Body |
|--------|----------|------|
| `GET` | `/api/metrics/list` | — |
| `POST` | `/api/metrics/store` | `{ "name": "PCS" }` |
| `GET` | `/api/metrics/{id}/view` | — |
| `GET` | `/api/metrics/{id}/status` | — |
| `POST` | `/api/metrics/update` | `{ "metric_id": 1, "name": "BOX" }` |

---

### 7.7 Vendors

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/vendors/list` | List vendors |
| `POST` | `/api/vendors/store` | Create vendor |
| `GET` | `/api/vendors/{id}/view` | View vendor |
| `GET` | `/api/vendors/{id}/status` | Toggle status |
| `POST` | `/api/vendors/update` | Update vendor |
| `GET` | `/api/vendors/{id}/ledger` | Vendor ledger |
| `GET` | `/api/vendors/{id}/payments` | Vendor payment history |
| `POST` | `/api/vendors/payments/store` | Record vendor payment |

**GET `/api/vendors/list`**
Query: `?vendor=name_or_phone&page=1`

**POST `/api/vendors/store`**
```json
{
  "name": "ABC Traders",
  "phone": "9876543210",
  "email": "abc@mail.com",
  "address": "123, Main St",
  "state": "TN",
  "city": "Chennai",
  "gst": "33ABCDE1234F1Z5"
}
```

**GET `/api/vendors/{id}/ledger`**
Query: `?from_date=2025-01-01&to_date=2025-01-31&search=`

**Response `data`:**
```json
{
  "vendor": { ... },
  "purchase_orders": { "data": [...], "total": 10 },
  "payments": [...],
  "payment_methods": [...],
  "total_gross": 500000,
  "total_paid": 300000,
  "balance": 200000,
  "remaining_opening_balance": 0
}
```

**POST `/api/vendors/payments/store`**
```json
{
  "vendor_id": 1,
  "payment": 1,
  "payment_amount": 50000,
  "comment": "Cheque payment"
}
```
> Payment is auto-allocated: opening balance → unpaid POs → prepaid balance.

---

### 7.8 Purchase Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/purchase_orders` | List purchase orders |
| `GET` | `/api/purchase_orders/create_data` | Form data for create screen |
| `GET` | `/api/purchase_orders/get_categories` | Categories dropdown |
| `GET` | `/api/purchase_orders/get_product` | Products by category |
| `GET` | `/api/purchase_orders/get_product_detail` | Product details |
| `GET` | `/api/purchase_orders/get_stock_variations` | Size/colour variations |
| `GET` | `/api/purchase_orders/get_product_stock` | Current product stock |
| `POST` | `/api/purchase_orders/store` | Create purchase order |
| `GET` | `/api/purchase_orders/{id}/detail` | View PO line items |
| `POST` | `/api/purchase_orders/update` | Update PO amount |
| `POST` | `/api/purchase_orders/refund` | Refund purchase |

**GET `/api/purchase_orders`**
Query: `?vendor=name&page=1`

Returns paginated list with computed status: `paid` / `unpaid` / `partial` / `overdue`.

**GET `/api/purchase_orders/get_product`**
Query: `?category=1&sub_category=2`

**GET `/api/purchase_orders/get_product_detail`**
Query: `?product=1`

**GET `/api/purchase_orders/get_stock_variations`**
Query: `?product_id=1`

**POST `/api/purchase_orders/store`**
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

**POST `/api/purchase_orders/update`**
```json
{
  "purchase_order_id": 1,
  "old_amount": 59000,
  "new_amount": 55000,
  "reason": "Price correction"
}
```

**POST `/api/purchase_orders/refund`**
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

### 7.9 Inventory — HO

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/inventory/stock` | List HO stock |
| `GET` | `/api/inventory/stock/{stock_id}/variations` | Size/colour variations |
| `GET` | `/api/inventory/get_sub_category` | Sub categories |
| `GET` | `/api/inventory/get_product` | Products |
| `GET` | `/api/inventory/get_product_detail` | Product + stock + IMEIs |
| `GET` | `/api/inventory/transfer` | List transfers |
| `GET` | `/api/inventory/transfer/{id}/bill` | Transfer bill details |
| `POST` | `/api/inventory/transfer/store` | Create transfer |

**GET `/api/inventory/stock`**
Query: `?shop=&branch=0&product=search&stock_in=1`
> `branch=0` = HO stock. Pass branch user_id for a specific branch.

**GET `/api/inventory/get_sub_category`**
Query: `?id=1` (category ID)

**GET `/api/inventory/get_product`**
Query: `?category=1&sub_category=2`

**GET `/api/inventory/get_product_detail`**
Query: `?product=1`
Returns: stock quantity, IMEI list, size/colour variations.

**GET `/api/inventory/transfer`**
Query: `?product=search&branch=5&page=1`
Returns paginated list, grouped by invoice.

**POST `/api/inventory/transfer/store`**
```json
{
  "branch": 5,
  "category": 1,
  "sub_category": 2,
  "product": 3,
  "quantity": 5,
  "imeis": ["123456789012345"],
  "variation_qty": {
    "12": 3,
    "13": 2
  }
}
```
> `variation_qty` keys are `StockVariation` IDs, values are quantities.

---

### 7.10 Branch Stock & Transfer

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/branch/stock` | List branch stock |
| `GET` | `/api/branch/stock/{stock_id}/variations` | Variations |
| `GET` | `/api/branch/get_sub_category` | Sub categories |
| `GET` | `/api/branch/get_product` | Products |
| `GET` | `/api/branch/get_product_detail` | Product + stock + IMEIs |
| `GET` | `/api/branch/transfer` | List transfers |
| `GET` | `/api/branch/transfer/{id}/bill` | Transfer bill |
| `POST` | `/api/branch/transfer/store` | Create transfer |

**GET `/api/branch/stock`**
Query: `?product=search&stock_in=1`

**POST `/api/branch/transfer/store`**
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
> `transfer_to`: `1` = other branch, `2` = return to HO

---

### 7.11 Customers

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/customers` | List customers |
| `POST` | `/api/customers/store` | Create customer |
| `GET` | `/api/customers/{id}/view` | View customer |
| `POST` | `/api/customers/update` | Update customer |
| `GET` | `/api/customers/{id}/order` | Customer order history |

**GET `/api/customers`**
Query: `?customer=name_or_phone&page=1`

**POST `/api/customers/store`**
```json
{
  "name": "Ravi Kumar",
  "phone": "9876543210",
  "alt_phone": "",
  "address": "Chennai",
  "pincode": "600001",
  "gender": 1,
  "dob": "1990-01-01",
  "gst": ""
}
```

---

### 7.12 Branch Billing (POS)

> **Role:** Branch  
> This is the core Point-of-Sale flow. Always load initial data first, then populate the cart.

#### Step-by-step POS flow:

1. `GET /api/branch/billing` — load categories, payment modes, staff list
2. `GET /api/branch/billing/get_sub_category?id={category_id}` — load sub categories
3. `GET /api/branch/billing/get_product?category=&sub_category=&product=` — load products
4. `GET /api/branch/billing/get_product_detail?id={product_id}` — get price, tax, stock
5. (Optional) `GET /api/branch/billing/get_variation_detail?id={variation_id}` — size/colour detail
6. (Optional) `GET /api/branch/billing/get_imei_product?product={id}` — IMEI numbers
7. Customer lookup: `GET /api/branch/billing/suggest_phone?phone=98` → autocomplete
8. `GET /api/branch/billing/get_customer_detail?phone=9876543210` — fetch customer
9. (Or) `POST /api/branch/billing/customer_store` — create new customer inline
10. `POST /api/branch/billing/store` — submit the bill

---

**POST `/api/branch/billing/customer_store`**
```json
{
  "name": "Ravi",
  "phone": "9876543210",
  "address": "Chennai",
  "gender": 1
}
```

**POST `/api/branch/billing/store`**
```json
{
  "billed_by": 3,
  "discount": 0,
  "customer": {
    "name": "Ravi",
    "phone": "9876543210",
    "address": "Chennai",
    "gender": 1
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

**Success Response `data`:**
```json
{
  "order_id": 42,
  "bill_id": "BR01"
}
```

**GET `/api/branch/billing/{id}/get_bill`**
Returns full bill: `order` + `order_details` + `order_payment_details`.

---

### 7.13 Branch Orders & Refunds

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/branch/orders` | List branch orders |
| `GET` | `/api/branch/orders/{id}/refund_data` | Get refund form data |
| `POST` | `/api/branch/orders/refund` | Create refund |

**GET `/api/branch/orders`**
Query: `?order=search_term&page=1`

**GET `/api/branch/orders/{id}/refund_data`**
Returns: order, order_details, payment_details, payment methods, staffs list.

**POST `/api/branch/orders/refund`**
```json
{
  "order_id": 42,
  "refunded_by": 5,
  "amount": 5000,
  "reason": "Customer returned",
  "payment": 1,
  "detail": "",
  "orders_details": [15, 16],
  "quantity": {
    "15": 1,
    "16": 2
  },
  "imeis": {
    "15": ["123456789012345"]
  }
}
```

---

### 7.14 GST Billing

#### HO

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/gst_bills` | List GST bills |
| `GET` | `/api/gst_bills/create_data` | Form data |
| `GET` | `/api/gst_bills/get_sub_category` | Sub categories |
| `GET` | `/api/gst_bills/get_product` | Products |
| `POST` | `/api/gst_bills/store` | Create GST bill |
| `GET` | `/api/gst_bills/{id}/view` | View bill |
| `POST` | `/api/gst_bills/bulk_upload` | Bulk upload (.xlsx) |

**GET `/api/gst_bills`**
Query: `?branch=0` (`0` = HO, pass branch ID for a branch)

**POST `/api/gst_bills/store`**
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
  "category": 1,
  "sub_category": 2,
  "product": 3,
  "imie": "123456789012345",
  "item_code": "IP15-001",
  "quantity": 1,
  "gross": 75000
}
```

**POST `/api/gst_bills/bulk_upload`**
Content-Type: `multipart/form-data`
Field: `file` — `.xlsx` file

#### Branch

Same endpoints under `/api/branch/gst_bills/`. The `branch` field in the POST body is not required (uses session user's branch).

---

### 7.15 Credits

#### HO Credits

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/ho/credits` | List credits |
| `GET` | `/api/ho/credits/{id}/payments` | Credit payment history |
| `POST` | `/api/ho/credits/payments/store` | Record credit payment |

**GET `/api/ho/credits`**
Query: `?date=2025-01-01&customer=name_or_phone&page=1`

**POST `/api/ho/credits/payments/store`**
```json
{
  "credit_id": 1,
  "payment_id": 2,
  "amount": 5000
}
```

#### Branch Credits

| Method | Endpoint |
|--------|----------|
| `GET` | `/api/branch/credits?date=&customer=` |
| `GET` | `/api/branch/credits/{id}/payments` |
| `POST` | `/api/branch/credits/payments/store` |

Same request/response shape as HO credits.

---

### 7.16 Reports

#### HO Reports

| Endpoint | Query Parameters |
|----------|-----------------|
| `GET /api/reports/daily` | `?branch=0&date=YYYY-MM-DD` |
| `GET /api/reports/orders` | `?branch=0&from_date=&to_date=&search=` |
| `GET /api/reports/sales` | `?branch=0&from_date=&to_date=` |
| `GET /api/reports/purchase` | `?from_date=&to_date=&vendor=` |
| `GET /api/reports/transfer` | `?branch=0&from_date=&to_date=&product=` |

> `branch=0` returns all HO data. Pass a branch user_id to filter by branch.

**Daily Report response includes:**
- `orders` (with customer, payments, refunds)
- `paymentSummary` (grouped by payment mode)
- `productIn` / `productOut` (transfers)
- `purchases`, `vendor payments`, `purchase refunds`
- `totalSales`, `totalPurchase`, `totalVendorPaid`, `profit`, `credit_amount`

#### Branch Reports

| Endpoint | Query Parameters |
|----------|-----------------|
| `GET /api/branch/reports/daily` | `?date=YYYY-MM-DD` |
| `GET /api/branch/reports/orders` | `?from_date=&to_date=&search=` |
| `GET /api/branch/reports/sales` | `?from_date=&to_date=` |
| `GET /api/branch/reports/transfer` | `?from_date=&to_date=&product=` |

---

### 7.17 Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/notifications` | All notifications |
| `GET` | `/api/notifications/{type}` | Notifications by type |

---

### 7.18 General Dropdowns

These endpoints return simple lists used to populate dropdowns/spinners:

| Endpoint | Returns |
|----------|---------|
| `GET /api/genders` | Gender list |
| `GET /api/payment_list` | Payment modes |
| `GET /api/finances` | Finance options |
| `GET /api/categories` | All categories (no pagination) |
| `GET /api/{category_id}/sub_categories` | Sub categories by category |
| `GET /api/staffs` | Staff list |
| `GET /api/branches` | All branches (HO role only) |

---

## 8. User Roles

After login, check `data.role_id` to determine which screens to show:

| `role_id` | Role | Access |
|-----------|------|--------|
| `2` | HO (Head Office) | Products, vendors, inventory, purchase orders, all reports, all branches |
| `3` | Branch | POS billing, branch stock, branch orders, branch reports, credits |

> **Admin** (`role_id = 1`) logs in via `/api/admin/login` and manages shops/branches only.

### Role-based Navigation Example (Kotlin)

```kotlin
when (user.role_id) {
    2 -> navigateToHoDashboard()
    3 -> navigateToBranchDashboard()
    else -> showError("Unknown role")
}
```

---

## Quick Reference — Retrofit API Interface (Kotlin)

```kotlin
interface ApiService {

    // Auth
    @POST("login")
    suspend fun login(@Body body: LoginRequest): Response<ApiResponse<UserData>>

    @POST("logout")
    suspend fun logout(): Response<ApiResponse<String>>

    // Dashboard
    @GET("dashboard")
    suspend fun getHoDashboard(
        @Query("from_date") fromDate: String?,
        @Query("to_date") toDate: String?
    ): Response<ApiResponse<DashboardData>>

    @GET("branch/dashboard")
    suspend fun getBranchDashboard(
        @Query("from_date") fromDate: String?,
        @Query("to_date") toDate: String?
    ): Response<ApiResponse<BranchDashboardData>>

    // Customers
    @GET("customers")
    suspend fun getCustomers(
        @Query("customer") search: String?,
        @Query("page") page: Int = 1
    ): Response<ApiResponse<PaginatedData<Customer>>>

    @POST("customers/store")
    suspend fun createCustomer(@Body body: CustomerRequest): Response<ApiResponse<Customer>>

    @GET("customers/{id}/view")
    suspend fun getCustomer(@Path("id") id: Int): Response<ApiResponse<Customer>>

    // Branch Billing
    @GET("branch/billing/suggest_phone")
    suspend fun suggestPhone(@Query("phone") phone: String): Response<ApiResponse<List<String>>>

    @GET("branch/billing/get_customer_detail")
    suspend fun getCustomerByPhone(@Query("phone") phone: String): Response<ApiResponse<Customer>>

    @POST("branch/billing/store")
    suspend fun createBill(@Body body: BillRequest): Response<ApiResponse<BillResponse>>

    @GET("branch/billing/{id}/get_bill")
    suspend fun getBill(@Path("id") id: Int): Response<ApiResponse<BillDetail>>

    // Orders
    @GET("branch/orders")
    suspend fun getBranchOrders(
        @Query("order") search: String?,
        @Query("page") page: Int = 1
    ): Response<ApiResponse<PaginatedData<Order>>>

    @POST("branch/orders/refund")
    suspend fun createRefund(@Body body: RefundRequest): Response<ApiResponse<Any>>

    // Reports
    @GET("branch/reports/daily")
    suspend fun getBranchDailyReport(@Query("date") date: String): Response<ApiResponse<DailyReport>>
}
```

---

*Generated for Sling Billing Software — Laravel 10 + Laravel Sanctum backend.*  
*Contact the backend team for any clarification on business logic or field validations.*
