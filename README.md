# CSV User Importer

A PHP-based command-line tool for importing user data from CSV files into a PostgreSQL database.

## Requirements

- Docker and Docker Compose
- Git
- PHP 8.3+ (for local development without Docker)
- PostgreSQL 13+ (for local development without Docker)

## Project Structure

```
src/           # Source code
├── Database/  # Database connection and table management
├── Model/     # Data models
├── Processor/ # Data validation and processing
├── Runner/    # Script execution logic
├── Scripts/   # Command line scripts
└── Transformer/# Data transformation
tests/         # Test files
data/          # Sample CSV files
```

## Getting Started

### With Docker (Recommended)

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

### Without Docker

1. Clone and setup:

```bash
git clone [repository-url]
cd csv-importer
composer install
```

2. Configure environment:

```bash
cp example.env .env
# Edit .env with your local PostgreSQL credentials
```

## Usage

### With Docker

```bash
# Display help
docker exec csv-importer-app-1 php src/Scripts/user_upload.php --help

# Create the users table
docker exec csv-importer-app-1 php src/Scripts/user_upload.php --create_table

# Drop the users table
docker exec csv-importer-app-1 php src/Scripts/user_upload.php --drop_table

# Import users (dry run)
docker exec csv-importer-app-1 php src/Scripts/user_upload.php --file data/users.csv --dry_run

# Import users
docker exec csv-importer-app-1 php src/Scripts/user_upload.php --file data/users.csv

# Import with custom database credentials
docker exec csv-importer-app-1 php src/Scripts/user_upload.php --file data/users.csv -u username -p password -h host
```

### Without Docker

```bash
# Display help
php src/Scripts/user_upload.php --help

# Create the users table
php src/Scripts/user_upload.php --create_table

# Drop the users table
php src/Scripts/user_upload.php --drop_table

# Import users (dry run)
php src/Scripts/user_upload.php --file data/users.csv --dry_run

# Import users
php src/Scripts/user_upload.php --file data/users.csv

# Import with custom database credentials
php src/Scripts/user_upload.php --file data/users.csv -u username -p password -h host
```

## Command Line Options

- `--file [csv file name]` - Name of the CSV file to be parsed (must have headers)
- `--create_table` - Build the database table
- `--drop_table` - Drop the database table if it exists
- `--dry_run` - Run the script but don't insert into the DB
- `-u [DB username]` - Database username
- `-p [DB password]` - Database password
- `-h [DB host]` - Database host
- `--help` - Display help message

## Development

### Running Tests

With Docker:

```bash
docker exec csv-importer-app-1 vendor/bin/phpunit
```

Without Docker:

```bash
vendor/bin/phpunit
```

### Running Static Analysis

With Docker:

```bash
docker exec csv-importer-app-1 vendor/bin/phpstan analyse
```

Without Docker:

```bash
vendor/bin/phpstan analyse
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
  docker exec -it csv-importer-app-1 bash
  ```
- Access PostgreSQL:
  ```bash
  docker exec -it csv-importer-db-1 psql -U postgres csv_importer
  ```

## CSV File Format

The CSV file must have headers and contain the following columns:

- name
- surname
- email

Example:

```csv
name,surname,email
John,Smith,john.smith@example.com
```

Notes:

- Email addresses must be in a valid format
- Email addresses must be unique in the database
- Names and surnames will be capitalized automatically
- Empty rows are skipped
- Invalid rows will be reported but won't stop the import process
