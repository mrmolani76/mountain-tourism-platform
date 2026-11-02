export interface InvoiceItem {
  id?: number
  uuid?: string
  invoiceId?: number
  productId?: number
  productUuid?: string
  productTitle?: string
  quantity: number
  unitPrice: number
  taxRate: number
  taxAmount: number
  discount: number
  total: number
}

export interface Invoice {
  id?: number
  uuid?: string
  invoiceNumber?: string
  invoiceDate: string
  dueDate?: string
  customerId?: number
  customerName?: string
  subTotal: number
  taxAmount: number
  discountAmount: number
  totalAmount: number
  paymentStatus: 'unpaid' | 'paid' | 'partial'
  notes?: string
  createdAt?: string
  updatedAt?: string
  created_at?: string
  updated_at?: string
  deleted_at?: string
  creator?: { uuid: string; full_name: string; fullName?: string }
  updator?: { uuid: string; full_name: string; fullName?: string }
  deletedBy?: string
  items?: InvoiceItem[]
  actions?: {
    writePermission?: boolean
    viewPermission?: boolean
    deletePermission?: boolean
    isLocked?: boolean
  }
}

export interface InvoiceFormItem {
  productUuid: string
  quantity: number
  unitPrice: number
  taxRate: number
  taxAmount: number
  discount: number
  total: number
}

export interface InvoiceForm {
  uuid?: string
  invoiceDate: string
  dueDate?: string
  customerId: number | null
  subTotal: number
  taxAmount: number
  discountAmount: number
  totalAmount: number
  paymentStatus: string
  notes?: string
  items: InvoiceFormItem[]
}

export const INVOICE_LISTING_FILTER_IDENTIFIER = 'invoiceListingFilters'
