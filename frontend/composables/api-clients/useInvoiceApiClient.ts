export const useInvoiceApiClient = () => {
  const { post, get, put, del } = useApiClient()
  const { saveUserPreference } = useSharedApiClient()

  const invoiceDataTableConfig = ref({} as DataTableConfig)
  const invoiceDataTableResult = ref({ data: [], pagination: { total: 0 }, status: false } as DataTableResult<any>)
  const loading = ref(false)

  const getInvoiceDataTableConfig = async () => {
    loading.value = true

    const result = await get<ApiResponse<DataTableConfig>>('/invoices/listing-config')

    if (result?.status)
      invoiceDataTableConfig.value = result.data

    loading.value = false
  }

  const getInvoiceDataTableData = async (body: any) => {
    loading.value = true

    const result = await post<DataTableResult<any>>('/invoices/list', body)

    if (result?.status)
      invoiceDataTableResult.value = result

    loading.value = false
  }

  const getInvoiceById = async (uuid: string) => {
    loading.value = true

    const response = await get<ApiResponse<any>>(`/invoices/fetch?uuid=${uuid}`)

    loading.value = false
    if (response?.status)
      return response.data.invoice
  }

  const getInvoiceViewById = async (uuid: string) => {
    loading.value = true

    const response = await get<ApiResponse<any>>(`/invoices/show?uuid=${uuid}`)

    loading.value = false
    if (response?.status)
      return response.data
  }

  const createInvoice = async (body: InvoiceForm) => {
    loading.value = true

    const result = await post<ApiResponse<SaveApiResponse>>('/invoices/store', body)

    loading.value = false

    if (result?.status)
      return result
  }

  const updateInvoice = async (body: InvoiceForm) => {
    loading.value = true

    const result = await put<ApiResponse<SaveApiResponse>>('/invoices/update', body)

    loading.value = false

    if (result?.status)
      return result
  }

  const deleteInvoice = async (data: Record<string, any>) => {
    const response = await del<ApiResponse<any>>('/invoices/delete', data)

    if (response?.status)
      return response.status

    return null
  }

  const restoreInvoice = async (data: Record<string, any>) => {
    const response = await post<ApiResponse<any>>('/invoices/restore', data)

    if (response?.status)
      return response.status

    return null
  }

  const getInvoiceFormInitialData = async () => {
    loading.value = true

    const response = await get<ApiResponse<any>>('/invoices/formInitialData')

    loading.value = false
    if (response?.status)
      return response.data
  }

  const saveInvoiceTableFilters = async (filterData: any) => {
    const params = {
      identifier: INVOICE_LISTING_FILTER_IDENTIFIER,
      filterData,
    }

    return await saveUserPreference(params)
  }

  const getInvoicePrintUrl = (uuid: string): string => {
    const apiBaseUrl = (useRuntimeConfig().public.apiBaseUrl) as string
    const printUrl = new URL(`${apiBaseUrl}/invoices/print`, window.location.origin)

    printUrl.searchParams.set('uuid', uuid)

    return printUrl.href
  }

  return {
    loading,
    invoiceDataTableConfig,
    invoiceDataTableResult,
    getInvoiceDataTableConfig,
    getInvoiceDataTableData,
    getInvoiceById,
    getInvoiceViewById,
    createInvoice,
    updateInvoice,
    deleteInvoice,
    restoreInvoice,
    getInvoiceFormInitialData,
    saveInvoiceTableFilters,
    getInvoicePrintUrl,
  }
}
