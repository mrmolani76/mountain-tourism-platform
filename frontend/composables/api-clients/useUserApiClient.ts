import type { VerticalNavItems } from '@/@layouts/types'
import type { User } from '@/types/user'
import { useAuthService } from '@/composables/auth/useAuthService'

/**
 * User / staff API — eQMS qualitycenter-spa `composables/users/userService.ts` paths,
 * backed by Laravel Core `UserListingController` (tenant Staff).
 *
 * Loading mirrors eQMS users/index:
 * - `configLoading` → full-page table skeleton until list/config returns
 * - `listLoading` → VDataTableServer overlay while rows reload
 * - `loading` → form / view fetch skeleton
 */
export const useUserApiClient = () => {
  const { post, get, put } = useApiClient()
  const { saveUserPreference } = useSharedApiClient()
  const auth = useAuthService()

  const userDataTableConfig = ref({} as DataTableConfig)
  const userDataTableResult = ref({
    data: [],
    pagination: { total: 0 },
    status: false,
  } as DataTableResult<any>)

  /** Form / view page fetches (eQMS `loading` on form/view). */
  const loading = ref(false)

  /** Listing config fetch — drives index skeleton (eQMS first `useUserService` call). */
  const configLoading = ref(false)

  /** Listing rows fetch — drives DataTable `:loading` (eQMS second call). */
  const listLoading = ref(false)

  const updateUserProfile = async (body: UserForm) => {
    loading.value = true
    try {
      const result = await put<ApiResponse<any>>('/user/updateProfile', {
        ...body,
        firstName: body.firstName,
        last_name: body.lastName,
      })

      if (result?.status)
        return result
    }
    finally {
      loading.value = false
    }
  }

  const getUserById = async (id: string) => {
    loading.value = true
    try {
      const response = await get<ApiResponse<{ user: User; permissions?: Permission }>>(`/user/fetch?id=${id}`)

      if (response?.status)
        return response?.data
    }
    finally {
      loading.value = false
    }
  }

  const getMenuItems = async (): Promise<ApiResponse<{ menu: VerticalNavItems }>> => {
    return auth.getMenuItems()
  }

  const getUserDataTableConfig = async () => {
    configLoading.value = true
    try {
      const result = await get<ApiResponse<DataTableConfig>>('/users/list/config')

      if (result?.status)
        userDataTableConfig.value = result.data
    }
    finally {
      configLoading.value = false
    }
  }

  const getUserDataTableData = async (body: any) => {
    listLoading.value = true
    try {
      const result = await post<DataTableResult<any>>('/users/list', body)

      if (result?.status)
        userDataTableResult.value = result
    }
    finally {
      listLoading.value = false
    }
  }

  /** Create staff — eQMS POST users/create; body is JSON for Laravel. */
  const createUser = async (body: Record<string, unknown>) => {
    loading.value = true
    try {
      const result = await post<ApiResponse<SaveApiResponse> & { uuid?: string }>('/users/create', body)

      if (result?.status)
        return result
    }
    finally {
      loading.value = false
    }
  }

  const updateUser = async (body: Record<string, unknown>) => {
    loading.value = true
    try {
      const result = await post<ApiResponse<SaveApiResponse> & { uuid?: string }>('/user/update', body)

      if (result?.status)
        return result
    }
    finally {
      loading.value = false
    }
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
    try {
      const response = await get<ApiResponse<{ roles: Array<{ id: string; name: string; roleName: string }> }>>('/user/formInitialData')

      if (response?.status)
        return response?.data
    }
    finally {
      loading.value = false
    }
  }

  const getUserViewById = async (uuid: string) => {
    loading.value = true
    try {
      const response = await get<ApiResponse<User>>(`/user/show?uuid=${uuid}`)

      if (response?.status)
        return response?.data
    }
    finally {
      loading.value = false
    }
  }

  const getUserProfileSetting = async () => {
    loading.value = true
    try {
      const response = await get<ApiResponse<User>>('/user/profile-setting')

      if (response?.status)
        return response?.data
    }
    finally {
      loading.value = false
    }
  }

  const updateUserLang = async (lang: string) => {
    return await put<ApiResponse<any>>('/user/update-lang', { lang })
  }

  return {
    loading,
    configLoading,
    listLoading,
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
