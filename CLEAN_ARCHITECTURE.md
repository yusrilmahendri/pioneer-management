# Clean Architecture Implementation for Laravel

This project follows a clean architecture pattern with three main layers: **Repository**, **Usecase**, and **Delivery**.

## Architecture Overview

```
app/
├── Delivery/           # HTTP Layer - Handles requests/responses
│   └── Http/
│       ├── Controllers/    # HTTP Controllers
│       └── Requests/       # Form Request Validation
├── Usecase/           # Business Logic Layer
│   ├── Contracts/          # Usecase Interfaces
│   └── {Module}Usecase.php # Business Logic Implementation
├── Repository/        # Data Access Layer
│   ├── Contracts/          # Repository Interfaces
│   └── {Module}Repository.php # Data Access Implementation
└── Models/            # Eloquent Models (unchanged)
```

## Layer Responsibilities

### 1. **Repository Layer** (`app/Repository/`)
- **Purpose**: Handle database connections and data operations
- **Responsibilities**:
  - Connect to specific database tables
  - Handle CRUD operations
  - Manage relationships and joins
  - Data filtering and querying
  - No business logic

**Example**: `UserRepository.php`
```php
public function getByEmailOrUsername(string $identifier): ?User
{
    return $this->model->where(function ($query) use ($identifier) {
        $query->where('email', $identifier)
              ->orWhere('username', $identifier);
    })->first();
}
```

### 2. **Usecase Layer** (`app/Usecase/`)
- **Purpose**: Handle business logic and application rules
- **Responsibilities**:
  - Business logic and calculations
  - Data validation and processing
  - Response formatting
  - Orchestrate multiple repository calls
  - Handle complex business rules

**Example**: `UserUsecase.php`
```php
public function loginUser(array $credentials): array
{
    $user = $this->userRepository->getByEmailOrUsername($credentials['username_or_email']);
    
    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        return ['status' => 'error', 'message' => 'Invalid credentials'];
    }
    
    $token = $user->createToken('api-token')->plainTextToken;
    
    return [
        'status' => 'success',
        'data' => ['token' => $token, 'user' => $this->formatUserData($user)]
    ];
}
```

### 3. **Delivery Layer** (`app/Delivery/`)
- **Purpose**: Handle HTTP requests and responses
- **Responsibilities**:
  - Parameter validation and formatting
  - HTTP request handling
  - Response formatting for API
  - Error handling
  - Route parameter processing

**Example**: `UserController.php`
```php
public function login(LoginRequest $request): JsonResponse
{
    try {
        $result = $this->userUsecase->loginUser($request->validated());
        
        if ($result['status'] === 'error') {
            return response()->json($result, 401);
        }

        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
    }
}
```

## Benefits

1. **Separation of Concerns**: Each layer has a single responsibility
2. **Testability**: Easy to unit test each layer independently
3. **Maintainability**: Changes in one layer don't affect others
4. **Scalability**: Easy to add new features following the same pattern
5. **Dependency Injection**: Proper IoC container usage

## Usage Examples

### Creating a New Module

1. **Create Repository Interface and Implementation**
```bash
app/Repository/Contracts/ProductRepositoryInterface.php
app/Repository/ProductRepository.php
```

2. **Create Usecase Interface and Implementation**
```bash
app/Usecase/Contracts/ProductUsecaseInterface.php
app/Usecase/ProductUsecase.php
```

3. **Create Delivery Layer**
```bash
app/Delivery/Http/Controllers/ProductController.php
app/Delivery/Http/Requests/CreateProductRequest.php
```

4. **Register Dependencies in Service Provider**
```php
// app/Providers/RepositoryServiceProvider.php
$this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
$this->app->bind(ProductUsecaseInterface::class, ProductUsecase::class);
```

### API Endpoints

The new clean architecture provides these endpoints:

```
POST /api/auth/login              # User login
POST /api/auth/register           # User registration

GET  /api/user/profile           # Get user profile
PUT  /api/user/profile           # Update user profile
POST /api/user/change-password   # Change password
GET  /api/user/dashboard         # Get dashboard data

GET  /api/users                  # Get all users (admin/owner)
POST /api/users                  # Create user (admin/owner)
GET  /api/users/{uuid}           # Get specific user (admin/owner)
PUT  /api/users/{uuid}           # Update user (admin/owner)
DELETE /api/users/{uuid}         # Delete user (admin/owner)
```

## Migration Guide

To migrate existing controllers to this architecture:

1. Move HTTP handling logic to `Delivery/Http/Controllers`
2. Move business logic to `Usecase` layer
3. Move database queries to `Repository` layer
4. Update dependency injection in service providers
5. Update routes to use new controllers

## Testing

Each layer can be tested independently:

```php
// Repository Layer Test
$repository = new UserRepository(new User());
$user = $repository->getByEmail('test@example.com');

// Usecase Layer Test (with mocked repository)
$mockRepo = Mockery::mock(UserRepositoryInterface::class);
$usecase = new UserUsecase($mockRepo);
$result = $usecase->loginUser($credentials);

// Delivery Layer Test (with mocked usecase)
$mockUsecase = Mockery::mock(UserUsecaseInterface::class);
$controller = new UserController($mockUsecase);
```

This architecture provides a solid foundation for building scalable and maintainable Laravel applications.