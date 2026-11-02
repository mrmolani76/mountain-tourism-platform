# Technical rules (MVP §7) — non-negotiable

1. **Free services only**  
   Weather: MET Norway Locationforecast (`api.met.no`). Maps: free OSM-based tiles with Leaflet or MapLibre. No paid APIs, no API keys, no trials that later charge. Anything else needs client OK first.

2. **Tenant isolation in one place**  
   **DB-per-tenant:** isolation is the active database connection (`tenancy/` middleware + `DATABASE_ROUTERS`). Do not add ad hoc cross-DB queries. (Historical note: shared-DB scoped manager was replaced 2026-11-02.)

3. **Money = integer minor units**  
   Store amounts in cents (or equivalent), never floats / Decimal for money math that can drift. Display formats in the UI/API layer.

4. **Permissions on the API**  
   UI hiding is not security. Check role and outlet access on the server.

5. **Weather from the server only**  
   Background job caches forecasts. Browsers never call MET Norway directly. Shared cache for all screens. Honor MET User-Agent + caching rules. Show credit: “Weather data from MET Norway”. Maps show OSM credit.

6. **Web only**  
   Browser on laptop, tablet, phone. No native apps in MVP.

7. **OpenAPI (Swagger)**  
   Document the API. Future guest portal reuses the same API.

8. **Per-tenant locale settings**  
   Timezone, currency, units (°C/km/h or °F/mph). Nightly posting, reports, and “today” use tenant local time.

9. **Devices**  
   Staff screens: 10-inch tablet + laptop. Lift operator page: phone.

10. **Money append-only**  
    Charge/Payment never edited or deleted. Void with reason, user, timestamp. Weather cancel → void reason `weather`.

11. **No secrets in git**  
    All settings in server `.env`. Nothing hard-coded to the VPS IP. Real domain later = Nginx + `.env` only.
