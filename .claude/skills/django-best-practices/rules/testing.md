# Testing

- Prefer pytest or Django TestCase consistently with whatever the repo adopts first.
- Must-cover when relevant:
  - Two tenants cannot read each other's data
  - Half-board stay posts room + board correctly; included meals not double-charged
  - Void does not delete charge rows
  - Rental item cannot be out twice
  - Slot capacity and staff double-booking
  - Checkout blocked until folio balance is zero
- Factory/fixtures must set `tenant_id` explicitly.
