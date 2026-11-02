export const useCustomerApiClient = () => {
  const { post, get, put, del } = useApiClient()
  const { saveUserPreference } = useSharedApiClient()

  const customerDataTableConfig = ref({} as DataTableConfig)
  const customerDataTableResult = ref({ data: [], pagination: { total: 0 }, status: false } as DataTableResult<any>)
  const loading = ref(false)

  const getCustomerDataTableConfig = async () => {
    loading.value = true

    const result = await get<ApiResponse<DataTableConfig>>('/customers/listing-config')

    if (result?.status)
      customerDataTableConfig.value = result.data

    loading.value = false
  }

  const getCustomerDataTableData = async (body: any) => {
    loading.value = true

    const result = await post<DataTableResult<any>>('/customers/list', body)

    if (result?.status)
      customerDataTableResult.value = result

    loading.value = false
  }

  const getCustomerById = async (uuid: string) => {
    loading.value = true

    const response = await get<ApiResponse<any>>(`/customers/fetch?uuid=${uuid}`)

    loading.value = false
    if (response?.status)
      return response.data.customer
  }

  const getCustomerViewById = async (uuid: string) => {
    loading.value = true

    const response = await get<ApiResponse<any>>(`/customers/show?uuid=${uuid}`)

    loading.value = false
    if (response?.status)
      return response.data
  }

  const createCustomer = async (body: CustomerForm) => {
    loading.value = true

    const result = await post<ApiResponse<SaveApiResponse>>('/customers/store', body)

    loading.value = false

    if (result?.status)
      return result
  }

  const updateCustomer = async (body: CustomerForm) => {
    loading.value = true

    const result = await put<ApiResponse<SaveApiResponse>>('/customers/update', body)

    loading.value = false

    if (result?.status)
      return result
  }

  const deleteCustomer = async (data: Record<string, any>) => {
    const response = await del<ApiResponse<any>>('/customers/delete', data)

    if (response?.status)
      return response.status

    return null
  }

  const restoreCustomer = async (data: Record<string, any>) => {
    const response = await post<ApiResponse<any>>('/customers/restore', data)

    if (response?.status)
      return response.status

    return null
  }

  const saveCustomerTableFilters = async (filterData: any) => {
    const params = {
      identifier: CUSTOMER_LISTING_FILTER_IDENTIFIER,
      filterData,
    }

    return await saveUserPreference(params)
  }

  return {
    loading,
    customerDataTableConfig,
    customerDataTableResult,
    getCustomerDataTableConfig,
    getCustomerDataTableData,
    getCustomerById,
    getCustomerViewById,
    createCustomer,
    updateCustomer,
    deleteCustomer,
    restoreCustomer,
    saveCustomerTableFilters,
  }
}
