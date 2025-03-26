# DigiPharmacy

## Architecture Overview

DigiPharmacy menggunakan kombinasi Domain-Driven Design (DDD) dan Progressive Architecture untuk memastikan sistem yang maintainable dan scalable.

### Core Principles

1. **Domain-Driven Design (DDD)**
   - Fokus pada domain bisnis
   - Ubiquitous language
   - Bounded contexts
   - Rich domain models

2. **Progressive Architecture**
   - Evolusi bertahap
   - Interface-first approach
   - Event-driven system
   - Forward compatibility

### Project Structure

```
app/
├── Domain/               # Core business logic (DDD)
│   ├── Auth/
│   │   ├── Models/       # Domain entities
│   │   ├── ValueObjects/ # Value objects
│   │   ├── Events/       # Domain events
│   │   ├── Repositories/ # Repository interfaces
│   │   └── Services/     # Domain services
│   ├── Inventory/
│   └── Order/
│
├── Application/          # Use cases & orchestration
│   ├── Contracts/        # Service interfaces
│   └── Services/         # Application services
│
├── Infrastructure/       # Technical implementations
│   ├── Persistence/      # Database implementations
│   ├── Events/           # Event system
│   └── External/         # External services
│
└── Interface/            # API & UI layer
    ├── Http/
    │   ├── Controllers/
    │   └── Middleware/
    └── Console/
```

### Layer Responsibilities

1. **Domain Layer** (`app/Domain/`)
   - Core business logic dan rules
   - Pure PHP, no framework dependencies
   - Entities, value objects, events
   - Repository interfaces

2. **Application Layer** (`app/Application/`)
   - Service interfaces (contracts)
   - Use case orchestration
   - Transaction management
   - Cross-domain coordination

3. **Infrastructure Layer** (`app/Infrastructure/`)
   - Framework integration
   - Database implementations
   - Event handling
   - External services

4. **Interface Layer** (`app/Interface/`)
   - HTTP controllers
   - API endpoints
   - Request validation
   - Response formatting

### Evolution Strategy

1. **Phase 1: MVP (0-3 months)**
   - Simple service implementations
   - Basic domain models
   - Direct database access

2. **Phase 2: Growth (3-6 months)**
   - Event-driven patterns
   - Rich domain models
   - Caching strategies

3. **Phase 3: Stabilization (6-12 months)**
   - Complex use cases
   - Advanced event handling
   - Performance optimizations

4. **Phase 4: Enterprise (12+ months)**
   - Distributed transactions
   - Message queues
   - Advanced monitoring



## Requirements
- PHP 8.2
- Node.js 18+
- MySQL 5.7
- Composer
- nerdctl

## Development Setup

1. **Clone repository**
```bash
git clone <repository-url>
cd digipharmacy
```

2. **Environment Setup**
```bash
Copy .env file
cp .env.example .env
Set environment variables
export APP_ENV=local # untuk development
atau
export APP_ENV=production # untuk production
```

3. **Container Build & Run**
```bash
# Pertama kali atau saat ada perubahan Dockerfile/dependencies
nerdctl compose up --build

# Untuk menjalankan container yang sudah ada
nerdctl compose up -d
```


4. **Useful Commands**
```bash
# Melihat logs
nerdctl compose logs -f digipharmacy-app-1

# Masuk ke container
nerdctl compose exec digipharmacy-app-1 sh

# Melihat status container
nerdctl compose ps
```

## Flow Development API

Untuk menambahkan API baru, ikuti langkah-langkah berikut sesuai Progressive Architecture:

### 1. Definisi Interface (Contracts)

Buat interface di `app/Application/Contracts/<Module>/` untuk mendefinisikan kontrak service:

```php
namespace App\Application\Contracts\<Module>;

interface ServiceInterface
{
    public function create(array $data): array;
    public function update(int $id, array $data): array;
    public function delete(int $id): void;
    public function getAll(): array;
}
```

### 2. Domain Events

1. **Base Event** di `app/Infrastructure/Events/DomainEvent.php`:
```php
abstract class DomainEvent
{
    public function __construct(
        public readonly string $id,
        public readonly \DateTime $occurredOn
    ) {
        $this->id = Str::uuid();
        $this->occurredOn = now();
    }

    abstract public function getEventName(): string;
}
```

2. **Specific Events** di `app/Infrastructure/Events/<Module>/`:
```php
class EntityCreated extends DomainEvent
{
    public function __construct(
        public readonly Entity $entity
    ) {
        parent::__construct();
    }

    public function getEventName(): string
    {
        return 'entity.created';
    }
}
```

### 3. Domain Layer

1. **Value Objects** di `app/Domain/<Module>/ValueObjects/`:
```php
class EntityId
{
    public function __construct(
        private readonly string $value
    ) {
        $this->validate($value);
    }

    private function validate(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException('ID cannot be empty');
        }
    }
}
```

2. **Repository Interface** di `app/Domain/<Module>/Repositories/`:
```php
interface EntityRepositoryInterface
{
    public function findById(EntityId $id): ?Entity;
    public function save(Entity $entity): void;
    public function delete(EntityId $id): void;
}
```

3. **Domain Service** di `app/Domain/<Module>/Services/`:
```php
class EntityService implements EntityServiceInterface
{
    public function __construct(
        private EntityRepositoryInterface $repository
    ) {}

    public function create(array $data): array
    {
        $entity = new Entity($data);
        $this->repository->save($entity);
        Event::dispatch(new EntityCreated($entity));
        return $entity->toArray();
    }
}
```

### 4. Infrastructure Layer

1. **Repository Implementation** di `app/Infrastructure/<Module>/Repositories/`:
```php
class EntityRepository implements EntityRepositoryInterface
{
    public function findById(EntityId $id): ?Entity
    {
        $model = EloquentModel::find($id->value());
        return $model ? EntityMapper::toDomain($model) : null;
    }
}
```

2. **Event Listeners** di `app/Infrastructure/<Module>/Listeners/`:
```php
class EntityCreatedListener
{
    public function handle(EntityCreated $event): void
    {
        // Handle the event
        Log::info('Entity created: ' . $event->entity->id);
    }
}
```

### 5. Interface Layer

1. **Controller** di `app/Http/Controllers/<Module>/`:
```php
class EntityController
{
    public function __construct(
        private EntityServiceInterface $service
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string'
        ]);

        $result = $this->service->create($validated);

        return response()->json($result, 201);
    }
}
```

2. **Routes** di `routes/api.php`:
```php
Route::prefix('v1')->group(function () {
    Route::apiResource('entities', EntityController::class);
});
```

### 6. Service Provider

Register di `app/Providers/<Module>ServiceProvider.php`:
```php
class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            EntityServiceInterface::class,
            EntityService::class
        );

        $this->app->bind(
            EntityRepositoryInterface::class,
            EntityRepository::class
        );
    }
}
```

## Development Checklist

### 1. Preparation
- [ ] Identifikasi domain/module baru
- [ ] Review existing code yang terkait
- [ ] Definisikan interface dan kontrak

### 2. Core Implementation
- [ ] Buat interface service di `/Application/Contracts`
- [ ] Buat base event (jika belum ada)
- [ ] Buat specific events
- [ ] Implementasi value objects
- [ ] Implementasi repository interface
- [ ] Implementasi domain service

### 3. Infrastructure
- [ ] Implementasi repository
- [ ] Buat event listeners
- [ ] Setup database migrations (jika perlu)

### 4. Interface
- [ ] Buat controller
- [ ] Definisikan routes
- [ ] Implementasi request validation

### 5. Dependency Injection
- [ ] Register interface bindings di service provider
- [ ] Register event listeners

### 6. Testing
- [ ] Unit tests untuk domain logic
- [ ] Integration tests untuk API
- [ ] Event handling tests

### 7. Documentation
- [ ] Update API documentation
- [ ] Update README jika perlu
- [ ] Document any new patterns used

### 2. Infrastructure Layer (Technical Implementation)

Infrastructure layer berisi implementasi teknis dari kontrak yang didefinisikan di domain layer.

1. **Buat Model Mapper** - *Transformer antara domain dan persistence model*
   Mapper bertugas:
   - Mengkonversi Eloquent model ke Domain model
   - Mengkonversi Domain model ke Eloquent model
   - Handling relasi antar model
   - Transformasi tipe data jika diperlukan di `app/Infrastructure/<Module>/Mappers/`
   ```php
   namespace App\Infrastructure\<Module>\Mappers;
   
   class YourModelMapper
   {
       public function toDomain(EloquentModel $eloquent): DomainModel
       {
           return new DomainModel(
               name: $eloquent->name,
               id: $eloquent->id
           );
       }
   }
   ```

2. **Buat Repository Implementation** - *Implementasi akses data konkrit*
   Repository berisi:
   - Implementasi method-method dari interface
   - Query builder untuk database
   - Caching strategy jika diperlukan
   - Error handling spesifik database di `app/Infrastructure/<Module>/Repositories/`
   ```php
   namespace App\Infrastructure\<Module>\Repositories;
   
   class YourModelRepository implements YourModelRepositoryInterface
   {
       public function __construct(
           private YourModelMapper $mapper
       ) {}
       
       // Implementation methods
   }
   ```

### 3. Container & Service Provider (Dependency Management)

Container dan Service Provider menangani dependency injection dan lifecycle object.

1. **Buat Container** - *Factory untuk service dan dependencies*
   Container bertugas:
   - Instantiasi service dan dependencies
   - Menyediakan singleton instances jika diperlukan
   - Mengatur lifecycle object
   - Menyederhanakan dependency graph di `app/Infrastructure/Container/`
   ```php
   namespace App\Infrastructure\Container;
   
   class YourContainer
   {
       public function getYourService(): YourService
       {
           return new YourService(
               $this->getYourRepository()
           );
       }
       
       public function getYourRepository(): YourRepositoryInterface
       {
           return new YourRepository(
               $this->getYourMapper()
           );
       }
   }
   ```

2. **Buat/Update Service Provider** - *Registrasi service ke Laravel*
   Provider bertugas:
   - Binding interface ke implementasi
   - Registrasi singleton services
   - Konfigurasi awal service
   - Boot-time initialization di `app/Providers/`
   ```php
   namespace App\Providers;
   
   class YourDomainServiceProvider extends ServiceProvider
   {
       public function register(): void
       {
           $this->app->bind(YourRepositoryInterface::class, function () {
               return $this->container->getYourRepository();
           });
           
           $this->app->bind(YourService::class, function () {
               return $this->container->getYourService();
           });
       }
   }
   ```

3. **Daftarkan Service Provider** - *Aktivasi service di aplikasi*
   Pendaftaran di config:
   - Menambahkan provider ke application providers
   - Mengatur urutan loading
   - Menentukan environment (local/production) di `config/app.php`
   ```php
   'providers' => ServiceProvider::defaultProviders()->merge([
       // ...
       App\Providers\YourDomainServiceProvider::class,
   ])->toArray(),
   ```

### 4. Controller & Route (Interface Layer)

Interface layer menangani HTTP request/response dan routing.

1. **Buat Controller** - *Handler untuk HTTP request*
   Controller bertugas:
   - Menerima HTTP request
   - Validasi input
   - Memanggil domain service
   - Memformat response
   - Error handling HTTP level di `app/Http/Controllers/`
   ```php
   namespace App\Http\Controllers;
   
   class YourController extends Controller
   {
       public function __construct(
           private YourService $service
       ) {}
       
       public function index()
       {
           $result = $this->service->getAll();
           return response()->json($result);
       }
   }
   ```

2. **Tambahkan Route** - *Definisi endpoint API*
   Route configuration:
   - HTTP method (GET, POST, PUT, DELETE)
   - URL pattern
   - Middleware (auth, throttle, cors)
   - Route grouping
   - Route naming di `routes/api.php`
   ```php
   Route::middleware('auth:sanctum')->group(function () {
       Route::get('/your-endpoint', [YourController::class, 'index']);
   });
   ```

Dengan mengikuti flow ini, kita memastikan:
1. Separation of concerns yang baik
2. Dependency injection yang terstruktur
3. Testability yang baik
4. Kode yang maintainable dan scalable

## Architecture Evolution

DigiPharmacy menggunakan Progressive Architecture untuk mendukung evolusi sistem secara bertahap:

### 1. Project Structure
```
app/
├── Domain/           # Core business logic & rules
│   ├── Auth/
│   ├── Inventory/
│   └── Order/
├── Application/      # Use cases & application services
│   ├── Contracts/    # Interfaces
│   └── Services/     # Integration services
└── Infrastructure/   # Technical implementations
    └── Events/       # Event system
```

### 2. Development Phases

#### Phase 1: MVP (0-3 months)
- Simple Integration Services
- Basic Domain Events
- Repository Pattern
- Focus: Core functionality

#### Phase 2: Growth (3-6 months)
- Event-Driven Architecture
- Async Operations
- Enhanced Domain Logic
- Focus: Scalability

#### Phase 3: Stabilization (6-12 months)
- Use Case Pattern
- Complex Orchestration
- Advanced Event Handling
- Focus: Maintainability

#### Phase 4: Enterprise (12+ months)
- Saga Pattern
- Message Queue
- Distributed Transactions
- Focus: Reliability

### 3. Progressive Implementation

#### Services
```php
// Interface ready for evolution
interface OrderServiceInterface {
    public function createOrder(array $data): Order;
}

// Simple first implementation
class OrderService implements OrderServiceInterface {
    public function createOrder(array $data): Order {
        return Order::create($data);
    }
}
```

#### Events
```php
// Base event structure
abstract class DomainEvent {
    public function __construct(
        public readonly string $id,
        public readonly \DateTime $occurredOn
    ) {}
}

// Simple event implementation
class OrderCreated extends DomainEvent {
    public function __construct(
        public readonly Order $order
    ) {
        parent::__construct(Str::uuid(), now());
    }
}
```

### 4. Benefits

1. **Minimal Refactoring**
   - Forward-compatible interfaces
   - Extensible structure
   - Event system ready

2. **Flexible Evolution**
   - Gradual complexity increase
   - No major restructuring
   - Backward compatible

3. **Clean Migration Path**
   - Progressive enhancement
   - No breaking changes
   - Step-by-step improvement

4. **Team Scalability**
   - Clear documentation
   - Consistent standards
   - Easy onboarding

# Menghentikan container
nerdctl compose down

# Rebuild specific service
nerdctl compose up -d --build digipharmacy-app-1

## Project Structure

├── resources/
│ ├── js/
│ │ ├── components/
│ │ │ └── App.tsx
│ │ └── app.tsx
│ └── views/
│ └── app.blade.php
├── docker-compose.yml
├── Dockerfile
├── package.json
├── tsconfig.json
└── vite.config.js


## Development vs Production

### Development Mode
```bash
export APP_ENV=local
# Pertama kali atau saat ada perubahan Dockerfile
nerdctl compose up --build

# Untuk development sehari-hari
nerdctl compose up -d

# Setelah container berjalan, jalankan npm run dev
nerdctl exec -it digipharmacy-app-1 sh
lsof -i :5173
kill -9 <PID>
npm run dev
```

Development mode akan:
- Menjalankan Laravel server di port 8000
- Menjalankan Vite dev server di port 5173
- Mengaktifkan hot reload untuk React components
- Memungkinkan debugging

Pastikan untuk menjalankan `npm run dev` setelah container up karena:
1. Container perlu fully running terlebih dahulu
2. Vite dev server perlu berjalan untuk hot reload
3. Development dependencies perlu ter-install dengan benar

### Production Mode
```bash
export APP_ENV=production
# Build ulang jika ada perubahan di Dockerfile atau dependencies
nerdctl compose up --build

# Jika tidak ada perubahan, cukup
nerdctl compose up -d
```

- Assets di-build dan diminifikasi
- Cache dioptimalkan
- Performa dioptimalkan
- Akses: http://localhost:8000

## Docker Configuration

### Multi-stage Build
Dockerfile menggunakan multi-stage build untuk optimasi:
1. Build stage: Kompilasi assets React/TypeScript
2. Production stage: Setup PHP dan aplikasi

### Volume Mounts
- `.:/var/www`: Source code
- `/var/www/html/node_modules`: Node modules (preserved in container)

### Ports
- 8000: Laravel application
- 5173: Vite dev server
- 3306: MySQL database

## Tech Stack
- Laravel
- React
- TypeScript
- Vite
- MySQL
- Docker/nerdctl

## Notes
- Development mode menggunakan Vite dev server untuk hot reload
- Production mode menggunakan pre-built assets
- Database credentials dapat dikonfigurasi melalui environment variables
