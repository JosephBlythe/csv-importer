# GitHub Copilot Instructions for csv-importer

This document provides essential guidelines and context for GitHub Copilot when assisting with this PHP project. Adhering to these instructions will help Copilot generate more relevant, accurate, and consistent code and advice.

## 1. Project Overview & Tech Stack

- **Primary Language:** PHP (version 8.3+)
- **Framework/Libraries:**
  - **Core:** Plain PHP with Composer
  - **Dependency Management:** Composer
  - **Database ORM/Abstraction:** PDO with custom repository classes
  - **Testing:** PHPUnit
  - **Linting/Static Analysis:** PHPStan, PHP-CS-Fixer
- **Architecture:** Clean Architecture
- **Containerization:** Docker / Docker Compose

## 2. Coding Standards & Best Practices

- **PSR Compliance:** All PHP code **MUST** adhere to the latest stable [PSR standards](https://www.php-fig.org/psr/). Specifically:
  - **PSR-12 (Extended Coding Style Guide):** Enforce consistent formatting, indentation (4 spaces, no tabs), line endings (LF), and general code layout.
  - **PSR-4 (Autoloading Standard):** All classes are autoloaded via Composer. Namespaces correspond directly to directory structure under `src/` (e.g., `App\Controller\UserController` maps to `src/Controller/UserController.php`).
  - **PSR-7 / PSR-15 / PSR-17 (HTTP Message Interfaces):** When dealing with HTTP requests and responses, use interfaces compliant with these PSRs (e.g., from `nyholm/psr7` or similar).
  - **PSR-3 (Logger Interface):** Use a PSR-3 compliant logger (e.g., Monolog).
  - **PSR-11 (Container Interface):** When dealing with Dependency Injection Containers, use PSR-11 interfaces.
- **Object-Oriented Programming (OOP):**
  - **Encapsulation:** Prioritize encapsulation by making properties `private` or `protected` and providing public methods for interaction.
  - **Composition over Inheritance:** Favor composition for building complex functionality.
  - **Dependency Injection (DI):** Use dependency injection extensively. Do **NOT** use `new` keyword directly for instantiating dependencies within classes; instead, inject them via constructor or setter methods.
  - **Interfaces:** Define and use interfaces to promote loose coupling and enable polymorphism.
  - **Single Responsibility Principle (SRP):** Each class and method should have one clear, well-defined responsibility.
- **Type Hinting:** Use strict type declarations (scalar type hints, return type declarations, property type declarations) for all arguments, return values, and properties where possible (`declare(strict_types=1);` at the top of files).
- **Error Handling:**
  - Use exceptions for all exceptional conditions. Do **NOT** use `die()`, `exit()`, or direct `echo` for errors in application logic.
  - Catch specific exceptions and log them using the PSR-3 logger.
- **Security:**
  - **Input Validation & Sanitization:** Always validate and sanitize all user input. Avoid trusting any user-provided data.
  - **Prepared Statements:** Use prepared statements for all database interactions to prevent SQL injection.
  - **Password Hashing:** Use `password_hash()` and `password_verify()` for secure password storage.
  - **CSRF Protection:** Implement CSRF tokens for all form submissions.
  - **Sensitive Data:** Never hardcode sensitive credentials or API keys. Use environment variables (e.g., `.env` files loaded by `symfony/dotenv` or `vlucas/phpdotenv`).

## 3. Project Structure & Naming Conventions

- **Main Source Code:** `src/` directory for all application PHP source code.
- **Tests:** `tests/` directory for all unit, integration, and functional tests. Matches `src` structure.
- **Vendor Dependencies:** `vendor/` managed by Composer. Do not modify files in this directory.
- **Naming Conventions:**
  - **Classes/Interfaces/Traits:** `PascalCase` (e.g., `UserController`, `UserRepositoryInterface`).
  - **Methods/Functions/Variables:** `camelCase` (e.g., `getUserById`, `processUserData`).
  - **Constants:** `UPPER_SNAKE_CASE` (e.g., `MAX_ATTEMPTS`).
  - **Namespaces:** `PascalCase`, matching directory structure from `src/` (e.g., `App\Controller`, `App\Service`).

## 4. Development Workflow & Tools

- **Composer:** Always use Composer for managing dependencies (`composer install`, `composer update`).
- **Git:** Follow a feature branching workflow (e.g., Gitflow or GitHub Flow).
- **Testing:**
  - Run tests frequently (`./vendor/bin/phpunit` or `./vendor/bin/pest`).
  - Every new feature or bug fix requires corresponding tests.
- **Static Analysis:** Run PHPStan (`./vendor/bin/phpstan`) and PHP-CS-Fixer (`./vendor/bin/php-cs-fixer`) before committing.
- **Database Migrations:** Use the framework's migration system for schema changes.
- **Console Commands:** Define custom console commands in `scripts`

## 5. Specific Instructions for Copilot Agent

- **Context Prioritization:** Prioritize context from open files, `#file:` references, and the overall project structure.
- **Code Generation:** When generating code, always strive for the most modern and performant PHP constructs (e.g., arrow functions, named arguments, match expression, attributes).
- **Refactoring:** When asked to refactor, prioritize creating smaller, more focused classes/methods, injecting dependencies, and improving readability.
- **Error Debugging:** When debugging errors, consider common PHP pitfalls, framework-specific debugging techniques, and suggest logging.
- **Test Generation:** When generating tests, aim for comprehensive unit tests that cover edge cases. Provide mock objects for dependencies.
- **File Modifications:** If a request involves modifying multiple files, clearly outline the changes for each file, and confirm before proceeding.
