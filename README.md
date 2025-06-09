# CSV User Importer

A PHP-based command-line tool for importing user data from CSV files into a PostgreSQL database.

## Requirements

- Docker and Docker Compose
- Git

## Project Structure

```
src/           # Source code
├── database/  # Database related files
├── model/     # Data models
├── transformer/# Data transformers
├── processor/ # Data processors
├── importer/  # Import functionality
└── scripts/   # Command line scripts
tests/         # Test files
data/          # Data files (CSV)
```

## Development Environment

This project uses Docker for development and includes:

- Ubuntu 24.04.2 (Noble Numbat)
- PostgreSQL 13
- PHP 8.3

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
cd csv-importer
```

2. Set up environment variables:
```bash
cp example.env .env
# Edit .env with your database credentials if needed
```

3. Start the Docker environment:
```bash
docker compose up -d --build
```

4. Install PHP dependencies:
```bash
docker compose exec app composer install
```

## Development

### Running Tests
To run the test suite:
```bash
docker compose exec app vendor/bin/phpunit
```

### Docker Commands
- Start containers:
  ```bash
  docker compose up -d
  ```
- Stop containers:
  ```bash
  docker compose down
  ```
- View logs:
  ```bash
  docker compose logs -f
  ```
- Access PHP container:
  ```bash
  docker compose exec app bash
  ```
- Access PostgreSQL:
  ```bash
  docker compose exec db psql -U postgres csv_importer
  ```

## Usage

To import users from a CSV file:

```bash
docker-compose exec app php src/scripts/user_upload.php [options] [csv_file]
```

Options:
- `--file [csv file name]` – Name of the CSV file to be parsed
- `--create_table` – Build the MySQL database table
- `--dry_run` – Run the script but not insert into the DB
- `-u [MySQL username]`
- `-p [MySQL password]`
- `-h [MySQL host]`
- `--help` – Display help
