export interface Customer {
  id?: number
  uuid?: string
  name: string
  email?: string
  phone?: string
  address?: string
  city?: string
  state?: string
  zip?: string
  gstNumber?: string
  status?: string
  createdAt?: string
  updatedAt?: string
  created_at?: string
  updated_at?: string
  deleted_at?: string
  creator?: { uuid: string; full_name: string; fullName?: string }
  updator?: { uuid: string; full_name: string; fullName?: string }
  deletedBy?: string
  actions?: {
    writePermission?: boolean
    viewPermission?: boolean
    deletePermission?: boolean
    isLocked?: boolean
  }
}

export interface CustomerForm {
  uuid?: string
  name: string
  email?: string
  phone?: string
  address?: string
  city?: string
  state?: string
  zip?: string
  gstNumber?: string
}

export const CUSTOMER_LISTING_FILTER_IDENTIFIER = 'customerListingFilters'
