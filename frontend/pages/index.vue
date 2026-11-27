<script setup lang="ts">
/**
 * Staff home — alpine "today" operations desk.
 * Data: GET /api/reports/today (graceful empty when unavailable).
 */
import type { TodayDashboard } from '@/composables/api-clients/useReportsApiClient'
import { formatCents } from '@/utils/money'

useHead({ title: 'Today' })

const store = authStore()
const reports = useReportsApiClient()

const user = computed(() => store.getUser)
const loading = ref(true)
const today = ref<TodayDashboard | null>(null)
const loadError = ref(false)

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 12)
    return 'Good morning'
  if (hour < 18)
    return 'Good afternoon'

  return 'Good evening'
})

const displayName = computed(() =>
  user.value?.prefixedUsername
  || user.value?.email?.split('@')[0]
  || 'team',
)

const roleLabel = computed(() =>
  user.value?.role?.roleName
  || user.value?.roleCode
  || 'Staff',
)

const dateLabel = computed(() => {
  const raw = today.value?.date
  const d = raw ? new Date(`${raw}T12:00:00`) : new Date()

  return d.toLocaleDateString('en-GB', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
})

const occupancySummary = computed(() => {
  const rows = today.value?.occupancy_by_building?.results ?? []
  const rooms = rows.reduce((sum, row) => sum + (row.rooms_total || 0), 0)
  const occupied = rows.reduce((sum, row) => sum + (row.occupied || 0), 0)

  return { rooms, occupied, pct: rooms > 0 ? Math.round((occupied / rooms) * 100) : null }
})

const arrivals = computed(() => today.value?.arrivals_today?.results ?? [])
const departures = computed(() => today.value?.departures_today?.results ?? [])
const warnings = computed(() => today.value?.weather_warnings?.results ?? [])
const lifts = computed(() => today.value?.lift_status?.results ?? [])

const deskLinks = [
  { title: 'Front desk', hint: 'Stays and check-in', to: '/hotel', icon: 'tabler-door-enter' },
  { title: 'Folios', hint: 'Charges and payments', to: '/folios', icon: 'tabler-receipt' },
  { title: 'POS', hint: 'Orders and settle', to: '/pos', icon: 'tabler-tools-kitchen-2' },
  { title: 'Rental', hint: 'Out and returns', to: '/rental', icon: 'tabler-skiing' },
  { title: 'Transport', hint: 'Trips and lifts', to: '/transport', icon: 'tabler-bus' },
  { title: 'Weather', hint: 'Limits and warnings', to: '/weather', icon: 'tabler-cloud-snow' },
  { title: 'Reports', hint: 'Today and ranges', to: '/reports/today', icon: 'tabler-chart-area-line' },
  { title: 'Staff', hint: 'Core settings', to: '/users', icon: 'tabler-users' },
]

function guestLabel(row: Record<string, unknown>): string {
  return String(
    row.guest_name
    || row.primary_guest_name
    || row.name
    || row.folio_id
    || row.id
    || 'Guest',
  )
}

function stayMeta(row: Record<string, unknown>): string {
  const room = row.room_code || row.room_name || row.unit_name
  const building = row.building_name

  return [room, building].filter(Boolean).map(String).join(' · ') || 'Stay'
}

async function loadToday() {
  loading.value = true
  loadError.value = false

  try {
    const data = await reports.today()
    today.value = data
    if (!data)
      loadError.value = true
  }
  catch {
    today.value = null
    loadError.value = true
  }
  finally {
    loading.value = false
  }
}

onMounted(loadToday)
</script>

<template>
  <div class="ops-home">
    <!-- Hero band -->
    <section class="ops-hero">
      <div class="ops-hero__glow" aria-hidden="true" />
      <div class="ops-hero__ridge" aria-hidden="true" />

      <div class="ops-hero__content">
        <p class="ops-hero__eyebrow">
          {{ dateLabel }}
          <span v-if="user?.tenantSlug"> · {{ user.tenantSlug }}</span>
        </p>
        <h1 class="ops-hero__title">
          {{ greeting }}, {{ displayName }}
        </h1>
        <p class="ops-hero__lede">
          Today on the mountain — arrivals, departures, open bills and weather.
        </p>
        <div class="ops-hero__meta">
          <span class="ops-pill">{{ roleLabel }}</span>
          <button
            type="button"
            class="ops-refresh"
            :disabled="loading"
            @click="loadToday"
          >
            <VIcon
              icon="tabler-refresh"
              size="18"
              :class="{ 'ops-spin': loading }"
            />
            Refresh
          </button>
        </div>
      </div>
    </section>

    <!-- Loading -->
    <div
      v-if="loading && !today"
      class="ops-loading"
    >
      <VProgressCircular
        indeterminate
        color="primary"
        size="36"
      />
      <span>Loading today…</span>
    </div>

    <template v-else>
      <p
        v-if="loadError"
        class="ops-banner"
      >
        Today figures could not load. Desk shortcuts below still work.
      </p>

      <!-- Pulse metrics -->
      <section class="ops-pulse">
        <div class="ops-pulse__item">
          <span class="ops-pulse__label">Open folios</span>
          <strong class="ops-pulse__value">{{ today?.open_folios.count ?? '—' }}</strong>
          <span class="ops-pulse__hint">
            Balance
            {{ today?.open_folios.balance_cents == null ? '—' : formatCents(today.open_folios.balance_cents) }}
          </span>
        </div>
        <div class="ops-pulse__item">
          <span class="ops-pulse__label">Revenue today</span>
          <strong class="ops-pulse__value">
            {{ today ? formatCents(today.revenue_by_outlet_today.total_cents) : '—' }}
          </strong>
          <span class="ops-pulse__hint">Non-voided charges</span>
        </div>
        <div class="ops-pulse__item">
          <span class="ops-pulse__label">Occupancy</span>
          <strong class="ops-pulse__value">
            {{ today && occupancySummary.pct !== null ? `${occupancySummary.pct}%` : '—' }}
          </strong>
          <span class="ops-pulse__hint">
            {{ today
              ? occupancySummary.rooms > 0
                ? `${occupancySummary.occupied} / ${occupancySummary.rooms} rooms`
                : 'No rooms configured'
              : '—' }}
          </span>
        </div>
        <div class="ops-pulse__item">
          <span class="ops-pulse__label">Rentals out</span>
          <strong class="ops-pulse__value">{{ today?.rentals_out.count ?? '—' }}</strong>
          <span class="ops-pulse__hint">Open or late</span>
        </div>
      </section>

      <!-- Arrivals / Departures -->
      <section class="ops-split">
        <div class="ops-panel">
          <header class="ops-panel__head">
            <h2>Arrivals</h2>
            <span>{{ arrivals.length }}</span>
          </header>
          <ul
            v-if="arrivals.length"
            class="ops-list"
          >
            <li
              v-for="(row, idx) in arrivals.slice(0, 8)"
              :key="`a-${idx}`"
            >
              <strong>{{ guestLabel(row) }}</strong>
              <span>{{ stayMeta(row) }}</span>
            </li>
          </ul>
          <p
            v-else
            class="ops-empty"
          >
            No arrivals listed for today.
          </p>
          <NuxtLink
            class="ops-panel__link"
            to="/hotel"
          >
            Open hotel desk
          </NuxtLink>
        </div>

        <div class="ops-panel">
          <header class="ops-panel__head">
            <h2>Departures</h2>
            <span>{{ departures.length }}</span>
          </header>
          <ul
            v-if="departures.length"
            class="ops-list"
          >
            <li
              v-for="(row, idx) in departures.slice(0, 8)"
              :key="`d-${idx}`"
            >
              <strong>{{ guestLabel(row) }}</strong>
              <span>{{ stayMeta(row) }}</span>
            </li>
          </ul>
          <p
            v-else
            class="ops-empty"
          >
            No departures listed for today.
          </p>
          <NuxtLink
            class="ops-panel__link"
            to="/folios"
          >
            Open folios
          </NuxtLink>
        </div>
      </section>

      <!-- Weather + lifts strip -->
      <section
        v-if="warnings.length || lifts.length"
        class="ops-status"
      >
        <div v-if="warnings.length">
          <h2>Weather warnings</h2>
          <ul class="ops-chips">
            <li
              v-for="(w, idx) in warnings.slice(0, 6)"
              :key="`w-${idx}`"
            >
              {{ String(w.title || w.code || w.id || 'Warning') }}
            </li>
          </ul>
        </div>
        <div v-if="lifts.length">
          <h2>Lifts</h2>
          <ul class="ops-chips ops-chips--muted">
            <li
              v-for="(lift, idx) in lifts.slice(0, 8)"
              :key="`l-${idx}`"
            >
              {{ String(lift.name || lift.code || lift.id) }}
              <em>{{ String(lift.status || lift.state || '') }}</em>
            </li>
          </ul>
        </div>
      </section>

      <!-- Desk shortcuts -->
      <section class="ops-desk">
        <header class="ops-desk__head">
          <h2>Desk</h2>
          <p>Jump to the station you need.</p>
        </header>
        <nav class="ops-desk__grid">
          <NuxtLink
            v-for="link in deskLinks"
            :key="link.to"
            :to="link.to"
            class="ops-desk__link"
          >
            <VIcon
              :icon="link.icon"
              size="22"
            />
            <span>
              <strong>{{ link.title }}</strong>
              <small>{{ link.hint }}</small>
            </span>
          </NuxtLink>
        </nav>
      </section>

      <p class="ops-footnote">
        Charges and payments are append-only. Fix mistakes with a void (reason, user, time).
      </p>
    </template>
  </div>
</template>

<style scoped>
.ops-home {
  --ops-ink: #0e1a24;
  --ops-ice: #e8f1f4;
  --ops-mist: #9bb4c0;
  --ops-pine: #1f6a5a;
  --ops-glacier: #3d8ea0;
  --ops-snow: #f4f8fa;
  max-width: 1120px;
}

.ops-hero {
  position: relative;
  overflow: hidden;
  border-radius: 4px;
  padding: 2rem 1.75rem 2.25rem;
  color: var(--ops-ice);
  background:
    radial-gradient(120% 90% at 100% 0%, rgba(61, 142, 160, 0.45), transparent 55%),
    linear-gradient(145deg, #0b1620 0%, #132a33 48%, #1a3d42 100%);
  animation: ops-fade-up 0.55s ease-out both;
}

.ops-hero__glow {
  position: absolute;
  inset: auto -10% -40% 40%;
  height: 70%;
  background: radial-gradient(circle, rgba(232, 241, 244, 0.18), transparent 65%);
  pointer-events: none;
}

.ops-hero__ridge {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 48px;
  background:
    linear-gradient(135deg, transparent 48%, rgba(15, 28, 36, 0.55) 49%),
    linear-gradient(225deg, transparent 48%, rgba(15, 28, 36, 0.4) 49%);
  background-size: 72px 48px, 96px 48px;
  opacity: 0.55;
  pointer-events: none;
}

.ops-hero__content {
  position: relative;
  z-index: 1;
  max-width: 40rem;
}

.ops-hero__eyebrow {
  margin: 0 0 0.65rem;
  font-size: 0.8rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--ops-mist);
}

.ops-hero__title {
  margin: 0 0 0.5rem;
  font-size: clamp(1.75rem, 3vw, 2.35rem);
  font-weight: 650;
  line-height: 1.15;
  letter-spacing: -0.02em;
}

.ops-hero__lede {
  margin: 0;
  color: rgba(232, 241, 244, 0.82);
  font-size: 1.02rem;
  line-height: 1.45;
}

.ops-hero__meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
  margin-top: 1.25rem;
}

.ops-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.7rem;
  border: 1px solid rgba(232, 241, 244, 0.28);
  border-radius: 999px;
  font-size: 0.78rem;
  text-transform: capitalize;
  color: var(--ops-ice);
}

.ops-refresh {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border: 0;
  background: transparent;
  color: var(--ops-ice);
  cursor: pointer;
  font: inherit;
  font-size: 0.9rem;
  opacity: 0.9;
}

.ops-refresh:hover {
  opacity: 1;
}

.ops-refresh:disabled {
  opacity: 0.55;
  cursor: default;
}

.ops-spin {
  animation: ops-spin 0.9s linear infinite;
}

.ops-loading {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 2rem;
  color: rgba(var(--v-theme-on-surface), 0.65);
}

.ops-banner {
  margin: 1.25rem 0 0;
  padding: 0.75rem 1rem;
  border-left: 3px solid var(--ops-glacier);
  background: color-mix(in srgb, var(--ops-glacier) 12%, transparent);
  color: rgba(var(--v-theme-on-surface), 0.8);
}

.ops-pulse {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0;
  margin-top: 1.5rem;
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  animation: ops-fade-up 0.6s 0.08s ease-out both;
}

.ops-pulse__item {
  padding: 1.25rem 1rem 1.35rem;
  border-right: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.ops-pulse__item:last-child {
  border-right: 0;
}

.ops-pulse__label {
  display: block;
  font-size: 0.75rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.ops-pulse__value {
  display: block;
  margin-top: 0.45rem;
  font-size: clamp(1.5rem, 2.4vw, 2rem);
  font-weight: 650;
  letter-spacing: -0.03em;
  line-height: 1.1;
  color: rgb(var(--v-theme-on-surface));
}

.ops-pulse__hint {
  display: block;
  margin-top: 0.35rem;
  font-size: 0.85rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.ops-split {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-top: 2rem;
  animation: ops-fade-up 0.65s 0.12s ease-out both;
}

.ops-panel__head {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.85rem;
  padding-bottom: 0.55rem;
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.ops-panel__head h2 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 600;
}

.ops-panel__head span {
  font-size: 0.85rem;
  color: rgba(var(--v-theme-on-surface), 0.5);
}

.ops-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.ops-list li {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid rgba(var(--v-border-color), calc(var(--v-border-opacity) * 0.7));
}

.ops-list strong {
  font-weight: 600;
}

.ops-list span {
  font-size: 0.85rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.ops-empty {
  margin: 0.5rem 0 1rem;
  color: rgba(var(--v-theme-on-surface), 0.5);
}

.ops-panel__link {
  display: inline-block;
  margin-top: 0.85rem;
  font-size: 0.92rem;
  font-weight: 600;
  color: var(--ops-pine);
  text-decoration: none;
}

.ops-panel__link:hover {
  text-decoration: underline;
}

.ops-status {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-top: 2rem;
}

.ops-status h2 {
  margin: 0 0 0.65rem;
  font-size: 0.95rem;
  font-weight: 600;
}

.ops-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.45rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.ops-chips li {
  padding: 0.3rem 0.65rem;
  border-radius: 2px;
  background: color-mix(in srgb, #c45c26 18%, transparent);
  font-size: 0.82rem;
}

.ops-chips--muted li {
  background: rgba(var(--v-theme-on-surface), 0.06);
}

.ops-chips em {
  margin-left: 0.35rem;
  font-style: normal;
  opacity: 0.65;
  text-transform: lowercase;
}

.ops-desk {
  margin-top: 2.5rem;
  animation: ops-fade-up 0.7s 0.16s ease-out both;
}

.ops-desk__head h2 {
  margin: 0 0 0.25rem;
  font-size: 1.05rem;
  font-weight: 600;
}

.ops-desk__head p {
  margin: 0 0 1rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.ops-desk__grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.65rem;
}

.ops-desk__link {
  display: flex;
  align-items: flex-start;
  gap: 0.7rem;
  padding: 0.85rem 0.75rem;
  border-radius: 2px;
  text-decoration: none;
  color: inherit;
  transition: background 0.18s ease, transform 0.18s ease;
}

.ops-desk__link:hover {
  background: rgba(var(--v-theme-on-surface), 0.04);
  transform: translateY(-1px);
}

.ops-desk__link strong {
  display: block;
  font-size: 0.95rem;
}

.ops-desk__link small {
  display: block;
  margin-top: 0.15rem;
  font-size: 0.78rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.ops-footnote {
  margin: 2rem 0 0.5rem;
  font-size: 0.82rem;
  color: rgba(var(--v-theme-on-surface), 0.45);
}

@keyframes ops-fade-up {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes ops-spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 960px) {
  .ops-pulse {
    grid-template-columns: 1fr 1fr;
  }

  .ops-pulse__item:nth-child(2) {
    border-right: 0;
  }

  .ops-pulse__item:nth-child(1),
  .ops-pulse__item:nth-child(2) {
    border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .ops-split,
  .ops-status,
  .ops-desk__grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 600px) {
  .ops-hero {
    padding: 1.5rem 1.15rem 1.75rem;
  }

  .ops-pulse,
  .ops-split,
  .ops-status,
  .ops-desk__grid {
    grid-template-columns: 1fr;
  }

  .ops-pulse__item {
    border-right: 0;
    border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  }

  .ops-pulse__item:last-child {
    border-bottom: 0;
  }
}
</style>
