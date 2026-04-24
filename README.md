# Losode Vendor API

A RESTful API built with **Laravel + SQLite** for a multi-vendor product and inventory management system. Vendors can register, manage their products, and track orders. The application is fully containerized for consistent local development.

---

## Tech Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13 |
| Authentication | Laravel Sanctum (API tokens) |
| Database | SQLite |
| Cache | Redis |
| Container | Docker + Docker Compose |
| Tests | PHPUnit |

---

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop) installed and running

---

## Local Setup

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/losode-vendor-api.git
cd losode-vendor-api
```

### 2. Environment File

Copy the example environment file:

```bash
cp .env.example .env
```

Ensure the following is set in your `.env`:

```env
APP_NAME=LosodeVendorAPI
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
```

### 3. Start the Container

Build the Docker image and start the container as a background daemon:

```bash
docker compose up -d --build
```

### 4. Generate App Key

```bash
docker compose exec app php artisan key:generate
```

### 5. Run Migrations

The `database.sqlite` file will be created automatically inside the container if it does not already exist:

```bash
docker compose exec app php artisan migrate
```

To also seed the database with sample vendors and products:

```bash
docker compose exec app php artisan migrate --seed
```

This creates two ready-to-use vendor accounts:

| Name | Email | Password |
|---|---|---|
| Adunni Styles | adunni@losode.test | password |
| Kola Crafts | kola@losode.test | password |

### 6. Access the Application

The application is served from inside the Docker container via `php artisan serve` and mapped to port **8000**:

```
http://localhost:8000
```

All API endpoints are prefixed with `/api/v1`.

---

## Stopping the Application

```bash
docker compose down
```

---

## Modifying Dependencies

The container uses PHP 8.3 CLI with SQLite support. Install new Composer packages through the running container to avoid needing PHP installed locally:

```bash
docker compose exec app composer require [package-name]
```

---

## API Documentation

All responses follow a consistent JSON envelope:

```json
{
  "success": true,
  "message": "Human-readable description",
  "data": { }
}
```

Error responses:

```json
{
  "success": false,
  "message": "What went wrong",
  "errors": {
    "field": ["Validation detail"]
  }
}
```

**Base URL:** `http://localhost:8000/api/v1`

Protected routes require a Bearer token in the `Authorization` header:

```
Authorization: Bearer {your_token_here}
```

---

### Authentication

#### Register a Vendor

```
POST /api/v1/register
```

**Body:**
```json
{
  "name": "Adunni Styles",
  "email": "adunni@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response `201`:**
```json
{
  "success": true,
  "message": "Registration successful.",
  "data": {
    "vendor": { "id": 1, "name": "Adunni Styles", "email": "adunni@example.com" },
    "token": "1|abc123..."
  }
}
```

---

#### Login

```
POST /api/v1/login
```

**Body:**
```json
{
  "email": "adunni@example.com",
  "password": "password123"
}
```

**Response `200`:**
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "vendor": { "id": 1, "name": "Adunni Styles", "email": "adunni@example.com" },
    "token": "1|abc123..."
  }
}
```

> Copy the `token` value. You will pass it as a Bearer token on all protected routes.

---

#### Logout 🔒

```
POST /api/v1/logout
```

Revokes the current token. No body required.

---

#### Get Authenticated Vendor 🔒

```
GET /api/v1/me
```

Returns the profile of the currently authenticated vendor.

---

### Public Products

#### List All Active Products

```
GET /api/v1/products
```

**Query Parameters:**

| Parameter | Type | Description |
|---|---|---|
| `search` | string | Filter products by name |
| `per_page` | integer | Results per page (default: 15) |

**Example:**
```
GET /api/v1/products?search=ankara&per_page=10
```

**Response `200`:**
```json
{
  "success": true,
  "message": "Products retrieved.",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Ankara Fabric",
        "description": "Premium hand-printed ankara",
        "price": "3500.00",
        "stock_quantity": 45,
        "status": "active",
        "vendor": { "id": 1, "name": "Adunni Styles" }
      }
    ],
    "total": 1,
    "per_page": 15,
    "last_page": 1
  }
}
```

---

#### View a Single Product

```
GET /api/v1/products/{id}
```

Returns a single active product. Returns `404` if the product does not exist or is inactive.

---

### Vendor Product Management 🔒

All routes below require authentication.

#### List Your Products

```
GET /api/v1/vendor/products
```

Returns all products belonging to the authenticated vendor, including inactive ones.

---

#### Create a Product

```
POST /api/v1/vendor/products
```

**Body:**
```json
{
  "name": "Aso-Oke Fabric",
  "description": "Premium hand-woven aso-oke for ceremonies",
  "price": 8500.00,
  "stock_quantity": 30,
  "status": "active"
}
```

| Field | Type | Required | Notes |
|---|---|---|---|
| `name` | string | Yes | Max 255 characters |
| `description` | string | No | — |
| `price` | numeric | Yes | Must be 0 or greater |
| `stock_quantity` | integer | Yes | Must be 0 or greater |
| `status` | string | No | `active` or `inactive`. Defaults to `active` |

**Response `201`:**
```json
{
  "success": true,
  "message": "Product created.",
  "data": {
    "product": { "id": 5, "name": "Aso-Oke Fabric", "price": "8500.00", ... }
  }
}
```

---

#### Update a Product

```
PUT /api/v1/vendor/products/{id}
```

Partial updates are supported — only send the fields you want to change. Returns `403` if the product belongs to a different vendor.

**Body (example — update price and stock only):**
```json
{
  "price": 9000.00,
  "stock_quantity": 25
}
```

---

#### Delete a Product

```
DELETE /api/v1/vendor/products/{id}
```

Permanently deletes the product. Returns `403` if the product belongs to a different vendor.

---

### Orders

#### Place an Order

```
POST /api/v1/orders
```

Available to guests and authenticated users. Stock is deducted atomically — concurrent orders for the last item are handled safely at the database level.

**Body:**
```json
{
  "product_id": 3,
  "customer_name": "Chidi Okonkwo",
  "customer_email": "chidi@example.com",
  "quantity": 2
}
```

| Field | Type | Required |
|---|---|---|
| `product_id` | integer | Yes |
| `customer_name` | string | Yes |
| `customer_email` | email | Yes |
| `quantity` | integer | Yes — must be at least 1 |

**Response `201`:**
```json
{
  "success": true,
  "message": "Order placed successfully.",
  "data": {
    "order": {
      "id": 1,
      "product_id": 3,
      "customer_name": "Chidi Okonkwo",
      "customer_email": "chidi@example.com",
      "quantity": 2,
      "unit_price": "8500.00",
      "total_price": "17000.00",
      "status": "confirmed"
    }
  }
}
```

**Response `422` — Insufficient stock:**
```json
{
  "success": false,
  "message": "Insufficient stock. Available: 1."
}
```

---

## Route Summary

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/register` | No | Register a new vendor |
| POST | `/api/v1/login` | No | Login and receive token |
| POST | `/api/v1/logout` | Yes | Revoke current token |
| GET | `/api/v1/me` | Yes | Get authenticated vendor |
| GET | `/api/v1/products` | No | List all active products |
| GET | `/api/v1/products/{id}` | No | View a single product |
| GET | `/api/v1/vendor/products` | Yes | List vendor's own products |
| POST | `/api/v1/vendor/products` | Yes | Create a product |
| PUT | `/api/v1/vendor/products/{id}` | Yes | Update a product |
| DELETE | `/api/v1/vendor/products/{id}` | Yes | Delete a product |
| POST | `/api/v1/orders` | No | Place an order |

---

## Running Tests

```bash
docker compose exec app php artisan test
```

Run a specific test class:

```bash
docker compose exec app php artisan test --filter=OrderTest
```

Run a single test method:

```bash
docker compose exec app php artisan test --filter=test_stock_cannot_go_below_zero
```

---

## Design Decisions

### Architecture — Service / Repository Pattern

Controllers are kept thin and contain no business logic. The flow for every request is:

```
Request → Controller → Service → Repository → Model → Database
```

- **Controllers** — receive HTTP input, return HTTP responses
- **Services** — contain all business logic, unaware of HTTP
- **Repositories** — contain all database queries, bound to interfaces
- **Models** — define schema, relationships, and casts

This separation makes each layer independently testable and easy to swap out.

### Concurrency & Overselling Prevention

When two users simultaneously order the last item in stock, the system uses an atomic conditional `UPDATE` at the database level:

```sql
UPDATE products
SET stock_quantity = stock_quantity - ?
WHERE id = ? AND stock_quantity >= ?
```

This runs inside a `DB::transaction`. Only one request can satisfy the `WHERE` condition — the other gets zero affected rows and receives a `422`. No race condition is possible.

### Token Authentication

Laravel Sanctum issues opaque API tokens stored as SHA-256 hashes. Old tokens are revoked on each login to prevent accumulation. Tokens can also be scoped with abilities for fine-grained permission control.

### Caching

Active product listings are cached in Redis for 5 minutes. The cache is automatically cleared whenever a vendor creates, updates, or deletes a product. Search queries bypass the cache entirely.

---

## Assumptions

- A guest placing an order is identified by name and email only — no account required
- Vendors can only view and manage their own products
- Product deletion is permanent (hard delete)
- Order prices are snapshotted at the time of placement — price changes do not affect historical orders
