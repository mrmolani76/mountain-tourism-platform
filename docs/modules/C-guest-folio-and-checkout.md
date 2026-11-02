# Module C — Guest folio and checkout

- **Phase**: 2 — full embed in [`work/phases/02-hotel-and-folio.md`](../../work/phases/02-hotel-and-folio.md)
- **PDF**: §5C + §4 money hard rule
- **Depends on**: A, B (stay)
- **Unlocks**: D–G charge-to-room, I reports, H weather voids

## Purpose
One folio per stay. Append-only charges/payments. Voids only. Checkout at zero. Invoice payload.

## Entities
Folio, Charge (outlet, tax, source), Payment (cash/card), Void (reason, user, time), Invoice payload.

## Rules (summary)
- Never edit/delete charge/payment amounts
- Void with reason; weather → `weather`
- Balance from active charges − payments
- Charge always has outlet + source
- Split payments = multiple rows
- Checkout rejected if balance ≠ 0
- Audit every charge/payment/void

## APIs
Folio read; charge/payment/void services; checkout; invoice; charge-to-room lookup.

## Done when
Lunch + lesson on room; weather void; half card/half cash; zero close; outlet reports from own charges.

## Tests
Immutability; void math; split tender; checkout gate; tenant isolation on folios.

## Package
`apps.folio`
