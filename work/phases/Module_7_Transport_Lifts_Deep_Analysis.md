# Module 7 — Transport & Lifts
## Detailed Functional Analysis and Development Specification

**Project:** Mountain Tourism Complex Platform  
**Source scope:** Mountain Tourism Complex Platform — MVP Scope v1 (October 2026), Section 5, Module G, pages 2 and 5–6  
**Project audit reference:** Project Audit Report — Mountain Tourism Platform, 3 November 2026  
**Implementation context:** Django REST Framework backend and Vue/Nuxt frontend  
**Purpose:** Translate the client's Transport & Lifts requirements into an implementation-ready work specification, separating explicit PDF requirements, existing repository findings and proposed implementation decisions.

---

## 1. Module objective

Module 7 manages how guests move around and arrive at the mountain complex. The PDF divides transport into three distinct groups:

1. **Own transport** — shuttle bus, taxi, private car, bicycle and motorbike.
2. **Lifts** — cable car, gondola, chairlift, funicular, underground funicular and elevator.
3. **External transport** — plane, train, metro/underground and intercity bus.

These groups must not be treated as identical booking types. Each group has different operational behaviour:

- Own transport manages vehicles, drivers, schedules, trips, seats, bookings, routes, stops, pickups and passenger lists.
- Lifts manage connections between buildings, opening hours, live status and lift passes.
- External transport records how guests arrive and depart and creates pickup/drop-off work for the transport desk.

The module must also integrate with:
- **Module A — Core & SaaS:** buildings, tenant settings, staff, roles and outlet/access control.
- **Module B — Hotel, Chalets & Huts:** reservation arrival/departure information.
- **Module D — Restaurants, Huts & Shop:** POS sale of lift-pass products.
- **Module C — Guest Folio & Checkout:** charge-to-room billing and financial records.
- **Module E — Equipment Rental:** bicycles and motorbikes are handled through rental, not the vehicle-trip workflow.
- **Module H — Weather:** warnings affect lifts, trips and relevant operations; staff decide whether to cancel.
- **Module I — Dashboard & Reports:** lift status appears on the lobby screen and transport operations contribute to reporting.

## 2. Source requirements and scope boundaries

### 2.1 Three transport groups defined by the PDF

| Group | Types listed in the PDF | What the system does |
|---|---|---|
| Own transport | Shuttle bus, taxi, private car, bicycle, motorbike | Vehicles, drivers, schedules, trips and bookings. Bicycles and motorbikes go through Module E Rental. |
| Lifts | Cable car, gondola, chairlift, funicular, underground funicular, elevator | Status, opening hours and lift passes |
| External | Plane, train, metro and underground, intercity bus | Records guest arrival/departure and creates a pickup or drop-off trip |

These groups and examples come directly from the client's scope. Do not collapse them into one generic transport record without preserving the distinct behaviours.

### 2.2 Explicit own-transport requirements

- Vehicles with seats.
- Drivers.
- Scheduled shuttle departures.
- On-demand taxi trips.
- Routes with stops on a map.
- Bookings.
- Printable passenger list.
- Staff draw routes on the map by defining stops and the path between them.
- No routing service is required.
- No driver or vehicle can be on two trips at the same time.
- A trip cannot be booked beyond its available seats.
- A train arrival recorded on a reservation creates a pickup trip for the transport desk to plan.
- The same pickup planning applies to departures.

### 2.3 Explicit lift requirements

- Each lift runs between two buildings.
- Each lift has a type.
- Each lift has opening hours.
- Each lift has a status: `open`, `closed` or `on hold`.
- A lift operator changes the status from a phone.
- Every status change is retained with its time and reason.
- Lift-pass products are supported.
- Lift passes can be sold at any POS or the front desk and charged to a room.
- No lift-gate hardware is included in the MVP.

### 2.4 Explicit acceptance criteria

The PDF's “Done when” checks require:

1. A driver cannot be double-booked.
2. A train arrival on a reservation creates a pickup trip.
3. A lift set to closed appears as closed on the lobby screen within one minute.

### 2.5 Explicit exclusions and boundaries

The PDF says:
- no routing service is needed because lift lines and trails are not represented properly by ordinary road routers;
- drivers do not log in for the MVP; they receive a printed passenger list;
- no lift-gate hardware is included in the MVP.

Do not add paid routing services, driver accounts/apps, RFID gates or turnstile integrations to this MVP.

The PDF does not explicitly define:
- GPS vehicle tracking;
- real-time driver location;
- route optimisation;
- online ride-hailing;
- automatic vehicle dispatch;
- automatic passenger notifications by SMS/WhatsApp;
- ticket scanning or gate validation;
- lift telemetry/PLC integration;
- maintenance/inspection management for vehicles or lifts;
- public transport timetable API integrations.

These are out of scope unless the client approves them separately.

## 3. Own Transport — Domain Behaviour

### 3.1 Supported own-transport types

The PDF lists:
- shuttle bus;
- taxi;
- private car;
- bicycle;
- motorbike.

Important exception: the PDF specifically says bicycles and motorbikes go out through **Module E Equipment Rental**. They should not be treated as a vehicle with a driver and trip booking in the own-transport trip scheduler.

The trip workflow should primarily support vehicles used for guest transport, such as shuttles, taxis and private cars.

### 3.2 Vehicles

The PDF requires vehicles with seat capacity.

Expected capabilities:
- create and maintain vehicle records;
- identify vehicle/type;
- record available seats;
- assign a vehicle to a trip;
- prevent the same vehicle from being assigned to overlapping trips;
- retain trip/vehicle history.

**Recommended fields (design proposal):**

| Field | Purpose | Source status |
|---|---|---|
| `name` / `code` | Vehicle identification | Recommended |
| `vehicle_type` | Shuttle, taxi, private car etc. | Based on PDF types |
| `seat_capacity` | Maximum passengers | PDF requires vehicles with seats |
| `registration_number` | Operational vehicle identification | Not specified; confirm |
| `is_active` | Prevent new assignment | Recommended |

The PDF does not prescribe registration number, make/model, luggage capacity or vehicle maintenance fields.

### 3.3 Drivers

The PDF requires drivers to be associated with own-transport trips.

Expected capabilities:
- maintain driver records;
- assign a driver to a trip;
- prevent the same driver from being assigned to overlapping trips;
- preserve driver assignment and trip history.

**Important:** Drivers do not log in for the MVP. They receive a printed passenger list. Do not build driver authentication, driver portal or driver mobile app as part of this scope.

The exact driver profile fields and identity requirements are not specified in the PDF. Reuse the existing staff/driver representation if one already exists, but do not assume that every driver must have a platform login.

## 4. Trips and Trip Types

### 4.1 Trip concept

The PDF defines a Trip as one run of own transport, such as:
- a scheduled shuttle departure;
- a taxi ride;
- a pickup.

A trip uses a vehicle, a driver and seats.

### 4.2 Scheduled shuttle departure

Expected behaviour:
- transport staff create a shuttle departure;
- select the route/stops;
- select departure date/time;
- assign a vehicle;
- assign a driver;
- expose available seats for booking;
- create/print a passenger list.

The PDF does not prescribe recurrence patterns, daily timetable templates or shuttle frequency configuration. Those details should be confirmed.

### 4.3 On-demand taxi trip

The PDF explicitly includes on-demand taxi trips.

Expected behaviour:
- transport desk creates a trip for a requested journey;
- pickup and destination/stops are recorded;
- vehicle and driver are assigned;
- seats are checked;
- trip booking/passenger information is retained.

The exact taxi quote, pricing and payment model are not specified in Module G. Do not invent a fare calculator or taxi meter integration.

### 4.4 Pickup and drop-off trips

A pickup is created from guest arrival information recorded on a reservation. The same principle applies to departure/drop-off planning.

The transport desk needs to review and plan these created tasks, including assigning a suitable vehicle and driver.

The PDF says the system creates a pickup trip for the transport desk to plan. This should not be interpreted as the system automatically dispatching a driver.

## 5. Routes, Stops and Map Drawing

### 5.1 Explicit requirement

Routes are drawn on the map by staff:
- staff define stops;
- staff draw the path between stops.

The PDF explicitly says no routing service is required because lift lines and trails are not represented by ordinary road-routing services.

### 5.2 Required behaviour

- Staff can define a route.
- Staff can add ordered stops.
- Staff can draw or define the route path on a map.
- A trip can reference a route.
- The route and its stops can be shown to transport staff.
- Passenger lists can identify the relevant trip/route/stops.

### 5.3 Important implementation boundary

The map is for staff-defined route geometry, not automated navigation.

Do not:
- call a paid directions API;
- calculate road routes automatically;
- assume roads connect all mountain locations;
- replace staff-drawn lift/trail paths with road router results.

The wider platform technical rules require free map technology and credit for OpenStreetMap. Use the project's selected free OpenStreetMap-based map stack, such as the already agreed Leaflet or MapLibre approach, subject to the existing repository.

### 5.4 Suggested route data

The PDF does not prescribe the storage format. A practical design may store:
- route name;
- ordered stop records;
- stop-to-building or map-position references;
- staff-drawn path geometry;
- route active status.

Confirm whether stops must always map to Module A buildings or can also be arbitrary map points.

## 6. Trip Bookings, Seats and Conflict Prevention

### 6.1 Seat-capacity rule

The PDF says a trip cannot be booked over its seats.

Expected validation:

`remaining_seats = vehicle_seat_capacity - confirmed_passenger_count`

A new booking must be rejected if it would exceed the vehicle's available seats.

If the trip has a smaller operational capacity than the vehicle, confirm whether a trip-specific seat limit is required. The PDF only explicitly states vehicle seats and a trip cannot be booked beyond its seats.

### 6.2 Vehicle conflict rule

The same vehicle cannot be on two trips at the same time.

For two trip intervals, an overlap exists when:

`existing_start < requested_end AND existing_end > requested_start`

Reject a new assignment if the same vehicle already has an overlapping trip.

### 6.3 Driver conflict rule

The same driver cannot be on two trips at the same time.

Apply the same interval-overlap validation to the driver assignment. This is an explicit client acceptance criterion.

### 6.4 Concurrency

A simple frontend check is not enough. Two transport staff members could assign the same driver or vehicle simultaneously.

The backend must perform conflict validation in a transaction and use database-safe locking/constraints where appropriate. Automated tests should cover concurrent assignment attempts.

### 6.5 Trip duration

The PDF requires time conflict detection but does not prescribe how trip duration is calculated. The trip needs a start and end time or an equivalent duration rule to test overlaps. Confirm whether staff enter the expected end time or whether a duration is configured per trip type/route.

## 7. Reservation Arrival and Departure Integration

### 7.1 Source data

Module B reservations record how the guest arrives:
- car;
- train;
- plane;
- bus;
- with time and station.

The Transport module consumes this arrival/departure information.

### 7.2 Pickup creation

The PDF's example:

A reservation says the guest arrives by train at **14:20 at the valley station**. The system creates a pickup trip for the transport desk to plan.

Expected flow:
1. Front desk creates/updates the guest reservation.
2. Arrival mode, time and station are saved.
3. The platform identifies that a pickup is required.
4. A pickup trip/task is created and linked to the reservation/guest.
5. It appears in the transport desk's planning queue.
6. Transport staff choose the route/stop, vehicle, driver and available seats.
7. Passenger list can be printed.

### 7.3 Departure/drop-off

The PDF says the same applies to departures.

Expected flow:
1. Departure details are recorded on the reservation.
2. A drop-off planning task/trip is created.
3. Transport desk assigns vehicle/driver and plans the run.
4. Passenger list is available for printing.

### 7.4 Avoid duplicate trips

The PDF does not prescribe deduplication details, but the integration must avoid creating multiple pickup tasks each time a reservation is edited or re-saved.

Recommended safeguards:
- link the generated trip/task to the source reservation and arrival/departure leg;
- update an unplanned task when relevant reservation details change;
- do not blindly create a second task on every save;
- define what happens if an already planned pickup's arrival time changes.

Confirm whether multiple guests on one reservation always travel together or can require separate trips.

## 8. Printable Passenger List

### 8.1 Explicit requirement

The PDF requires a printable passenger list. It also states that drivers do not log in and receive a printed list.

### 8.2 Recommended passenger list contents

The PDF does not prescribe all print fields. A practical list may include:
- trip reference;
- service date and departure time;
- vehicle and driver;
- route/stops;
- passenger names;
- pickup point/station;
- relevant arrival/departure details;
- passenger count.

Only include personal data necessary for the driver's trip. The client must confirm whether guest phone numbers or other contact information should appear.

### 8.3 Print workflow

- Transport desk opens a planned trip.
- System renders the passenger list.
- Staff prints it for the assigned driver.
- Driver uses the printed list; no driver login is required.

The PDF does not prescribe paper size or a separate PDF download format. Browser printing is a reasonable implementation choice, but confirm if a downloadable PDF is required.

## 9. Lifts — Configuration and Operations

### 9.1 Lift types

The PDF lists:
- cable car;
- gondola;
- chairlift;
- funicular;
- underground funicular;
- elevator.

The type should be configurable or represented in a controlled choice list that can accommodate the client-defined lift types.

### 9.2 Lift connection

Each lift runs between two buildings.

Expected fields/relationships:
- origin building;
- destination building;
- lift type;
- operating hours;
- current status.

The origin and destination must reference the existing Building records in Module A. Do not create a duplicate building/location registry.

### 9.3 Opening hours

The PDF explicitly requires opening hours.

The exact format is not specified. A useful design may support:
- weekday-specific opening and closing time;
- seasonal opening hours if required;
- closed days or exceptions.

Seasonal lift opening hours are a sensible consideration in a winter/summer resort but the PDF only says opening hours. Confirm whether weekday/season-specific lift schedules are required before expanding the model.

### 9.4 Lift status

Allowed statuses explicitly listed:

| Status | Meaning |
|---|---|
| `open` | Lift is operating/open |
| `closed` | Lift is closed |
| `on hold` | Lift is temporarily on hold |

Do not silently rename or remove these client-defined states.

### 9.5 Lift operator mobile workflow

The PDF says the lift operator changes status from a phone.

Expected mobile-friendly workflow:
1. Operator opens the lift status screen.
2. Selects the relevant lift.
3. Chooses `open`, `closed` or `on hold`.
4. Enters a reason for the change.
5. Saves the status.
6. The system records who changed it and when.
7. Lobby screen reflects the new status within one minute.

A responsive web page is sufficient; the PDF says web-only and does not require a native app.

### 9.6 Status history

Every lift status change must be retained with:
- timestamp;
- reason.

For a reliable audit trail, also retain the authenticated staff member who made the change. The PDF explicitly requires time and reason, and the user identity is a recommended audit detail.

Do not simply overwrite the current status without retaining status history.

## 10. Lift Passes

### 10.1 Explicit requirement

The PDF requires lift-pass products such as:
- 1 day;
- 6 days;
- pedestrian;
- summer;
- adult;
- child.

These are examples of product variants. The complex must be able to offer the relevant pass products.

### 10.2 Sales channels

The PDF explicitly says lift passes can be:
- sold at any POS; or
- sold at the front desk;
- charged to a guest room.

The POS/front desk sales must use the shared product and billing architecture rather than a separate disconnected checkout system.

### 10.3 Lift pass product configuration

The exact product schema is not prescribed. A proposed pass product may describe:
- pass name;
- duration/validity category;
- eligible category (adult/child/pedestrian, etc.);
- season or use type (winter/summer);
- price;
- tax;
- active status.

Use the existing Module D Product/Category concepts where appropriate, while retaining lift-pass-specific attributes in a linked lift-pass configuration.

### 10.4 Issued pass record

A product definition and a purchased pass instance are different:

- **Pass product:** what can be sold, such as “6-day adult winter pass”.
- **Issued pass:** the pass purchased by a particular guest, with a purchase date and validity period.

A separate issued-pass record is a recommended design if the product must be associated with a guest/stay and a validity window. The PDF requires pass products and sales but does not specify activation, validity start rules, person binding or physical card issuance. Confirm these details.

### 10.5 No gate hardware

The PDF explicitly excludes lift-gate hardware from the MVP.

Therefore, the MVP should record/sell passes but does not need:
- RFID card readers;
- turnstiles;
- QR gate scanners;
- real-time gate authorisation;
- physical card encoding.

## 11. Weather Integration

Module H treats weather as an operational feature, not just a display widget.

### 11.1 Impact on transport and lifts

The PDF says weather warnings attach to affected:
- lifts;
- trips;
- outdoor service slots.

When a weather limit is crossed, warnings appear on the dashboard and transport/service screens.

### 11.2 Human decision rule

The PDF explicitly states:

**People decide; the system never cancels automatically.**

For Module 7:
- show relevant weather warnings on affected lifts and trips;
- allow authorised staff to decide whether to cancel;
- when staff use “Cancel for weather”, associated bookings are cancelled;
- charges are voided with reason `weather`;
- a guest list is printed for staff to inform passengers.

Do not automatically close a lift or cancel a trip merely because a threshold was crossed. The warning informs the responsible person.

### 11.3 Cancellation and financial integration

The shared money rule says a charge is never edited or deleted; it is voided with a reason, user and timestamp. Weather cancellations must use Module C's void mechanism and the exact reason `weather`.

The detailed weather threshold and warning generation belong to Module H. Module 7 must expose the operational affected trip/lift and execute the staff-approved cancellation consequences.

## 12. Suggested Data Model

The following model decomposition is a proposal, not a schema prescribed by the PDF. Compare it with existing `backend/modules/transport/` and core models before making migrations.

| Proposed model | Responsibility |
|---|---|
| `TransportType` | Transport type and group: own / lift / external |
| `Vehicle` | Vehicle details and seat capacity |
| `Driver` or existing staff relation | Driver assigned to trips |
| `TransportRoute` | Staff-defined route |
| `TransportRouteStop` | Ordered route stops |
| `TransportRouteGeometry` or geometry field | Staff-drawn path |
| `Trip` | Shuttle, taxi, pickup or drop-off run |
| `TripPassenger` / `TripBooking` | Passenger booking and seat allocation |
| `ReservationTransportLeg` or source reference | Link reservation arrival/departure to pickup/drop-off planning |
| `Lift` | Lift between two buildings, type and hours |
| `LiftStatusHistory` | Status, timestamp, reason and actor |
| `LiftPassProduct` | Lift-pass product details linked to product catalogue |
| `IssuedLiftPass` | Guest-specific purchased pass/validity, if required |

### Relationship overview

```text
Tenant database
 ├── Building (Module A)
 │    └── Lift
 │         ├── origin_building
 │         ├── destination_building
 │         └── LiftStatusHistory
 │
 ├── TransportType
 ├── Vehicle
 ├── Driver / Staff
 ├── TransportRoute
 │    └── TransportRouteStop
 │
 ├── Trip
 │    ├── Vehicle
 │    ├── Driver
 │    ├── Route
 │    └── TripPassenger / TripBooking
 │
 ├── Reservation arrival/departure (Module B)
 │    └── Pickup/drop-off Trip planning
 │
 ├── Product / Category (Module D)
 │    └── LiftPassProduct
 │         └── IssuedLiftPass
 │
 └── Folio / Charge / Payment (Module C)
      └── Lift pass and eligible trip charges
```

### Data design notes

- Reuse existing `Vehicle`, `Driver` and `Trip` models if they already represent these responsibilities.
- Reuse Module A Building and Staff.
- Reuse Module B reservation arrival/departure data.
- Reuse Module D product catalogue and POS channels for lift pass sales.
- Reuse Module C Folio/Charge/Payment for room billing.
- Preserve the distinct transport groups. External transport is arrival/departure information; it is not an internally operated vehicle with a driver.
- Follow the existing DB-per-tenant architecture. Do not add redundant `tenant_id` fields to tenant tables without an architecture decision.

## 13. Suggested API Capability Map

The PDF does not prescribe endpoint URLs. This is a proposed DRF capability map, not a claim that the routes already exist.

| Area | Suggested API capability |
|---|---|
| Transport types | List and configure own/lift/external types |
| Vehicles | List, create, update, deactivate, view seat capacity |
| Drivers | List/select drivers available for trip assignments |
| Routes | Create/update route and map geometry |
| Route stops | Add, order, update and remove stops |
| Trip | Create, update, plan and list shuttle/taxi/pickup/drop-off trips |
| Trip availability | Check vehicle, driver and seat availability |
| Trip booking | Add/list/cancel passenger bookings |
| Passenger list | Render printable passenger manifest |
| Reservation integration | Create/update pickup/drop-off planning task from reservation arrival/departure |
| Lifts | List, create, update lift and opening hours |
| Lift status | Update open/closed/on-hold status with reason |
| Lift history | Retrieve status changes with time/reason/actor |
| Lift passes | Configure pass products and sale information |
| Issued passes | Record purchased pass and guest reference if needed |
| Weather warning | Display warning on affected trip/lift |
| Weather cancellation | Execute staff-approved cancellation, booking cancellation, charge void and guest list print |

### Backend rules

- Use the established project authentication and tenant database routing.
- The 3 November audit identifies a critical authentication defect across B–I ViewSets: `authentication_classes = []` is set while `IsAuthenticated` is required. Fix and test this before depending on Transport APIs.
- Enforce Transport Desk and Lift Operator permissions on the server.
- Check that assigned drivers and vehicles are available for the entire trip interval.
- Enforce vehicle seat capacity on the backend.
- Protect against concurrent assignments/bookings.
- Use the authenticated request user as status-change/void actor; do not trust actor IDs from request bodies.
- Keep lift status changes append-only in a history table.
- Use Module C for monetary charges and voids.
- Do not introduce external paid routing APIs.

## 14. Frontend Screens

Suggested screens for the required workflows:

### Own transport
1. Transport dashboard.
2. Vehicle list and form.
3. Driver selection/availability.
4. Route list and route editor.
5. Map route drawing screen with ordered stops.
6. Shuttle schedule/trip list.
7. Create scheduled shuttle trip.
8. On-demand taxi trip screen.
9. Pickup/drop-off planning queue.
10. Trip passenger booking screen.
11. Vehicle/driver conflict feedback.
12. Printable passenger list.

### Lifts
13. Lift list and lift configuration.
14. Lift origin/destination building setup.
15. Opening hours setup.
16. Mobile-friendly lift operator status screen.
17. Lift status history.
18. Lift-pass product configuration.
19. Lift-pass sales entry through POS/front desk integration.

### Weather and lobby integration
20. Weather warning indicator on lift/trip screens.
21. Weather cancellation action and guest information print list.
22. Lobby screen lift-status display (shared with Module I).

These are recommended screen boundaries. The PDF does not prescribe layouts or a specific component library.

## 15. Existing Repository Audit Findings

The Project Audit Report dated 3 November 2026 reports:

| Item | Audit finding |
|---|---|
| Trip model | Present |
| Vehicle model | Present |
| Driver model | Present |
| Transport APIs | Partial CRUD / thin business depth |
| Lift model | Missing |
| LiftPass model | Missing |
| Lift status history | Not reported as implemented |
| Route drawing / stops | Not verified as complete |
| Driver/vehicle conflict logic | Not verified as complete |
| Seat-capacity validation | Not verified as complete |
| Reservation pickup integration | Not implemented/verified |
| Domain API authentication | Critical defect applies to Transport and other B–I ViewSets |

The audit describes Module G as partial transport support with missing Lift and LiftPass models.

**Do not rebuild Vehicle, Driver or Trip without inspecting existing code.** Review current model definitions, migrations, serializers, views, services, URLs and tests. Preserve migration immutability and extend the working implementation.

## 16. Recommended Development Sequence

Implement one step at a time, with code and automated tests before moving forward.

| Step | Task | Completion evidence |
|---:|---|---|
| 0 | Inspect existing Transport app and fix/verify domain API authentication | Authenticated Transport Desk requests work; unauthorised requests fail |
| 1 | Transport type groups | Own / Lifts / External behaviours represented distinctly |
| 2 | Vehicle and driver availability | Vehicle seat capacity and driver records usable |
| 3 | Route and stop management | Staff can define ordered stops and map path |
| 4 | Scheduled shuttle trip | Trip uses route, vehicle, driver and departure time |
| 5 | On-demand taxi trip | Transport Desk can create and assign taxi trip |
| 6 | Driver/vehicle overlap prevention | No driver or vehicle can be assigned to overlapping trips |
| 7 | Passenger booking and seat limit | Booking over available vehicle seats is rejected |
| 8 | Passenger list print | Driver receives printable passenger list; no driver login |
| 9 | Reservation arrival/departure integration | Train/plane/bus arrival creates pickup/drop-off planning task |
| 10 | Lift model and building connection | Each lift connects two existing buildings and has a type |
| 11 | Lift opening hours and status | Open / closed / on hold |
| 12 | Lift status history and phone UI | Every change records time and reason; operator can update from phone |
| 13 | Lobby status propagation | Closed lift appears on lobby screen within one minute |
| 14 | Lift pass products | Pass types and sales channels available |
| 15 | POS/front desk and Folio integration | Pass can be sold at POS/front desk and charged to room |
| 16 | Weather warning/cancellation integration | Staff sees warnings and can cancel with weather void/guest list |
| 17 | Acceptance and regression tests | All Module G “Done when” scenarios pass |

## 17. Test Scenarios

### 17.1 Transport group tests

- Own transport types are handled as vehicle/trip operations where applicable.
- Bicycle and motorbike rental is routed to Module E, not the trip scheduler.
- Lift types use Lift operations, not vehicle trip scheduling.
- External plane/train/bus records guest arrival/departure and creates internal pickup/drop-off planning work.

### 17.2 Vehicle and driver conflict tests

- A vehicle cannot be assigned to two overlapping trips.
- A driver cannot be assigned to two overlapping trips.
- Non-overlapping assignments are allowed.
- Concurrent assignment attempts cannot double-book the same driver or vehicle.
- An inactive or unavailable vehicle/driver cannot be newly assigned.

### 17.3 Seat capacity tests

- A trip can accept passengers up to the vehicle's seat capacity.
- A booking that exceeds remaining seats is rejected.
- Concurrent booking attempts cannot overbook the final seat(s).
- Cancelled passenger bookings release seats according to the agreed booking status policy.

### 17.4 Routes and passenger list tests

- Staff can create a route with ordered stops.
- Staff can draw/store the route path without a routing service.
- A trip can use a saved route.
- Passenger list displays the correct trip, route and passenger set.
- Driver can receive the printout without logging into the platform.

### 17.5 Reservation pickup tests

- A train arrival recorded on a reservation creates a pickup planning task.
- Arrival time and station are retained.
- Saving the same reservation again does not create duplicate pickup trips.
- Changing arrival information updates or flags the linked planning task according to the agreed rule.
- Departure information creates a drop-off planning task.

### 17.6 Lift tests

- Lift connects two existing buildings.
- Lift type is stored.
- Opening hours are available.
- Lift status accepts only open, closed or on hold.
- Status change requires/records a reason.
- Status history records timestamp and actor.
- Lift operator can update status from a phone-sized browser UI.
- Closed status is visible on the lobby screen within one minute.

### 17.7 Lift-pass tests

- Lift pass examples such as 1-day, 6-day, pedestrian, summer, adult and child can be configured as products/variants.
- Pass can be sold at POS.
- Pass can be sold at front desk.
- Eligible sale can be charged to the guest room through Module C.
- No gate hardware or gate scan is required for MVP.

### 17.8 Weather tests

- Warning can be displayed on affected lifts and trips.
- Crossing a weather limit does not automatically cancel the trip or close the lift.
- Authorised staff can choose to cancel for weather.
- Related bookings are cancelled.
- Existing charges are voided with reason `weather` through Module C.
- Guest information list can be printed.

### 17.9 Client acceptance tests

**Scenario A — Driver double booking**

Given Driver A is assigned to a trip from 10:00 to 11:00, when staff attempts to assign Driver A to another trip that overlaps, the second assignment is rejected.

**Scenario B — Reservation pickup**

Given a reservation with train arrival at 14:20 at the valley station, when the reservation is saved, a pickup planning task appears for the Transport Desk with the arrival details.

**Scenario C — Lift status on lobby screen**

Given a lift is open, when a Lift Operator changes its status to closed with a reason, the status history records the change and the lobby screen displays closed within one minute.

## 18. Client Questions to Resolve

The PDF defines the main flows but leaves these operational details open:

1. What fields are mandatory for a vehicle (registration, make/model, luggage capacity, vehicle label)?
2. What driver information must be stored, and should drivers be represented as staff without login?
3. How is trip duration/end time entered or calculated for conflict checking?
4. Can a trip's passenger capacity be lower than the vehicle's physical seat capacity?
5. Are seats reserved per passenger, per family/group or by booking?
6. Can one reservation's guests be split across multiple pickup trips?
7. How are pickups grouped when several guests arrive at similar times/stations?
8. What happens when a train/flight arrival time changes after a pickup has already been planned?
9. Can routes include arbitrary map points, or must each stop be an existing Building?
10. Which free map library has already been selected in the repository: Leaflet or MapLibre?
11. Are shuttle schedules recurring by weekday/season, or entered as individual departures?
12. What exact taxi pricing/payment rules apply, if any?
13. Are lift opening hours different by weekday or season?
14. Can a lift have scheduled closures or only manual status changes?
15. What information must be included in a lift status-change reason?
16. What are the exact lift-pass validity rules: calendar days, consecutive days or activation date?
17. Are lift passes assigned to a named guest, or can they be transferable?
18. Does a pass require a physical card/printed identifier even though gate hardware is out of scope?
19. What tax and product rules apply to lift passes?
20. For weather cancellation, who is authorised to cancel and how are guests informed after the passenger list is printed?

## 19. Definition of Done

Module 7 should not be marked complete until:

- Own transport, lifts and external transport have distinct behaviours.
- Vehicles and drivers can be maintained and assigned to trips.
- Scheduled shuttle and on-demand taxi trips can be planned.
- Routes can be drawn by staff with stops and a path, without a routing service.
- Trips support passenger bookings and printable passenger lists.
- A driver and a vehicle cannot be double-booked.
- Trip passenger bookings cannot exceed available seats.
- Reservation train arrival creates a pickup planning task; departure creates a drop-off task.
- Lifts connect two buildings and have type, opening hours and status.
- Lift operator can update status from a phone browser.
- Every lift status change retains time and reason.
- Lobby screen reflects a closed lift within one minute.
- Lift-pass products can be sold at POS and front desk and charged to room.
- No gate hardware is required.
- Weather warnings can be shown and staff-controlled weather cancellation follows booking/void/print rules.
- Automated tests cover conflicts, capacity, pickups, lift status history and lobby propagation.

## 20. Source References

- **Client MVP PDF:** *Mountain Tourism Complex Platform — MVP Scope v1*, Section 5, Module G, pages 5–6. This is the source for transport groups, vehicle/driver/trip operations, staff-drawn map routes, no routing service, seat and double-booking restrictions, reservation pickups/drop-offs, lift operations and passes.
- **Client MVP PDF:** Users and roles, page 2. It states that drivers do not log in for the MVP and receive a printed passenger list.
- **Client MVP PDF:** Core concepts and money-flow rules, pages 2–3; technical rules, page 7. These define Trip, Lift and Lift Pass concepts, Folio/Charge/Payment, append-only money and integer minor units.
- **Client MVP PDF:** Module H and owner/lobby information, page 6. Weather warnings affect lifts and trips, staff make cancellation decisions, and lobby screens show lift status.
- **Project audit:** *Project Audit Report — Mountain Tourism Platform*, dated 3 November 2026, Module G maturity finding and recommended next actions. It reports existing Trip/Vehicle/Driver models, missing Lift/LiftPass models, partial transport functionality and the shared B–I authentication defect.

---

**Implementation principle:** First fix/verify the shared domain authentication defect and inspect the existing Trip, Vehicle and Driver code. Then complete own-transport scheduling and conflict rules, add reservation pickup/drop-off integration, implement Lift and LiftPass, and finally connect weather warnings and lobby status. Do not add driver login, routing services or lift-gate hardware to the MVP.
