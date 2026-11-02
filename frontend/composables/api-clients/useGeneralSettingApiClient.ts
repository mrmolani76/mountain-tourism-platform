export const useGeneralSettingApiClient = () => {
  const { get, put } = useApiClient()
  const loading = ref(false)

  const fetchGeneralSettings = async () => {
    loading.value = true

    const response = await get<ApiResponse<GeneralSetting>>('/general-settings/form-initial')

    loading.value = false

    return response?.data
  }

  const updateGeneralSetting = async (body: Partial<GeneralSetting>) => {
    const payload = {
      ...body,
    }

    return await put<ApiResponse<any>>('/general-settings/update', payload)
  }

  return {
    loading,
    fetchGeneralSettings,
    updateGeneralSetting,
  }
}
