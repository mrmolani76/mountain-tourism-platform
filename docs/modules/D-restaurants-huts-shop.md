# Module D — Restaurants, huts and shop

- **Phase**: 3 — full embed in [`work/phases/03-pos-rental-services.md`](../../work/phases/03-pos-rental-services.md)
- **PDF**: §5D
- **Depends on**: A (outlets), B (board on stay), C (charges/payments)

## Purpose
Seasonal products, tablet orders (API), board check, packed lunches, pay now or charge to room, cashier shifts, print payloads.

## Entities
Product, Category, Order, OrderLine, BoardCheck result, PackedLunchOrder, CashierShift, ticket/receipt payloads.

## Rules
- Season visibility
- Included board meal vs payable extras (wine)
- Charge lines keep outlet
- Packed lunch evening-before → morning kitchen list

## Done when
Half-board dinner included; wine charged to room.

## Package
`apps.pos`
