import type { StaffSessionPayload, StaffUser, User } from '@/types/user'
import type { VerticalNavItems } from '@/@layouts/types'
import { API } from '@/composables/core/useApi'

/**
 * Tenant staff auth — eQMS qualitycenter-spa `composables/auth/useAuthService.ts` pattern.
 *
 * Pages/components call this service (or authStore). They must not call $fetch / API directly.
 * Server AuthZ is authoritative; ROLE_PERMISSIONS below are UI menu hints only.
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

function emptyLink() {
  return {
    fullName: '',
    label: '',
    url: '',
    type: 'text',
  }
}

/** Map Laravel Core `/auth/me` staff payload into the Vuexy `User` shape. */
export function mapStaffToUser(staff: StaffUser): User {
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

export const useAuthService = () => {
  const login = async (body: { email: string; password: string }) => {
    const staff = await API.post<StaffUser>('auth/login', body)

    if (!staff)
      return null

    return {
      status: true,
      data: mapStaffToUser(staff),
    } as ApiResponse<User>
  }

  const logout = async () => {
    return await API.post<{ detail: string }>('auth/logout', {})
  }

  /**
   * Session probe — EQMS `getUserData` equivalent.
   * Loads user into authStore when called from store.saveUser.
   */
  const getUserDetails = async (): Promise<ApiResponse<StaffSessionPayload> | null> => {
    const staff = await API.get<StaffUser>('auth/me')

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

  const getMenuItems = async (): Promise<ApiResponse<{ menu: VerticalNavItems }>> => {
    return {
      status: true,
      data: { menu: appNavigation },
    }
  }

  const resetPassword = async (body: object) => {
    return await API.post<ApiResponse<any>>('auth/reset-password', body)
  }

  const sendResetPasswordMail = async (body: { email: string }) => {
    // MVP: owners/managers reset passwords in-app; no email send yet.
    return await API.post<ApiResponse<any>>('auth/reset-password', body)
  }

  return {
    login,
    logout,
    getUserDetails,
    getMenuItems,
    resetPassword,
    sendResetPasswordMail,
  }
}
