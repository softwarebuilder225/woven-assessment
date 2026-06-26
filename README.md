# Woven Investor API

Laravel backend for importing investor CSV data and exposing it via REST APIs. Built for the Woven Advice backend developer technical assessment.

The app accepts a CSV upload, stores investors and their investments in a relational database, and serves aggregate statistics and investor listings via JSON (with optional CSV export).

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

Place the provided CSV at `storage/samples/investors_with_dates.csv` for local testing (this folder is gitignored).

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

To use **MySQL** instead:

```bash
docker compose up -d
```

Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=woven_investors
DB_USERNAME=root
DB_PASSWORD=secret
```

Then run `php artisan migrate`.

## API endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/import` | Upload CSV file |
| GET | `/api/aggregates/average-age` | Average age across all investors |
| GET | `/api/aggregates/average-investment-amount` | Average amount across all investment records |
| GET | `/api/aggregates/total-investments` | Total number of investment records |
| GET | `/api/investors` | Paginated list of investors with total investment amount |
| GET | `/api/investors?format=csv` | Export all investors as CSV |

### Examples

Import CSV:

```bash
curl -X POST http://localhost:8000/api/import \
  -F "file=@storage/samples/investors_with_dates.csv"
```

Aggregates:

```bash
curl http://localhost:8000/api/aggregates/average-age
curl http://localhost:8000/api/aggregates/average-investment-amount
curl http://localhost:8000/api/aggregates/total-investments
```

Investor listing:

```bash
curl "http://localhost:8000/api/investors?per_page=10"
curl "http://localhost:8000/api/investors?format=csv" -o investors.csv
```

## Assumptions

- CSV `investor_id` is stored as `external_id` on the `investors` table (separate from the internal primary key).
- CSV dates use `DD-MM-YYYY` format.
- One investment amount per investor per date; duplicates on re-import are updated, not inserted twice.
- **Average investment amount** is the mean of all individual investment records, not the mean of per-investor totals.
- **Investment amount** on the investor listing is the sum of all investments for that investor.
- CSV imports are processed in chunks of 500 rows inside database transactions.

## Architecture

```
Controller (thin) → Service (business logic) → Eloquent / DB
```

Services live in `app/Services/`. Controllers handle HTTP only; validation uses Form Requests.

## Next steps

- Queue large CSV imports as background jobs
- Add API authentication (e.g. Laravel Sanctum)
- Add OpenAPI documentation
- Add rate limiting on upload endpoint

## License

MIT
