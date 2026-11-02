# Frontend Architecture

- Pages/routes by role or module (hotel, folio, POS, rental, transport, lifts, weather).
- Composables for API calls and shared state helpers.
- Prefer Pinia (or Nuxt-native state already in repo) for cross-page staff session state.
- Do not duplicate folio totaling logic on the client as the source of truth.
- Match folder conventions already present once the Nuxt app is scaffolded.
