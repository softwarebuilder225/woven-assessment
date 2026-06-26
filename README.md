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
| GET | `/api/aggregates/average-investment-amount` | Average total investment amount per investor |
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
- **Average investment amount** is the mean of each investor's total invested amount (parallel to average age being per-investor).
- **Investment amount** on the investor listing is the sum of all investments for that investor.

## Scalability (10k+ records)

The assessment expects the app to handle large CSV files and serve data efficiently. This is how each part is addressed:

| Requirement | Approach |
|-------------|----------|
| CSV import | Stream rows with `fgetcsv()` — never load the full file into memory. Persist in chunks of 500 using `upsert()` inside transactions. |
| Duplicate handling | `upsert()` on `external_id` and `(investor_id, investment_date)` avoids row-by-row SELECT/INSERT. |
| Aggregates | `AVG()` / `COUNT()` run in the database, not in PHP. |
| Investor listing | Cursor pagination (no slow `OFFSET` on large tables). `withSum()` fetches totals in one query per page. |
| CSV export | Streamed response with `chunkById(500)` — processes 500 rows at a time. |
| Indexes | Unique on `investors.external_id` and `investments(investor_id, investment_date)`. |

With 10k rows, memory usage stays flat during import and export because only one chunk is held at a time.

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
