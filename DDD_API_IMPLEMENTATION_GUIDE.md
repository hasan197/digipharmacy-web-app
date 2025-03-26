# Domain-Driven Design (DDD) API Implementation Guide

This document provides a comprehensive guide for implementing new APIs in the DigiPharmacy application following Domain-Driven Design (DDD) principles and Progressive Architecture. The structure and patterns described here are based on the Sales API implementation.

## Architecture Overview

Our application follows a layered architecture with clear separation of concerns:

1. **Domain Layer** - Contains business logic, domain models, and domain services
2. **Application Layer** - Orchestrates use cases and provides interfaces for the presentation layer
3. **Infrastructure Layer** - Implements repositories and external services
4. **Presentation Layer** - Controllers and API endpoints

## Directory Structure

For a new domain (e.g., "NewFeature"), create the following directory structure:

```
app/
├── Domain/
│   └── NewFeature/
│       ├── Models/              # Domain entities
│       ├── ValueObjects/        # Immutable value objects
│       ├── Repositories/        # Repository interfaces
│       ├── Services/            # Domain services
│       └── Exceptions/          # Domain-specific exceptions
├── Application/
│   ├── Contracts/
│   │   └── NewFeature/          # Service interfaces
│   └── Services/                # Service implementations
├── Infrastructure/
│   └── NewFeature/
│       ├── Repositories/        # Repository implementations
│       └── Mappers/             # Data mappers between domain and persistence
└── Http/
    └── Controllers/             # API controllers
```

## Implementation Steps

### 1. Define Domain Models

Create domain entities in `app/Domain/NewFeature/Models/`:

```php
namespace App\Domain\NewFeature\Models;

use App\Domain\NewFeature\ValueObjects\EntityId;

class Entity
{
    private EntityId $id;
    private string $name;
    // Other properties...

    public function __construct(EntityId $id, string $name, /* other params */)
    {
        $this->id = $id;
        $this->name = $name;
        // Initialize other properties...
    }

    // Getters
    public function getId(): EntityId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    // Domain methods
    public function updateDetails(/* params */): void
    {
        // Implementation...
    }

    // Array representation for API responses
    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'name' => $this->name,
            // Other properties...
        ];
    }
}
```

### 2. Create Value Objects

Value objects in `app/Domain/NewFeature/ValueObjects/`:

```php
namespace App\Domain\NewFeature\ValueObjects;

class EntityId
{
    private int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(EntityId $other): bool
    {
        return $this->value === $other->getValue();
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }
}
```

### 3. Define Repository Interfaces

Repository interfaces in `app/Domain/NewFeature/Repositories/`:

```php
namespace App\Domain\NewFeature\Repositories;

use App\Domain\NewFeature\Models\Entity;
use App\Domain\NewFeature\ValueObjects\EntityId;

interface EntityRepositoryInterface
{
    public function findById(EntityId $id): ?Entity;
    public function findBySearchCriteria(array $criteria): array;
    public function save(Entity $entity): Entity;
    public function delete(EntityId $id): bool;
}
```

### 4. Create Domain Exceptions

Domain exceptions in `app/Domain/NewFeature/Exceptions/`:

```php
namespace App\Domain\NewFeature\Exceptions;

use Exception;

class EntityNotFoundException extends Exception
{
    public function __construct(int $entityId)
    {
        parent::__construct("Entity with ID {$entityId} not found");
    }
}
```

### 5. Implement Domain Services

Domain services in `app/Domain/NewFeature/Services/`:

```php
namespace App\Domain\NewFeature\Services;

use App\Domain\NewFeature\Models\Entity;
use App\Domain\NewFeature\Repositories\EntityRepositoryInterface;
use App\Domain\NewFeature\ValueObjects\EntityId;
use App\Domain\NewFeature\Exceptions\EntityNotFoundException;

class EntityService
{
    public function __construct(
        private readonly EntityRepositoryInterface $entityRepository
    ) {}

    public function findById(int $id): Entity
    {
        $entityId = new EntityId($id);
        $entity = $this->entityRepository->findById($entityId);
        
        if (!$entity) {
            throw new EntityNotFoundException($id);
        }
        
        return $entity;
    }
    
    // Other domain service methods...
}
```

### 6. Define Application Service Interfaces

Application service interfaces in `app/Application/Contracts/NewFeature/`:

```php
namespace App\Application\Contracts\NewFeature;

use App\Domain\NewFeature\Models\Entity;

interface EntityManagementServiceInterface
{
    public function getAllEntities(array $filters = []): array;
    public function getEntityById(int $id): Entity;
    public function createEntity(array $data): Entity;
    public function updateEntity(int $id, array $data): Entity;
    public function deleteEntity(int $id): bool;
}
```

### 7. Implement Application Services

Application services in `app/Application/Services/`:

```php
namespace App\Application\Services;

use App\Application\Contracts\NewFeature\EntityManagementServiceInterface;
use App\Domain\NewFeature\Models\Entity;
use App\Domain\NewFeature\Services\EntityService;

class EntityManagementService implements EntityManagementServiceInterface
{
    public function __construct(
        private readonly EntityService $entityService
    ) {}

    public function getAllEntities(array $filters = []): array
    {
        $entities = $this->entityService->findBySearchCriteria($filters);
        
        // Convert domain objects to array representation
        return array_map(function (Entity $entity) {
            return $entity->toArray();
        }, $entities);
    }
    
    // Other application service methods...
}
```

### 8. Implement Repository in Infrastructure Layer

Repository implementations in `app/Infrastructure/NewFeature/Repositories/`:

```php
namespace App\Infrastructure\NewFeature\Repositories;

use App\Domain\NewFeature\Models\Entity;
use App\Domain\NewFeature\Repositories\EntityRepositoryInterface;
use App\Domain\NewFeature\ValueObjects\EntityId;
use App\Infrastructure\NewFeature\Mappers\EntityMapper;
use App\Models\EntityModel; // Eloquent model

class EntityRepository implements EntityRepositoryInterface
{
    public function __construct(
        private readonly EntityMapper $mapper
    ) {}

    public function findById(EntityId $id): ?Entity
    {
        $model = EntityModel::find($id->getValue());
        
        if (!$model) {
            return null;
        }
        
        return $this->mapper->toDomain($model);
    }
    
    // Other repository methods...
}
```

### 9. Create Data Mappers

Data mappers in `app/Infrastructure/NewFeature/Mappers/`:

```php
namespace App\Infrastructure\NewFeature\Mappers;

use App\Domain\NewFeature\Models\Entity;
use App\Domain\NewFeature\ValueObjects\EntityId;
use App\Models\EntityModel; // Eloquent model
use DateTime;

class EntityMapper
{
    public function toDomain(EntityModel $model): Entity
    {
        return new Entity(
            new EntityId($model->id),
            $model->name,
            // Map other properties...
            new DateTime($model->created_at)
        );
    }

    public function toPersistence(Entity $entity): array
    {
        return [
            'id' => $entity->getId()->getValue(),
            'name' => $entity->getName(),
            // Map other properties...
        ];
    }
}
```

### 10. Create API Controller

API controllers in `app/Http/Controllers/`:

```php
namespace App\Http\Controllers;

use App\Application\Contracts\NewFeature\EntityManagementServiceInterface;
use App\Domain\NewFeature\Exceptions\EntityNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class EntityController extends Controller
{
    public function __construct(
        private readonly EntityManagementServiceInterface $entityService
    ) {}

    protected function handleException(Exception $e): JsonResponse
    {
        if ($e instanceof EntityNotFoundException) {
            return response()->json([
                'message' => 'Not found',
                'error' => $e->getMessage()
            ], 404);
        }

        // Log unexpected errors
        \Log::error('Entity management error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'message' => 'An unexpected error occurred',
            'error' => $e->getMessage()
        ], 500);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];
            
            // Apply filters from request...
            
            $result = $this->entityService->getAllEntities($filters);
            
            return response()->json($result);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                // Validation rules...
            ]);

            $result = $this->entityService->createEntity($validated);

            return response()->json([
                'message' => 'Entity created successfully',
                'entity' => $result->toArray()
            ], 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    // Other controller methods (show, update, destroy)...
}
```

### 11. Register Service Bindings

Register your service bindings in a service provider:

```php
namespace App\Providers;

use App\Application\Contracts\NewFeature\EntityManagementServiceInterface;
use App\Application\Services\EntityManagementService;
use App\Domain\NewFeature\Repositories\EntityRepositoryInterface;
use App\Infrastructure\NewFeature\Repositories\EntityRepository;
use Illuminate\Support\ServiceProvider;

class NewFeatureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EntityRepositoryInterface::class, EntityRepository::class);
        $this->app->bind(EntityManagementServiceInterface::class, EntityManagementService::class);
    }
}
```

### 12. Define API Routes

Define your API routes in `routes/api.php`:

```php
use App\Http\Controllers\EntityController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/entities', [EntityController::class, 'index']);
    Route::post('/entities', [EntityController::class, 'store']);
    Route::get('/entities/{id}', [EntityController::class, 'show']);
    Route::put('/entities/{id}', [EntityController::class, 'update']);
    Route::delete('/entities/{id}', [EntityController::class, 'destroy']);
});
```

## Best Practices

1. **Keep Domain Logic Pure**: Domain models should contain business logic, not infrastructure concerns.
2. **Use Value Objects**: For concepts that are defined by their attributes rather than identity.
3. **Repository Pattern**: Repositories abstract the data access layer and return domain objects.
4. **Service Layer**: Domain services encapsulate business logic that doesn't belong to any specific entity.
5. **Exception Handling**: Create domain-specific exceptions for better error handling.
6. **Validation**: Validate input at the controller level before passing to the application services.
7. **Immutability**: Make value objects immutable to prevent unexpected side effects.
8. **Dependency Injection**: Use constructor injection to provide dependencies.

## Testing

1. **Unit Tests**: Test domain models and services in isolation.
2. **Integration Tests**: Test repositories with a test database.
3. **API Tests**: Test controllers with HTTP requests.

## DigiPharmacy-Specific Conventions

### Naming Conventions

1. **Controllers**: Use singular entity name + `Controller` (e.g., `CustomerController`, `ProductController`)
2. **Domain Models**: Use singular entity name (e.g., `Customer`, `Product`)
3. **Repositories**: Use singular entity name + `Repository` (e.g., `CustomerRepository`)
4. **Interfaces**: Use `Interface` suffix (e.g., `CustomerRepositoryInterface`)
5. **Services**: Use domain name + `Service` or `ManagementService` (e.g., `CustomerService`, `SalesManagementService`)
6. **Value Objects**: Use descriptive names that reflect their purpose (e.g., `CustomerId`, `PaymentDetails`)
7. **Exceptions**: Use descriptive names that reflect the error condition (e.g., `CustomerNotFoundException`)

### Coding Standards

1. **Type Declarations**: Always use strict type declarations for method parameters and return types
2. **Constructor Property Promotion**: Use PHP 8's constructor property promotion for cleaner code
3. **Readonly Properties**: Use readonly properties where appropriate to enforce immutability
4. **Method Naming**: Use `get`, `find`, `create`, `update`, `delete` prefixes for standard CRUD operations
5. **Validation**: Always validate input data at the controller level
6. **Error Handling**: Use domain-specific exceptions and handle them in the controller

## Eloquent Model Integration

Eloquent models serve as the persistence layer in our architecture. Here's how they integrate with domain models:

### 1. Eloquent Models Location

Eloquent models are stored in `app/Models/` directory and follow Laravel conventions:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address'
    ];
    
    // Relationships
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
```

### 2. Data Mappers

Data mappers are responsible for transforming Eloquent models to domain models and vice versa:

```php
namespace App\Infrastructure\Customer\Mappers;

use App\Domain\Customer\Models\Customer as CustomerDomain;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Models\Customer as CustomerEloquent;
use DateTime;

class CustomerMapper
{
    public function toDomain(CustomerEloquent $model): CustomerDomain
    {
        return new CustomerDomain(
            new CustomerId($model->id),
            $model->name,
            $model->phone,
            $model->email,
            $model->address,
            new DateTime($model->created_at)
        );
    }

    public function toPersistence(CustomerDomain $customer): array
    {
        return [
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
            'phone' => $customer->getPhone(),
            'address' => $customer->getAddress()
        ];
    }
}
```

### 3. Repository Implementation with Eloquent

```php
namespace App\Infrastructure\Customer\Repositories;

use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Infrastructure\Customer\Mappers\CustomerMapper;
use App\Models\Customer as CustomerEloquent;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(
        private readonly CustomerMapper $mapper
    ) {}

    public function findById(CustomerId $id): ?Customer
    {
        $model = CustomerEloquent::find($id->getValue());
        
        if (!$model) {
            return null;
        }
        
        return $this->mapper->toDomain($model);
    }

    public function save(Customer $customer): Customer
    {
        $data = $this->mapper->toPersistence($customer);
        
        if ($customer->getId()->getValue() === 0) {
            // Create new record
            $model = CustomerEloquent::create($data);
        } else {
            // Update existing record
            $model = CustomerEloquent::find($customer->getId()->getValue());
            $model->update($data);
        }
        
        return $this->mapper->toDomain($model);
    }
    
    // Other repository methods...
}
```

## Service Provider Registration

Register your service bindings in a dedicated service provider for your feature. Create a new service provider in `app/Providers/` directory:

```php
namespace App\Providers;

use App\Application\Contracts\Customer\CustomerManagementServiceInterface;
use App\Application\Services\CustomerManagementService;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use App\Domain\Customer\Services\CustomerService;
use App\Infrastructure\Customer\Repositories\CustomerRepository;
use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        
        // Bind services
        $this->app->bind(CustomerManagementServiceInterface::class, CustomerManagementService::class);
        
        // Register singleton services if needed
        // $this->app->singleton(SomeService::class);
    }
}
```

Then register this service provider in `config/app.php`:

```php
'providers' => [
    // Other service providers...
    App\Providers\CustomerServiceProvider::class,
],
```

## Common Gotchas and Solutions

### 1. Circular Dependencies

**Problem**: Services depending on each other creating circular dependencies.

**Solution**: Use the mediator pattern or event-driven communication between services.

### 2. Anemic Domain Models

**Problem**: Domain models with no behavior, just getters and setters.

**Solution**: Move business logic from services into domain models where it belongs.

### 3. Repository Return Types

**Problem**: Inconsistent return types from repositories.

**Solution**: Always return domain models or collections of domain models, never Eloquent models.

### 4. Direct Eloquent Model Access in Controllers

**Problem**: Controllers directly accessing Eloquent models, bypassing the domain and application layers, which violates DDD principles and creates tight coupling between presentation and persistence layers.

**Solution**: 
- Always use application services to interact with domain models
- Never import or use Eloquent models directly in controllers
- Pass all data through the application service layer
- Use DTOs or simple arrays for data transfer between layers

**Bad Practice (Avoid):**
```php
use App\Models\Customer; // Importing Eloquent model directly

class CustomerController extends Controller
{
    public function index()
    {
        // Direct access to Eloquent model
        $customers = Customer::latest()->get();
        return response()->json($customers);
    }
}
```

**Good Practice (Recommended):**
```php
use App\Application\Contracts\Customer\CustomerManagementServiceInterface;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerManagementServiceInterface $customerService
    ) {}
    
    public function index()
    {
        // Access data through application service
        $customers = $this->customerService->getAllCustomers();
        return response()->json($customers);
    }
}
```

### 5. Transaction Management

**Problem**: Handling database transactions across multiple repositories.

**Solution**: Use a Unit of Work pattern or handle transactions at the application service level:

```php
public function createCustomerWithRelatedEntities(array $data): Customer
{
    return DB::transaction(function () use ($data) {
        $customer = $this->customerService->createCustomer(
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['address'] ?? null
        );
        
        // Create related entities...
        
        return $customer;
    });
}
```

### 5. Validation in Multiple Layers

**Problem**: Duplicating validation logic in controllers and services.

**Solution**: Use form request validation in controllers and domain-specific validation in domain models.

### 6. Handling Pagination

**Problem**: Returning paginated results while maintaining DDD principles.

**Solution**: Return a specialized DTO that includes both domain objects and pagination metadata:

```php
class PaginatedResult
{
    public function __construct(
        private readonly array $items,
        private readonly int $total,
        private readonly int $perPage,
        private readonly int $currentPage
    ) {}
    
    // Getters...
    
    public function toArray(): array
    {
        return [
            'data' => $this->items,
            'meta' => [
                'total' => $this->total,
                'per_page' => $this->perPage,
                'current_page' => $this->currentPage,
                'last_page' => ceil($this->total / $this->perPage)
            ]
        ];
    }
}
```

## Example Implementation

For a complete example, refer to the Sales API implementation in the codebase, which follows this pattern.

## Quick Reference for New API Implementation

When implementing a new API, follow these steps in order:

1. Define domain models and value objects
2. Create repository interfaces
3. Implement domain services
4. Define application service interfaces
5. Implement application services
6. Create Eloquent models (if not already existing)
7. Implement data mappers
8. Implement repositories
9. Create API controllers
10. Register service bindings
11. Define API routes

---

By following this guide, you can implement new APIs in the DigiPharmacy application that adhere to Domain-Driven Design principles and maintain consistency with the existing codebase.

## Container Development Environment

### Container Management with Nerdctl

DigiPharmacy menggunakan nerdctl untuk mengelola container development environment. Berikut adalah container yang digunakan:

```bash
# List semua container yang sedang berjalan
nerdctl ps

# Output contoh:
CONTAINER ID    IMAGE                            COMMAND                   CREATED           STATUS    PORTS                                                  NAMES
abc123def456    digipharmacy-app                "docker-php-entrypoi…"   2 hours ago       Up        0.0.0.0:8000->8000/tcp                               digipharmacy-app-1
def456ghi789    mysql:8.0                       "docker-entrypoint.s…"   2 hours ago       Up        0.0.0.0:3306->3306/tcp                               digipharmacy-db-1
ghi789jkl012    node:18                         "docker-entrypoint.s…"   2 hours ago       Up        0.0.0.0:5173->5173/tcp                               digipharmacy-app-1
```

### Mengakses PHP Container

```bash
# Masuk ke container PHP
nerdctl exec -it digipharmacy-app-1 bash

# Menjalankan perintah PHP Artisan
nerdctl exec digipharmacy-app-1 php artisan <command>

# Contoh:
nerdctl exec digipharmacy-app-1 php artisan migrate
nerdctl exec digipharmacy-app-1 php artisan route:list
nerdctl exec digipharmacy-app-1 php artisan make:migration create_products_table

# Menjalankan Composer
nerdctl exec digipharmacy-app-1 composer <command>

# Contoh:
nerdctl exec digipharmacy-app-1 composer install
nerdctl exec digipharmacy-app-1 composer require package/name
```

### Mengakses Node Container

```bash
# Masuk ke container Node
nerdctl exec -it digipharmacy-app-1 bash

# Menjalankan perintah NPM
nerdctl exec digipharmacy-app-1 npm <command>

# Contoh:
nerdctl exec digipharmacy-app-1 npm install
nerdctl exec digipharmacy-app-1 npm run dev
nerdctl exec digipharmacy-app-1 npm run build
```

### Mengakses MySQL Container

```bash
# Masuk ke MySQL CLI
nerdctl exec -it digipharmacy-db-1 mysql -u root -p

# Mengeksekusi query MySQL dari luar container
nerdctl exec digipharmacy-db-1 mysql -u root -p"password" -e "SELECT * FROM database.table"

# Backup database
nerdctl exec digipharmacy-db-1 mysqldump -u root -p"password" database > backup.sql

# Restore database
nerdctl exec -i digipharmacy-db-1 mysql -u root -p"password" database < backup.sql
```

### Tips Penggunaan Container

1. **Logging**
```bash
# Melihat log container
nerdctl logs digipharmacy-app-1
nerdctl logs digipharmacy-db-1

# Melihat log secara real-time
nerdctl logs -f digipharmacy-app-1
```

2. **Restart Container**
```bash
# Restart satu container
nerdctl restart digipharmacy-app-1

# Restart semua container
nerdctl restart $(nerdctl ps -q)
```

3. **Mengelola Cache**
```bash
# Membersihkan cache Laravel di dalam container
nerdctl exec digipharmacy-app-1 php artisan cache:clear
nerdctl exec digipharmacy-app-1 php artisan config:clear
nerdctl exec digipharmacy-app-1 php artisan view:clear

# Membersihkan cache Node di dalam container
nerdctl exec digipharmacy-app-1 npm cache clean --force
```

4. **Menjalankan Test**
```bash
# Menjalankan PHPUnit test
nerdctl exec digipharmacy-app-1 php artisan test

# Menjalankan Jest test
nerdctl exec digipharmacy-app-1 npm run test
```

5. **Troubleshooting**
```bash
# Memeriksa status container
nerdctl ps -a

# Memeriksa penggunaan resource
nerdctl stats

# Memeriksa konfigurasi container
nerdctl inspect digipharmacy-app-1
```

### Environment Variables

Container menggunakan environment variables yang didefinisikan dalam file `.env`. Pastikan file ini dikonfigurasi dengan benar untuk development environment:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=digipharmacy-db-1
DB_PORT=3306
DB_DATABASE=digipharmacy
DB_USERNAME=root
DB_PASSWORD=your_password

# Node
NODE_ENV=development
VITE_API_URL=http://localhost:8000
```

### Container Networks

Container terhubung dalam network yang sama, memungkinkan komunikasi antar container menggunakan nama container sebagai hostname:

```bash
# Melihat networks
nerdctl network ls

# Memeriksa detail network
nerdctl network inspect digipharmacy_default
```

### Data Persistence

Volume digunakan untuk menyimpan data persisten seperti database dan file upload:

```bash
# Melihat volumes
nerdctl volume ls

# Memeriksa detail volume
nerdctl volume inspect digipharmacy_mysql_data
```
