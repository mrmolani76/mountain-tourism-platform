export const useAuditTrailApiClient = () => {
  const { get, post } = useApiClient()
  const { saveUserPreference } = useSharedApiClient()

  const getModuleWiseAuditTrailListConfig = async (params: Record<string, any>) => {
    const response = await get<ApiResponse<any>>('moduleWiseAuditTrail/listingConfig', params)

    if (response?.status)
      return response.data
  }

  const getAuditListing = async (params: any) => {
    const auditTrails = {} as any

    const response = await post<DataTableResult<any>>('moduleWiseAuditTrail/list', params)

    if (response?.status) {
      auditTrails.data = response.data
      auditTrails.dataCount = response.pagination.total

      return auditTrails
    }
  }

  /**
   * Save audit trail table filters.
   * @param selectedColumns Array of selected column names.
   * @param identifier Unique identifier for the filter set.
   */
  const saveAuditTrailTableFilters = async (selectedColumns: Array<string>, identifier: string) => {
    const params = {
      identifier,
      filterData: selectedColumns,
    }

    const response: ApiResponse<any> | null = await saveUserPreference(params)

    if (response?.status)
      return response.data

    return null
  }

  const exportAuditTrailListing = async (url: string, params: ExportListingFormData) => {
    const response = await post<ApiResponse<any>>(url, params)

    if (response?.status)
      return response.data
  }

  const getEntityAuditTrailListingConfig = async (body: Array<ColumnListing>) => {
    const response = await post<ApiResponse<any>>('/entity-activities/listing-config', body)

    if (response?.status)
      return response.data

    return null
  }

  const getEntityAuditTrailListing = async (listingUrl: string, params: any) => {
    const auditTrails = {} as any

    const response = await post<DataTableResult<any>>(listingUrl, params)

    if (response?.status) {
      auditTrails.data = response.data
      auditTrails.dataCount = response.pagination.total

      return auditTrails
    }
  }

  const getAuditTrailCompare = async (listingUrl: string, params: any) => {
    const response = await post<ApiResponse<any>>(listingUrl, params)

    if (response?.status)
      return response.data
  }

  return { getModuleWiseAuditTrailListConfig, getAuditListing, saveAuditTrailTableFilters, exportAuditTrailListing, getEntityAuditTrailListingConfig, getEntityAuditTrailListing, getAuditTrailCompare }
}
