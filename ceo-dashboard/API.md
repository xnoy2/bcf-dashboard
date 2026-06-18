# CEO Dashboard — Integration API (v1)

A read-only JSON API that exposes the same data the dashboard shows, so other
apps can pull it without logging in. All responses are served from the snapshot
cache (refreshed every ~5 min by the scheduler), so they are fast and never
block on upstream providers.

## Base URL

```
https://dashboard.bespokegardenroomsballycastle.co.uk/api/v1
```

## Authentication

Send the API key on every request, in any one of these forms:

```
X-API-Key: <key>
Authorization: Bearer <key>
?api_key=<key>          # convenient for quick browser/cURL checks
```

- Missing/invalid key → `401`
- API key not configured on the server → `503`
- Rate limit: **120 requests / minute** per IP (`429` when exceeded)

The key lives in the `DASHBOARD_API_KEY` env var. Rotate it by changing that
variable; no code change needed.

## Response envelope

```json
{
  "data": { ... },
  "meta": { "account": "all", "generated_at": "2026-06-17T05:05:35+01:00" }
}
```

`generated_at` is when that data was last refreshed in the cache (`null` if it
has not been warmed yet). `account` is present only on account-aware endpoints.

## Endpoints

| Method & path | Description | `account`? |
|---|---|---|
| `GET /ping` | Health check + endpoint/version discovery | – |
| `GET /overview` | Composed CEO cockpit (KPIs, charts, all sources) | yes |
| `GET /pipeline` | Sales pipeline summary + Lead Explorer rows | yes |
| `GET /finance` | Cash-in, transactions, accounts | yes |
| `GET /appointments` | GHL calendar appointment counts | yes |
| `GET /security` | Cloudflare SSL/DNS/compliance + alerts | – |
| `GET /staff` | Staff portal: headcount + attendance | – |
| `GET /client-projects` | Delivery / project progress (BGR + BCF) | – |
| `GET /operations` | Operations overview | – |
| `GET /work-report` | Work report rows | – |
| `GET /renewals` | GoDaddy domain renewals | – |

`account` accepts `all` (default), `bcf`, `bgr`, or `rg`. An unknown value
returns `422`.

## Examples

```bash
KEY="<your-key>"
BASE="https://dashboard.bespokegardenroomsballycastle.co.uk/api/v1"

# Discovery / health
curl -H "X-API-Key: $KEY" "$BASE/ping"

# Sales pipeline for Ballycastle Climbing Frames
curl -H "X-API-Key: $KEY" "$BASE/pipeline?account=bcf"

# Whole-group finance
curl -H "Authorization: Bearer $KEY" "$BASE/finance?account=all"
```
