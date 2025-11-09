# Pioneer Management Dashboard API Documentation



## Business Model

The `Business` model represents a business entity in the system. It is defined in `app/Models/Business.php` and uses the `business` table. The model includes all columns from the table (guarded = []).

### Relationships

- **businessCategory()**: Belongs to a `BusinessCategory` via `id_business_category` foreign key.
- **businessStatus()**: Belongs to a `BusinessStatus` via `id_business_status` foreign key.
- **users()**: Many-to-many relationship with `User` via the `business_account` pivot table (`id_business`, `id_user`).
- **owner()**: Many-to-many relationship with `User` via `business_account`, returns the primary owner (limited fields, no pivot data, limit 1).
- **products()**: Has many `Product` via `id_business` foreign key.

#### Serialization
When the `owner` relationship is loaded, the model customizes the JSON output to include the owner's `id` and `name`.

---
## Business Category Model

The `BusinessCategory` model represents the business categories in the system. It is defined in `app/Models/BusinessCategory.php` and uses the `business_category` table. The model includes the following fields:

- `business_category` (string): The name of the business category.
- `created_by` (integer, nullable): The user ID who created the category.
- `updated_by` (integer, nullable): The user ID who last updated the category.

### Relationships
- **businesses()**: Returns all businesses that belong to this category. (hasMany relationship to `Business` model via `id_business_category` foreign key)

---
## Overview

This API provides role-based dashboard functionality for the Pioneer Management system. The system supports three main roles with strict hierarchy enforcement:

- **Admin**: Full system access, user management (can create owners), expense approval
- **Owner**: Business analytics, employee management (can create employees), financial reports
- **Employee**: Personal dashboard, product management, history tracking (cannot create users)

## Role-Based User Creation Hierarchy

The system implements a strict role hierarchy where:
- **Admin** → Can create **Owner** accounts only
- **Owner** → Can create **Employee** accounts only  
- **Employee** → Cannot create any user accounts

For complete role hierarchy documentation, see [ROLE_HIERARCHY_DOCUMENTATION.md](./ROLE_HIERARCHY_DOCUMENTATION.md)

## Authentication

All protected endpoints require authentication using Laravel Sanctum tokens.

### Required Headers for Protected Routes

```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Login

```http
POST /api/auth/login
Content-Type: application/json
```

**Request Body:**
```json
{
    "username_or_email": "user@example.com",
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": "uuid-string",
            "name": "John Doe",
            "email": "user@example.com",
            "username": "johndoe",
            "account_role": "employee",
            "job_role": "Sales Representative",
            "placement": "Jakarta Office",
            "phone": "1234567890",
            "business": {
                "id": 1,
                "name": "Warung Kopi Santai",
                "category": "Food & Beverage"
            }
        },
        "token": "1|abc123def456...",
        "token_type": "Bearer"
    }
}
```

**Error Response (401):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

### Register (Admin Creation)

```http
POST /api/auth/register
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "System Admin",
    "email": "admin@system.com",
    "username": "sysadmin",
    "password": "securepassword123",
    "password_confirmation": "securepassword123"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": "uuid-string",
            "name": "System Admin",
            "email": "admin@system.com",
            "username": "sysadmin",
            "account_role": "admin",
            "created_at": "2025-11-09T10:00:00Z"
        },
        "token": "1|abc123def456..."
    }
}
```

### Forgot Password

```http
POST /api/auth/forgot-password
Content-Type: application/json
```

**Request Body:**
```json
{
    "email": "user@example.com"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Password reset link has been sent to your email"
}
```

### Reset Password

```http
POST /api/auth/reset-password
Content-Type: application/json
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "token": "reset_token_here",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Password has been reset successfully"
}
```

## Role-Based User Creation

### Admin Creates Owner

```http
POST /api/admin/create-owner
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Required Role:** Admin

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "username": "johndoe",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "1234567890",
    "business_name": "John's Coffee Shop",
    "business_description": "Premium coffee and pastries",
    "business_category": "Food & Beverage"
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Owner created successfully",
    "data": {
        "user": {
            "id": "uuid-string",
            "name": "John Doe",
            "email": "john@example.com",
            "username": "johndoe",
            "account_role": "owner",
            "phone": "1234567890",
            "created_at": "2025-11-09T10:30:00Z"
        }
    }
}
```

### Owner Creates Employee

```http
POST /api/owner/create-employee
Authorization: Bearer {owner_token}
Content-Type: application/json
```

**Required Role:** Owner

**Request Body:**
```json
{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "username": "janesmith",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "0987654321",
    "birth_of_date": "1990-05-15",
    "birth_of_place": "Jakarta",
    "gender": "female",
    "start_date": "2025-11-09",
    "placement": "Jakarta Office",
    "job_role": "Cashier",
    "salary": 5000000,
    "business_id": 1
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Employee created successfully",
    "data": {
        "user": {
            "id": "uuid-string",
            "name": "Jane Smith",
            "email": "jane@example.com",
            "username": "janesmith",
            "account_role": "employee",
            "job_role": "Cashier",
            "placement": "Jakarta Office",
            "salary": 5000000,
            "business_id": 1,
            "created_at": "2025-11-09T10:30:00Z"
        }
    }
}
```

## Dashboard Routes

### Main Dashboard

```http
GET /api/dashboard
Authorization: Bearer {token}
```

**Description:** Returns role-specific dashboard data based on authenticated user's account_role.

**Success Response (200):**
```json
{
    "success": true,
    "message": "Dashboard data retrieved successfully",
    "data": {
        "role": "employee",
        "redirect_url": "/dashboard/employee"
    }
}
```

### Admin Dashboard

```http
GET /api/dashboard/admin
Authorization: Bearer {admin_token}
```

**Required Role:** Admin

**Success Response (200):**
```json
{
    "success": true,
    "message": "Admin dashboard data retrieved successfully",
    "data": {
        "overview": {
            "total_users": 150,
            "total_businesses": 25,
            "total_revenue": 50000000,
            "monthly_growth": 15.2,
            "pending_expenditures": 5,
            "system_alerts": 2
        },
        "recent_activities": [...],
        "user_statistics": {...}
    }
}
```

### Owner Dashboard

```http
GET /api/dashboard/owner
Authorization: Bearer {owner_token}
```

**Required Role:** Owner

**Success Response (200):**
```json
{
    "success": true,
    "message": "Owner dashboard data retrieved successfully",
    "data": {
        "overview": {
            "total_revenue": 5000000,
            "monthly_revenue": 500000,
            "total_products": 100,
            "active_products": 85,
            "total_employees": 15,
            "total_expenses": 200000,
            "monthly_profit": 300000
        },
        "monthly_trends": [...],
        "top_employees": [...],
        "business_performance": {...}
    }
}
```

### Employee Dashboard

```http
GET /api/dashboard/employee
Authorization: Bearer {employee_token}
```

**Required Role:** Employee

**Success Response (200):**
```json
{
    "success": true,
    "message": "Employee dashboard data retrieved successfully",
    "data": {
        "overview": {
            "total_products": 25,
            "total_transactions": 150,
            "total_revenue": 1500000,
            "monthly_sales": 300000
        },
        "recent_products": [...],
        "user_info": {
            "name": "John Doe",
            "placement": "Jakarta Office",
            "job_role": "Sales Representative",
            "account_role": "employee"
        },
        "business_info": {
            "id": 1,
            "name": "Warung Kopi Santai",
            "category": "Food & Beverage",
            "status": "Active"
        }
    }
}
```

## User Profile Management

### Get User Profile

```http
GET /api/user/profile
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Profile retrieved successfully",
    "data": {
        "user": {
            "id": "uuid-string",
            "name": "John Doe",
            "email": "john@example.com",
            "username": "johndoe",
            "account_role": "employee",
            "phone": "1234567890",
            "birth_of_date": "1990-01-15",
            "birth_of_place": "Jakarta",
            "gender": "male",
            "job_role": "Sales Representative",
            "placement": "Jakarta Office",
            "salary": 5000000,
            "business_id": 1,
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-11-09T10:00:00Z"
        }
    }
}
```

### Update User Profile

```http
PUT /api/user/profile
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:** (all fields optional)
```json
{
    "name": "John Updated Doe",
    "phone": "0987654321",
    "birth_of_date": "1990-01-15",
    "birth_of_place": "Jakarta",
    "gender": "male"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "user": {
            "id": "uuid-string",
            "name": "John Updated Doe",
            "email": "john@example.com",
            "updated_at": "2025-11-09T10:30:00Z"
        }
    }
}
```

### Change Password

```http
POST /api/user/change-password
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "current_password": "oldpassword123",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Password changed successfully"
}
```

### Get Dashboard Data

```http
GET /api/user/dashboard
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Dashboard data retrieved successfully",
    "data": {
        "user": {...},
        "dashboard_route": "/dashboard/employee",
        "permissions": [...],
        "recent_activities": [...]
    }
}
```

## Product Management

### Get All Products

```http
GET /api/products
Authorization: Bearer {token}
```

**Query Parameters:**
- `search` (optional): Search by product name or description
- `category_id` (optional): Filter by category UUID
- `business_id` (optional): Filter by business ID
- `status` (optional): Filter by product status
- `per_page` (optional): Items per page (default: 15, max: 100)
- `page` (optional): Page number (default: 1)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Products retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": "uuid-string",
                "name_product": "Coffee Latte",
                "deskripsi": "Premium coffee with steamed milk",
                "price": 25000,
                "stock": 50,
                "category": {
                    "id": "uuid-string",
                    "name": "Beverages"
                },
                "status": {
                    "id": "uuid-string",
                    "name": "Active"
                },
                "business_id": 1,
                "user_id": "creator-uuid",
                "created_at": "2025-11-09T10:00:00Z"
            }
        ],
        "per_page": 15,
        "total": 100,
        "last_page": 7
    }
}
```

### Get My Products

```http
GET /api/products/my-products
Authorization: Bearer {token}
```

**Query Parameters:** Same as Get All Products

**Success Response:** Same structure as Get All Products but filtered by authenticated user

### Get Product Statistics

```http
GET /api/products/statistics
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Product statistics retrieved successfully",
    "data": {
        "total_products": 100,
        "active_products": 85,
        "inactive_products": 15,
        "out_of_stock": 5,
        "low_stock": 12,
        "total_value": 15000000,
        "categories_breakdown": [...],
        "monthly_added": 25
    }
}
```

### Get Products by Business

```http
GET /api/products/business/{businessId}
Authorization: Bearer {token}
```

**Path Parameters:**
- `businessId` (required): Business ID

**Query Parameters:** Same as Get All Products

**Success Response:** Same structure as Get All Products but filtered by business

### Get Single Product

```http
GET /api/products/{uuid}
Authorization: Bearer {token}
```

**Path Parameters:**
- `uuid` (required): Product UUID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Product retrieved successfully",
    "data": {
        "product": {
            "id": "uuid-string",
            "name_product": "Coffee Latte",
            "deskripsi": "Premium coffee with steamed milk",
            "price": 25000,
            "stock": 50,
            "category": {...},
            "status": {...},
            "business": {...},
            "creator": {...},
            "created_at": "2025-11-09T10:00:00Z",
            "updated_at": "2025-11-09T10:00:00Z"
        }
    }
}
```

### Create New Product

```http
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "category_id": "uuid-string",
    "status_id": "uuid-string",
    "name_product": "New Coffee Product",
    "deskripsi": "Delicious coffee description",
    "price": 30000,
    "stock": 100
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Product created successfully",
    "data": {
        "product": {
            "id": "new-uuid-string",
            "name_product": "New Coffee Product",
            "deskripsi": "Delicious coffee description",
            "price": 30000,
            "stock": 100,
            "category_id": "uuid-string",
            "status_id": "uuid-string",
            "business_id": 1,
            "user_id": "creator-uuid",
            "created_at": "2025-11-09T11:00:00Z"
        }
    }
}
```

### Update Product

```http
PUT /api/products/{uuid}
Authorization: Bearer {token}
Content-Type: application/json
```

**Path Parameters:**
- `uuid` (required): Product UUID

**Request Body:** (all fields optional)
```json
{
    "name_product": "Updated Product Name",
    "deskripsi": "Updated description",
    "price": 32000,
    "stock": 75,
    "category_id": "new-category-uuid",
    "status_id": "new-status-uuid"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Product updated successfully",
    "data": {
        "product": {
            "id": "uuid-string",
            "name_product": "Updated Product Name",
            "updated_at": "2025-11-09T11:30:00Z"
        }
    }
}
```

### Delete Product

```http
DELETE /api/products/{uuid}
Authorization: Bearer {token}
```

**Path Parameters:**
- `uuid` (required): Product UUID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Product deleted successfully"
}
```

## Business Management

### Get All Businesses (Public)

```http
GET /api/businesses-public
```

**Access:** Public (No authentication required)

**Query Parameters:**
- `search` (optional): Search by business name
- `category_id` (optional): Filter by category ID
- `status_id` (optional): Filter by status ID
- `order_by` (optional): Sort by field (default: created_at)
- `order_direction` (optional): asc or desc (default: desc)

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Businesses retrieved successfully",
    "data": [
        {
            "id": 1,
            "business": "Warung Kopi Santai",
            "description": "Cozy coffee shop with local atmosphere",
            "address": "Jl. Sudirman No. 123, Jakarta",
            "phone": "081234567890",
            "email": "info@warunkkopi.com",
            "website": "https://warunkkopi.com",
            "category": {
                "id": 1,
                "business_category": "Food & Beverage"
            },
            "status": {
                "id": 1,
                "business_status": "Active"
            },
            "start_date": "2023-01-15",
            "created_at": "2023-01-15T00:00:00Z",
            "updated_at": "2025-11-09T10:00:00Z"
        }
    ]
}
```

### Get All Businesses (Admin Only)

```http
GET /api/businesses
Authorization: Bearer {admin_token}
```

**Required Role:** Admin

**Query Parameters:**
- `search` (optional): Search by business name
- `category_id` (optional): Filter by category ID
- `status_id` (optional): Filter by status ID
- `user_id` (optional): Filter by user ID (businesses assigned to user)
- `order_by` (optional): Sort by field (default: created_at)
- `order_direction` (optional): asc or desc (default: desc)

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Businesses retrieved successfully",
    "data": [
        {
            "id": 1,
            "business": "Warung Kopi Santai",
            "description": "Cozy coffee shop with local atmosphere",
            "address": "Jl. Sudirman No. 123, Jakarta",
            "phone": "081234567890",
            "email": "info@warunkkopi.com",
            "website": "https://warunkkopi.com",
            "category": {
                "id": 1,
                "business_category": "Food & Beverage"
            },
            "status": {
                "id": 1,
                "business_status": "Active"
            },
            "users": [
                {
                    "id": "uuid-string",
                    "name": "John Doe",
                    "account_role": "owner"
                }
            ],
            "products_count": 25,
            "employees_count": 5,
            "start_date": "2023-01-15",
            "created_at": "2023-01-15T00:00:00Z",
            "updated_at": "2025-11-09T10:00:00Z"
        }
    ]
}
```

### Get My Businesses

```http
GET /api/businesses/my-businesses
Authorization: Bearer {admin_token}
```

**Required Role:** Admin

**Description:** Returns all businesses that the authenticated user is assigned to through the business_account pivot table.

**Success Response (200):**
```json
{
    "status": "success",
    "message": "User businesses retrieved successfully",
    "data": [
        {
            "id": 1,
            "business": "Warung Kopi Santai",
            "description": "Cozy coffee shop with local atmosphere",
            "category": {
                "id": 1,
                "business_category": "Food & Beverage"
            },
            "status": {
                "id": 1,
                "business_status": "Active"
            },
            "start_date": "2023-01-15",
            "created_at": "2023-01-15T00:00:00Z"
        }
    ]
}
```

### Get Business Statistics

```http
GET /api/businesses/statistics
Authorization: Bearer {admin_token}
```

**Required Role:** Admin

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Business statistics retrieved successfully",
    "data": {
        "total": 50,
        "active": 45,
        "inactive": 5,
        "by_category": {
            "Food & Beverage": 25,
            "Retail": 15,
            "Services": 10
        }
    }
}
```

### Get Businesses by Category

```http
GET /api/businesses/category/{categoryId}
Authorization: Bearer {admin_token}
```

**Required Role:** Admin

**Path Parameters:**
- `categoryId` (required): Category ID (integer)

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Businesses retrieved successfully",
    "data": [
        {
            "id": 1,
            "business": "Warung Kopi Santai",
            "category": {
                "id": 1,
                "business_category": "Food & Beverage"
            },
            "status": {
                "id": 1,
                "business_status": "Active"
            }
        }
    ]
}
```

### Get Single Business

```http
GET /api/businesses/{id}
Authorization: Bearer {admin_token}
```

**Required Role:** Admin

**Path Parameters:**
- `id` (required): Business ID (integer)

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Business retrieved successfully",
    "data": {
        "id": 1,
        "business": "Warung Kopi Santai",
        "description": "Cozy coffee shop with local atmosphere",
        "address": "Jl. Sudirman No. 123, Jakarta",
        "phone": "081234567890",
        "email": "info@warunkkopi.com",
        "website": "https://warunkkopi.com",
        "category": {
            "id": 1,
            "business_category": "Food & Beverage"
        },
        "status": {
            "id": 1,
            "business_status": "Active"
        },
        "start_date": "2023-01-15",
        "created_at": "2023-01-15T00:00:00Z",
        "updated_at": "2025-11-09T10:00:00Z"
    }
}
```

**Error Response (404):**
```json
{
    "status": "error",
    "message": "Business not found",
    "data": null
}
```

### Create New Business

```http
POST /api/businesses
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Required Role:** Admin only

**Request Body:**
```json
{
    "business": "New Coffee Shop",
    "id_business_category": 1,
    "id_business_status": 1,
    "user_id": 123,
    "description": "Modern coffee shop with artisan coffee",
    "address": "Jl. Asia Afrika No. 45, Bandung",
    "phone": "081234567890",
    "email": "info@newcoffeeshop.com",
    "website": "https://newcoffeeshop.com"
}
```

**Validation Rules:**
- `business`: required, string, max 255 characters
- `id_business_category`: required, must exist in business_category table
- `id_business_status`: required, must exist in business_status table
- `user_id`: required, must exist in users table (the owner this business belongs to)
- `description`: optional, string
- `address`: optional, string
- `phone`: optional, string, max 20 characters
- `email`: optional, valid email format
- `website`: optional, valid URL format

**Important Notes:**
- When creating a business, admin must specify which user (owner) the business belongs to via `user_id`
- The system automatically creates an entry in the `business_account` pivot table
- `created_by` is automatically set to the authenticated admin's ID

**Success Response (201):**
```json
{
    "status": "success",
    "message": "Business created successfully",
    "data": {
        "id": 2,
        "business": "New Coffee Shop",
        "description": "Modern coffee shop with artisan coffee",
        "address": "Jl. Asia Afrika No. 45, Bandung",
        "phone": "081234567890",
        "email": "info@newcoffeeshop.com",
        "website": "https://newcoffeeshop.com",
        "id_business_category": 1,
        "id_business_status": 1,
        "businessCategory": {
            "id": 1,
            "business_category": "Food & Beverage"
        },
        "businessStatus": {
            "id": 1,
            "business_status": "Active"
        },
        "users": [
            {
                "id": 123,
                "name": "John Doe",
                "account_role": "owner"
            }
        ],
        "created_at": "2025-11-09T11:00:00Z",
        "updated_at": "2025-11-09T11:00:00Z"
    }
}
```

**Error Response (422):**
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "business": ["The business field is required."],
        "id_business_category": ["The selected id business category is invalid."],
        "user_id": ["The selected user id is invalid."]
    }
}
```

### Update Business

```http
PUT /api/businesses/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json
```

**Required Role:** Admin only

**Path Parameters:**
- `id` (required): Business ID (integer)

**Request Body:** (all fields optional)
```json
{
    "business": "Updated Coffee Shop Name",
    "description": "Updated description",
    "address": "Jakarta Selatan",
    "phone": "081987654321",
    "email": "newemail@coffeeshop.com",
    "website": "https://newwebsite.com",
    "id_business_category": 2,
    "id_business_status": 1
}
```

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Business updated successfully",
    "data": {
        "id": 1,
        "business": "Updated Coffee Shop Name",
        "description": "Updated description",
        "address": "Jakarta Selatan",
        "phone": "081987654321",
        "updated_at": "2025-11-09T11:30:00Z"
    }
}
```

**Error Response (404):**
```json
{
    "status": "error",
    "message": "Business not found",
    "data": null
}
```

### Delete Business

```http
DELETE /api/businesses/{id}
Authorization: Bearer {admin_token}
```

**Required Role:** Admin only

**Path Parameters:**
- `id` (required): Business ID (integer)

**Success Response (200):**
```json
{
    "status": "success",
    "message": "Business deleted successfully",
    "data": null
}
```

**Error Response (404):**
```json
{
    "status": "error",
    "message": "Business not found",
    "data": null
}
```

## User Management

### Get All Users

```http
GET /api/users
Authorization: Bearer {admin_or_owner_token}
```

**Required Roles:** Admin, Owner

**Query Parameters:**
- `search` (optional): Search by name, email, username
- `role` (optional): Filter by account_role (admin, owner, employee)
- `business_id` (optional): Filter by business ID
- `placement` (optional): Filter by placement
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number (default: 1)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Users retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": "uuid-string",
                "name": "John Doe",
                "email": "john@example.com",
                "username": "johndoe",
                "account_role": "employee",
                "job_role": "Sales Representative",
                "placement": "Jakarta Office",
                "salary": 5000000,
                "business_id": 1,
                "business": {
                    "id": 1,
                    "name_business": "Warung Kopi Santai"
                },
                "created_at": "2025-01-01T00:00:00Z"
            }
        ],
        "per_page": 15,
        "total": 150,
        "last_page": 10
    }
}
```

### Get Single User

```http
GET /api/users/{uuid}
Authorization: Bearer {admin_or_owner_token}
```

**Required Roles:** Admin, Owner

**Path Parameters:**
- `uuid` (required): User UUID

**Success Response (200):**
```json
{
    "success": true,
    "message": "User retrieved successfully",
    "data": {
        "user": {
            "id": "uuid-string",
            "name": "John Doe",
            "email": "john@example.com",
            "username": "johndoe",
            "account_role": "employee",
            "phone": "1234567890",
            "birth_of_date": "1990-01-15",
            "birth_of_place": "Jakarta",
            "gender": "male",
            "start_date": "2025-01-01",
            "job_role": "Sales Representative",
            "placement": "Jakarta Office",
            "salary": 5000000,
            "business_id": 1,
            "business": {...},
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-11-09T10:00:00Z"
        }
    }
}
```

### Update User

```http
PUT /api/users/{uuid}
Authorization: Bearer {admin_or_owner_token}
Content-Type: application/json
```

**Required Roles:** Admin, Owner

**Path Parameters:**
- `uuid` (required): User UUID

**Request Body:** (all fields optional)
```json
{
    "name": "John Updated Doe",
    "phone": "0987654321",
    "job_role": "Senior Sales Representative",
    "placement": "Jakarta Pusat Office",
    "salary": 6000000,
    "business_id": 2
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "User updated successfully",
    "data": {
        "user": {
            "id": "uuid-string",
            "name": "John Updated Doe",
            "job_role": "Senior Sales Representative",
            "salary": 6000000,
            "updated_at": "2025-11-09T11:30:00Z"
        }
    }
}
```

### Delete User

```http
DELETE /api/users/{uuid}
Authorization: Bearer {admin_or_owner_token}
```

**Required Roles:** Admin, Owner

**Path Parameters:**
- `uuid` (required): User UUID

**Success Response (200):**
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

### Get Users by Business

```http
GET /api/users/business/{businessId}
Authorization: Bearer {admin_or_owner_token}
```

**Required Roles:** Admin, Owner

**Path Parameters:**
- `businessId` (required): Business ID

**Query Parameters:** Same as Get All Users

**Success Response:** Same structure as Get All Users but filtered by business

### Get Users by Role

```http
GET /api/users/role/{role}
Authorization: Bearer {admin_or_owner_token}
```

**Required Roles:** Admin, Owner

**Path Parameters:**
- `role` (required): Role name (admin, owner, employee)

**Query Parameters:** Same as Get All Users

**Success Response:** Same structure as Get All Users but filtered by role

### Assign User to Business

```http
POST /api/users/assign-to-business
Authorization: Bearer {admin_or_owner_token}
Content-Type: application/json
```

**Required Roles:** Admin, Owner

**Request Body:**
```json
{
    "user_id": "uuid-string",
    "business_id": 1
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "User assigned to business successfully",
    "data": {
        "user": {
            "id": "uuid-string",
            "name": "John Doe",
            "business_id": 1,
            "updated_at": "2025-11-09T12:00:00Z"
        }
    }
}
```

## Expenditure Management

### Get Expenditures

```http
GET /api/dashboard/expenditures
Authorization: Bearer {admin_or_owner_token}
```

**Required Roles:** Admin, Owner

**Query Parameters:**
- `status` (optional): pending, approved, rejected
- `employee_id` (optional): Filter by employee UUID
- `start_date` (optional): Filter from date (Y-m-d format)
- `end_date` (optional): Filter to date (Y-m-d format)
- `category` (optional): Filter by expense category
- `per_page` (optional): Items per page (default: 15)

**Success Response (200):**
```json
{
    "success": true,
    "message": "Expenditures retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": "uuid-string",
                "category": "Transportation",
                "description": "Taxi fare for client meeting",
                "amount": 50000,
                "status": "pending",
                "receipt_image": "storage/receipts/image.jpg",
                "employee": {
                    "id": "uuid-string",
                    "name": "John Doe"
                },
                "submitted_at": "2025-11-09T10:00:00Z",
                "processed_at": null,
                "processed_by": null
            }
        ],
        "per_page": 15,
        "total": 50
    }
}
```

### Approve/Reject Expenditure

```http
POST /api/dashboard/expenditures/{uuid}/approve
Authorization: Bearer {admin_or_owner_token}
Content-Type: application/json
```

**Required Roles:** Admin, Owner

**Path Parameters:**
- `uuid` (required): Expenditure UUID

**Request Body:**
```json
{
    "action": "approve",
    "notes": "Approved for legitimate business expense"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Expenditure approved successfully",
    "data": {
        "expenditure": {
            "id": "uuid-string",
            "status": "approved",
            "notes": "Approved for legitimate business expense",
            "processed_at": "2025-11-09T12:00:00Z",
            "processed_by": "admin-uuid"
        }
    }
}
```

## Reports

### Generate Reports

```http
GET /api/dashboard/reports
Authorization: Bearer {admin_or_owner_token}
```

**Required Roles:** Admin, Owner

**Query Parameters:**
- `period` (optional): daily, weekly, monthly, yearly (default: monthly)
- `start_date` (optional): Custom start date (Y-m-d format)
- `end_date` (optional): Custom end date (Y-m-d format)
- `type` (optional): financial, users, products, businesses

**Success Response (200):**
```json
{
    "success": true,
    "message": "Reports generated successfully",
    "data": {
        "period": "monthly",
        "date_range": {
            "start": "2025-11-01",
            "end": "2025-11-30"
        },
        "financial_summary": {
            "total_revenue": 50000000,
            "total_expenses": 15000000,
            "net_profit": 35000000,
            "profit_margin": 70
        },
        "business_metrics": {
            "active_businesses": 45,
            "new_businesses": 5,
            "total_products": 500,
            "total_transactions": 2500
        },
        "user_metrics": {
            "total_users": 150,
            "new_users": 25,
            "active_users": 140
        }
    }
}
```

## HTTP Status Codes

The API uses standard HTTP status codes to indicate success or failure:

- **200 OK**: Request successful
- **201 Created**: Resource created successfully  
- **400 Bad Request**: Invalid request data
- **401 Unauthorized**: Authentication required or invalid token
- **403 Forbidden**: Access denied due to insufficient permissions
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation errors
- **500 Internal Server Error**: Server error

---

## Error Response Examples

### Authentication Errors (401)

**Missing Token:**
```json
{
    "success": false,
    "message": "Unauthenticated",
    "error": "Token not provided"
}
```

**Invalid Token:**
```json
{
    "success": false,
    "message": "Unauthenticated", 
    "error": "Token is invalid or expired"
}
```

**Invalid Credentials:**
```json
{
    "success": false,
    "message": "Invalid credentials",
    "error": "The provided username/email and password do not match our records"
}
```

### Authorization Errors (403)

**Insufficient Permissions:**
```json
{
    "success": false,
    "message": "Access denied",
    "error": "Insufficient permissions for this action",
    "required_roles": ["admin", "owner"],
    "user_role": "employee"
}
```

**Role Hierarchy Violation:**
```json
{
    "success": false,
    "message": "Owner can only create: employee",
    "error": "ROLE_HIERARCHY_VIOLATION"
}
```

### Validation Errors (422)

**Required Fields Missing:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": ["The name field is required."],
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

**Duplicate Values:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "username": ["The username has already been taken."]
    }
}
```

**Invalid Format:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email must be a valid email address."],
        "price": ["The price must be a number."],
        "start_date": ["The start date must be a valid date."]
    }
}
```

### Not Found Errors (404)

**Resource Not Found:**
```json
{
    "success": false,
    "message": "Resource not found",
    "error": "The requested user/product/business was not found or you don't have access"
}
```

**Route Not Found:**
```json
{
    "success": false,
    "message": "Route not found",
    "error": "The specified route does not exist or you don't have access"
}
```

### Server Errors (500)

**Internal Server Error:**
```json
{
    "success": false,
    "message": "Internal server error",
    "error": "An unexpected error occurred. Please try again later."
}
```

---

## Role-Based Access Summary

| Endpoint | Admin | Owner | Employee | Public |
|----------|-------|-------|----------|--------|
| `/api/dashboard` | ✅ | ✅ | ✅ | ❌ |
| `/api/dashboard/admin/*` | ✅ | ❌ | ❌ | ❌ |
| `/api/dashboard/owner/*` | ❌ | ✅ | ❌ | ❌ |
| `/api/dashboard/employee/*` | ❌ | ❌ | ✅ | ❌ |
| `/api/businesses-public` | ✅ | ✅ | ✅ | ✅ |
| `/api/businesses` | ✅ | ❌ | ❌ | ❌ |
| `/api/businesses/*` (all routes) | ✅ | ❌ | ❌ | ❌ |

---

## Business Management

### Get All Businesses (Public)
```http
GET /api/businesses
```

**Query Parameters:**
- `search` (optional): Search by business name
- `category_id` (optional): Filter by category ID
- `status_id` (optional): Filter by status ID
- `per_page` (optional): Items per page (default: 15)

### Admin Business Management

#### Assign Employee to Business
```http
POST /api/dashboard/admin/assign-employee-to-business
```

**Request Body:**
```json
{
    "user_id": "employee-uuid-here",
    "business_id": 1
}
```

#### Remove Employee from Business
```http
POST /api/dashboard/admin/remove-employee-from-business
```

**Request Body:**
```json
{
    "user_id": "employee-uuid-here"
}
```

#### Get Employees by Business
```http
GET /api/dashboard/admin/businesses/{businessId}/employees
```

---

## API Endpoints Summary

### Authentication Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|---------|-------------|
| POST | `/api/auth/login` | Public | User login |
| POST | `/api/auth/register` | Public | Admin registration |
| POST | `/api/auth/forgot-password` | Public | Request password reset |
| POST | `/api/auth/reset-password` | Public | Reset password |

### Role-Based User Creation
| Method | Endpoint | Required Role | Description |
|--------|----------|---------------|-------------|
| POST | `/api/admin/create-owner` | Admin | Admin creates Owner |
| POST | `/api/owner/create-employee` | Owner | Owner creates Employee |

### Dashboard Endpoints  
| Method | Endpoint | Required Role | Description |
|--------|----------|---------------|-------------|
| GET | `/api/dashboard` | Any | Role-based dashboard redirect |
| GET | `/api/dashboard/admin` | Admin | Admin dashboard data |
| GET | `/api/dashboard/owner` | Owner | Owner dashboard data |
| GET | `/api/dashboard/employee` | Employee | Employee dashboard data |

### User Management (Admin/Owner Only)
| Method | Endpoint | Required Role | Description |
|--------|----------|---------------|-------------|
| GET | `/api/users` | Admin, Owner | Get all users |
| GET | `/api/users/{uuid}` | Admin, Owner | Get single user |
| PUT | `/api/users/{uuid}` | Admin, Owner | Update user |
| DELETE | `/api/users/{uuid}` | Admin, Owner | Delete user |

### Product Management
| Method | Endpoint | Access | Description |
|--------|----------|---------|-------------|
| GET | `/api/products` | Authenticated | Get all products |
| POST | `/api/products` | Authenticated | Create product |
| PUT | `/api/products/{uuid}` | Authenticated | Update product |
| DELETE | `/api/products/{uuid}` | Authenticated | Delete product |

### Business Management
| Method | Endpoint | Access | Description |
|--------|----------|---------|-------------|
| GET | `/api/businesses-public` | Public | Get all businesses (public) |
| GET | `/api/businesses` | Admin | Get all businesses (admin only) |
| GET | `/api/businesses/my-businesses` | Admin | Get user's assigned businesses |
| GET | `/api/businesses/statistics` | Admin | Get business statistics |
| GET | `/api/businesses/category/{categoryId}` | Admin | Get businesses by category |
| GET | `/api/businesses/{id}` | Admin | Get single business |
| POST | `/api/businesses` | Admin | Create business |
| PUT | `/api/businesses/{id}` | Admin | Update business |
| DELETE | `/api/businesses/{id}` | Admin | Delete business |

## Role-Based Access Summary

| Resource | Admin | Owner | Employee | Public |
|----------|-------|-------|----------|--------|
| **User Creation** | Owner only | Employee only | ❌ | ❌ |
| **Dashboard** | ✅ | ✅ | ✅ | ❌ |
| **User Management** | ✅ | ✅ | ❌ | ❌ |
| **Business Management** | ✅ (Full CRUD) | ❌ | ❌ | Read only (public endpoint) |
| **Product Management** | ✅ | ✅ | ✅ | ❌ |

**Important Notes:**
- All `/api/businesses` routes (except `/api/businesses-public`) are **Admin only**
- Business-user relationship is many-to-many through `business_account` pivot table
- When creating a business, admin **must specify** the `user_id` (owner) via the request payload
- The system automatically creates the business-user relationship in `business_account` table
- `created_by` in `business_account` records which admin created the assignment
- Owner and Employee can view public businesses via `/api/businesses-public`
- Only Admin can create, update, delete, and manage business assignments

## Installation & Setup

### Prerequisites
- PHP 8.1+
- Laravel 11.x
- MySQL/PostgreSQL
- Composer

### Setup Steps

1. **Install Dependencies**
   ```bash
   composer install && npm install
   ```

2. **Environment & Database**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```

3. **Start Server**
   ```bash
   php artisan serve
   ```

### Middleware Registration

Ensure `CheckAccountRole` middleware is registered as `account_role` in `bootstrap/app.php`:

```php
use App\Http\Middleware\CheckAccountRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'account_role' => CheckAccountRole::class,
        ]);
    })
```

**Note:** The middleware checks the `account_role` column directly on the User model, not through Spatie Permission package.

### Default Test Credentials

- **Admin**: `admin@system.com` / `admin123`
- **Owner**: `owner@example.com` / `owner123`  
- **Employee**: `employee@example.com` / `employee123`

This comprehensive API provides complete role-based access control with hierarchical user creation, ensuring proper security and separation of concerns across all user levels.