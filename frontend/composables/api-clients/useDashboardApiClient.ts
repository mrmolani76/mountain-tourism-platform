export const useDashboardApiClient = () => {
  const { get } = useApiClient()

  const loading = ref(false)

  const getSuperUserDashboardCount = async () => {
    loading.value = true

    const response = await get<ApiResponse<DashboardCount>>('/dashboard/stats')

    loading.value = false
    if (response?.status)
      return response?.data
  }

  return {
    loading,
    getSuperUserDashboardCount,
  }
}
