export const useSharedApiClient = () => {
  const { post, get } = useApiClient()

  const saveUserPreference = async (body: { identifier: string; filterData: { [key: string]: any } }) => {
    return await post<ApiResponse<any>>('/saveUserPreference', body)
  }

  const getDatatableListingConfig = async (body: { holders: Array<ColumnListing> }) => {
    const response = await post<ApiResponse<any>>('/datatableListingConfig', body)

    if (response?.status)
      return response.data

    return null
  }

  const getCountries = async () => {
    const response = await get<ApiResponse<any>>('/getcountries')

    if (response?.status)
      return response.data

    return []
  }

  const getTimezones = async () => {
    const response = await get<ApiResponse<any>>('/getTimezones')

    if (response?.status)
      return response.data

    return []
  }

  const getStates = async (params: {
    countryCode: string
  }) => {
    const response = await get<ApiResponse<any>>('/getStates', params)

    if (response?.status)
      return response.data

    return []
  }

  const getViewFileExtensionConfig = async () => {
    const response = await get<ApiResponse<any>>('/fetchAllowedViewerExtensionConfig')
    if (response?.status)
      return response.data
  }

  return { saveUserPreference, getCountries, getTimezones, getStates, getDatatableListingConfig, getViewFileExtensionConfig }
}
