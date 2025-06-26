# GoFrame

GoFrame is a simple and complete PHP framework for rapid development of small to medium-sized projects. It provides a minimal MVC structure, routing, database abstraction with automatic table synchronization, validation, logging, and JSON output handling.

## Features

- **MVC Structure**: Organized folders for Controllers, Models, and Core logic.
- **Routing**: Simple HTTP router with support for route parameters and RESTful methods.
- **Database Abstraction**: PDO-based singleton connection, automatic table synchronization, and CRUD operations.
- **Validation**: Utility for validating input data against model rules.
- **Logging**: File-based logging with support for log levels.
- **JSON Output**: Consistent API responses with status codes.
- **Environment Configuration**: Uses `.env` for secure configuration.

## Project Structure

```
.env
public/
    index.php
src/
    Config/
        Database.php
    Controllers/
        UserController.php
    Core/
        Router.php
        Helpers/
            Output.php
            Logger.php
            Validation.php
            Mysql/
    Models/
        User.php
    routes/
        web.php
vendor/
    (Composer dependencies)
goframe-cli.php
composer.json
```

## Requirements

- PHP 8.0 or higher
- Composer
- MySQL database

## Installation

1. **Clone the repository:**
   ```sh
   git clone https://github.com/yourusername/go-frame.git
   cd go-frame
   ```

2. **Install dependencies:**
   ```sh
   composer install
   ```

3. **Configure environment:**
   - Copy `.env.example` to `.env` (if needed) and set your database credentials.

4. **Set up web server:**
   - Point your web server's document root to the `public/` directory.

## Usage

### Routing

Define your routes in [`src/routes/web.php`](src/routes/web.php):

```php
$router->get('/users', [UserController::class, 'getAll']);
$router->post('/users', [UserController::class, 'create']);
```

### Controllers

Controllers handle HTTP requests and interact with models. Example: [`GoFrame\Controllers\UserController`](src/Controllers/UserController.php).

### Models

Models extend [`GoFrame\Core\Mysql\BaseModel`](src/Core/Mysql/BaseModel.php) and define table structure and columns. Example: [`GoFrame\Models\User`](src/Models/User.php).

### Database

Database connection is managed by [`GoFrame\Config\Database`](src/Config/Database.php) using PDO and environment variables.

### Logging

Use [`GoFrame\Core\Helpers\Logger`](src/Core/Helpers/Logger.php) for file-based logging.

### Validation

Use [`GoFrame\Core\Helpers\Validation`](src/Core/Helpers/Validation.php) to validate input data against model rules.

### CLI Model Generator

Generate new models interactively:
```sh
php goframe-cli.php
```

## Example API Request

```sh
curl -X POST http://localhost/users \
     -H "Content-Type: application/json" \
     -d '{"id":"uuid","name":"John","email":"john@example.com"}'
```

## License

MIT

---

**Author:** Jairo Santos  
**Project:**