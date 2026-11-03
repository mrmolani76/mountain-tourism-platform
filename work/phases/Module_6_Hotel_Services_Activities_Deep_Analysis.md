# Module 6 — Hotel Services & Activities
## Detailed Functional Analysis and Development Specification

**Project:** Mountain Tourism Complex Platform  
**Source scope:** Mountain Tourism Complex Platform — MVP Scope v1 (October 2026), Section 5, Module F, pages 4–5  
**Project audit reference:** Project Audit Report — Mountain Tourism Platform, 3 November 2026  
**Implementation context:** Django REST Framework backend and Vue/Nuxt frontend  
**Purpose:** Convert the client's Module F requirements into an implementation-ready specification while clearly separating PDF requirements, audit findings and recommended design decisions.

---

## 1. Module objective

Module 6 manages bookable services and activities throughout the mountain complex.

The client wants **one booking engine for every service**. The complex should be able to add and configure its own service types; the development team should not build a separate software module for each service such as sauna, spa, gym, climbing wall or ski school.

The same configurable engine should support:
- indoor and outdoor services;
- services taking place in different buildings;
- services with a duration and a capacity;
- services with a price or that are included in a guest's stay;
- minimum-age rules;
- required staff such as therapists, instructors or guides;
- bookable time slots configured by weekday and season;
- bookings made by the front desk or service desk;
- pay-now or charge-to-room billing;
- guided hikes and ski tours with participant check-out/check-in tracking.

The module is not complete when Service and Slot CRUD screens exist. It must enforce the client's operational rules: capacity cannot be exceeded, a staff member cannot be assigned to overlapping lessons, cancelled bookings must have their charges removed through the correct financial process, and a guided outing cannot close while any participant is still marked as out.

## 2. Source requirements and scope boundaries

### 2.1 Explicit requirements from the client's PDF

| Area | Requirement stated in the PDF |
|---|---|
| Booking engine | One booking engine for every service |
| Configurability | The complex adds services itself |
| Service examples | Sauna, thermal bath, spa treatments, game room, gym, climbing wall, ski school, guided hikes, ski tours and other services |
| Duration | Each service has a duration |
| Capacity | Each service has a capacity |
| Price | Each service has a price or is included in the stay |
| Age | Each service can define a minimum age |
| Staff requirements | Service defines the staff it needs, e.g. therapist, instructor or guide |
| Environment | Service is indoor or outdoor |
| Location | Service is associated with the building where it happens |
| Slots | Time slots are configured by weekday and season |
| Staff scheduling | Staff are assigned to slots |
| Booking channels | Front desk or service desk |
| Payment methods | Pay now or charge to room |
| Guided outings | Participants are checked out at the start and checked back in at the end |
| Outing close rule | An outing stays open until everyone is marked back |

### 2.2 Explicit acceptance criteria

The PDF's “Done when” checks require:

1. A sauna slot cannot exceed its capacity.
2. One instructor cannot be in two lessons at once.
3. A cancelled booking removes its charge.
4. An outing with a missing participant cannot be closed.

These are business acceptance criteria, not optional UI enhancements.

### 2.3 Scope boundaries

The PDF does not explicitly define:
- public guest self-service booking portal;
- online payment gateway;
- recurring subscription or membership plans;
- waitlists;
- automatic reminders by SMS/WhatsApp/email;
- customer reviews or ratings;
- staff payroll or timesheets;
- instructor qualifications/certification management;
- medical/fitness questionnaires or waivers;
- dynamic pricing;
- third-party calendar synchronisation;
- weather-triggered activity cancellation automation;
- detailed equipment allocation for lessons;
- refund policy for cancelled bookings.

Do not silently add these capabilities to the MVP. Some may be useful later, but require separate approval and scope.

## 3. Core design principle: one configurable booking engine

The PDF explicitly says:

> One booking engine for every service. The complex adds services itself; we do not build a separate module per service.

This is the most important architecture decision for Module 6.

### 3.1 What this means

The software should not have independent booking implementations for:
- Sauna Booking;
- Spa Booking;
- Gym Booking;
- Ski Lesson Booking;
- Guided Hike Booking.

Instead, the platform should provide:
1. a configurable Service definition;
2. a schedule/slot definition;
3. staff assignment;
4. a common Booking workflow;
5. capacity and conflict validation;
6. a common billing integration.

A service's configuration determines how the common engine behaves.

### 3.2 Example service configurations

| Service | Duration | Capacity | Staff requirement | Location | Environment |
|---|---:|---:|---|---|---|
| Sauna | 60 min | 8 | Attendant, if configured | Main Hotel | Indoor |
| Spa treatment | 45 min | 1 | Therapist | Main Hotel | Indoor |
| Ski lesson | 90 min | 6 | Instructor | Mid Station | Outdoor |
| Guided hike | 180 min | 10 | Guide | Mountain Hut / route | Outdoor |
| Climbing wall | 60 min | 8 | Instructor | Main Hotel | Indoor |

These values are illustrative only. The client PDF provides the service attributes and examples, but no sample durations, capacities or prices.

## 4. Service Management

### 4.1 Required service configuration

Each service must support the following information as explicitly specified by the PDF:

| Attribute | Meaning |
|---|---|
| Service name/type | Name of the service, such as sauna, spa treatment or ski lesson |
| Duration | How long one booking/slot lasts |
| Capacity | Maximum number of participants in the slot |
| Price or included-in-stay | Either a price applies or the service is included in the guest's stay |
| Minimum age | Minimum allowed participant age |
| Required staff | Staff type/role needed, e.g. therapist, instructor or guide |
| Indoor/outdoor | Whether the activity takes place indoors or outdoors |
| Building | Building where the service happens |

### 4.2 Configurable service types

The PDF lists examples but also allows the complex to add anything else. Therefore, service names must be data/configuration, not fixed code branches.

The application should be able to support a new service using the same service setup and booking flow without requiring developers to add a new Django app or a new Nuxt page for that service.

### 4.3 Recommended fields

The following are implementation proposals to represent the PDF requirements. Exact field names and extra fields are not dictated by the PDF.

| Proposed field | Purpose | Source status |
|---|---|---|
| `name` | Service display name | Required concept |
| `description` | Explain the service | Recommendation |
| `duration_minutes` | Service duration | PDF requirement |
| `capacity` | Maximum participants per slot | PDF requirement |
| `pricing_mode` | Paid or included in stay | Represents PDF rule |
| `price_amount` | Price when paid | PDF requirement when not included |
| `minimum_age` | Minimum eligible age | PDF requirement |
| `required_staff_type` | Therapist/instructor/guide etc. | PDF requirement |
| `environment` | Indoor or outdoor | PDF requirement |
| `building` | Service building/location | PDF requirement |
| `is_active` | Prevent new booking without deleting history | Recommendation |

If the service needs more than one staff member or more than one staff type, use a service-to-staff-requirement relationship rather than a single text field. The PDF says “the staff it needs” and gives examples, but does not prescribe whether one or multiple staff members are required.

### 4.4 Price or included in the stay

The PDF gives two alternatives:
- the service has a price; or
- it is included in the stay.

The implementation must make this distinction explicit. A service included in a stay should not automatically create a second charge for the same entitlement.

The PDF does not define whether included services are included for all room plans, only selected board/accommodation packages, or only specific guests. Confirm the entitlement rule and reuse the relevant stay/package concepts from Module B rather than hard-coding “included for every hotel guest.”

## 5. Staff Requirements and Assignment

### 5.1 Staff types

The PDF gives examples of staff needed by a service:
- therapist;
- instructor;
- guide.

These should be treated as configurable staff requirements or role/type labels, connected to the existing staff system in Module A.

### 5.2 Staff assignment to slots

The PDF explicitly requires staff to be assigned to time slots.

Expected behaviour:
- a service slot can have its required staff assigned;
- the assigned staff member must be eligible for the service requirement;
- staff assignments must be visible to the front desk/service desk;
- the same staff member cannot be assigned to two overlapping lessons.

### 5.3 Conflict validation

The key acceptance rule is:

**One instructor cannot be in two lessons at once.**

This should apply to the relevant staff member across all services/slots, not only within one service type. For example, an instructor assigned to a ski lesson from 10:00–11:30 must not also be assigned to a climbing lesson that overlaps that period.

A conflict check should compare the assigned staff member's existing active assignments against the new slot's actual start and end times.

Typical overlap condition:

`existing_start < requested_end AND existing_end > requested_start`

For recurring schedules, expand or resolve each concrete occurrence before confirming a booking/assignment. The exact treatment of travel/buffer time between buildings is not defined in the PDF; do not invent a buffer rule without client approval.

### 5.4 Recommended staff data

Reuse Module A's staff records. Add only the service-specific relation or qualification/type data that is missing.

Potential design:
- staff member;
- staff type/role for the service;
- slot assignment;
- assignment start/end;
- assignment status.

The PDF does not require a full HR, payroll, certification or working-hours module.

## 6. Time Slot and Schedule Management

### 6.1 Explicit requirement

The PDF requires:

**Time slots by weekday and season; staff assigned to slots.**

This means the complex must be able to configure when a service is offered during the week and during which season.

### 6.2 Schedule configuration

A schedule should be based on:
- service;
- weekday;
- season;
- start time;
- duration (or service duration);
- capacity (normally inherited from the service unless the client wants slot-specific overrides);
- assigned staff.

The PDF does not prescribe whether slots are generated automatically for future dates or created individually. A reusable weekday/season schedule is the natural interpretation of the requirement, with actual dated occurrences resolved for booking and conflict checks.

### 6.3 Concrete slot occurrences

A reusable schedule rule and a bookable occurrence are different concepts:

- **Schedule rule:** “Ski lesson runs every Tuesday in Winter at 10:00.”
- **Slot occurrence:** “Ski lesson on Tuesday, 10 November, 10:00–11:30.”

This separation is a recommended technical design. It helps the application:
- calculate actual availability;
- prevent capacity overbooking;
- check staff conflicts;
- handle season/date changes;
- keep historical bookings tied to the actual slot that was booked.

### 6.4 Timezone

The platform's technical rules say timezone is configured per tenant and that “today” and scheduled work use the tenant's local time. Service schedules and slot occurrences must therefore use the tenant's timezone consistently.

## 7. Booking Management

### 7.1 Booking channels

The PDF specifies that bookings can be made from:
- Front Desk;
- Service Desk.

The same booking engine should serve both desks. The channel can be recorded for operational traceability, but the PDF does not mandate a particular channel field.

### 7.2 Required booking behaviour

A booking should:
1. identify the service;
2. identify the concrete date/time slot;
3. identify the participant or guest;
4. validate minimum age;
5. validate available capacity;
6. validate the slot is active and bookable;
7. determine whether the service is paid or included in the stay;
8. assign/retain relevant staff/slot details;
9. create the booking;
10. route billing to pay-now or charge-to-room when payment is applicable.

The PDF does not list the exact booking fields, participant identity format, booking statuses or cancellation statuses. These are implementation details to define consistently.

### 7.3 Capacity calculation

For a given slot:

`remaining_capacity = slot_capacity - active_booked_participants`

The booking must be rejected if the requested participant count would make the total exceed capacity.

Example:
- Slot capacity = 8.
- Active booking count = 6.
- New request = 3.
- Result = reject, because 6 + 3 > 8.

This must be enforced by the backend and protected against concurrent bookings. Two simultaneous requests must not both consume the same final available places.

### 7.4 Minimum age validation

The service configuration includes a minimum age. Booking validation should compare the participant's age at the service date with that minimum.

The PDF does not define:
- whether age is calculated from date of birth or entered age;
- whether a guardian can book for a minor;
- whether exceptions/waivers exist.

Use date of birth and calculate age at the event date if the client confirms DOB is available; do not accept an unverified age value as authoritative without a policy.

### 7.5 Service included in the stay

If a service is included in the guest's stay, booking should record the entitlement/usage without creating an extra duplicate charge.

The exact inclusion scope must be confirmed. The PDF does not define whether inclusion depends on board plan, room plan, a special package or a guest-specific entitlement.

## 8. Booking Billing: Pay Now or Charge to Room

### 8.1 Explicit requirement

The PDF says bookings are made from the front desk or service desk and can be:
- paid now; or
- charged to the room.

### 8.2 Pay now

If a service has a price and the customer pays immediately, record payment through the platform's shared financial model.

The PDF does not require a payment gateway. Module C defines cash/card as staff-recorded payments, not online gateway processing.

### 8.3 Charge to room

For a hotel guest:
1. resolve the active Stay;
2. resolve the relevant Folio;
3. create the service charge through Module C;
4. retain source/outlet/tax attribution;
5. link the charge back to the service booking.

Do not create a separate service-specific folio or payment ledger.

### 8.4 Included service

When the service is included in the stay:
- record the booking/usage;
- do not post a duplicate paid-service charge for the included entitlement;
- preserve enough information to report the booking/usage.

A service that is included for one guest may still be paid for another, depending on the entitlement rules. Confirm this with the client.

### 8.5 Cancellation and charge removal

The PDF's acceptance rule says a cancelled booking removes its charge.

The platform's general money rule says a charge/payment is never edited or deleted; it is voided with reason, user and timestamp. These rules must be reconciled as follows:

- Cancel the booking operationally.
- If a charge has already been posted, remove its financial effect using Module C's void operation with the required reason/actor/timestamp.
- Do not physically delete the charge record.
- If the booking has not yet created a charge, ensure cancellation does not create one.
- If payment/refund is involved, follow the shared financial policy; the PDF does not specify the refund workflow.

This interpretation preserves both the Module F acceptance outcome and the platform-wide append-only financial rule.

## 9. Guided Hikes and Ski Tours — Participant Headcount

Guided outings have a special operational workflow beyond ordinary timed service booking.

### 9.1 Explicit requirement

For guided outings:
- participants are checked out at the start;
- participants are checked back in at the end;
- an outing remains open until everyone is marked back.

This is a safety-critical operational record in the scope, not merely a normal booking status.

### 9.2 Distinguish three records

A recommended design separates:
1. **Service booking:** the participant booked a place on the guided outing.
2. **Outing occurrence:** the actual group outing taking place at a date/time with a guide.
3. **Participant attendance/headcount:** whether each booked participant has been checked out and checked back in.

This separation supports multiple participants in one outing and makes the close rule enforceable. The PDF does not prescribe exact model names.

### 9.3 Pre-outing preparation

Before the outing starts, staff should be able to see:
- outing/service;
- date and planned time;
- assigned guide(s);
- booked participant list;
- total expected headcount.

The PDF explicitly requires participant tracking, but does not define a mandatory briefing/checklist or emergency contact process.

### 9.4 Check out participants at start

At the start:
- staff opens the outing occurrence;
- staff checks each participating person out as they leave;
- the system records who has gone out;
- the outing remains open.

Do not mark all participants checked out merely because the outing has started. The requirement is to track participants individually.

### 9.5 Check participants back in at end

At return:
- staff checks each participant back in;
- the system records each return;
- headcount shows who is still out;
- the outing cannot close while any expected participant is not marked back.

### 9.6 Close validation

The core rule:

`can_close_outing = all_expected_participants_are_marked_back`

If even one participant is not marked returned, the close action must fail with a clear message showing the outstanding participant(s).

A missing participant must not be treated as returned automatically because the scheduled end time has passed.

### 9.7 Exception handling

The PDF does not specify how to handle a participant who is missing, transferred to another guide, taken to a hospital or otherwise accounted for through an exceptional process.

Do not create an override that silently marks a participant as returned. Any exception workflow requires client approval, audit logging and an explicit reason/authorised role.

## 10. Weather and Outdoor Services Dependency

Module H defines weather warnings for outdoor service slots at an affected building and says people decide; the system never cancels automatically.

For Module 6:
- outdoor services should be identifiable through the indoor/outdoor attribute;
- warnings from Module H may be displayed against affected outdoor slots;
- the staff/owner decides whether to cancel;
- if cancellation occurs, affected bookings and charges must follow the agreed cancellation/void workflow.

The PDF's weather section explicitly includes outdoor service slots among affected operations. The detailed warning propagation belongs to Module H, while Module F owns service booking and participant workflows.

Do not implement automatic weather cancellation in Module 6.

## 11. Suggested Data Model

The following model breakdown is a proposal, not a prescribed schema from the client. Compare it with existing `backend/modules/services/` models and migrations before making changes.

| Proposed model | Responsibility |
|---|---|
| `Service` | Configurable service definition and its attributes |
| `ServiceStaffRequirement` | Staff type/count required for the service, if multiple are supported |
| `ServiceScheduleRule` | Weekday/season schedule definition |
| `ServiceSlot` or `ServiceSlotOccurrence` | Concrete bookable date/time occurrence, duration and capacity |
| `ServiceSlotStaff` | Staff assignment to a concrete slot |
| `ServiceBooking` | Booking header/customer/service/slot/payment context |
| `ServiceBookingParticipant` | Participants included in a booking, if group bookings are supported |
| `GuidedOuting` | Actual guided outing occurrence and operational lifecycle |
| `OutingParticipant` | Individual booked participant and out/back timestamps/status |

### Relationship overview

```text
Tenant database
 ├── Building (Module A)
 ├── Staff / Staff roles (Module A)
 ├── Season (Module A)
 │
 └── Service
      ├── ServiceStaffRequirement
      ├── ServiceScheduleRule
      │    └── ServiceSlotOccurrence
      │         ├── ServiceSlotStaff
      │         ├── ServiceBooking
      │         │    └── ServiceBookingParticipant
      │         └── GuidedOuting (for guided hike / ski tour)
      │              └── OutingParticipant
      │
      ├── Stay / Guest (Module B)
      └── Folio / Charge / Payment (Module C)
```

### Data design notes

- Reuse existing Module A Building, Staff and Season records.
- Reuse Module B Stay/guest records where a hotel guest is booking.
- Reuse Module C Folio/Charge/Payment services for financial records.
- Do not create one database model or app per service type.
- The exact relationship between a ServiceSlot and GuidedOuting should be chosen after reviewing existing code; a guided outing may be a specialised occurrence of the common service slot.
- Follow the existing DB-per-tenant architecture. Do not add redundant `tenant_id` fields to tenant database tables without a documented architectural reason.

## 12. Suggested API Capability Map

The PDF does not prescribe URL paths. The following is a proposed DRF capability map, not a claim that the endpoints already exist.

| Area | Suggested API capability |
|---|---|
| Services | List, create, retrieve, update, activate/deactivate |
| Service configuration | Maintain duration, capacity, price/included mode, minimum age, environment and building |
| Staff requirements | Configure required staff type/count if supported |
| Schedule rules | Configure weekday and season availability |
| Slot occurrences | Generate/list concrete bookable slots |
| Staff assignment | Assign/remove staff and check time conflicts |
| Availability | Return remaining capacity for a slot |
| Booking | Create, retrieve, list and cancel booking |
| Guest lookup | Find/select guest and active stay |
| Billing | Pay now or post charge to room via Module C |
| Cancellation | Cancel booking and void existing charge via Module C |
| Guided outing | Open outing, list participants, track headcount |
| Participant checkout | Mark an individual participant as out |
| Participant return | Mark an individual participant as back |
| Outing close | Close only when every expected participant is back |

### Backend rules

- Enforce permissions on the API, not only in the frontend.
- Validate staff outlet/building access where applicable.
- Validate booking capacity on the server.
- Protect against concurrent overbooking.
- Protect against overlapping staff assignments.
- Validate minimum age using the agreed age source.
- Use tenant-local timezone and season settings.
- Keep booking state changes and participant headcount events auditable.
- Use Module C for charge, payment and void operations.
- Avoid trusting a client-submitted staff actor/user ID; use the authenticated request user for audit actor fields.

## 13. Frontend Screens

Suggested screens to support the required workflows:

1. Service list.
2. Create/edit service form.
3. Service configuration view.
4. Service staff requirements setup.
5. Weekday/season schedule setup.
6. Slot calendar/list.
7. Staff assignment view with conflict feedback.
8. Slot availability view.
9. Front Desk booking screen.
10. Service Desk booking screen.
11. Guest search/active stay selector.
12. Booking detail and cancellation screen.
13. Pay-now / charge-to-room selection.
14. Guided outing participant roster.
15. Participant check-out screen.
16. Participant check-in screen.
17. Outing headcount and close screen.
18. Outdoor service weather-warning indicator (integration with Module H).

These are recommendations for screen organisation. The PDF does not provide wireframes, UI component choices or exact navigation.

## 14. Existing Repository Audit Findings

The Project Audit Report dated 3 November 2026 reports the following for Module F:

| Item | Audit finding |
|---|---|
| Service model | Present |
| Slot model | Present |
| APIs | Models and ViewSets exist, but business workflows are thin |
| Booking headcount logic | Missing |
| Capacity enforcement | Not complete/verified |
| Staff slot conflict | Not complete/verified |
| `services/` business layer | Empty |
| Domain API authentication | Critical defect affects services and other B–I ViewSets |

The audit describes Module F as “Shell/Partial”: CRUD exists, but the PDF's capacity, staff conflict and guided outing acceptance rules are not implemented.

**Do not recreate Service and Slot models without inspection.** Review existing models, migrations, serializers, views, URLs and tests. Extend the current schema and preserve migration immutability.

## 15. Recommended Development Sequence

Implement one task at a time. Every step should include validation and automated tests.

| Step | Task | Completion evidence |
|---:|---|---|
| 0 | Inspect existing Services app and fix/verify domain API authentication | Authenticated desk users access authorised APIs; unauthorised requests are rejected |
| 1 | Complete Service configuration | All PDF service attributes are saved and validated |
| 2 | Staff requirements | Service identifies required therapist/instructor/guide type |
| 3 | Weekday and season schedule rules | Services can be configured by weekday and season |
| 4 | Concrete slot occurrences | Actual dated/time slots can be listed and booked |
| 5 | Staff slot assignment | Staff can be assigned and overlapping assignments are rejected |
| 6 | Capacity availability | Remaining capacity is calculated reliably |
| 7 | Booking workflow | Front Desk/Service Desk can create bookings |
| 8 | Minimum-age validation | Ineligible participant booking is rejected |
| 9 | Payment and Folio integration | Pay now and charge-to-room paths work; included services do not create duplicate charges |
| 10 | Cancellation and charge void | Cancelled booking is removed operationally and posted charge is voided through Module C |
| 11 | Guided outing roster | Booked participants appear against the actual outing |
| 12 | Participant checkout/check-in | Each person can be marked out and back individually |
| 13 | Outing close validation | Outing stays open while any participant is not marked back |
| 14 | Weather-warning integration | Outdoor slots can show Module H warnings; staff retain cancellation decision |
| 15 | Acceptance and regression tests | All four PDF “Done when” checks pass |

## 16. Test Scenarios

### 16.1 Service configuration tests

- Complex can create multiple service types without code changes.
- Service can define duration and capacity.
- Service can be priced or marked included in stay.
- Service can define minimum age.
- Service can define indoor/outdoor and a building.
- Service can define required staff type(s).
- Inactive service cannot accept new bookings.

### 16.2 Schedule tests

- Service availability can be defined by weekday.
- Service availability can be defined by season.
- Concrete slots use tenant-local time.
- Slots outside the applicable season/schedule cannot be booked.
- Historical bookings remain readable if schedule rules change later.

### 16.3 Capacity tests

- A slot can accept bookings up to its capacity.
- A request that exceeds remaining capacity is rejected.
- Cancellation releases capacity according to booking status.
- Concurrent requests cannot both take the final available places.

### 16.4 Staff conflict tests

- Staff member A can be assigned to one slot.
- Staff member A cannot be assigned to another overlapping slot.
- Non-overlapping assignments are allowed.
- Conflict detection operates across different services, not only the same service.
- Concurrent assignment attempts cannot create an overlapping schedule.

### 16.5 Minimum-age tests

- Participant meeting the minimum age can book.
- Participant below the minimum age is rejected.
- Age is calculated using the client-approved source and service date.
- Any guardian/exception policy is enforced only if specified by the client.

### 16.6 Booking and billing tests

- Front Desk can create a booking.
- Service Desk can create a booking.
- Paid service can be paid now.
- Hotel guest service can be charged to the correct Folio.
- Included service records usage without duplicate charge.
- Cancelling a booking releases the slot capacity.
- If a charge already exists, cancellation voids the charge through Module C rather than deleting the financial record.

### 16.7 Guided outing tests

- Outing has an expected participant roster.
- Staff can mark each participant as checked out at the start.
- Staff can mark each participant as checked back in.
- Outing cannot close while at least one participant is not marked back.
- Outing closes after all expected participants are marked back.
- Passing the planned end time does not auto-mark missing participants as returned.

### 16.8 Client acceptance tests

**Scenario A — Capacity**

Given a sauna slot capacity of N, when active bookings reach N, the next booking must be rejected.

**Scenario B — Instructor conflict**

Given an instructor assigned to a lesson from 10:00 to 11:00, when another overlapping lesson is assigned to the same instructor, the assignment must be rejected.

**Scenario C — Cancelled booking**

Given a paid/room-charged service booking, when it is cancelled, the booking is cancelled operationally and its existing charge is removed from the balance through the platform's void process.

**Scenario D — Guided outing missing participant**

Given an outing with several participants, when all but one are marked back, close must be blocked. It can close only after the outstanding participant is marked back or an explicitly approved exception process is followed.

## 17. Client Questions to Resolve

The PDF gives the main service attributes and acceptance rules but leaves these details open:

1. Can a service require more than one staff member, or more than one staff type?
2. Is capacity defined only at Service level, or can individual slots override it?
3. Can one booking reserve multiple participants in one request?
4. Can a participant make multiple bookings in the same time period for different services?
5. What exact participant data is required for minimum-age validation?
6. Are guardians/parent consent needed for participants under 18?
7. Which stay plans or guest categories include a service?
8. Can a service be partly included with an extra paid upgrade?
9. What is the cancellation deadline and refund policy?
10. If a booking is cancelled after payment, should the customer receive a cash/card refund, a folio credit or another treatment?
11. What schedule generation horizon should be used for weekday/season rules?
12. Should staff have setup/buffer time between services or buildings?
13. Can a guided outing have multiple guides?
14. Who is authorised to open, check participants out/back in and close an outing?
15. What approved exception procedure applies if a participant is missing or cannot be checked back in?
16. Should weather warnings from Module H be displayed on all outdoor service slots, and what manual cancellation process is expected?

## 18. Definition of Done

Module 6 should not be marked complete until:

- One configurable booking engine supports all service types.
- The complex can add services without code changes.
- Every service can define duration, capacity, price/included-in-stay, minimum age, required staff, indoor/outdoor and building.
- Time slots can be configured by weekday and season.
- Staff can be assigned to slots.
- Booking is available from Front Desk and Service Desk.
- Booking validates minimum age and remaining capacity.
- Pay-now and charge-to-room paths work through shared financial services.
- Cancellation releases capacity and removes an existing charge through Module C's void process.
- Guided outing participants can be checked out and checked back in individually.
- An outing cannot close while any participant remains unmarked as back.
- Automated tests cover capacity, staff overlap, cancellation/void and missing participant rules.

## 19. Source References

- **Client MVP PDF:** *Mountain Tourism Complex Platform — MVP Scope v1*, Section 5, Module F, pages 4–5. This is the source for one booking engine, configurable service types, service attributes, weekday/season slots, staff assignments, booking channels, payment paths and guided outing headcount workflow.
- **Client MVP PDF:** Core concepts and money-flow rules, pages 2–3; technical rules, page 7. These define Service/Slot concepts, Folio/Charge/Payment relationships, append-only money and integer minor units.
- **Client MVP PDF:** Module H, page 6. Weather warnings apply to outdoor service slots; people decide and the system does not cancel automatically.
- **Project audit:** *Project Audit Report — Mountain Tourism Platform*, dated 3 November 2026, Module F maturity finding and recommended next actions. It reports Service and Slot models/ViewSets, missing booking headcount logic, empty Services business services and the shared B–I authentication defect.

---

**Implementation principle:** Preserve the client's one-engine-for-all-services requirement. First inspect and reuse the existing Service and Slot implementation. Then build the business layer for schedules, slot occurrences, staff conflicts, capacity, bookings and participant headcount. Do not create separate booking modules per activity type, and do not automatically close or mark missing outing participants as returned.
