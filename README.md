# DigiPharmacy Wep App
Berikut adalah kode yang diminta:

```markdown
The DigiPharmacy Web App is an example platform designed using Docker, Laravel, React, and TypeScript. It should be able to enable users to browse a wide range of medications, place orders, and manage prescriptions with ease. The application integrates secure payment gateways, real-time inventory management, and user-friendly interfaces to enhance the overall customer experience.


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

# Menghentikan container
nerdctl compose down

# Rebuild specific service
nerdctl compose up -d --build digipharmacy-app-1
```


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

