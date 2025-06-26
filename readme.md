# GoFrame

GoFrame is a lightweight PHP framework that helps you quickly build small to medium web projects. It comes with everything you need to organize your code, connect to a database, and create APIs easily.

## Main Features

- **Easy Structure:** Organize your code with folders for controllers, models, and core logic.
- **Simple Routing:** Set up URLs for your app and connect them to your code.
- **Database Ready:** Connects to MySQL and can create tables automatically.
- **Validation:** Check if the data sent to your app is correct.
- **Logging:** Save important messages or errors to log files.
- **JSON Output:** Sends responses in JSON format, perfect for APIs.
- **Environment Settings:** Use a `.env` file to keep your database and app settings safe.

## Project Structure

```
.env
public/
    index.php
src/
    Config/
    Controllers/
    Core/
    Models/
    routes/
vendor/
goframe-cli.php
composer.json
```

## Requirements

- PHP 8.0 or newer
- Composer
- MySQL

## Getting Started

1. **Clone the repository:**
   ```sh
   git clone https://github.com/yourusername/go-frame.git
   cd go-frame
   ```

2. **Install dependencies:**
   ```sh
   composer install
   ```

3. **Set up your environment:**
   - Edit the `.env` file with your database details.

4. **Start your server:**
   - Point your web server to the `public/` folder.

## How to Use

- **Routes:**  
  Set up your app's URLs in [`src/routes/web.php`](src/routes/web.php).

- **Controllers:**  
  Write your logic in controller files, like [`src/Controllers/UserController.php`](src/Controllers/UserController.php).

- **Models:**  
  Describe your database tables in model files, like [`src/Models/User.php`](src/Models/User.php).

- **Create Models Easily:**  
  Run the command below and answer the questions to generate a new model:
  ```sh
  php goframe-cli.php
  ```

## Example API Request

Send a new user to your API:
```sh
curl -X POST http://localhost/users \
     -H "Content-Type: application/json" \
     -d '{"id":"uuid","name":"John","email":"john@example.com"}'
```

## License

MIT

---

**Author:** Jairo Santos