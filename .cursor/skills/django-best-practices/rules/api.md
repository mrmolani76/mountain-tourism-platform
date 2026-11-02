# API (DRF)

- Authenticate every staff endpoint.
- Scope querysets by the logged-in user's tenant (super admin excepted for central ops).
- Validate input at serializer / explicit validator layer.
- Return stable error shapes; do not leak other tenants' existence in messages.
- Checkout and void endpoints enforce role rules from scope (e.g. manager voids).
- Prefer idempotent-safe designs for payments recording where practical.
