# Pioneer Management Dashboard API Documentation

## Overview

This API provides role-based dashboard functionality for the Pioneer Management system. The system supports three main roles:

- **Admin**: Full system access, user management, expense approval
- **Owner**: Business analytics, employee management, financial reports
- **Employee**: Personal dashboard, product management, history tracking

## Authentication

All dashboard endpoints require authentication using Laravel Sanctum tokens.

### Login
```http
POST /api/login
```

**Request Body:**
```json
{
    "username_or_email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Login successful.",
    "token": "1|abc123...",
    "user": {
        "name": "John Doe",
        "email": "user@example.com",
        "username": "johndoe",
        "account_role": "employee",
        "job_role": "Sales Representative",
        "placement": "Jakarta Office",
        "business": {
            "id": 1,
            "name": "Warung Kopi Santai"
        }
    },
    "dashboard_route": "/dashboard/employee"
}
```

### Headers Required for Protected Routes
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## Dashboard Routes

### Main Dashboard
```http
GET /api/dashboard
```
Returns role-specific dashboard data based on authenticated user's `account_role`.

---

## Employee Dashboard

### Employee Dashboard Index
```http
GET /api/dashboard/employee
```

**Response:**
```json
{
    "status": "success",
    "message": "Employee dashboard data retrieved successfully",
    "data": {
        "overview": {
            "total_products": 25,
            "total_transactions": 150,
            "total_revenue": 1500000
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
            "status": "Active",
            "location": {
                "provinsi": "Sumatera Selatan",
                "kabupaten": "Kota Palembang"
            },
            "start_date": "2021-03-12"
        }
    }
}
```

### Products Management

#### Get Employee Products
```http
GET /api/dashboard/employee/products
```

**Query Parameters:**
- `search` (optional): Search by product name or description
- `category_id` (optional): Filter by category UUID
- `per_page` (optional): Items per page (default: 10)

#### Create New Product
```http
POST /api/dashboard/employee/products
```

**Request Body:**
```json
{
    "category_id": "uuid-here",
    "status_id": "uuid-here", 
    "name_product": "Product Name",
    "deskripsi": "Product description",
    "price": 50000,
    "stock": 100
}
```

#### Update Product
```http
PUT /api/dashboard/employee/products/{uuid}
```

**Request Body:** (all fields optional)
```json
{
    "name_product": "Updated Product Name",
    "price": 55000,
    "stock": 90
}
```

### Transaction Histories
```http
GET /api/dashboard/employee/histories
```

**Query Parameters:**
- `start_date` (optional): Filter from date (Y-m-d format)
- `end_date` (optional): Filter to date (Y-m-d format)
- `per_page` (optional): Items per page (default: 15)

### Expenditures Management

#### Get Employee Expenditures
```http
GET /api/dashboard/employee/expenditures
```

**Query Parameters:**
- `start_date` (optional): Filter from date
- `end_date` (optional): Filter to date
- `category` (optional): Filter by expense category
- `per_page` (optional): Items per page (default: 15)

#### Create New Expenditure
```http
POST /api/dashboard/employee/expenditures
```

**Request Body:**
```json
{
    "category": "Transportation",
    "description": "Taxi fare for client meeting",
    "amount": 50000,
    "receipt_image": "base64_encoded_image_or_file_path"
}
```

### Vouchers Management

#### Get Employee Vouchers
```http
GET /api/dashboard/employee/vouchers
```

**Query Parameters:**
- `status` (optional): active, expired, used_up
- `per_page` (optional): Items per page (default: 15)

#### Create New Voucher
```http
POST /api/dashboard/employee/vouchers
```

**Request Body:**
```json
{
    "kode_promo": "DISCOUNT50",
    "tipe_promo": "Percentage Discount",
    "start_date": "2025-01-01",
    "end_date": "2025-12-31",
    "kouta": 100
}
```

---

## Owner Dashboard

### Owner Dashboard Index
```http
GET /api/dashboard/owner
```

**Response:**
```json
{
    "status": "success",
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
        "top_employees": [...]
    }
}
```

### Business Analytics
```http
GET /api/dashboard/owner/analytics
```

**Query Parameters:**
- `period` (optional): daily, weekly, monthly, yearly (default: monthly)

### Employee Management
```http
GET /api/dashboard/owner/employees
```

**Query Parameters:**
- `search` (optional): Search by name, email, placement, job_role
- `placement` (optional): Filter by placement
- `per_page` (optional): Items per page (default: 15)

### Expense Management
```http
GET /api/dashboard/owner/expenses
```

**Query Parameters:**
- `status` (optional): pending, approved, rejected
- `employee_id` (optional): Filter by employee UUID
- `start_date` (optional): Filter from date
- `end_date` (optional): Filter to date
- `per_page` (optional): Items per page (default: 15)

#### Approve/Reject Expense
```http
POST /api/dashboard/owner/expenses/{uuid}/approve
```

**Request Body:**
```json
{
    "action": "approve", // or "reject"
    "notes": "Approved for business travel expenses"
}
```

### Financial Reports
```http
GET /api/dashboard/owner/financial-reports
```

**Query Parameters:**
- `year` (optional): Year for report (default: current year)
- `month` (optional): Specific month (1-12)

---

## Admin Dashboard

### Admin Dashboard Index
```http
GET /api/dashboard/admin
```

### User Management
```http
GET /api/dashboard/admin/users
```

**Query Parameters:**
- `role` (optional): Filter by account_role
- `search` (optional): Search by name, email, username
- `per_page` (optional): Items per page (default: 15)

### Expenditure Management
```http
GET /api/dashboard/admin/expenditures
```

**Query Parameters:**
- `status` (optional): pending, approved, rejected
- `start_date` (optional): Filter from date
- `end_date` (optional): Filter to date
- `per_page` (optional): Items per page (default: 15)

#### Approve/Reject Expenditure
```http
POST /api/dashboard/admin/expenditures/{uuid}/approve
```

**Request Body:**
```json
{
    "action": "approve", // or "reject"
    "notes": "Administrative approval"
}
```

### System Reports
```http
GET /api/dashboard/admin/reports
```

**Query Parameters:**
- `period` (optional): daily, weekly, monthly, yearly (default: monthly)

---

## Error Responses

### Authentication Error
```json
{
    "status": "error",
    "message": "Unauthorized access. Please login first."
}
```

### Authorization Error
```json
{
    "status": "error",
    "message": "Access denied. Insufficient permissions.",
    "required_roles": ["employee"],
    "user_role": "admin"
}
```

### Validation Error
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "name_product": ["The name product field is required."],
        "price": ["The price must be a number."]
    }
}
```

### Not Found Error
```json
{
    "status": "error",
    "message": "Product not found or unauthorized"
}
```

---

## Role-Based Access Summary

| Endpoint | Admin | Owner | Employee |
|----------|-------|-------|----------|
| `/api/dashboard` | ✅ | ✅ | ✅ |
| `/api/dashboard/admin/*` | ✅ | ❌ | ❌ |
| `/api/dashboard/owner/*` | ❌ | ✅ | ❌ |
| `/api/dashboard/employee/*` | ❌ | ❌ | ✅ |

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

## Installation Notes

1. **Middleware Registration**: The custom `CheckAccountRole` middleware is registered as `account.role` in `app/Http/Kernel.php`

2. **Database Requirements**: 
   - Existing tables: users, products, vouchers, pembayarans, business
   - New tables: expenditures, users.business_id (migrations provided)

3. **Model Relationships**: Updated User model with relationships to products, expenditures, vouchers, and business

4. **Authentication**: Uses Laravel Sanctum for API token authentication

5. **Business Integration**: Users can now be assigned to businesses, and employee dashboard shows business information

## Migration Files

Run the following migrations:
```bash
php artisan migrate --path=database/migrations/2025_11_08_100000_create_expenditures_table.php
php artisan migrate --path=database/migrations/2025_11_08_100001_businesss_id_to_users_table.php
```

This API structure provides a comprehensive role-based dashboard system that scales with your business needs and maintains clear separation of concerns for each user role.