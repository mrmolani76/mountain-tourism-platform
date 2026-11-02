export interface Product {
  id?: number
  uuid?: string
  title: string
  slug: string
  sku?: string
  description?: string
  category?: string
  sellingPrice: number
  purchasePrice: number
  quantity: number
  alertQuantity: number
  unit: string
  hsnCode?: string
  taxRate: number
  discount?: number
  image?: string
  barcode?: string
  status?: 'active' | 'inactive' | 'out_of_stock'
  is_deleted?: boolean
  created_at?: string
  updated_at?: string
  deleted_at?: string
  actions?: {
    writePermission?: boolean
    viewPermission?: boolean
    deletePermission?: boolean
    isLocked?: boolean
  }
}

export interface ProductForm {
  id?: number
  uuid?: string
  title: string
  slug: string
  sku?: string
  description?: string
  category?: string
  sellingPrice: number
  purchasePrice: number
  quantity: number
  alertQuantity: number
  unit: string
  hsnCode?: string
  taxRate: number
  discount?: number
  image?: File | string
  barcode?: string
  status?: 'active' | 'inactive' | 'out_of_stock'
}

export const PRODUCT_LISTING_FILTER_IDENTIFIER = 'products_listing_filter'
