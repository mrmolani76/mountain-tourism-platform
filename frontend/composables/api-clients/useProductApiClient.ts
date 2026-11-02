export const useProductApiClient = () => {
  const { post, get, put, del } = useApiClient()
  const { saveUserPreference } = useSharedApiClient()

  const productDataTableConfig = ref({} as DataTableConfig)
  const productDataTableResult = ref({ data: [], pagination: { total: 0 }, status: false } as DataTableResult<any>)
  const loading = ref(false)

  const getProductDataTableConfig = async () => {
    loading.value = true

    const result = await get<ApiResponse<DataTableConfig>>('/products/listing-config')

    if (result?.status)
      productDataTableConfig.value = result.data

    loading.value = false
  }

  const getProductDataTableData = async (body: any) => {
    loading.value = true

    const result = await post<DataTableResult<any>>('/products/list', body)

    if (result?.status)
      productDataTableResult.value = result

    loading.value = false
  }

  const getProductById = async (uuid: string) => {
    loading.value = true

    const response = await get<ApiResponse<Product>>(`/products/fetch?uuid=${uuid}`)

    loading.value = false
    if (response?.status)
      return response?.data
  }

  const createProduct = async (body: ProductForm) => {
    loading.value = true

    const formData = new FormData();

    Object.entries(body || {}).forEach(([key, value]) => {
      if (value !== undefined && value !== null)
        formData.append(key, value)
    })

    const result = await post<ApiResponse<SaveApiResponse>>('/products/store', formData)

    loading.value = false

    if (result?.status)
      return result
  }

  const updateProduct = async (body: ProductForm) => {
    loading.value = true

    const formData = new FormData();

    Object.entries(body || {}).forEach(([key, value]) => {
      if (value !== undefined && value !== null)
        formData.append(key, value)
    })

    const result = await put<ApiResponse<SaveApiResponse>>('/products/update', formData)

    loading.value = false

    if (result?.status)
      return result
  }

  const deleteProducts = async (data: Record<string, any>) => {
    const response = await del<ApiResponse<any>>('/products/delete', data)

    if (response?.status)
      return response.status

    return null
  }

  const enableProducts = async (data: Record<string, any>) => {
    const response = await post<ApiResponse<any>>('/products/enable', data)

    if (response?.status)
      return response.status

    return null
  }

  const disableProducts = async (data: Record<string, any>) => {
    const response = await post<ApiResponse<any>>('/products/disable', data)

    if (response?.status)
      return response.status

    return null
  }

  const exportProductsListing = async (params: object) => {
    const response = await post<ApiResponse<any>>('/products/export', params)

    if (response?.status)
      return response
  }

  const saveProductTableFilters = async (filterData: any) => {
    const params = {
      identifier: PRODUCT_LISTING_FILTER_IDENTIFIER,
      filterData,
    }

    return await saveUserPreference(params)
  }

  const formInitialData = async () => {
    loading.value = true

    const response = await get<ApiResponse<any>>('/products/formInitialData')

    loading.value = false
    if (response?.status)
      return response?.data
  }

  const getProductViewById = async (uuid: string) => {
    loading.value = true

    const response = await get<ApiResponse<Product>>(`/products/show?uuid=${uuid}`)

    loading.value = false
    if (response?.status)
      return response?.data
  }

  return {
    loading,
    productDataTableConfig,
    productDataTableResult,
    getProductDataTableConfig,
    getProductDataTableData,
    getProductById,
    createProduct,
    updateProduct,
    deleteProducts,
    enableProducts,
    disableProducts,
    exportProductsListing,
    saveProductTableFilters,
    formInitialData,
    getProductViewById,
  }
}
