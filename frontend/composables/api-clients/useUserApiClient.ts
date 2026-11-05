import type { VerticalNavItems } from '@/@layouts/types'
import type { User } from '@/types/user'
import { useAuthService } from '@/composables/auth/useAuthService'

/**
 * User / staff API — eQMS qualitycenter-spa `composables/users/userService.ts` paths,
 * backed by Laravel Core `UserListingController` (tenant Staff).
 */
export const useUserApiClient = () => {
  const { post, get, put } = useApiClient()
  const { saveUserPreference } = useSharedApiClient()
  const auth = useAuthService()

  const userDataTableConfig = ref({} as DataTableConfig)
  const userDataTableResult = ref({ data: [], pagination: { total: 0 }, status: false } as DataTableResult<any>)
  const loading = ref(false)

  const updateUserProfile = async (body: UserForm) => {
    loading.value = true

    const result = await put<ApiResponse<any>>('/user/updateProfile', {
      ...body,
      firstName: body.firstName,
      last_name: body.lastName,
    })

    loading.value = false

    if (result?.status)
      return result
  }

  const getUserById = async (id: string) => {
    loading.value = true

    const response = await get<ApiResponse<{ user: User; permissions?: Permission }>>(`/user/fetch?id=${id}`)

    loading.value = false
    if (response?.status)
      return response?.data
  }

  const getMenuItems = async (): Promise<ApiResponse<{ menu: VerticalNavItems }>> => {
    return auth.getMenuItems()
  }

  const getUserDataTableConfig = async () => {
    loading.value = true

    const result = await get<ApiResponse<DataTableConfig>>('/users/list/config')

    if (result?.status)
      userDataTableConfig.value = result.data

    loading.value = false
  }

  const getUserDataTableData = async (body: any) => {
    loading.value = true

    const result = await post<DataTableResult<any>>('/users/list', body)

    if (result?.status)
      userDataTableResult.value = result

    loading.value = false
  }

  /** Create staff — eQMS POST users/create; body is JSON for Laravel. */
  const createUser = async (body: Record<string, unknown>) => {
    loading.value = true

    const result = await post<ApiResponse<SaveApiResponse> & { uuid?: string }>('/users/create', body)

    loading.value = false

    if (result?.status)
      return result
  }

  const updateUser = async (body: Record<string, unknown>) => {
    loading.value = true

    const result = await post<ApiResponse<SaveApiResponse> & { uuid?: string }>('/user/update', body)

    loading.value = false

    if (result?.status)
      return result
  }

  const deleteUsers = async (data: Record<string, any>) => {
    const response = await post<ApiResponse<any>>('/users/destroy', data)

    if (response?.status)
      return response.status

    return null
  }

  const saveUserTableFilters = async (filterData: any) => {
    const params = {
      identifier: USER_LISTING_FILTER_IDENTIFIER,
      filterData,
    }

    return await saveUserPreference(params)
  }

  const enableUsers = async (data: Record<string, any>) => {
    const response = await post<ApiResponse<any>>('/users/usersActivate', data)

    if (response?.status)
      return response.status

    return null
  }

  const disableUsers = async (data: Record<string, any>) => {
    const response = await post<ApiResponse<any>>('/users/usersDeactivate', data)

    if (response?.status)
      return response.status

    return null
  }

  const exportUsersListing = async (params: object) => {
    const response = await post<ApiResponse<any>>('/users/export', params)

    if (response?.status)
      return response
  }

  const userPasswordReset = async (params: object) => {
    return await post<ApiResponse<any>>('/user/passwordReset', params)
  }

  const formInitaldata = async () => {
    loading.value = true

    const response = await get<ApiResponse<{ roles: Array<{ id: string; name: string; roleName: string }> }>>('/user/formInitialData')

    loading.value = false
    if (response?.status)
      return response?.data
  }

  const getUserViewById = async (uuid: string) => {
    loading.value = true

    const response = await get<ApiResponse<User>>(`/user/show?uuid=${uuid}`)

    loading.value = false
    if (response?.status)
      return response?.data
  }

  const getUserProfileSetting = async () => {
    loading.value = true

    const response = await get<ApiResponse<User>>('/user/profile-setting')

    loading.value = false
    if (response?.status)
      return response?.data
  }

  const updateUserLang = async (lang: string) => {
    return await put<ApiResponse<any>>('/user/update-lang', { lang })
  }

  return {
    loading,
    userDataTableConfig,
    userDataTableResult,
    resetPassword: auth.resetPassword,
    sendResetPasswordMail: auth.sendResetPasswordMail,
    login: auth.login,
    logout: auth.logout,
    getUserById,
    getUserDetails: auth.getUserDetails,
    getMenuItems,
    createUser,
    updateUser,
    getUserDataTableConfig,
    exportUsersListing,
    deleteUsers,
    enableUsers,
    disableUsers,
    saveUserTableFilters,
    getUserDataTableData,
    userPasswordReset,
    updateUserProfile,
    formInitaldata,
    getUserViewById,
    getUserProfileSetting,
    updateUserLang,
  }
}
