/**
 * EQMS DataTable listing client for Hotel resources.
 *
 *   GET  hotel/{resource}/list/config
 *   POST hotel/{resource}/list
 *
 * Same shape as useUserApiClient listing helpers.
 */
export const useHotelListingApiClient = (resource: string) => {
  const { get, post } = useApiClient()

  const tableConfig = ref({} as DataTableConfig)
  const listResult = ref({
    data: [],
    pagination: { total: 0 },
    status: false,
  } as DataTableResult<any>)

  const configLoading = ref(false)
  const listLoading = ref(false)

  const listBase = computed(() => `hotel/${resource}`)

  const getListingConfig = async () => {
    configLoading.value = true
    try {
      const result = await get<ApiResponse<DataTableConfig>>(`${listBase.value}/list/config`)

      if (result?.status)
        tableConfig.value = result.data
    }
    finally {
      configLoading.value = false
    }
  }

  const getListingData = async (body: Record<string, any> = {}) => {
    listLoading.value = true
    try {
      const result = await post<DataTableResult<any>>(`${listBase.value}/list`, body)

      if (result?.status)
        listResult.value = result
    }
    finally {
      listLoading.value = false
    }
  }

  return {
    tableConfig,
    listResult,
    configLoading,
    listLoading,
    getListingConfig,
    getListingData,
  }
}
