---
name: task-interruption-safety
description: Use for long-running work or when the user asks to pause, resume, or preserve task progress.
---

# Task Interruption Safety

- Do not assume hardware state, battery percentage, or power status. Check only when a reliable authorized system tool is available and relevant.
- Before pausing, save files and record the work item, status, changed files, verification completed, blockers, and next step in the work item or change report.
- Do not start a long command if the user asked to stop or pause.
- Do not kill a running operation if doing so could corrupt data; report its state and ask before interruption when needed.
- Never commit or push as an automatic checkpoint. Follow explicit user approval and preserve ignored secrets.
- On resume, inspect current repository/work-item state before repeating operations.
