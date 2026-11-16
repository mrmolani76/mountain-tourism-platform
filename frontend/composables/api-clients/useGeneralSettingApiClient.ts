/**
 * Core tenant settings — Laravel `GET|PATCH /api/settings` (owner only).
 */

export const useGeneralSettingApiClient = () => {
  const { get, patch } = useApiClient()
  const loading = ref(false)

  const fetchGeneralSettings = async (): Promise<GeneralSetting | null> => {
    loading.value = true

    try {
      const response = await get<GeneralSetting>('settings')

      return response ?? null
    }
    finally {
      loading.value = false
    }
  }

  const updateGeneralSetting = async (body: Partial<GeneralSetting>) => {
    const payload: Record<string, unknown> = {}

    if (body.currency !== undefined)
      payload.currency = body.currency
    if (body.timezone !== undefined)
      payload.timezone = body.timezone
    if (body.units !== undefined)
      payload.units = body.units
    if (body.tax_rates !== undefined)
      payload.tax_rates = body.tax_rates

    const updated = await patch<GeneralSetting>('settings', payload)

    if (!updated)
      return { status: false as const, message: 'Save failed.' }

    return {
      status: true as const,
      message: 'Settings saved.',
      data: updated,
    }
  }

  return {
    loading,
    fetchGeneralSettings,
    updateGeneralSetting,
  }
}
