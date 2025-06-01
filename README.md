# GP SYS Framework

GP SYS is HMVC based PHP Framework that simplifies your work in developing the web application.

## Table of Contents

 [Requirements](#requirements)
- [Installation](#installation)
- [Directory Structure](#directory-structure)
- [Getting Started](#getting-started)
- [Features](#features)
- [Classes](#classes)
  - [DBQuery](#dbquery)
  - [Database](#database)
  - [Model](#model)
  - [Events](#events)
- [Usage](#usage)
  - [Frame Queries](#frame-queries)
    - [Select Query](#select-query)
    - [Insert Query](#insert-query)
    - [Update Query](#update-query)
    - [Delete Query](#delete-query)
  - [Running Queries](#running-queries)
  - [Transaction Management](#transaction-management)
  - [Creating a Custom DB Driver](#creating-a-custom-db-driver)
  - [Creating an ORM Model](#creating-an-orm-model)
  - [Performing CRUD Operations with ORM Model](#performing-crud-operations-with-orm-model)
  - [Handling Relationships](#handling-relationships)
  - [Events](#events)
  - [Creating and Loading ORM Models with Relations](#creating-and-loading-orm-models-with-relations)
- [Example](#example)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)
- [Author](#author)

---

## Requirements

- PHP 8.1 or higher
- Composer (optional but recommended for autoloading).

---


## Installation

-  **Clone the template:**
   ```bash
   git clone https://github.com/periyandavar/gp_sys_template.git
   cd gp_sys_template

-  **Install dependencies**:
```
composer install
```
- **Init the application**:
```
php vendor\bin\run init -e=dev -s=1

#-e - environment
#-s - supress system errors.
```
 
- **Configure your environment**:

    Copy and edit the appropriate config files in config/ for your environment (dev, prod, etc).
    Copy the env file contents to .env file.
- **Set up your web server**:

    Point your document root to the public/ directory.

## Getting Started

- Configure your web server to point to the public/ directory.
- Set up your environment variables (if needed) in a .env file.
- Start building your modules inside the src/ directory.

## Features

- **HMVC Architecture**: Organize your code into modules for better maintainability.
- **Core Utilities**: Includes helpers for reflection, testing, and property/method access.
- **Testing Support**: Base TestCase class with helpers for invoking private/protected methods and properties.
- **Composer Autoloading**: PSR-4 autoloading for easy class management.
- **PHPStan Integration**: Static analysis ready with a sample phpstan.neon configuration.


## Directory Structure

Your project should follow a modular structure similar to:

```
project-root/
├── config/
│   ├── constants.php
│   ├── dev/
│   │   ├── config.php
│   │   └── db.php
│   ├── local/
│   │   ├── config.php
│   │   └── db.php
│   ├── prod/
│   │   ├── config.php
│   │   └── db.php
│   └── test/
│       ├── config.php
│       └── db.php
├── console/
│   ├── commands.php
│   ├── config.php
│   └── Welcome.php
├── migrations/
│   ├── Migration_YYYYMMDD_HHMMSS_Description.php
│   └── ...
├── src/
│   ├── DataModel/
│   │   └── App.php
│   ├── Filters/
│   │   └── AppFilter.php
│   ├── Module/
│   │   ├── App/
│   │   │   ├── Controller/
│   │   │   │   └── AppController.php
│   │   │   ├── Module.php
│   │   │   ├── routes.php
│   │   │   └── View/
│   │   │       └── AppView.php
│   │   └── Crud/
│   │       ├── Controller/
│   │       │   └── CrudController.php
│   │       ├── Module.php
│   │       ├── routes.php
│   │       ├── autoloads.php
│   │       └── services.php
│   ├── Service/
│   │   └── AppService.php
│   └── View/
│       └── login.php
├── tests/
│   └── ExampleTest.php
├── vendor/
├── public/
│   └── index.php
├── composer.json
└── README.md
```

---

## Usage Example

### 1. Define a Data Model

```php
// src/DataModel/App.php
namespace App\DataModel;

use System\Core\Data\DataRecord;

class App extends DataRecord
{
    public $id;
    public $name;
    public $version;
    public $description;
    public $author;
    public $created_at;
    public $updated_at;

    public function table(): string { return 'app'; }
    public function primaryKey(): string { return 'id'; }
}
```

### 2. Create a Controller

```php
// src/Module/App/Controller/AppController.php
namespace App\Module\App\Controller;

use App\Module\App\View\AppView;
use System\Core\Base\Controller\Controller;

class AppController extends Controller
{
    public function indexPage()
    {
        $view = new AppView();
        $view->addContents($view->getHomePage());
        return $view->get();
    }
}
```

### 3. Register Routes

```php
// src/Module/App/routes.php
use Router\Router;

return [
    ['/', 'app/indexPage'],
    ['/home', 'app/indexPage2'],
];
```


### 4. Service Registration

Services are reusable components (like database connections, mailers, etc.) that you can register and inject throughout your application.

- Register your services in each module’s `services.php` file (e.g., `src/Module/App/services.php`):

```php
// src/Module/App/services.php
return [
    'appService' => function() {
        return new \App\Service\AppService();
    },
    'bookService' => BookService::class,
    'test' => [
        'class' => Test::class,
        'params' => [
            'param1' => 'value1',
            'param2' => 'value2',
        ],
        'singleton' => true
    ]
    // Add more services here
];
```

- The framework will automatically load and make these services available for dependency injection or via the service locator.

- You can retrieve them anywhere using Container as follows

```
$test = Container::get(Test::class);
```

---

### 5. Autoload Registration

Autoload registration allows you to define additional files as helpers or classes(Model, Service, Library) that should be loaded automatically when your module is initialized.

- Register autoloads in each module’s `autoloads.php` file (e.g., `src/Module/Crud/autoloads.php`):

```php
// src/Module/Crud/autoloads.php
return [
    'helper' => [
        __DIR__ . '/Helper/CrudHelper.php',
    ],
    'service' => [
        'app' => AppService::class
    ],
    'model' => [
        'app' => App::class
    ],
    'library' => [
        'Cache' => Cache::class
    ]
];
```

**usage**
```php
public class AppController {
    public function action() {
        $service = $this->load->service->app; // AppService object.
        $model = $this->load->model->app; // App object.
        $library = $this->load->library->cache; // Cache library object.

        // to load not registered class
        $this->loader->service(AppService::class);// service
        $this->loader->model(AppModel::class);// model
        $this->loader->library(Library::class); // library
        $this->loader->helper('helper_file.php'); // helper
    }
}

```

- These files will be included automatically when the module is loaded, so you can define helper functions, constants, or perform other setup tasks.

---


### 4. Configure Database

Edit your environment config, e.g. `config/dev/db.php`:

```php
return [
    'default' => [
        'host' => 'localhost',
        'user' => 'youruser',
        'password' => 'yourpass',
        'database' => 'yourdb',
        'driver' => 'pdo/mysql',
    ]
];
```

### 5. Run Migrations

Place migration files in the `migrations/` directory and run migration as follows.

```
php console/run migrate
```

### 6. Start the Application

Point your web server to the `public/` directory and access your app in the browser.

---

**Tip:**  
For more advanced usage, see the `tests/` directory for test cases and the `src/Module/Crud` module for a CRUD example.

---

## Command System

The framework includes a powerful command-line system for automation, scaffolding, and database management. All commands are located in:

```
src/Core/Command/
```

### Creating Modules, Migrations, and Commands

You can scaffold new modules, migrations, and commands using the built-in `create` command:

```bash
php console/run create:module ModuleName
php console/run create:migration MigrationName
php console/run create:command CommandName
```

This will generate the appropriate directory structure and stub files under `src/Module/ModuleName/`, `migrations/`, or `console/`.

### Running Migrations

To run or rollback database migrations:

```bash
php console/run migrate
php console/run rollback
```

### Custom Commands

You can create your own custom commands using `php console/run create:command <command_name>`. It will create a command under the `console/` directory and registered in `console/commands.php`.

### Example: Running a Command

```bash
php console/run <command>
```

This will execute your newly created command.

### Built-in Tool Shortcuts

The `console/run` script also provides shortcuts for common tools:

- `php console/run test` — Runs PHPUnit tests
- `php console/run phpstan` — Runs PHPStan static analysis
- `php console/run cs-fixer` — Runs PHP CS Fixer

---
