# Premium Cut Backend

## Project Description
Premium Cut is a comprehensive e-commerce platform backend providing a robust API for managing products, categories, shopping carts, orders, and user profiles. It features advanced product filtering, multi-currency support, and a complete administrative dashboard.

## Key Features
- **Product Management**: Support for variants, options, prices, and attribute-based filtering.
- **Advanced Filtering**: Strategy-pattern based filtering pipeline for scalable search and discovery.
- **Cart & Order Flow**: Complete checkout process with guest and authenticated cart support.
- **Payment Integration**: Integration with Tap payments (including webhooks and redirects).
- **Geolocation & Multi-currency**: Automatic currency detection based on user IP.
- **Admin Dashboard**: Statistical overviews and management interfaces for orders and products.
- **Caching**: Optimized cache key generation and prefix-based invalidation.

## Tech Stack
- **Framework**: Laravel 10
- **Authentication**: JWT (tymon/jwt-auth)
- **Database**: MySQL
- **Image Processing**: Intervention Image
- **Geolocation**: GeoIP2

## Installation Instructions

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd premiumcut-backend
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Environment Setup**:
   Copy the example environment file and configure your variables:
   ```bash
   cp .env.example .env
   ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Generate JWT Secret**:
   ```bash
   php artisan jwt:secret
   ```

6. **Database Migration**:
   Run the migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

7. **Storage Link**:
   ```bash
   php artisan storage:link
   ```

## API Endpoint Overview
The API is structured around several main resources. All endpoints are prefixed with `/api`.

- **Authentication**: `/api/auth/*` (login, register, logout, password reset)
- **Products**: `/api/products/*` (index, show, variants, options)
- **Categories**: `/api/category/*` and `/api/sub-category/*`
- **Cart**: `/api/cart/*` (guest and authenticated support)
- **Orders**: `/api/order/*` (checkout, history)
- **Admin**: `/api/admin/*` and `/api/dashboard/*` (requires admin role)

Full route names follow the `resource.action` pattern (e.g., `products.index`, `cart.add`).

## Authentication Setup
The project uses JWT for API authentication. 
- **Guard**: `api` (uses `jwt` driver)
- **Middleware**: Use `auth:api` to protect routes.
- **Tokens**: Pass the token in the `Authorization: Bearer <token>` header.

## Development Workflow
- **Coding Standards**: Use Laravel Pint for code styling.
  ```bash
  ./vendor/bin/pint
  ```
- **Testing**: Run tests using Artisan.
  ```bash
  php artisan test
  ```
- **Architecture**: Follows the Repository/Service pattern to decouple business logic from the persistence layer.

## Deployment Instructions
1. Ensure `APP_ENV` is set to `production`.
2. Run `composer install --optimize-autoloader --no-dev`.
3. Cache configurations and routes:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Set up a task scheduler for cron jobs.
5. Ensure the `storage` and `bootstrap/cache` directories are writable by the web server.
