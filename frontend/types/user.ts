export interface User extends Entity {
  id: number
  fullName: string
  firstName: string
  lastName: string
  email: string
  askEmail: boolean
  prefixedUsername: string
  theme: string
  lang: string
  sid: string
  userType: string
  roleName: string
  roleCode?: string
  tenantSlug?: string
  role: Role
  termsAccepted: boolean
  phoneNo: number | null
  roleId: number | null
  lastLogin: string
  loginCount: string
  userProfile: string | null
  city: string | null
  address1: string | null
  address2: string | null
  zip: string | null
}

export interface AuthCredentials {
  email: string
  password: string
}

export interface ClientData {
  saasCustomer: string
  environment: string
}

export interface UserForm {
  uuid: string
  email: string
  firstName: string
  lastName: string
  phoneNo: number | null
  roleId: number | null
  lang: string
  active: boolean
  userProfile: string | null
  city: string | null
  address1: string | null
  address2: string | null
  zip: string | null
}

export interface UpdatePasswordForm {
  currentPassword: string
  password: string
  confirmPassword: string
  logoutFromAllDevices: boolean
}

export interface Role {
  id: number
  name: string
  gaurdName: string
  roleName: string
  additionalData: Array<any>
}

/**
 * Staff shape returned by Laravel Module Core (`/api/auth/me`).
 * `role` is the human label, `role_code` is the stable code.
 */
export interface StaffUser {
  id: number
  email: string
  role: string
  role_code: string
  is_active: boolean
  tenant_slug: string | null
}

export interface StaffSessionPayload {
  user: User
  staff: StaffUser
  userImage: string
  userPermissions: Array<string>
}
