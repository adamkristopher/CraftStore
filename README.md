# CraftShop

A modern e-commerce platform for craft supplies built with PHP using Test-Driven Development (TDD) principles.

## 🚀 Features

- **Modern PHP Architecture**: Built with PHP 8.1+ using PSR-4 autoloading
- **Test-Driven Development**: Comprehensive test suite with PHPUnit
- **Docker Support**: Containerized development environment
- **MVC Pattern**: Clean separation of concerns with Controllers, Models, and Views
- **Database Integration**: MySQL database with PDO for secure data access
- **Shopping Cart**: Full cart functionality with add/remove operations
- **Product Management**: Complete product catalog system
- **Custom Router**: Lightweight routing system for clean URLs

## 📋 Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Docker & Docker Compose (optional)

## 🛠️ Installation

### Option 1: Docker (Recommended)

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd craftshop
   ```

2. **Start the development environment**
   ```bash
   docker-compose up -d
   ```

3. **Install dependencies**
   ```bash
   docker-compose exec app composer install
   ```

4. **Run database migrations**
   ```bash
   docker-compose exec app php src/Database/Migration.php
   ```

5. **Access the application**
   - Application: http://localhost:8000
   - Database: localhost:3307

### Option 2: Local Development

1. **Clone and navigate to the project**
   ```bash
   git clone <repository-url>
   cd craftshop
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure database**
   - Create a MySQL database named `craftshop`
   - Update database credentials in your environment

4. **Run migrations**
   ```bash
   php src/Database/Migration.php
   ```

5. **Start the development server**
   ```bash
   php -S localhost:8000 -t public
   ```

## 🧪 Testing

This project follows Test-Driven Development practices with comprehensive test coverage.

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run tests with Docker
docker-compose exec app composer test
```

### Test Structure

- **Unit Tests**: Located in `tests/Unit/`
- **Test Coverage**: Configured to include all `src/` files
- **PHPUnit Configuration**: Defined in `phpunit.xml`

## 📁 Project Structure

```
craftshop/
├── public/                 # Web-accessible files
│   └── index.php          # Application entry point
├── src/                   # Application source code
│   ├── Controllers/       # HTTP request handlers
│   ├── Core/             # Core framework components
│   ├── Database/         # Database connection & migrations
│   ├── Exceptions/       # Custom exception classes
│   ├── Models/           # Data models
│   └── Services/         # Business logic services
├── tests/                # Test suite
│   └── Unit/            # Unit tests
├── views/               # View templates
│   └── cart/           # Cart-specific views
├── vendor/             # Composer dependencies
├── docker-compose.yml  # Docker configuration
├── Dockerfile         # PHP container definition
└── composer.json      # PHP dependencies
```

## 🛒 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Home page |
| GET | `/products` | List all products |
| GET | `/product/{id}` | Show specific product |
| GET | `/cart` | View shopping cart |
| POST | `/cart/add` | Add item to cart |
| POST | `/cart/remove` | Remove item from cart |

## 🗄️ Database

The application uses MySQL with the following key tables:
- `products` - Product catalog
- `cart_items` - Shopping cart data
- Additional tables managed through migrations

### Running Migrations

```bash
# With Docker
docker-compose exec app php src/Database/Migration.php

# Local development
php src/Database/Migration.php
```

## 🐳 Docker Configuration

### Services

- **app**: PHP 8.4 application server
- **mysql**: MySQL 8.0 database server

### Environment Variables

- `DB_HOST`: Database host (default: mysql)
- `DB_DATABASE`: Database name (default: craftshop)
- `DB_USERNAME`: Database user (default: root)
- `DB_PASSWORD`: Database password (default: secret)

## 🧩 Development

### Adding New Features

1. Write tests first (TDD approach)
2. Implement the feature
3. Ensure all tests pass
4. Update documentation

### Code Style

- Follow PSR-4 autoloading standards
- Use meaningful variable and method names
- Include proper error handling
- Write comprehensive tests

## 📝 License

This project is open source. Please check the license file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for your changes
4. Ensure all tests pass
5. Submit a pull request

## 📞 Support

For questions or support, please open an issue in the repository.

---

**Built with ❤️ using PHP, Docker, and Test-Driven Development**
