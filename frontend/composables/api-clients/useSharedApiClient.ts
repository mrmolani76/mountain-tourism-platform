export const useSharedApiClient = () => {
  const { post, get } = useApiClient()

  const saveUserPreference = async (_body: { identifier: string; filterData: { [key: string]: any } }) => {
    // Mountain Core has no preference API yet. Avoid 404 noise; filters stay session-local.
    return { status: true } as ApiResponse<any>
  }

  const getDatatableListingConfig = async (body: { holders: Array<ColumnListing> }) => {
    // Mountain Core returns columnSearchable on each list/config. Skip legacy EQMS endpoint.
    if (!body.holders?.length)
      return null

    try {
      const response = await post<ApiResponse<any>>('/datatableListingConfig', body)

      if (response?.status)
        return response.data
    }
    catch {
      // Endpoint not present on Laravel Core — filters use list/config columnSearchable.
    }

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
