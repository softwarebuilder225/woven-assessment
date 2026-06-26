# Woven Investor API

Laravel backend for importing investor CSV data and exposing it via REST APIs. Built for the Woven Advice backend developer technical assessment.

## Setup

```bash
git clone <your-repo-url> woven
cd woven

composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

## Run

```bash
php artisan serve
```

Visit **http://localhost:8000/up** — you should see "Application up".

## Run tests

```bash
php artisan test
```

## Database

Local development uses **SQLite** (`database/database.sqlite`).

To use **MySQL** instead (e.g. with Docker):

```bash
docker compose up -d
```

Then update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=woven_investors
DB_USERNAME=root
DB_PASSWORD=secret
```

```bash
php artisan migrate
```

## License

MIT
