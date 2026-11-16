<script setup lang="ts">
/**
 * Core outlets — EQMS listing + dialog CRUD via ResourceCrudPage.
 * API: outlets/list/* + outlets CRUD (owner create/update/delete).
 */

useHead({ title: 'Outlets' })

definePageMeta({
  navActiveLink: 'outlets',
})

const { get } = useApiClient()

const loadBuildings = async (): Promise<Array<HotelOption>> => {
  const response = await get<{ success?: boolean; data?: Array<{ id: number; name: string }> }>('buildings', {
    page: '1',
    page_size: '100',
  })

  const rows = Array.isArray(response?.data) ? response.data : []

  return rows.map(row => ({
    value: row.id,
    title: row.name,
  }))
}

const fields: Array<HotelFormField> = [
  {
    key: 'building_id',
    label: 'Building',
    type: 'select',
    required: true,
    optionsLoader: loadBuildings,
  },
  { key: 'name', label: 'Name', required: true, col: 12 },
  {
    key: 'outlet_type',
    label: 'Outlet type',
    type: 'select',
    required: true,
    options: [
      { value: 'restaurant', title: 'Restaurant' },
      { value: 'self_service', title: 'Self Service' },
      { value: 'hut_kitchen', title: 'Hut Kitchen' },
      { value: 'panorama_restaurant', title: 'Panorama Restaurant' },
      { value: 'shop', title: 'Shop' },
      { value: 'rental', title: 'Rental' },
      { value: 'spa', title: 'Spa' },
      { value: 'sauna', title: 'Sauna' },
      { value: 'gym', title: 'Gym' },
      { value: 'ski_school', title: 'Ski School' },
      { value: 'reception', title: 'Reception' },
      { value: 'tickets', title: 'Tickets' },
      { value: 'sports', title: 'Sports' },
      { value: 'transport', title: 'Transport' },
      { value: 'lifts', title: 'Lifts' },
      { value: 'other', title: 'Other' },
    ],
  },
  { key: 'is_active', label: 'Active', type: 'checkbox', col: 12 },
]
</script>

<template>
  <ResourceCrudPage
    title="Outlets"
    subtitle="Business units in a building: restaurant, rental, spa, lifts, …"
    endpoint="outlets"
    :fields="fields"
    :defaults="{ outlet_type: 'other', is_active: true }"
    add-label="New outlet"
  />
</template>
