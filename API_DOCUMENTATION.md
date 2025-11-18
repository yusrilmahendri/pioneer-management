# Pioneer Management API Documentation

## Base URL

```
/api
```

## Authentication

All protected endpoints require Sanctum Bearer token in header:

```http
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 1. Authentication Endpoints

### POST /api/auth/login

**Request:**

```json
{
    "username_or_email": "string, required",
    "password": "string, required"
}
```

**Response:**

```json
{
    "status": "string",
    "message": "string", 
    "data": {
        "token": "string",
        "token_expires_at": "datetime",
        "user": {
            "id": "integer",
            "username": "string",
            "email": "string",
            "account_role": "string"
        }
    }
}
```

### POST /api/auth/register

**Request:**

```json
{
    "username": "string, required",
    "email": "string, required",
    "password": "string, required",
    "password_confirmation": "string, required"
}
```

### POST /api/auth/logout

**Headers:** Authorization required

**Response:**

```json
{
    "status": "string",
    "message": "string"
}
```

---

## 2. User Management

### GET /api/user-management

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor

**Query Parameters:**

- `search` (string, optional)
- `per_page` (integer, optional, default: 15)

**Response:**

```json
{
    "status": "string",
    "message": "string",
    "data": {
        "users": "paginated_user_array"
    }
}
```

### POST /api/user-management

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "username": "string, required",
    "email": "string, required",
    "password": "string, required",
    "account_role": "string, required",
    "id_business": "integer, optional"
}
```

### PUT /api/user-management/{uuid}

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor

**Request:**

```json
{
    "username": "string, optional",
    "email": "string, optional",
    "account_role": "string, optional",
    "id_business": "integer, optional"
}
```

### DELETE /api/user-management/{uuid}

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor

---

## 3. Business Management

### GET /api/business

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor, employee

**Query Parameters:**

- `search` (string, optional)
- `per_page` (integer, optional, default: 15)

### POST /api/business

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "name_business": "string, required",
    "id_business_category": "integer, required",
    "id_business_status": "integer, required"
}
```

### PUT /api/business/{id}

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "name_business": "string, optional",
    "id_business_category": "integer, optional",
    "id_business_status": "integer, optional"
}
```

### DELETE /api/business/{id}

**Headers:** Authorization required  
**Roles:** admin

---

## 4. Product Management

### GET /api/product

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor, employee

**Query Parameters:**

- `search` (string, optional)
- `per_page` (integer, optional, default: 15)

### POST /api/product

**Headers:** Authorization required

**Request:**

```json
{
    "name_product": "string, required",
    "id_product_category": "integer, required",
    "id_product_status": "integer, required",
    "price_product": "decimal, required"
}
```

### PUT /api/product/{uuid}

**Headers:** Authorization required

**Request:**

```json
{
    "name_product": "string, optional",
    "id_product_category": "integer, optional",
    "id_product_status": "integer, optional",
    "price_product": "decimal, optional"
}
```

### DELETE /api/product/{uuid}

**Headers:** Authorization required

---

## 5. Expenditure Management

### GET /api/expenditure

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor, employee

**Query Parameters:**

- `search` (string, optional)
- `per_page` (integer, optional, default: 15)

### POST /api/expenditure

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor, employee

**Request:**

```json
{
    "id_business": "integer, required",
    "expenditure_name": "string, required",
    "expenditure_amount": "decimal, required",
    "expenditure_description": "string, optional"
}
```

### POST /api/expenditure/{uuid}/approve

**Headers:** Authorization required  
**Roles:** admin, owner

### POST /api/expenditure/{uuid}/reject

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "rejection_reason": "string, optional"
}
```

---

## 6. Dashboard

### GET /api/dashboard

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor, employee

**Response:**

```json
{
    "status": "string",
    "message": "string",
    "data": {
        "total_businesses": "integer",
        "total_products": "integer",
        "total_expenditures": "integer",
        "pending_expenditures": "integer"
    }
}
```

---

## 7. Category & Status Management

### Business Categories

#### GET /api/business-categories

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor, employee

**Query Parameters:**

- `search` (string, optional)
- `order_by` (string, optional, default: 'created_at')
- `order_direction` (string, optional, default: 'desc')
- `per_page` (integer, optional, default: 15)

**Response:**

```json
{
    "status": "string",
    "message": "string",
    "data": "paginated_categories_array"
}
```

#### POST /api/business-categories

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "name_category_business": "string, required, min:2, max:100, unique"
}
```

#### PUT /api/business-categories/{id}

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "name_category_business": "string, optional, min:2, max:100, unique"
}
```

#### DELETE /api/business-categories/{id}

**Headers:** Authorization required  
**Roles:** admin

**Note:** Cannot delete category if it's being used by any businesses

#### GET /api/business-categories/statistics

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor

**Response:**

```json
{
    "status": "string",
    "message": "string",
    "data": {
        "statistics": [
            {
                "id": "integer",
                "name": "string",
                "businesses_count": "integer"
            }
        ]
    }
}
```

### Business Statuses

#### GET /api/business-statuses

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor, employee

**Query Parameters:**

- `search` (string, optional)
- `order_by` (string, optional, default: 'created_at')
- `order_direction` (string, optional, default: 'desc')
- `per_page` (integer, optional, default: 15)

#### POST /api/business-statuses

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "business_status": "string, required, min:2, max:50, unique"
}
```

#### PUT /api/business-statuses/{id}

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "business_status": "string, optional, min:2, max:50, unique"
}
```

#### DELETE /api/business-statuses/{id}

**Headers:** Authorization required  
**Roles:** admin

**Note:** Cannot delete status if it's being used by any businesses

#### GET /api/business-statuses/statistics

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor

### Product Categories

#### GET /api/product-categories

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor, employee

**Query Parameters:**

- `search` (string, optional)
- `order_by` (string, optional, default: 'created_at')
- `order_direction` (string, optional, default: 'desc')
- `per_page` (integer, optional, default: 15)

#### POST /api/product-categories

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "name_category_product": "string, required, min:2, max:100, unique"
}
```

#### PUT /api/product-categories/{id}

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "name_category_product": "string, optional, min:2, max:100, unique"
}
```

#### DELETE /api/product-categories/{id}

**Headers:** Authorization required  
**Roles:** admin

**Note:** Cannot delete category if it's being used by any products

#### GET /api/product-categories/statistics

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor

### Product Statuses

#### GET /api/product-statuses

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor, employee

**Query Parameters:**

- `search` (string, optional)
- `order_by` (string, optional, default: 'created_at')
- `order_direction` (string, optional, default: 'desc')
- `per_page` (integer, optional, default: 15)

#### POST /api/product-statuses

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "product_status": "string, required, min:2, max:50, unique"
}
```

#### PUT /api/product-statuses/{id}

**Headers:** Authorization required  
**Roles:** admin, owner

**Request:**

```json
{
    "product_status": "string, optional, min:2, max:50, unique"
}
```

#### DELETE /api/product-statuses/{id}

**Headers:** Authorization required  
**Roles:** admin

**Note:** Cannot delete status if it's being used by any products

#### GET /api/product-statuses/statistics

**Headers:** Authorization required  
**Roles:** admin, owner, supervisor

---

## Error Responses

All endpoints return consistent error responses:

```json
{
    "status": "error",
    "message": "string",
    "errors": "object_or_null"
}
```

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `409` - Conflict
- `422` - Validation Error
- `500` - Internal Server Error

---

## Role Hierarchy

- **Admin**: Full system access
- **Owner**: Manage own businesses and staff
- **Supervisor**: View and manage assigned business operations
- **Employee**: View assigned business data and create expenditure requests