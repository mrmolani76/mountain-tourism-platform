# All-module kanban todos (executable)

Only `NN-*.md` files are pickup/complete items. Historical Module A design: [A-core-and-super-admin.md](A-core-and-super-admin.md).

**Order:** finish Module 1 (**29–33**), then **34** (shared auth), then modules in PDF dependency order.

| Module | IDs | Flow |
|--------|-----|------|
| 1 Core (closeout) | 29–33 | decision → audit → edge tests → policy → exit |
| Shared auth | 34 | Fix domain ViewSets auth before B–I depth |
| 2 Hotel | 35–39 | inventory/reservation → check-in+folio → nightly → calendar → exit |
| 3 Folio | 40–44 | cents → void/audit → charge-to-room → checkout/invoice → exit |
| 4 POS | 45–48 | season menu → board+folio → packed/shift → exit |
| 5 Rental | 49–51 | catalog → out/return/late → exit |
| 6 Services | 52–55 | config/slots → booking engine → outing → exit |
| 7 Transport | 56–59 | trips/conflicts → pickup → lifts/passes → exit |
| 8 Weather | 60–63 | MET → warnings → cancel/lobby → exit |
| 9 Reports | 64–66 | Today → range+CSV → MVP exit |

Deep specs: `work/phases/Module_*_Deep_Analysis.md`.

Rules: WIP = 1; API/backend first; no setup/frontend unless user asks.
