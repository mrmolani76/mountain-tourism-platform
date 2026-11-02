# Architecture

- Prefer domain packages under `backend/` (core, hotel, folio, pos, rental, services, transport, weather).
- Flow: ViewSet/APIView → Service → Model/QuerySet.
- Keep HTTP layer thin: auth, parse, call service, return response.
- Shared utilities only when used by 2+ domains.
- Do not put folio math or void rules only in the frontend.
