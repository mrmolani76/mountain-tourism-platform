# Module 8 — Weather Management
## Detailed Functional Analysis and Development Specification

**Project:** Mountain Tourism Complex Platform  
**Source scope:** Mountain Tourism Complex Platform — MVP Scope v1 (October 2026), Section 5, Module H, pages 5–6; supporting technical rules on page 7  
**Project audit reference:** Project Audit Report — Mountain Tourism Platform, 3 November 2026  
**Implementation context:** Django REST Framework backend and Vue/Nuxt frontend  
**Purpose:** Translate the client's weather requirements into an implementation-ready specification. PDF requirements, audit findings and implementation recommendations are distinguished throughout.

---

## 1. Module objective

Weather is a core operational feature of the mountain complex because weather conditions can differ significantly by altitude and can affect lifts, transport trips and outdoor services.

The module must:
- maintain a weather point for every building and lift station, including coordinates and altitude;
- retrieve forecasts from the MET Norway Locationforecast API;
- fetch forecast data on the server every hour and store it;
- display current conditions, hourly forecasts for the next 48 hours and approximately 9 days ahead;
- show temperature, wind, gusts, wind direction, precipitation as snow/rain, cloud/fog, humidity and UV;
- allow each complex to configure weather limits per weather point;
- create warnings when a forecast crosses a configured limit;
- attach warnings to affected lifts, trips and outdoor service slots in the relevant time window;
- show warnings on dashboard, transport and service screens;
- leave cancellation decisions to staff;
- support a staff-approved “Cancel for weather” operation that cancels affected bookings, voids charges using reason `weather` and prints a guest list to inform;
- let staff enter the daily avalanche danger level from the official regional bulletin;
- provide a read-only lobby screen through a private link, without login.

**Core rule:** Weather warns staff early; people decide. The system must never cancel operations automatically merely because a weather limit is crossed.

## 2. Source requirements and scope boundaries

### 2.1 Explicit requirements from the client PDF

| Area | Requirement stated in the PDF |
|---|---|
| Weather point | Every building and lift station is a weather point |
| Weather location | Each point has coordinates and altitude |
| Forecast provider | MET Norway Locationforecast API (`api.met.no`) |
| API use | Free, allows commercial use, no API key needed |
| Fetch schedule | Background job fetches every hour |
| Storage | Forecast data is stored |
| API operation | Follow MET Norway caching rules and User-Agent header requirements |
| Failure behaviour | If API is down, last available data remains on screen with its time |
| Forecast range | Current conditions, next 48 hours by hour, approximately 9 days ahead |
| Weather fields | Temperature, wind, gusts, wind direction, precipitation (snow/rain), cloud/fog, humidity and UV |
| Altitude behaviour | Precipitation is shown as snow or rain using temperature at that altitude |
| Limits | Complex sets limits per weather point |
| Warning targets | Warnings attach to affected lifts, trips and outdoor service slots in the relevant time window |
| Warning surfaces | Dashboard, transport screens and service screens |
| Cancellation | People decide; system never cancels by itself |
| Weather cancellation | Cancels bookings, voids charges with reason `weather`, prints list of guests to inform |
| Avalanche | Staff enters level 1–5 every morning from official regional bulletin |
| Lobby screen | Read-only private-link page, no login |
| Lobby content | Valley and summit weather, lift status, avalanche level and today's activities |
| Attribution | Show “Weather data from MET Norway”; maps show OpenStreetMap credit |

### 2.2 Technical requirements elsewhere in the PDF

The technical rules on page 7 also require:
- weather to be called from the server only;
- data to be cached and shared by all screens;
- browsers must never fetch the weather API separately;
- tenant timezone must be respected for “today” and scheduled operations.

### 2.3 Scope boundaries

The PDF does not explicitly require:
- automatic lift closure or trip cancellation;
- automatic guest SMS, WhatsApp, email or push notifications;
- predictive avalanche modelling;
- automatic avalanche data import;
- snow-depth or freezing-level data from a paid provider;
- severe-weather decision automation;
- a paid weather provider;
- user-specific weather subscriptions;
- guest mobile applications;
- weather-dependent dynamic pricing.

The PDF lists richer weather data such as snow depth and freezing level from a paid provider as a possible future feature after pilot feedback, not part of the MVP.

Do not add these capabilities to the MVP without client approval.

## 3. Weather Points

### 3.1 Definition

The PDF states that every building and lift station is a weather point. Each point has:
- coordinates;
- altitude.

This is necessary because valley, mid-station and summit conditions can differ.

### 3.2 Reuse existing Buildings and Lifts

Module A already owns Building records. Module G owns Lift records/stations and references two buildings. Weather should extend or reference these existing entities rather than create a duplicate building/location registry.

A weather point may be:
- a building weather point;
- a lift-station weather point, where the lift station is independently represented.

The exact schema is not specified by the PDF. Avoid duplicating a building weather point if a lift station is the same physical location and can reference the existing building point.

### 3.3 Required data

| Field/concept | Purpose | Source status |
|---|---|---|
| Related building or lift station | Identify where weather applies | PDF requirement |
| Latitude/longitude | API forecast location | PDF requirement: coordinates |
| Altitude | Elevation-aware forecast/precipitation display | PDF requirement |
| Display name | Identify point on screens | Practical requirement |
| Active status | Exclude retired points from fetching | Recommendation |

The PDF does not specify coordinate formats, precision, geocoding workflow or whether coordinates are entered manually. These are implementation details.

### 3.4 Example weather points

The PDF's sample complex includes:
- Valley station — 1,050 m;
- Main hotel — 1,200 m;
- Chalets — 1,250 m;
- Mid station — 1,800 m;
- Mountain hut — 2,100 m;
- Summit station — 2,600 m.

These are demo seed examples from the PDF, not mandatory altitudes for every deployment.

## 4. MET Norway Forecast Integration

### 4.1 Explicit provider and endpoint

The client specifies the **MET Norway Locationforecast API** at `api.met.no`.

The PDF states that the service:
- is free;
- allows commercial use;
- requires no API key;
- must be used in accordance with its caching rules;
- requires an appropriate User-Agent header.

The implementation must follow the provider's actual technical usage requirements. Do not assume that “free” means unlimited requests or that caching/User-Agent rules can be ignored.

### 4.2 Server-side only

This is a non-negotiable platform technical rule.

Correct architecture:

```text
Scheduled background job
        |
        v
Backend Weather Service
        |
        v
MET Norway Locationforecast API
        |
        v
Validate / normalise / store forecast
        |
        v
Weather API for application screens
        |
        +--> Owner dashboard
        +--> Transport screens
        +--> Service screens
        +--> Lobby screen
```

Incorrect architecture:
- each Vue component calls MET Norway directly;
- each browser independently fetches the forecast;
- each screen has its own provider request/cache.

All application screens must read from the platform's stored/shared weather data.

### 4.3 Hourly background fetch

The PDF requires a background job to fetch weather every hour.

Expected behaviour:
1. identify active weather points;
2. obtain or update the forecast for each point;
3. include the point's coordinates and altitude context as appropriate for the provider request/data handling;
4. validate the response;
5. store the forecast data and retrieval time;
6. make the updated data available to all screens;
7. record failures without erasing the last successful forecast.

The exact scheduler technology is not prescribed. Use the existing project scheduler/cron/background-job approach rather than adding another infrastructure service without need.

### 4.4 Cache and stored forecast data

The PDF says the forecast is fetched hourly and stored, while the technical rules require cached/shared data.

A useful design stores:
- the weather point;
- forecast issue/retrieval time;
- forecast valid time;
- the forecast variables;
- provider/source attribution;
- the most recent successful update time.

The PDF does not prescribe whether the provider response is stored raw, normalised, or both. Choose a design that preserves enough information for the required forecast screens and respects provider caching terms.

### 4.5 User-Agent and provider compliance

The backend must send a meaningful User-Agent consistent with MET Norway's documented requirements and the project's identity/contact policy.

Do not:
- omit the User-Agent;
- send high-frequency requests from every browser;
- ignore cache instructions;
- bypass provider limits by creating unnecessary duplicate calls.

### 4.6 API failure behaviour

The PDF explicitly says that if the API is down, the last data stays on screen with its time.

Expected behaviour:
- retain the last successful forecast;
- show when it was last updated;
- show an appropriate stale/unavailable indicator if the latest fetch failed;
- do not replace valid stored weather with empty/null data because a request failed;
- record the latest fetch error for operational diagnosis.

The exact stale threshold and UI wording are not specified. The fact that the last data and its time remain visible is required.

## 5. Forecast Horizon and Weather Variables

### 5.1 Required display horizon

The PDF requires:
- current conditions;
- next 48 hours by hour;
- approximately 9 days ahead.

The 48-hour view is hourly. The PDF does not prescribe whether the approximately 9-day view is hourly, three-hourly or daily. Use the resolution actually supplied by the provider and present it clearly; do not invent precision the provider does not return.

### 5.2 Required forecast variables

| Variable | Required display |
|---|---|
| Temperature | Temperature at the weather point/altitude |
| Wind | Wind speed |
| Wind gusts | Gust speed |
| Wind direction | Direction |
| Precipitation | Amount/type |
| Snow or rain | Display based on temperature at that altitude |
| Cloud | Cloud conditions |
| Fog | Fog visibility/condition where available |
| Humidity | Relative humidity |
| UV | UV information |

The PDF lists the variables but does not specify units, rounding, iconography, colour scales or exact provider-field mapping. Use tenant settings for units (°C / km/h or °F / mph) as configured in Module A.

### 5.3 Snow versus rain

The PDF explicitly says precipitation is shown as snow or rain from the temperature at that altitude.

This means precipitation presentation must be associated with the weather point's local conditions rather than using one generic complex-wide precipitation label.

The PDF does not state the exact temperature threshold or mixed-precipitation algorithm. Use a defensible provider-supplied precipitation type where available or agree a documented rule with the client. Do not silently assume a universal threshold such as exactly 0°C without checking the provider data and client expectation.

### 5.4 Altitude-specific forecasts

The purpose of multiple weather points is to represent changing conditions with elevation. The valley and summit must show their own forecasts.

Do not:
- copy valley data to the summit;
- treat a complex as one single forecast point;
- discard altitude when identifying the relevant building/station.

## 6. Weather Limits

### 6.1 Explicit requirement

Each complex can define limits per weather point.

The PDF gives examples:
- wind gusts at the summit station;
- precipitation per day;
- fog;
- very low temperature.

### 6.2 Required behaviour

Staff/owner must be able to configure limits for a particular weather point and the relevant metric.

Examples:
- maximum wind gust at Summit station;
- maximum daily precipitation at a building;
- fog threshold at a lift station;
- minimum temperature threshold at an exposed point.

### 6.3 Recommended configuration structure

The PDF does not prescribe exact field names or comparison operators. A proposed configuration may contain:

| Proposed field | Purpose |
|---|---|
| `weather_point` | Point to which the limit applies |
| `metric` | Wind gust, precipitation, fog, temperature etc. |
| `operator` | Greater-than, less-than or other supported comparison |
| `threshold_value` | Configured threshold |
| `unit` | Unit for display/comparison |
| `evaluation_window` | Relevant period, e.g. daily precipitation or forecast time |
| `is_active` | Enable/disable limit |

Only add operators/windows appropriate to supported metrics. Confirm the exact threshold semantics with the client.

### 6.4 Metric-specific evaluation

Different limits may need different evaluation windows:
- wind gust: forecast value at a particular time;
- precipitation per day: accumulated daily total;
- fog: a forecast condition over a time period;
- very low temperature: minimum/forecast temperature.

The PDF provides examples but does not formally define each aggregation method. Define and document each metric's calculation before implementation.

## 7. Warning Generation and Propagation

### 7.1 Explicit requirement

When a forecast crosses a configured limit, a warning is attached to everything affected in that time window:
- lifts;
- trips;
- outdoor service slots at the relevant building.

Warnings must show on:
- dashboard;
- transport screens;
- service screens.

### 7.2 Warning is time-bound

A warning must relate to:
- the weather point;
- the metric/limit crossed;
- the forecast time window;
- the affected operations at that location/time.

Do not show a summit wind warning indiscriminately on every operation in the complex if it does not affect them.

### 7.3 Affected-operation mapping

Recommended resolution flow:

1. Evaluate forecast values against active limits for a weather point.
2. Identify the forecast interval(s) that cross the limit.
3. Identify lifts, trips and outdoor service slots associated with that weather point/building and intersecting the interval.
4. Create or update warning records for those affected operations.
5. expose warning state to the dashboard, transport and service screens.

The PDF requires attachment to affected operations in the relevant time window. The exact geographic relationship (for example, whether a building warning affects a lift connected to that building or only a lift station point) must be defined from the complex's operational mapping.

### 7.4 Warning lifecycle

A practical warning lifecycle may include:
- active;
- cleared/resolved when forecast no longer crosses the threshold;
- superseded by a newer evaluation.

These statuses are design recommendations. The PDF requires warnings to appear when limits are crossed but does not prescribe status names or whether warnings are retained historically. Preserve warning history for traceability.

### 7.5 Avoid duplicate warnings

Hourly forecast refreshes can repeatedly detect the same limit crossing. The system should update or reuse a warning for the same point/metric/operation/time window rather than creating uncontrolled duplicates each hour.

This is an implementation recommendation. Keep the warning history/last-evaluated time where useful.

## 8. Staff-Controlled “Cancel for Weather” Workflow

### 8.1 Human decision rule

The PDF states:

**People decide, the system never cancels on its own.**

A warning is informational/operational. It must not automatically:
- cancel a lift;
- cancel a transport trip;
- cancel an outdoor activity slot;
- cancel guest bookings.

Only an authorised staff member performs a weather cancellation.

### 8.2 Explicit cancellation consequences

When staff selects **Cancel for weather** on a trip, slot or lift day, the system must:
1. cancel affected bookings;
2. void their charges with reason `weather`;
3. print the list of guests to inform.

The wording in the PDF applies the cancellation action to a trip, a slot or a lift day. Implement the action consistently for these affected-operation types.

### 8.3 Recommended workflow

1. Staff opens an affected lift, trip or outdoor service slot.
2. Staff reviews the active warning and forecast window.
3. Staff chooses `Cancel for weather`.
4. System shows the affected bookings and financial consequences for review.
5. Staff confirms the action.
6. The operation is marked cancelled for weather.
7. Associated bookings are cancelled.
8. Existing charges are voided using Module C's shared void service with exact reason `weather`.
9. A guest contact/information list is generated for printing.
10. The operation and cancellation audit records remain available.

The confirmation preview is a recommended safeguard; the PDF does not prescribe a specific confirmation dialog.

### 8.4 Append-only money reconciliation

The platform-wide money rule says a charge/payment is never edited or deleted. It is voided with:
- reason;
- user;
- timestamp.

Therefore “voids their charges” must be implemented through Module C's void mechanism, not by deleting charges or setting their amount to zero.

Use the exact weather reason specified by the PDF: `weather`.

### 8.5 Partial failures and consistency

A weather cancellation can involve several bookings and charges. Avoid leaving an operation partly cancelled with some charges still active because one request failed.

Use a transaction or a carefully designed, retryable workflow with clear per-booking/per-charge completion state. The PDF does not prescribe implementation mechanics, but the final folio outcome must be correct.

### 8.6 Guest information printout

The PDF requires printing the list of guests to inform.

The PDF does not specify all fields. A practical printout may include:
- cancelled operation name/type;
- date/time;
- guest name;
- booking reference;
- relevant reservation/stay reference;
- contact information only if the client approves and it is necessary.

Do not automatically send SMS/WhatsApp/email as part of the MVP unless separately approved.

## 9. Avalanche Danger Level

### 9.1 Explicit requirement

The avalanche danger level:
- is a value from 1 to 5;
- is entered by staff every morning;
- comes from the official regional bulletin.

### 9.2 Required behaviour

- Staff can enter the daily avalanche level.
- The accepted value must be 1, 2, 3, 4 or 5.
- The entry is date-specific.
- The latest daily level is visible on the lobby screen.
- The source is the official regional bulletin.

### 9.3 Recommended audit fields

The PDF says the level is entered by staff every morning. A useful record should retain:
- date the level applies to;
- level (1–5);
- staff member who entered it;
- time of entry.

An optional reference/remark to the bulletin may be useful, but is not explicitly required.

### 9.4 Not an automated forecast

Do not imply that the platform calculates avalanche danger using MET Norway. The PDF says staff enter the level from the official regional bulletin. This is a manual operational input in the MVP.

### 9.5 Missing daily entry

The PDF does not define what the lobby screen should show if staff have not entered the level for the current day. Recommended behaviour is to show “not entered” rather than silently carry yesterday's level forward as today's value. Confirm this with the client.

## 10. Lobby Screen

### 10.1 Explicit requirement

The lobby screen is:
- read-only;
- opened by a private link;
- accessible without login.

It must show:
- weather at valley and summit;
- lift status;
- avalanche level;
- today's activities.

### 10.2 Access model

The PDF specifically says no login, but the URL is private.

Therefore:
- do not use staff authentication for this screen;
- protect access through an unguessable private token/link;
- allow read-only data only;
- provide a way for an authorised owner/admin to regenerate or revoke the link (recommended);
- do not expose editing actions or internal management APIs.

The PDF does not prescribe token length, expiry, rotation or link-management UI. Agree the required security/operational policy.

### 10.3 Lobby content

| Content | Source |
|---|---|
| Valley weather | Weather point for valley station |
| Summit weather | Weather point for summit station |
| Lift status | Module G lift current status |
| Avalanche level | Daily staff entry |
| Today's activities | Module F scheduled activities/bookings as agreed |

### 10.4 Read-only update behaviour

The lobby screen should read the latest stored weather and operational data from the platform backend. It must not call MET Norway directly.

The exact refresh interval is not prescribed. Weather data is refreshed hourly; lift status should be reflected promptly, with the explicit acceptance criterion that a lift set to closed appears on the lobby screen within one minute.

### 10.5 Attribution

The lobby/forecast interface must show:
- `Weather data from MET Norway`;
- OpenStreetMap credit on maps where applicable.

Follow the source/provider attribution requirements. Do not remove attribution from the UI.

## 11. Suggested Data Model

The following is a proposed model decomposition, not a schema prescribed by the client. Inspect the existing `backend/modules/weather/` implementation before creating or changing models.

| Proposed model | Responsibility |
|---|---|
| `WeatherPoint` | Reference to building/lift station, coordinates and altitude |
| `WeatherFetchRun` | Hourly fetch attempt, success/failure and timing |
| `WeatherForecastSnapshot` | Stored provider forecast for a point |
| `WeatherForecastValue` | Normalised values by valid time/variable, if normalised storage is used |
| `WeatherLimit` | Per-point configured metric threshold |
| `WeatherWarning` | Limit crossing and forecast time window |
| `WeatherWarningTarget` | Affected lift, trip or outdoor service slot |
| `WeatherCancellation` | Staff-approved cancellation action and outcome |
| `AvalancheDailyLevel` | Manual daily 1–5 value and staff audit |
| `LobbyScreenLink` | Private read-only access token/settings, if needed |

### Relationship overview

```text
Tenant database
 ├── Building (Module A)
 │    └── WeatherPoint
 ├── Lift / Lift Station (Module G)
 │    └── WeatherPoint (or reference to associated building point)
 │
 ├── WeatherPoint
 │    ├── WeatherFetchRun
 │    ├── WeatherForecastSnapshot / Values
 │    └── WeatherLimit
 │         └── WeatherWarning
 │              └── WeatherWarningTarget
 │                   ├── Lift (Module G)
 │                   ├── Trip (Module G)
 │                   └── Outdoor Service Slot (Module F)
 │
 ├── WeatherCancellation
 │    ├── affected bookings (Modules F/G)
 │    └── charge voids (Module C)
 │
 ├── AvalancheDailyLevel
 └── LobbyScreenLink
```

### Data design notes

- Keep weather point references connected to existing buildings/lifts.
- Store forecast retrieval time separately from the forecast valid time.
- Retain the last successful forecast when a new fetch fails.
- Keep warning evaluation tied to a specific forecast period.
- Keep cancellation decisions and outcomes auditable.
- Follow the project's DB-per-tenant architecture; do not add redundant `tenant_id` fields to tenant tables without architectural approval.

## 12. Suggested API Capability Map

The PDF does not prescribe endpoint paths. These are proposed DRF capabilities, not claims that the endpoints already exist.

| Area | Suggested API capability |
|---|---|
| Weather points | List/create/update points and coordinates/altitude |
| Forecast read | Retrieve stored forecast by point and horizon |
| Fetch status | Retrieve last success/failure and last-updated time |
| Weather limits | List/create/update/activate per-point limits |
| Warnings | List active/history warnings by point/operation/time |
| Warning targets | Retrieve affected lifts, trips and service slots |
| Weather cancellation | Staff-approved cancel-for-weather action |
| Cancellation preview | Show affected bookings/charges before confirmation (recommendation) |
| Guest information list | Render/print affected guest list |
| Avalanche | Enter/update daily danger level and retrieve current/history |
| Lobby data | Read-only aggregate for valley/summit, lifts, avalanche and activities |
| Lobby private link | Create/revoke/rotate private read-only access link (recommended) |

### Backend rules

- Weather provider calls must happen server-side only.
- Screens read shared stored data.
- Hourly fetch must respect provider caching and User-Agent rules.
- API failures must not erase last good data.
- Evaluate thresholds using the configured point, metric, time and unit.
- Attach warnings only to relevant operations at the matching building/point and time window.
- Never automatically cancel operations when a threshold is crossed.
- Staff cancellation must call shared booking cancellation and Module C charge-void services.
- Use the authenticated request user as cancellation/avalanche audit actor.
- Restrict lobby endpoints to read-only data and private-link authorisation.

## 13. Frontend Screens

Suggested screens based on the PDF requirements:

1. Weather Point list.
2. Weather Point create/edit form (building/lift station, coordinates, altitude).
3. Weather overview by weather point.
4. Valley forecast view.
5. Summit forecast view.
6. Point forecast detail (current, 48-hour hourly, approximately 9-day).
7. Weather fetch status/last-updated view.
8. Per-point Weather Limit configuration.
9. Active Weather Warnings list.
10. Warning detail with affected lifts/trips/outdoor slots.
11. Weather cancellation review/confirmation.
12. Printable guest information list.
13. Daily Avalanche Level entry screen.
14. Avalanche level history.
15. Private Lobby Screen link management.
16. Public-facing read-only Lobby Screen.
17. Weather warning indicators on Transport screens (Module G).
18. Weather warning indicators on Service screens (Module F).
19. Owner dashboard weather summary (Module I).

The PDF does not prescribe the visual design or component library. Use the existing Nuxt/Vue design system and ensure the lobby screen is legible on a TV/display.

## 14. Existing Repository Audit Findings

The Project Audit Report dated 3 November 2026 reports:

| Item | Audit finding |
|---|---|
| Weather Limit model | Present |
| Weather Warning model | Present |
| Weather ViewSets/APIs | CRUD-level structure exists |
| MET Norway forecast integration | Not reported as complete |
| Hourly fetch and persistence | Not reported as complete |
| Warning propagation to lifts/trips/outdoor slots | Missing |
| Cancel-for-weather workflow | Missing |
| Charge void with `weather` reason | Missing |
| Avalanche daily entry | Not confirmed complete |
| Lobby screen | Frontend module screens are placeholders; not pilot-ready |
| Domain API authentication | Critical defect affects Weather and other B–I ViewSets |

The audit describes Module H as shell/partial: Limit and Warning CRUD exists, but propagation to affected operations and cancellation/void consequences are not implemented.

**Do not rebuild WeatherLimit and WeatherWarning without inspection.** Review existing models, migrations, serializers, views, URLs, services and tests. Add the provider integration and missing business logic while preserving migration immutability.

## 15. Recommended Development Sequence

Implement one step at a time, with code and automated tests in each step.

| Step | Task | Completion evidence |
|---:|---|---|
| 0 | Inspect Weather app and fix/verify domain API authentication | Protected Weather APIs work for authorised staff |
| 1 | Weather points | Every building/lift station has coordinates and altitude |
| 2 | MET Norway server client | Backend can fetch forecast with correct User-Agent and compliant caching |
| 3 | Hourly scheduled fetch | Active points refresh every hour |
| 4 | Forecast persistence/failure handling | Stored forecasts available; failure retains last successful data and timestamp |
| 5 | Forecast read APIs | Current, 48-hour hourly and approximately 9-day views available |
| 6 | Weather data UI | Required variables, altitude point and units are displayed |
| 7 | Weather limit configuration | Owner can set limits per point |
| 8 | Warning evaluation engine | Forecast threshold crossing creates/updates warning windows |
| 9 | Operation mapping | Warnings attach to affected lifts, trips and outdoor service slots |
| 10 | Warning UI | Dashboard, transport and service screens show relevant warnings |
| 11 | Manual cancel-for-weather | Staff can approve cancellation; no automatic cancellation |
| 12 | Booking/charge consequences | Bookings cancel and charges void with reason `weather` |
| 13 | Guest information print | Affected guest list can be printed |
| 14 | Avalanche daily entry | Staff enters level 1–5 and audit information |
| 15 | Private lobby link and read-only page | No-login private-link page shows required information |
| 16 | Lobby updates and attribution | Closed lift appears within one minute; credits displayed |
| 17 | Acceptance and regression tests | Weather threshold and cancellation scenarios pass |

## 16. Test Scenarios

### 16.1 Weather point tests

- Every building can be represented as a weather point.
- Every lift station has an associated weather point or an explicitly linked building point.
- Coordinates and altitude are stored and returned.
- Valley and summit forecasts are treated as separate points.

### 16.2 Provider integration tests

- Backend calls MET Norway, not the browser.
- User-Agent is present and meaningful.
- Requests follow provider caching requirements.
- Fetch is scheduled hourly.
- Successful forecast response is stored.
- API failure leaves the last successful forecast visible.
- Last successful update time is displayed.
- One failed point fetch does not erase forecasts for other points.

### 16.3 Forecast display tests

- Current conditions are available.
- Next 48 hours are displayed hourly.
- Approximately 9-day forecast is available at the provider's supported resolution.
- Temperature, wind, gusts, direction, precipitation, snow/rain, cloud/fog, humidity and UV are represented where supplied by the provider.
- Snow/rain display is based on conditions at the relevant altitude.
- Tenant units are respected.
- MET Norway attribution appears.

### 16.4 Limit and warning tests

- Limits can be configured independently per weather point.
- Forecast values crossing a limit create a warning.
- Warning records identify the metric and affected time window.
- Only operations associated with the relevant point/building/time are targeted.
- Relevant lifts are warned.
- Relevant trips are warned.
- Relevant outdoor service slots are warned.
- Dashboard shows warnings.
- Transport screen shows relevant warnings.
- Service screen shows relevant warnings.
- Re-running hourly evaluation does not create uncontrolled duplicate warnings.

### 16.5 Manual cancellation tests

- Threshold crossing alone does not cancel a trip/lift/slot.
- Unauthorised staff cannot cancel for weather.
- Authorised staff can cancel an affected trip, slot or lift day.
- Related bookings are cancelled.
- Existing charges are voided through Module C.
- Void reason is exactly `weather`.
- Void records retain actor and timestamp.
- Affected guest list can be printed.
- Retrying a cancellation does not duplicate voids or damage folio balances.

### 16.6 Avalanche tests

- Staff can enter values 1 through 5.
- Values outside 1–5 are rejected.
- Daily entry stores applicable date and staff audit information.
- Lobby screen displays the current day's value.
- Missing current-day entry is not silently presented as an official current value.

### 16.7 Lobby screen tests

- Private link grants read-only access without staff login.
- Invalid/revoked link is rejected.
- Lobby screen shows valley weather.
- Lobby screen shows summit weather.
- Lobby screen shows lift status.
- Lobby screen shows avalanche level.
- Lobby screen shows today's activities.
- Lift status change to closed appears within one minute.
- Lobby screen does not call MET Norway directly.
- Weather and map attribution are shown where applicable.

### 16.8 Client acceptance scenarios

**Scenario A — Lowering a limit creates warnings**

Given weather points and associated operations exist, when an authorised owner lowers a configured limit and the stored forecast crosses the new threshold, warnings appear on the matching lift, trips and outdoor service slots in the relevant time window.

**Scenario B — Weather cancellation leaves folios correct**

Given an affected trip or service slot has bookings and existing charges, when authorised staff uses `Cancel for weather`:
- affected bookings are cancelled;
- associated charges are voided with reason `weather`;
- folio balances are correct;
- guest information list is printable;
- the operation is not cancelled before staff confirmation.

## 17. Client Questions to Resolve

The PDF defines the main behaviour but leaves some implementation details open:

1. Who is allowed to configure weather limits: Owner only, or specific managers too?
2. Which exact metrics and operators are required for every limit?
3. What aggregation window defines “precipitation per day”?
4. What exact fog metric/threshold should be used?
5. What temperature/precipitation rule should determine snow versus rain if the provider does not supply a suitable type?
6. How should a weather point map to affected lifts, trips and outdoor service slots?
7. Does a warning affect a lift if either station crosses a limit, or only a specified station?
8. How much forecast time overlap is required for an operation to be considered affected?
9. Should warning clearance be automatic when the forecast no longer crosses the limit?
10. What stale-data age should trigger a visible warning to staff?
11. Which regional official bulletin is used for avalanche level at each complex?
12. Who can enter or correct the daily avalanche level?
13. What should the lobby screen display when today's avalanche level has not yet been entered?
14. Who is authorised to cancel a lift day, trip or outdoor service slot for weather?
15. Should cancellation require a second confirmation or manager approval?
16. What exact guest contact information can be printed for staff?
17. How should the private lobby link be issued, rotated and revoked?
18. Should lobby-screen refresh be automatic at a fixed interval? The PDF specifies the one-minute status propagation acceptance for closed lifts but not the general polling interval.

## 18. Definition of Done

Module 8 should not be marked complete until:

- Every building and lift station is represented as a weather point with coordinates and altitude.
- MET Norway Locationforecast is called from the server only.
- The background job fetches hourly and stores results.
- Provider User-Agent and caching rules are respected.
- API failure preserves the last forecast and its timestamp.
- Current, next 48 hours hourly and approximately 9-day forecast are displayed.
- Required weather variables and altitude-specific precipitation display are supported.
- Each complex can set limits per weather point.
- Crossing a limit attaches warnings to affected lifts, trips and outdoor slots in the relevant time window.
- Warnings display on dashboard, transport and service screens.
- The system never automatically cancels operations.
- Staff-approved weather cancellation cancels bookings, voids charges with reason `weather` and prints affected guest list.
- Staff can enter daily avalanche level 1–5.
- Private-link lobby screen is read-only and requires no staff login.
- Lobby screen shows valley/summit weather, lift status, avalanche level and today's activities.
- A lift set to closed appears on the lobby screen within one minute.
- MET Norway and OpenStreetMap attribution is displayed where required.
- Automated tests cover fetching, stale data, warning propagation and folio-safe cancellation.

## 19. Source References

- **Client MVP PDF:** *Mountain Tourism Complex Platform — MVP Scope v1*, Section 5, Module H, pages 5–6. This is the source for weather points, MET Norway API, hourly fetching, forecast horizon/variables, limits, warning targets, manual cancellation, avalanche level and lobby screen.
- **Client MVP PDF:** Technical rules, page 7. This requires server-side weather calls, cached/shared data and no browser-side provider calls.
- **Client MVP PDF:** Module G and owner/lobby information, page 6. This describes affected lift/trip warnings, weather cancellation, guest information print and lobby lift status.
- **Client MVP PDF:** Core financial rules, pages 2–3. These require append-only money, with charge voids retaining reason, actor and timestamp.
- **Project audit:** *Project Audit Report — Mountain Tourism Platform*, dated 3 November 2026, Module H maturity finding and recommended next actions. It reports Weather Limit/Warning CRUD but missing operation propagation and weather cancellation/void wiring, plus the shared B–I authentication defect.

---

**Implementation principle:** First inspect and reuse the existing WeatherLimit and WeatherWarning implementation. Fix the shared authentication issue, then add weather points, server-side hourly provider integration, stored forecast views, limit evaluation, operation mapping, staff-controlled cancellation and finally the private lobby screen. Keep the human decision rule intact: warnings can be automatic; cancellation cannot.
