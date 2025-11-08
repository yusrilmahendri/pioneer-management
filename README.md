<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Pioneer Management Dashboard

## Overview

Pioneer Management is a comprehensive business management system built with Laravel 11.x, implementing clean architecture principles and role-based access control. The system provides hierarchical user management, business analytics, and operational dashboards for different user roles.

## Key Features

- **Role-Based Access Control**: Strict hierarchy enforcement (Admin → Owner → Employee)
- **Clean Architecture**: Repository-Usecase-Delivery pattern implementation
- **JWT Authentication**: Secure token-based authentication with Laravel Sanctum
- **Business Management**: Multi-business support with employee assignment
- **Dashboard Analytics**: Role-specific dashboards and reporting
- **User Management**: Hierarchical user creation with proper validation

## Architecture

This application follows Clean Architecture principles with three distinct layers:

- **Repository Layer**: Data access and persistence
- **Usecase Layer**: Business logic and domain rules
- **Delivery Layer**: HTTP request handling and response formatting

## Role Hierarchy System

The system implements a strict role-based hierarchy:

```
Admin (System Level)
  └── Can create Owner accounts
  
Owner (Business Level)  
  └── Can create Employee accounts
  
Employee (Operational Level)
  └── Cannot create user accounts
```

## Documentation

Complete project documentation is available in the following files:

- **[API Documentation](./API_DOCUMENTATION.md)**: Complete API endpoints and usage
- **[Role Hierarchy Documentation](./ROLE_HIERARCHY_DOCUMENTATION.md)**: Detailed role-based user creation system
- **[Clean Architecture](./CLEAN_ARCHITECTURE.md)**: Architecture implementation details
- **[Migration Status](./MIGRATION_STATUS.md)**: Database migration tracking

## Quick Start

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd pioneer-management
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Run the application**
   ```bash
   php artisan serve
   ```

### Default Admin Account

After running migrations and seeders, use these credentials:
- **Email**: `admin@system.com`
- **Password**: `admin123`
- **Role**: `admin`

## API Usage

### Authentication

All API endpoints require authentication except `/api/auth/login` and `/api/auth/register`.

```bash
# Login
POST /api/auth/login
{
    "username_or_email": "admin@system.com",
    "password": "admin123"
}

# Use returned token in subsequent requests
Authorization: Bearer {token}
```

### Role-Based User Creation

```bash
# Admin creates Owner
POST /api/admin/users/create-owner
Authorization: Bearer {admin_token}

# Owner creates Employee  
POST /api/owner/users/create-employee
Authorization: Bearer {owner_token}
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## Project Structure

```
app/
├── Delivery/Http/          # HTTP layer (Controllers, Requests, Middleware)
├── Repository/             # Data access layer
├── Usecase/               # Business logic layer
└── Models/                # Eloquent models

database/
├── migrations/            # Database schema migrations
├── seeders/              # Database seeders
└── factories/            # Model factories

routes/
├── api.php               # API routes with role-based protection
└── web.php               # Web routes
```

## Security Features

- **Role-Based Access Control**: Hierarchical user management
- **JWT Authentication**: Secure token-based authentication
- **Input Validation**: Comprehensive request validation
- **Route Protection**: Middleware-based access control
- **Password Security**: Bcrypt hashing and reset functionality

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support

For support and questions:
- Review the [API Documentation](./API_DOCUMENTATION.md)
- Check [Role Hierarchy Documentation](./ROLE_HIERARCHY_DOCUMENTATION.md)
- Submit issues via GitHub Issues

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
