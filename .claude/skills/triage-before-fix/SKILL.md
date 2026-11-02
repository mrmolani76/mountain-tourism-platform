---
name: triage-before-fix
description: >-
  MUST activate when any test fails / is red / flaky. Classify failure before
  editing test or SUT. Never "fix the test to go green" when app code is broken.
---

# Triage Before Fix

## Step 1 — What does the test protect?

Auth, tenant isolation, money/void, board plan, rental conflict, capacity, validation, etc.

## Step 2 — Reproduce with real output

Run the failing command and capture full error (when execution is allowed in the session).

## Step 3 — Classify with evidence

| Bucket | Action |
|--------|--------|
| Test wrong/outdated | Update test to match current spec |
| App code broken | Fix app, not the assertion |
| Infra/env/data | Fix env/data; do not loosen assertions |
| Behavior changed | Update test + docs + code together |

Cite evidence (test line, error, spec).

## Step 4 — Fix only after classification

If money or tenant tests fail, treat as high severity. Do not delete coverage to pass CI.
