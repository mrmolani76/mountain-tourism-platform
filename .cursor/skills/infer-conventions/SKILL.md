---
name: infer-conventions
description: Use when the user asks to document or align coding conventions based on existing repository code.
---

# Infer Project Conventions

Document actual practice, not an aspirational rewrite.

1. Read `CLAUDE.md`, relevant rules/skills, and existing formatter/type-checker settings.
2. Sample representative files across the relevant module and layer.
3. Record a convention only when there are at least three consistent examples and no meaningful competing pattern.
4. Mark mixed patterns as conflicts; ask before choosing one or planning a cleanup.
5. Skip framework defaults, generated code, and behavior already enforced by tools.
6. Keep rule text short, scoped to the narrowest applicable paths, and in English.
7. Update all maintained mirrors and indexes when the user asks to persist the convention.

Do not use external rule-recording services or make a broad code sweep for a one-file task.
