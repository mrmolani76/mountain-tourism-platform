# Modules and “done when” (MVP §5)

## A. Core and super admin
Login; staff password reset by owners/managers (no email in MVP); super admin creates complexes/owners; buildings; outlets; staff+roles+outlet access; seasons; currency/tax/timezone/units; audit log for charge/payment/void.

**Done when:** two complexes on the same server cannot see each other's data, proven by automated test.

## B. Hotel, chalets and huts
Room types/rooms; hut dorms sell beds; season night prices; board plans; reservations (arrival mode/time/station); calendar; check-in opens stay+folio; nightly room/board posting.

**Done when:** 3-night half-board posts 3 room nights + 3 board charges; included dinners not charged again; checkout ends at zero balance.

## C. Guest folio and checkout
Folio lines with outlet/date/amount/tax; charge to room by room/guest; cash/card payments (recorded, no gateway); void with reason (manager); checkout blocked until zero; printable invoice.

**Done when:** summit lunch + ski lesson on room; lesson voided for wind; guest pays half card half cash; folio closes at zero; each outlet report shows only its sales.

## D. Restaurants, huts and shop
Products/menus by season; tablet orders; board check; packed lunches; kitchen ticket + 80mm receipt; pay now or charge to room; cashier shift open/close.

**Done when:** half-board dinner marked included; wine at same table charged to room.

## E. Equipment rental
Item types/sizes/codes; day price + deposit; rent to hotel guest or walk-in; return; damage fee; out list + overdue highlight.

**Done when:** one item cannot be rented twice at once; late return adds extra day.

## F. Hotel services and activities
One booking engine for all services; slots by weekday/season; staff on slots; pay now or charge to room; guided outing headcount open until all back.

**Done when:** sauna cannot over capacity; instructor not double-booked; cancelled booking removes charge; outing with missing participant cannot close.

## G. Transport and lifts
Own transport (vehicles/drivers/routes/trips), lifts (status/passes), external (arrival creates pickup). No driver login (printed passenger list). No gate hardware.

**Done when:** driver not double-booked; train arrival on reservation creates pickup; lift closed shows on lobby within a minute.

## H. Weather
MET Norway hourly cache; limits; warnings; cancel-for-weather voids; avalanche level; lobby TV screen.

**Done when:** lowering a limit warns matching lift/trips/outdoor slots; weather cancellation leaves folios correct.

## I. Owner dashboard and reports
Today dashboard; date-range reports + CSV.

**Done when:** report totals match folio charges + direct sales to the cent.
