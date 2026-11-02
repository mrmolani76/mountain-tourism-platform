# Domain glossary

Use these names in DB, code, API, and docs.

| Term | Meaning |
|------|---------|
| Tenant | One complex. Every business table carries `tenant_id`. Staff belong to one tenant (from login). No subdomain tenancy in MVP. |
| Building | Place with type, map position, altitude. Also a weather point. |
| Outlet | Business unit in a building (restaurant, hut kitchen, shop, rental, spa, sports, transport, lifts). Own products, staff, reports. |
| Season | Winter, summer, or closed, with dates. Prices/menus/services can differ. |
| Stay | One visit in a room, chalet, or hut bed. |
| Board plan | Room only, B&B, half board, full board. |
| Folio | Bill of a stay. |
| Charge | One folio line. Always outlet, tax, source. Never edited/deleted. |
| Payment | Cash or card recorded by staff. Several per folio. Never edited/deleted. |
| Void | Correction for a charge/payment with reason, user, timestamp. |
| Service | Bookable hotel service (sauna, spa, ski lesson, guided hike, …). |
| Slot | One time slot of a service with capacity and staff. |
| Rental | One rented item from out to return. |
| Transport type | Own transport, lifts, or external. |
| Trip | One run of own transport: shuttle, taxi, pickup (vehicle, driver, seats). |
| Lift | Lift between two buildings with status. |
| Lift pass | Pass product that lets a guest ride. |
| Weather limit | Limit set by complex at a weather point (e.g. gusts). |
| Warning | Raised on lifts/trips/outdoor slots when forecast crosses a limit. Staff decide; system never auto-cancels. |
| Avalanche level | 1–5, entered by staff each morning from official bulletin. |
| Lobby screen | Read-only TV page via private link, no login. |
