---
name: browser-e2e
description: Use for requested Playwright/browser acceptance work across the Nuxt/Vuetify staff UI and Laravel API, including complete desk workflows.
---

# Browser E2E

## Before running

- Inspect the existing frontend and test tooling. Do not assume an E2E directory or install browser packages.
- Use only a local/test environment the user authorized. Do not touch production or tenant data outside an approved test setup.
- Read test credentials from the configured ignored test environment. Never print or commit credentials.
- Follow `work-tracker`; if only authoring tests, also follow `testing-agent`.

## Test design

- Cover real staff flows and their API outcomes, including authorization and tenant boundaries where applicable.
- Prefer role, label, and accessible-name selectors, then stable test IDs. Avoid brittle DOM selectors and arbitrary sleeps.
- Use deterministic isolated data and clean up only data created by the test.
- Assert useful loading, validation, empty, success, and error states.

## Failure handling

- On a failure, activate `triage-before-fix` before changing application or test code.
- Create a bug report for a confirmed application defect before fixing it.
- Never weaken assertions, hide a failure, or call an unexecuted test successful.
- Run browser tests only when the user explicitly authorizes execution.

Use `change-report` and `verify-before-done` before closing tracked work.
