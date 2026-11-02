import type { VerticalNavItems } from '@/@layouts/types'
import type { LinkEntity } from '@/types/link-entity'
import type { StaffSessionPayload, StaffUser, User } from '@/types/user'

/**
 * Role permissions are UI hints only. Django decides every action on the server.
 */
const ROLE_PERMISSIONS: Record<string, Array<string>> = {
  owner: ['*'],
  manager: ['hotel', 'folio', 'pos', 'rental', 'services', 'transport', 'weather', 'reports', 'staff'],
  front_desk: ['hotel', 'folio', 'pos'],
  cashier: ['folio', 'pos'],
  rental_desk: ['rental', 'folio'],
  service_staff: ['services'],
  transport_desk: ['transport'],
  lift_operator: ['weather'],
}

function emptyLink(): LinkEntity {
  return {
    fullName: '',
    label: '',
    url: '',
    type: 'text',
  }
}

/**
 * Map the compact Django staff payload into the template's `User` shape.
 * Only real values are kept; address and phone fields stay empty.
 */
function mapStaffToUser(staff: StaffUser): User {
  const username = staff.email.split('@')[0]
  const now = new Date().toISOString()

  return {
    id: staff.id,
    uuid: String(staff.id),
    fullName: username,
    firstName: username,
    lastName: '',
    email: staff.email,
    askEmail: false,
    prefixedUsername: username,
    theme: 'system',
    lang: 'en',
    sid: staff.tenant_slug ?? '',
    userType: 'staff',
    roleName: staff.role_code,
    roleCode: staff.role_code,
    tenantSlug: staff.tenant_slug ?? undefined,
    role: {
      id: 0,
      name: staff.role_code,
      gaurdName: 'staff',
      roleName: staff.role,
      additionalData: [],
    },
    termsAccepted: true,
    phoneNo: null,
    roleId: null,
    lastLogin: '',
    loginCount: '',
    userProfile: null,
    city: null,
    address1: null,
    address2: null,
    zip: null,
    creator: emptyLink(),
    updator: emptyLink(),
    deletedAt: '',
    createdAt: now,
    updatedAt: now,
    active: staff.is_active,
  }
}

export const useUserApiClient = () => {
  const { post, get, put, del } = useApiClient()
  const { saveUserPreference } = useSharedApiClient()

  const userDataTableConfig = ref({} as DataTableConfig)
  const userDataTableResult = ref({ data: [], pagination: { total: 0 }, status: false } as DataTableResult<any>)
  const loading = ref(false)

  const login = async (body: { email: string; password: string }) => {
    loading.value = true

    const staff = await post<StaffUser>('/auth/login/', body)

    loading.value = false
    if (!staff)
      return null

    return {
      status: true,
      data: mapStaffToUser(staff),
    } as ApiResponse<User>
  }

  const logout = async () => {
    return await post<{ detail: string }>('/auth/logout/', {})
  }

  const getUserDetails = async (): Promise<ApiResponse<StaffSessionPayload> | null> => {
    loading.value = true

    const staff = await get<StaffUser>('/auth/me/')

    loading.value = false
    if (!staff)
      return null

    return {
      status: true,
      data: {
        user: mapStaffToUser(staff),
        staff,
        userImage: '',
        userPermissions: ROLE_PERMISSIONS[staff.role_code] ?? [],
      },
    }
  }

  const updateUserProfile = async (body: UserForm) => {
    loading.value = true

    const payload = {
      ...body,
      firstName: body.firstName,
      last_name: body.lastName,
    }

    const result = await put<ApiResponse<any>>('/user/updateProfile', payload)

    loading.value = false

    if (result?.status)
      return result
  }

  const getUserById = async (id: string) => {
    loading.value = true

    const response = await get<ApiResponse<{ user: User;permissions: Permission }>>(`/user/fetch?id=${id}`)

    loading.value = false
    if (response?.status)
      return response?.data
  }

  const getMenuItems = async (): Promise<ApiResponse<{ menu: VerticalNavItems }>> => {
    return {
      status: true,
      data: { menu: appNavigation },
    }
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

  const createUser = async (body: UserForm) => {
    loading.value = true

    const formData = new FormData();

    Object.entries(body || {}).forEach(([key, value]) => {
      if (value !== undefined && value !== null)
        formData.append(key, value)
    })

    const result = await post<ApiResponse<SaveApiResponse>>('/user', formData)

    loading.value = false

    if (result?.status)
      return result
  }

  const updateUser = async (body: UserForm) => {
    loading.value = true

    const formData = new FormData();

    Object.entries(body || {}).forEach(([key, value]) => {
      if (value !== undefined && value !== null)
        formData.append(key, value)
    })

    const result = await post<ApiResponse<SaveApiResponse>>('/user/update', formData)

    loading.value = false

    if (result?.status)
      return result
  }

  const deleteUsers = async (data: Record<string, any>) => {
    const response = await del<ApiResponse<any>>('/users/delete', data)

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

  const resetPassword = async (body: object) => {
    return await post<ApiResponse<any>>('/reset/password', body)
  }

  const sendResetPasswordMail = async (body: { email: string }) => {
    return await post<ApiResponse<any>>('/reset/password/mail', body)
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
    const response = await post<ApiResponse<any>>('/user/passwordReset', params)
    if (response?.status)
      return response;

    return response;
  }

  const formInitaldata = async () => {
    loading.value = true

    const response = await get<ApiResponse<any>>('/user/formInitialData')

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
    const response = await put<ApiResponse<any>>('/user/update-lang', { lang })
    if (response?.status)
      return response;

    return response;
  }

  return {
    loading,
    userDataTableConfig,
    userDataTableResult,
    resetPassword,
    sendResetPasswordMail,
    login,
    logout,
    getUserById,
    getUserDetails,
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
