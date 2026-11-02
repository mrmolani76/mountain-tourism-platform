---
name: verify-before-done
description: >-
  MUST activate before claiming "done", "finished", "ready for review", or
  similar. Runs the done checklist and refuses to declare done if required
  verification and change report are missing.
---

# Verify Before Done

## Checklist

### 1. Request
- [ ] Each explicit ask addressed

### 2. Files
- [ ] List created/modified/deleted
- [ ] No unintended changes (`git diff --name-only`)

### 3. Tests (when verification allowed)
- [ ] Affected tests run with real results
- [ ] Failures → `triage-before-fix`

### 4. Non-negotiables if touched

| Area | Checked |
|------|---------|
| Tenant scope | [ ] Yes / [ ] N/A |
| Money append-only / voids | [ ] Yes / [ ] N/A |
| Role / outlet authz | [ ] Yes / [ ] N/A |
| Domain vocabulary names | [ ] Yes / [ ] N/A |
| MVP scope boundary | [ ] Yes / [ ] N/A |

### 5. Work kanban (if this was a tracked item)
- [ ] MD was in `inprogress/` while editing (not left in `todo/`)
- [ ] Completion Log filled
- [ ] MD moved to `work/completed/` with `status: completed`
- [ ] PLAN row in **Completed** only
- [ ] `work/changes/YYYY-MM-DD--NN-*.md` written
- [ ] SUMMARY refreshed
- [ ] `bash scripts/check-work-kanban.sh` OK (or equivalent manual check)

### 6. Git
- [ ] Asked **"Commit aur push karun?"**
- [ ] No commit/push without explicit yes

## Refusal

Do not declare done without change report, or when a tracked item skipped kanban moves, or when touched money/tenant behavior has no verification story.
