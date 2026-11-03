# Module 2 --- Hotel, Chalets & Huts

## Detailed Functional Analysis and Development Specification

**Project:** Mountain Tourism Complex Platform\
**Source scope:** Mountain Tourism Complex Platform --- MVP Scope v1
(October 2026), Section 5, Module B, pages 3--4\
**Implementation context:** Django REST Framework backend and Vue/Nuxt
frontend\
**Prepared:** 3 November 2026\
**Purpose:** Convert the client's Module B requirements into an
implementation-ready analysis while separating explicit PDF requirements
from recommendations and audit findings.

------------------------------------------------------------------------

## 1. Module objective

The Hotel, Chalets & Huts module manages accommodation inventory and a
guest's stay across the mountain complex.

It must support:

-   Room types and rooms located in any building.
-   Family chalets, booked in the same way as rooms.
-   Mountain hut dormitories where individual beds are sold separately,
    not the entire dormitory.
-   Seasonal room-night prices.
-   Board plans with a price per person per night.
-   Reservations with guest counts, accommodation allocation, deposit
    and arrival details.
-   A calendar grid organised by building, room and date.
-   Check-in that opens a stay and its guest folio.
-   Automatic nightly posting of room-night and board charges.

This module is not complete merely when CRUD screens exist. It must
satisfy the client's acceptance scenario: a three-night half-board stay
creates three room-night charges and three board charges, included
dinners are not charged again, and checkout ends with a zero balance.

## 2. Scope boundary and dependencies

### 2.1 In scope from the client's PDF

  -----------------------------------------------------------------------
  Area                                Explicit requirement
  ----------------------------------- -----------------------------------
  Room inventory                      Room types and rooms in any
                                      building

  Chalets                             Family chalets are booked like
                                      rooms

  Hut inventory                       Dormitory beds are sold
                                      individually

  Room pricing                        Price per night per season

  Board plans                         Room only, B&B, half board, full
                                      board

  Board pricing                       Price per person per night

  Reservation                         Dates, adults, children, room or
                                      bed, board plan, deposit, arrival
                                      mode and arrival details

  Calendar                            Grid by building, room and date

  Check-in                            Opens the stay and its folio

  Nightly posting                     Room nights and board are posted
                                      automatically every night

  Checkout outcome                    The Module B acceptance scenario
                                      expects checkout to end at zero
                                      balance
  -----------------------------------------------------------------------

### 2.2 Dependencies on other modules

  -----------------------------------------------------------------------
  Dependency                          Integration required
  ----------------------------------- -----------------------------------
  Module A --- Core & SaaS            Tenant context, buildings, staff,
                                      roles, seasons, settings

  Module C --- Guest Folio & Checkout Open folio on check-in; post room
                                      and board charges; checkout balance
                                      validation

  Module D --- Restaurants, Huts &    Identify meals included in a
  Shop                                guest's board plan so they are not
                                      sold/charged again

  Owner / Front Desk roles            Access to reservation and stay
                                      operations according to assigned
                                      permissions
  -----------------------------------------------------------------------

**Important:** The PDF defines the business outcomes, but does not
prescribe exact API URLs, database table names, status values,
cancellation policy, deposit accounting rules, or UI design. Those must
be agreed or designed during implementation rather than represented as
client-mandated requirements.

## 3. Accommodation inventory

### 3.1 Room Types

The system must allow the complex to define room types.

Examples such as Standard, Deluxe and Family are illustrative only; the
PDF does not prescribe a fixed list.

Expected capabilities:

-   Create and maintain room type records.
-   Associate rooms with a room type.
-   Use the room type when selecting accommodation for a reservation.
-   Support room types across buildings where appropriate.

**Recommended fields (design proposal):**

  Field            Purpose
  ---------------- ---------------------------------------------
  `name`           Room type name
  `description`    Optional description
  `max_adults`     Proposed occupancy validation
  `max_children`   Proposed occupancy validation
  `is_active`      Prevent new bookings against inactive types

The PDF does not explicitly require occupancy-capacity fields. Confirm
whether the client expects them before treating them as mandatory.

### 3.2 Rooms

Rooms may exist in any building in the complex.

Expected capabilities:

-   Create a room and assign it to a building.
-   Assign a room type.
-   Identify the room by a number or name.
-   Make rooms selectable during reservations.
-   Display room occupancy/availability on the calendar.

**Recommended fields (design proposal):**

  Field           Purpose
  --------------- -----------------------------------
  `building`      Building in which the room exists
  `room_type`     Room type reference
  `room_number`   Human-readable room identifier
  `name`          Optional display name
  `is_active`     Operational availability flag

A room's operational status (for example, out of service) is a useful
design consideration, but the supplied PDF does not define a room-status
workflow. Do not assume a full housekeeping or maintenance module is
included.

### 3.3 Chalets

The PDF states that family chalets are booked like rooms.

Therefore, the reservation and availability logic should not require a
separate chalet booking engine. A chalet should be represented as an
accommodation unit that can be selected and reserved through the same
core flow as a room.

**Implementation decision to confirm:** whether chalets should be
represented as a distinct accommodation type, a building/unit type, or a
room-like inventory record. The PDF does not dictate the schema.

### 3.4 Hut dormitories and individual beds

This is a specific business rule in the PDF:

> Hut dormitories sell single beds, not whole rooms.

Required behaviour:

-   Represent a hut dormitory.
-   Maintain its individual bed inventory.
-   Allow a reservation to allocate one or more individual beds.
-   Track availability per bed for the requested stay dates.
-   Prevent the same bed from being allocated to overlapping stays.

**Illustrative example:**

  Hut           Bed      Example availability
  ------------- -------- ----------------------
  Dormitory A   Bed 01   Available
  Dormitory A   Bed 02   Reserved
  Dormitory A   Bed 03   Available
  Dormitory A   Bed 04   Occupied

The availability values above are examples, not a status list specified
by the PDF.

**Critical validation:** overlapping reservations must not allocate the
same bed for intersecting stay dates. Apply the same date-overlap
protection to rooms and chalets.

## 4. Seasonal pricing

### 4.1 Room price

The PDF requires a **price per night per season**.

The platform already has the Season concept in Module A. Accommodation
pricing must refer to the tenant's configured seasons rather than
inventing a separate season system.

Required business behaviour:

-   Select the applicable season.
-   Define the room price per night for that season.
-   Resolve the applicable price for the dates in a reservation.
-   Preserve the price basis used for the guest's stay so later price
    changes do not silently rewrite historical charges.

The final point is a recommended accounting/data-integrity practice; the
PDF does not specify price snapshot implementation.

### 4.2 Board-plan price

The PDF requires each board plan to have a **price per person per
night**.

The reservation must identify the selected board plan. The nightly
posting process must calculate the board charge using the applicable
number of people and nights.

**Recommended design question:** Should board-plan prices also vary by
season? The PDF explicitly says room prices are per night per season,
while board plans have a price per person per night. It does not
explicitly state seasonal board-plan pricing. Confirm before
implementing seasonal board prices.

## 5. Board plans

The PDF specifies these four board plans:

  Board plan   Meaning
  ------------ --------------------------------------
  Room Only    Accommodation without included meals
  B&B          Bed and breakfast
  Half Board   Breakfast and dinner
  Full Board   Breakfast, lunch and dinner

Required capabilities:

-   Maintain the four plan types or equivalent configurable plan
    records.
-   Set a price per person per night.
-   Select a board plan in a reservation.
-   Use the selected plan when generating nightly charges.
-   Make meal-inclusion information available to the restaurant/POS
    module.

### Meal inclusion and POS integration

The client gives a concrete example: for a half-board guest, dinner is
included, while a bottle of wine ordered at the same table is charged to
the room.

The system must distinguish included meals from additional purchases.

  -----------------------------------------------------------------------
  Example item                        Expected treatment
  ----------------------------------- -----------------------------------
  Included dinner under Half Board    Must not be charged again

  Bottle of wine                      Additional charge

  Meal not included in the guest's    Treat according to POS billing
  board plan                          rules
  -----------------------------------------------------------------------

The PDF does not define all meal entitlement rules, such as whether
lunch is included on arrival/departure days or how missed meals are
handled. Those details need client confirmation.

## 6. Reservation management

### 6.1 Reservation data required by the PDF

A reservation must record:

-   Stay dates.
-   Number of adults.
-   Number of children.
-   Selected room or bed.
-   Selected board plan.
-   Deposit.
-   How the guest arrives: car, train, plane or bus.
-   Arrival time and station.

### 6.2 Suggested reservation workflow

1.  Staff starts a reservation.
2.  Staff enters the stay dates and guest counts.
3.  Staff selects a room, chalet or individual hut bed.
4.  System checks availability for the entire date range.
5.  Staff selects a board plan.
6.  System calculates the expected accommodation and board amount using
    applicable prices.
7.  Staff records the deposit.
8.  Staff records arrival mode, time and station.
9.  Staff saves the reservation.

This sequence is a recommended workflow derived from the required
reservation data. The PDF does not prescribe a particular screen
sequence or reservation status lifecycle.

### 6.3 Availability and date-overlap rules

At minimum, the system should prevent:

-   A room being reserved by two overlapping stays.
-   A chalet/unit being reserved by two overlapping stays.
-   An individual hut bed being reserved by two overlapping stays.

A typical overlap condition for date-only stays is:

`existing_check_in < requested_check_out AND existing_check_out > requested_check_in`

This treats the check-out date as the date the accommodation becomes
available to the next guest. Confirm the property's operational
check-in/check-out time rules separately; the PDF does not define times
of day.

### 6.4 Deposit

The PDF requires the reservation to capture a deposit.

It does not specify:

-   Whether the deposit is a fixed amount or percentage.
-   Whether it is paid at booking or can be outstanding.
-   Whether it is refundable.
-   How it is represented in the guest folio.
-   Whether a deposit can be split across payment methods.

Coordinate the accounting treatment with Module C rather than
implementing a separate, disconnected payment ledger.

### 6.5 Arrival details

The PDF lists arrival modes: car, train, plane and bus, with time and
station.

These details are important because the Transport module can use guest
arrival information to plan pickups. The transport workflow is specified
in Module G, not fully in Module B.

## 7. Reservation calendar

The PDF explicitly requires a **calendar grid by building, room and
date**.

Expected calendar capabilities:

-   Filter or navigate by building.
-   Show accommodation units against dates.
-   Display reserved/occupied periods.
-   Identify available periods.
-   Allow staff to inspect a reservation from its calendar allocation.

A useful design can include room and chalet rows, and a separate
hut/dormitory bed view. The separate bed view is a UI recommendation to
make the individual-bed rule usable.

The PDF does not specify drag-and-drop booking, colour conventions,
calendar library, or mobile layout. These are implementation choices.

## 8. Check-in and Stay lifecycle

The PDF's check-in rule is explicit:

**Check-in opens the stay and its folio.**

Expected process:

1.  Staff locates the reservation.
2.  Staff performs check-in.
3.  The system creates or activates the Stay record.
4.  The system opens the associated guest Folio through Module C.
5.  The accommodation allocation becomes occupied for the stay period.
6.  The stay becomes eligible for nightly posting.

Do not implement check-in as only a status-field update. It must trigger
the stay and folio business operations.

### Reservation vs Stay

A reservation represents the planned booking. A stay represents the
guest's active visit after check-in.

The distinction is important because:

-   A reservation can exist before arrival.
-   A stay begins at check-in.
-   Nightly charges belong to the stay/folio.
-   Checkout closes the stay after the billing conditions are met.

The PDF uses both terms but does not prescribe the exact status enum or
whether a reservation can be checked in partially.

## 9. Automatic nightly posting

This is a core acceptance requirement.

The PDF states:

**Room nights and board are posted automatically every night.**

### 9.1 Required behaviour

For each eligible active stay, the nightly process must:

1.  Identify the stay and its accommodation.
2.  Determine the applicable room-night price.
3.  Determine the applicable board plan.
4.  Calculate the board charge per person per night.
5.  Post the room-night and board charges to the guest folio.
6.  Avoid duplicate posting if the job retries.
7.  Retain the charge source and relevant tax details through Module C.

The no-duplicate retry behaviour is a recommended reliability
requirement for scheduled jobs. The PDF requires automatic posting but
does not prescribe the technical idempotency mechanism.

### 9.2 Client acceptance example

A guest stays three nights on Half Board.

Expected result:

  Charge type       Expected quantity
  --------------- -------------------
  Room nights                       3
  Board charges                     3

Included dinners must not be charged again by the restaurant. Checkout
must end at a zero balance.

### 9.3 Calculation model

For explanation:

-   Room total = sum of applicable room-night prices.
-   Board total = board price per person per night × chargeable guest
    count × chargeable nights.

The PDF requires a board-plan price per person per night, but does not
define whether children have a different rate, whether infants are
chargeable, or how partial nights are treated. Confirm these rules with
the client.

### 9.4 Operational safeguards recommended

-   Record which stay/night each posting represents.
-   Ensure a retry cannot post the same night twice.
-   Log failed nightly postings for staff review.
-   Use tenant-local date/time and timezone settings from Module A.
-   Do not edit or delete posted money; follow the append-only
    charge/void rules in the shared folio design.

These are implementation safeguards and should be aligned with Module
C's money rules.

## 10. Check-out and folio integration

The Module B acceptance scenario says checkout ends at a zero balance.
Module C explicitly defines the checkout gate: checkout is blocked until
the folio balance is zero.

Expected combined workflow:

1.  Staff opens the guest's stay/folio.
2.  Staff reviews room-night and board charges.
3.  Staff reviews other outlet charges.
4.  Guest payments are recorded through Module C.
5.  The system verifies the folio balance is zero.
6.  Checkout is allowed and the stay is closed.
7.  The accommodation becomes available after the stay's check-out
    boundary.

The actual payment recording, invoice, charge void and balance
enforcement belong to Module C. Module B should call or integrate with
those services rather than duplicate billing logic.

## 11. Suggested data model

The following is a proposed model decomposition for implementation.
These names are not mandated by the client's PDF and must be reconciled
with the existing repository before creating migrations.

  -----------------------------------------------------------------------
  Proposed model                      Responsibility
  ----------------------------------- -----------------------------------
  `RoomType`                          Defines accommodation room
                                      categories

  `Room`                              A room in a building

  `Chalet` or room-like accommodation Represents a family chalet if not
  unit                                modelled as a Room

  `HutDormitory`                      Represents a dormitory in a
                                      mountain hut

  `HutBed`                            Individual bookable bed

  `RoomSeasonPrice`                   Room price per night for a season

  `BoardPlan`                         Room Only, B&B, Half Board, Full
                                      Board

  `BoardPlanPrice`                    Board price per person per night

  `Reservation`                       Booking header and guest/date
                                      information

  `ReservationAccommodation`          Allocated room/chalet/bed

  `ReservationArrival`                Arrival mode, time and station

  `Stay`                              Active checked-in visit
  -----------------------------------------------------------------------

### Relationship overview

``` text
Tenant
 ├── Building
 │    ├── Room
 │    │    └── RoomType
 │    ├── Chalet / accommodation unit
 │    └── HutDormitory
 │         └── HutBed
 │
 ├── Season
 │    └── RoomSeasonPrice
 │
 ├── BoardPlan
 │    └── BoardPlanPrice
 │
 └── Reservation
      ├── ReservationAccommodation
      ├── ReservationArrival
      └── Stay
           └── Guest Folio (Module C)
```

Every tenant-owned record must follow the project's established tenant
database/routing model. The supplied audit says the project uses
DB-per-tenant direction; do not add a redundant `tenant_id` column to
tenant database tables without checking the established architecture.

## 12. Suggested API capability map

The PDF does not prescribe endpoint paths. The following is a proposed
DRF API surface, not a statement that these endpoints already exist.

  -----------------------------------------------------------------------
  Area                                Suggested API capability
  ----------------------------------- -----------------------------------
  Room types                          List, create, retrieve, update,
                                      deactivate

  Rooms                               List, create, retrieve, update,
                                      availability

  Chalets                             List, create, retrieve, update,
                                      availability

  Hut dormitories                     List, create, retrieve, update

  Hut beds                            List, create, retrieve, update,
                                      availability

  Room seasonal prices                List, create, update, resolve price

  Board plans                         List, create, retrieve, update

  Board prices                        List, create, update, resolve price

  Reservations                        List, create, retrieve, update,
                                      cancel if policy is defined

  Availability                        Search by accommodation
                                      type/building/date

  Calendar                            Retrieve allocations for a
                                      building/date range

  Check-in                            Check in reservation and open
                                      stay/folio

  Night audit                         Run or schedule nightly posting;
                                      inspect posting results

  Checkout                            Coordinate with Module C's
                                      zero-balance rule
  -----------------------------------------------------------------------

### API design rules

-   Use serializers for input validation and output representation.
-   Put business operations in service-layer functions, not only in
    ViewSet methods.
-   Enforce authentication and permissions on the server.
-   Use tenant-aware database routing already present in the project.
-   Add tests for date overlap, pricing, check-in, posting and checkout
    integration.
-   Keep money calculations consistent with the project's agreed
    integer-minor-unit rule. The audit found current folio amounts use
    `DecimalField`, while the technical scope says integer minor units;
    resolve this cross-module inconsistency before money integration.

## 13. Permissions and roles

The PDF defines these relevant roles:

-   Owner: can access everything in the complex.
-   Front desk: works with hotel, chalets and hut operations;
    reservations, check-in/check-out, folios and payments.

A practical permission design should allow front-desk staff to perform
reservation and stay workflows, while owner-level users can configure
accommodation inventory and pricing. The PDF does not provide a detailed
per-action permission matrix, so confirm exact create/edit/void rights
with the client.

Do not rely on hiding buttons in the frontend. The API must enforce
permissions.

## 14. Frontend screens

Suggested screens based on the required workflows:

1.  Room Types list and form.
2.  Rooms list and form, with building filter.
3.  Chalet inventory list and form.
4.  Hut dormitory and bed inventory.
5.  Seasonal room pricing.
6.  Board plan and board pricing setup.
7.  Reservation list and reservation form.
8.  Availability search.
9.  Building/room/date calendar.
10. Reservation detail with arrival information.
11. Check-in screen.
12. Active stays list and stay detail.
13. Nightly posting status/exception view for authorised staff.
14. Checkout handoff to folio.

These are proposed screens. The PDF does not supply wireframes or
prescribe a UI component library.

## 15. Current repository audit findings

The 3 November 2026 Project Audit Report provides the following status
for Module B:

  -----------------------------------------------------------------------
  Item                                Audit finding
  ----------------------------------- -----------------------------------
  Room model                          Present

  Stay model                          Present

  BoardPlan model                     Present

  Reservation                         Missing

  HutBed / bed inventory              Missing

  RoomSeasonPrice                     Missing

  Nightly posting job                 Missing

  Check-in creates folio              Not implemented; check-in only
                                      changes status

  Domain API authentication           Critical issue affecting B--I
                                      ViewSets
  -----------------------------------------------------------------------

The audit also reports that domain ViewSets for hotel and other B--I
modules set `authentication_classes = []` while requiring
`IsAuthenticated`. The audit identifies this as a critical
authentication defect. Fix and test the shared domain authentication
pattern before relying on protected Hotel APIs.

**Do not recreate existing models blindly.** Inspect current model
definitions, migrations, serializers, views, services and tests first.
Extend existing code and preserve migration history.

## 16. Development plan --- one step at a time

Recommended order:

  ------------------------------------------------------------------------
                          Step Task                  Completion evidence
  ---------------------------- --------------------- ---------------------
                             0 Audit existing Hotel  Protected Hotel API
                               code and fix/verify   works for an
                               domain authentication authenticated staff
                                                     user

                             1 Room Types            CRUD, validation and
                                                     tests

                             2 Rooms and building    Room management and
                               relationship          building filtering

                             3 Chalet representation Chalet can be
                                                     reserved through the
                                                     common accommodation
                                                     flow

                             4 Hut dormitory and     Individual bed
                               beds                  inventory and overlap
                                                     protection

                             5 Seasonal room pricing Correct price
                                                     resolved for a stay
                                                     date

                             6 Board plans and       Four plans and
                               pricing               per-person/night
                                                     calculation

                             7 Reservation           Required reservation
                                                     and arrival data
                                                     saved

                             8 Availability and      Building/room/date
                               calendar              grid and
                                                     conflict-safe
                                                     availability

                             9 Check-in and Stay     Check-in opens Stay
                                                     and Folio

                            10 Nightly posting       Three-night stay
                                                     produces three room
                                                     and three board
                                                     charges, without
                                                     duplicates

                            11 POS meal inclusion    Included meals are
                                                     not charged again;
                                                     extras are charged

                            12 Checkout integration  Checkout blocked
                                                     until folio balance
                                                     is zero

                            13 Regression and        Client's Module B
                               acceptance tests      "Done when" scenario
                                                     passes
  ------------------------------------------------------------------------

### Definition of done

Module B should not be marked complete until:

-   Rooms can be managed in any building.
-   Family chalets use the room-style reservation flow.
-   Hut dormitories sell individual beds.
-   Room prices are resolved per night per season.
-   The four board plans are supported with per-person/night pricing.
-   Reservations capture all PDF-required fields.
-   The calendar shows building, room and date allocations.
-   Check-in opens a Stay and Folio.
-   Nightly room and board charges post automatically.
-   Included meals are not charged again by POS.
-   Checkout succeeds only when the folio balance is zero.
-   Automated tests cover booking conflicts, nightly posting and the
    acceptance scenario.

## 17. Client questions to resolve before finalising implementation

The PDF leaves these details open. Record the answers rather than
guessing:

1.  Are children charged the same board-plan rate as adults, or is there
    a child rate?
2.  Does board-plan pricing vary by season?
3.  How are arrival day and departure day meals treated?
4.  What deposit rules apply: fixed amount, percentage, refundable, and
    when is it due?
5.  Can a reservation contain multiple rooms/beds and multiple board
    plans?
6.  Can one reservation be checked in partially?
7.  What are the exact check-in and check-out times?
8.  What reservation cancellation/no-show rules are required?
9.  Should chalets be stored as a distinct entity or as a room-like
    accommodation unit?
10. What should staff do when nightly posting fails or a stay has
    incomplete pricing?

## 18. Source references

-   **Client MVP PDF:** *Mountain Tourism Complex Platform --- MVP Scope
    v1*, Section 5, Module B, pages 3--4. It specifies room types/rooms,
    individual hut beds, seasonal room pricing, board plans, reservation
    fields, calendar grid, check-in, nightly posting and the three-night
    half-board acceptance test.
-   **Project audit:** *Project Audit Report --- Mountain Tourism
    Platform*, dated 3 November 2026, section 3 (Module maturity) and
    section 5 (findings). It reports existing Room, Stay and BoardPlan
    models, missing Reservation/HutBed/RoomSeasonPrice/nightly posting,
    check-in not opening a Folio, and the domain authentication defect.

------------------------------------------------------------------------

**Implementation principle:** Build and test one feature at a time.
First inspect the existing repository, then implement only the missing
or incomplete behaviour. Do not treat proposed fields, screens or
workflows in this analysis as explicit client requirements unless
confirmed.
