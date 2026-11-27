---
name: testing-best-practices
description: Use when designing or reviewing Laravel API, domain, tenant-isolation, or frontend tests.
---

# Testing Best Practices

- Read nearby tests and use the repository's installed framework and conventions.
- Test observable behavior and project-owned constraints, not framework internals or implementation trivia.
- Cover changed decisions, validation, authorization, error paths, tenant isolation, and money-ledger invariants when relevant.
- Prefer a focused feature/API test for request-reachable behavior; unit-test isolated pure business logic.
- Use deterministic test data and isolate external services through existing fakes.
- Keep distinct regression coverage. Do not delete or weaken tests to get a green result.
- Do not add test dependencies without explicit approval.
- Do not run tests unless the user explicitly authorizes execution in the current request.

For Laravel details, use `.cursor/skills/laravel-best-practices/rules/testing.md`. For test-only edits, use `testing-agent`; for failures, use `triage-before-fix`.
