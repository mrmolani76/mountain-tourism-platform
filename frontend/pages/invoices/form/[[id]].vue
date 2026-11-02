<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  navActiveLink: 'invoices',
  checkPermission: 'invoices.add',
})

const route = useRoute()
const invoiceId = computed(() => route.params.id as string)
const isEditMode = computed(() => !!invoiceId.value)
const nuxtApp = useNuxtApp()
const isButtonDisabled = ref(false)
const submitLabel = computed(() => isEditMode.value ? t('update') : t('save'))

useHead({
  title: computed(() => isEditMode.value ? t('editInvoice') : t('addInvoice')),
})

const { loading, getInvoiceById, createInvoice, updateInvoice, getInvoiceFormInitialData } = useInvoiceApiClient()
const { createCustomer, loading: customerSaving } = useCustomerApiClient()

// Form options from API
const customerOptions = ref<Array<{ id: number; uuid: string; name: string; gst_number: string }>>([])
const productOptions = ref<Array<{ id: number; uuid: string; title: string; selling_price: number; tax_rate: number; quantity: number; unit: string }>>([])
const paymentStatusOptions = ref<Array<{ value: string; label: string }>>([])

// Invoice header
const formData = ref<InvoiceForm>({
  invoiceDate: '',
  dueDate: '',
  customerId: null,
  subTotal: 0,
  taxAmount: 0,
  discountAmount: 0,
  totalAmount: 0,
  paymentStatus: 'unpaid',
  notes: '',
  items: [],
})

// Invoice items rows
const invoiceItems = ref<Array<{
  productUuid: string
  quantity: number
  unitPrice: number
  taxRate: number
  taxAmount: number
  discount: number
  total: number
}>>([])

const refInvoiceForm = ref()

// ── Inline Create Customer ────────────────────────────────────────────
const createCustomerDialog = ref(false)
const refCreateCustomerForm = ref()

const newCustomerData = ref<CustomerForm>({
  name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  zip: '',
  gstNumber: '',
})

const openCreateCustomerDialog = () => {
  newCustomerData.value = {
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    zip: '',
    gstNumber: '',
  }
  createCustomerDialog.value = true
}

const saveNewCustomer = async () => {
  const { valid } = await refCreateCustomerForm.value.validate()
  if (!valid)
    return

  const response = await createCustomer(newCustomerData.value)

  if (response?.status) {
    // Naye customer ko dropdown mein add karo
    const newCustomer = {
      id: response.data?.id ?? response.id ?? Date.now(),
      uuid: response.data?.uuid ?? response.uuid ?? '',
      name: newCustomerData.value.name,
      gst_number: newCustomerData.value.gstNumber ?? '',
    }

    // Agar response mein actual customer object aaye to use use karo
    const created = (response as any).data?.customer ?? (response as any).customer
    if (created) {
      newCustomer.id = created.id
      newCustomer.uuid = created.uuid
      newCustomer.name = created.name
      newCustomer.gst_number = created.gst_number ?? created.gstNumber ?? ''
    }

    customerOptions.value.unshift(newCustomer)

    // Automatically select karo
    formData.value.customerId = newCustomer.id

    nuxtApp.$toast.success(response.message)
    createCustomerDialog.value = false
  }
}

// ─────────────────────────────────────────────────────────────────────

const createEmptyItem = () => ({
  productUuid: '',
  quantity: 1,
  unitPrice: 0,
  taxRate: 0,
  taxAmount: 0,
  discount: 0,
  total: 0,
})

const addItem = () => {
  invoiceItems.value.push(createEmptyItem())
}

const removeItem = (index: number) => {
  if (invoiceItems.value.length > 1)
    invoiceItems.value.splice(index, 1)
}

const onProductSelect = (index: number, productUuid: string) => {
  const product = productOptions.value.find(p => p.uuid === productUuid)
  if (product) {
    invoiceItems.value[index].unitPrice = Number(product.selling_price)
    invoiceItems.value[index].taxRate = Number(product.tax_rate)
    recalcItem(index)
  }
}

const recalcItem = (index: number) => {
  const item = invoiceItems.value[index]
  const lineAmount = item.quantity * item.unitPrice

  item.taxAmount = Math.round(((lineAmount * item.taxRate) / 100) * 100) / 100
  item.total = Math.round((lineAmount + item.taxAmount - item.discount) * 100) / 100
}

// Computed totals
const subTotal = computed(() =>
  invoiceItems.value.reduce((sum, item) => sum + (item.quantity * item.unitPrice), 0),
)

const taxTotal = computed(() =>
  invoiceItems.value.reduce((sum, item) => sum + item.taxAmount, 0),
)

const discountTotal = computed(() =>
  invoiceItems.value.reduce((sum, item) => sum + item.discount, 0),
)

const grandTotal = computed(() => subTotal.value + taxTotal.value - discountTotal.value)

const loadFormInitialData = async () => {
  const data = await getInvoiceFormInitialData()
  if (data) {
    customerOptions.value = data.customers ?? []
    productOptions.value = data.products ?? []
    paymentStatusOptions.value = data.paymentStatuses ?? []
  }
}

const loadInvoice = async () => {
  if (!invoiceId.value)
    return

  const invoice = await getInvoiceById(invoiceId.value)

  if (invoice) {
    const inv = ((invoice as any).data || invoice) as any
    const src = inv.invoice ?? inv

    formData.value = {
      uuid: src.uuid,
      invoiceDate: src.invoiceDate ?? src.invoice_date ?? '',
      dueDate: src.dueDate ?? src.due_date ?? '',
      customerId: src.customerId ?? src.customer_id ?? null,
      subTotal: Number(src.subTotal ?? src.sub_total ?? 0),
      taxAmount: Number(src.taxAmount ?? src.tax_amount ?? 0),
      discountAmount: Number(src.discountAmount ?? src.discount_amount ?? 0),
      totalAmount: Number(src.totalAmount ?? src.total_amount ?? 0),
      paymentStatus: src.paymentStatus ?? src.payment_status ?? 'unpaid',
      notes: src.notes ?? '',
      items: [],
    }

    invoiceItems.value = (src.items ?? []).map((item: any) => ({
      productUuid: item.productUuid,
      quantity: Number(item.quantity ?? 1),
      unitPrice: Number(item.unitPrice ?? item.unit_price ?? 0),
      taxRate: Number(item.taxRate ?? item.tax_rate ?? 0),
      taxAmount: Number(item.taxAmount ?? item.tax_amount ?? 0),
      discount: Number(item.discount ?? 0),
      total: Number(item.total ?? 0),
    }))

    ;(src.items ?? []).forEach((item: any) => {
      if (item.productUuid && !productOptions.value.find((p: any) => p.uuid === item.productUuid)) {
        productOptions.value.push({
          id: item.productId,
          uuid: item.productUuid,
          title: item.productTitle,
          selling_price: item.unitPrice,
          tax_rate: item.taxRate,
          quantity: 0,
          unit: '',
        })
      }
    })

    if (invoiceItems.value.length === 0)
      invoiceItems.value.push(createEmptyItem())
  }
}

const saveInvoice = async () => {
  isButtonDisabled.value = true

  const payload: InvoiceForm = {
    ...formData.value,
    subTotal: subTotal.value,
    taxAmount: taxTotal.value,
    discountAmount: discountTotal.value,
    totalAmount: grandTotal.value,
    items: invoiceItems.value.map(item => ({
      productUuid: item.productUuid,
      quantity: item.quantity,
      unitPrice: item.unitPrice,
      taxRate: item.taxRate,
      taxAmount: item.taxAmount,
      discount: item.discount,
      total: item.total,
    })),
  }

  if (isEditMode.value)
    payload.uuid = invoiceId.value

  let response
  if (isEditMode.value)
    response = await updateInvoice(payload)
  else
    response = await createInvoice(payload)

  if (response?.status) {
    nuxtApp.$toast.success(response.message)
    navigateTo('/invoices')
  }
  else {
    isButtonDisabled.value = false
  }
}

const handleCancel = () => {
  navigateTo('/invoices')
}

const formatCurrency = (val: number) =>
  `₹${Number(val ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

onMounted(async () => {
  await loadFormInitialData()
  if (isEditMode.value)
    await loadInvoice()
  else
    invoiceItems.value.push(createEmptyItem())
})
</script>

<template>
  <template v-if="loading">
    <VSkeletonLoader
      :loading="loading"
      type="heading@2, actions"
    />
  </template>
  <template v-else>
    <FormHeaderBreadcrums
      :uuid="invoiceId"
      page="invoices"
      :action="isEditMode ? t('update') : t('create')"
    />

    <VForm
      ref="refInvoiceForm"
      @submit.prevent="
        refInvoiceForm.validate().then(function (response) {
          response.valid ? saveInvoice() : ''
        })
      "
    >
      <!-- Invoice Header Card -->
      <VCard class="tw-mb-4">
        <VCardItem>
          <div class="tw-grid tw-grid-cols-2 tw-gap-4 tw-pt-4">
            <div class="tw-grid tw-gap-1">
              <VLabel>
                {{ $t('invoiceDate') }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <VTextField
                v-model="formData.invoiceDate"
                type="date"
                :rules="[v => !!v || t('invoiceDateRequired')]"
                required
              />
            </div>

            <div class="tw-grid tw-gap-1">
              <VLabel>{{ $t('dueDate') }}</VLabel>
              <VTextField
                v-model="formData.dueDate"
                type="date"
              />
            </div>

            <!-- Customer Field with inline Create button -->
            <div class="tw-grid tw-gap-1">
              <VLabel>
                {{ $t('customer') }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <div class="tw-flex tw-gap-2 tw-items-start">
                <VAutocomplete
                  v-model="formData.customerId"
                  :items="customerOptions"
                  itemTitle="name"
                  itemValue="id"
                  :placeholder="t('selectCustomer')"
                  :rules="[v => !!v || t('customerRequired')]"
                  clearable
                  class="tw-flex-1"
                />
                <VBtn
                  icon
                  variant="tonal"
                  color="primary"
                  size="40"
                  class="tw-mt-1 tw-flex-shrink-0"
                  :title="t('createCustomer')"
                  @click="openCreateCustomerDialog"
                >
                  <VIcon
                    icon="tabler-user-plus"
                    size="20"
                  />
                  <VTooltip activator="parent">
                    {{ t('createCustomer') }}
                  </VTooltip>
                </VBtn>
              </div>
            </div>

            <div class="tw-grid tw-gap-1">
              <VLabel>
                {{ $t('paymentStatus') }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <VAutocomplete
                v-model="formData.paymentStatus"
                :items="paymentStatusOptions"
                itemTitle="label"
                itemValue="value"
                :placeholder="t('selectPaymentStatus')"
                :rules="[v => !!v || t('paymentStatusRequired')]"
              />
            </div>

            <div class="tw-col-span-2 tw-grid tw-gap-1">
              <VLabel>{{ $t('notes') }}</VLabel>
              <VTextarea
                v-model="formData.notes"
                :placeholder="t('enterNotes')"
                rows="2"
              />
            </div>
          </div>
        </VCardItem>
      </VCard>

      <!-- Invoice Items Card -->
      <VCard class="tw-mb-4">
        <VCardItem>
          <div class="tw-flex tw-justify-between tw-items-center tw-py-2">
            <span class="tw-text-lg tw-font-semibold">{{ $t('invoiceItems') }}</span>
            <VBtn
              color="primary"
              size="small"
              prependIcon="tabler-plus"
              @click="addItem"
            >
              {{ $t('addItem') }}
            </VBtn>
          </div>

          <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-sm">
              <thead>
                <tr class="tw-border-b">
                  <th class="tw-text-left tw-py-2 tw-pr-2 tw-min-w-42">
                    {{ $t('product') }}
                  </th>
                  <th class="tw-text-right tw-py-2 tw-px-2 tw-w-20">
                    {{ $t('qty') }}
                  </th>
                  <th class="tw-text-right tw-py-2 tw-px-2 tw-w-28">
                    {{ $t('unitPrice') }}
                  </th>
                  <th class="tw-text-right tw-py-2 tw-px-2 tw-w-20">
                    {{ $t('taxRate') }} %
                  </th>
                  <th class="tw-text-right tw-py-2 tw-px-2 tw-w-24">
                    {{ $t('taxAmount') }}
                  </th>
                  <th class="tw-text-right tw-py-2 tw-px-2 tw-w-24">
                    {{ $t('discount') }}
                  </th>
                  <th class="tw-text-right tw-py-2 tw-px-2 tw-w-24">
                    {{ $t('total') }}
                  </th>
                  <th class="tw-w-10" />
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, index) in invoiceItems"
                  :key="index"
                  class="tw-border-b"
                >
                  <td class="tw-py-1 tw-pr-2 tw-w-32">
                    <VAutocomplete
                      v-model="item.productUuid"
                      :items="productOptions"
                      itemTitle="title"
                      itemValue="uuid"
                      :placeholder="t('selectProduct')"
                      density="compact"
                      hideDetails
                      @update:modelValue="onProductSelect(index, $event)"
                    />
                  </td>
                  <td class="tw-py-1 tw-px-2">
                    <VTextField
                      v-model.number="item.quantity"
                      type="number"
                      min="1"
                      density="compact"
                      hideDetails
                      @update:modelValue="recalcItem(index)"
                    />
                  </td>
                  <td class="tw-py-1 tw-px-2">
                    <VTextField
                      v-model.number="item.unitPrice"
                      type="number"
                      min="0"
                      step="0.01"
                      density="compact"
                      hideDetails
                      @update:modelValue="recalcItem(index)"
                    />
                  </td>
                  <td class="tw-py-1 tw-px-2">
                    <VTextField
                      v-model.number="item.taxRate"
                      type="number"
                      min="0"
                      step="0.01"
                      density="compact"
                      hideDetails
                      @update:modelValue="recalcItem(index)"
                    />
                  </td>
                  <td class="tw-py-1 tw-px-2 tw-text-right">
                    {{ formatCurrency(item.taxAmount) }}
                  </td>
                  <td class="tw-py-1 tw-px-2">
                    <VTextField
                      v-model.number="item.discount"
                      type="number"
                      min="0"
                      step="0.01"
                      density="compact"
                      hideDetails
                      @update:modelValue="recalcItem(index)"
                    />
                  </td>
                  <td class="tw-py-1 tw-px-2 tw-text-right tw-font-medium">
                    {{ formatCurrency(item.total) }}
                  </td>
                  <td class="tw-py-1 tw-text-center">
                    <VBtn
                      icon
                      size="28"
                      variant="text"
                      color="error"
                      :disabled="invoiceItems.length <= 1"
                      @click="removeItem(index)"
                    >
                      <VIcon
                        icon="tabler-trash"
                        size="16"
                      />
                    </VBtn>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totals -->
          <div class="tw-flex tw-justify-end tw-mt-4">
            <div class="tw-w-72">
              <div class="tw-flex tw-justify-between tw-py-1 tw-border-b">
                <span class="tw-text-gray-600">{{ $t('subTotal') }}</span>
                <span class="tw-font-medium">{{ formatCurrency(subTotal) }}</span>
              </div>
              <div class="tw-flex tw-justify-between tw-py-1 tw-border-b">
                <span class="tw-text-gray-600">{{ $t('taxTotal') }}</span>
                <span class="tw-font-medium">{{ formatCurrency(taxTotal) }}</span>
              </div>
              <div class="tw-flex tw-justify-between tw-py-1 tw-border-b">
                <span class="tw-text-gray-600">{{ $t('discount') }}</span>
                <span class="tw-font-medium">{{ formatCurrency(discountTotal) }}</span>
              </div>
              <div class="tw-flex tw-justify-between tw-py-2">
                <span class="tw-font-semibold tw-text-base">{{ $t('grandTotal') }}</span>
                <span class="tw-font-bold tw-text-base">{{ formatCurrency(grandTotal) }}</span>
              </div>
            </div>
          </div>
        </VCardItem>
      </VCard>

      <!-- Action Buttons -->
      <div class="d-flex flex-wrap gap-2 tw-justify-start tw-mt-2">
        <VBtn
          :disabled="isButtonDisabled"
          :loading="isButtonDisabled"
          color="primary"
          variant="outlined"
          @click="handleCancel"
        >
          {{ $t('cancel') }}
        </VBtn>
        <VBtn
          :disabled="isButtonDisabled"
          :loading="isButtonDisabled"
          color="primary"
          type="submit"
        >
          {{ submitLabel }}
        </VBtn>
      </div>
    </VForm>
  </template>

  <!-- ── Inline Create Customer Dialog ───────────────────────────── -->
  <VDialog
    v-model="createCustomerDialog"
    maxWidth="540"
    persistent
    transition="dialog-transition"
  >
    <VCard>
      <VCardTitle class="tw-flex tw-items-center tw-gap-2 tw-px-6 tw-pt-5 tw-pb-2">
        <VIcon
          icon="tabler-user-plus"
          size="22"
          color="primary"
        />
        <span>{{ $t('createCustomer') }}</span>
      </VCardTitle>

      <VDivider />

      <VCardText class="tw-px-6 tw-pt-4">
        <VForm ref="refCreateCustomerForm">
          <div class="tw-grid tw-grid-cols-2 tw-gap-4">
            <div class="tw-col-span-2 tw-grid tw-gap-1">
              <VLabel>
                {{ $t('name') }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <VTextField
                v-model="newCustomerData.name"
                :placeholder="t('enterName')"
                :rules="[v => !!v || t('nameRequired')]"
                required
              />
            </div>

            <div class="tw-grid tw-gap-1">
              <VLabel>{{ $t('email') }}</VLabel>
              <VTextField
                v-model="newCustomerData.email"
                :placeholder="t('enterEmail')"
                type="email"
                :rules="[v => !v || /.+@.+\..+/.test(v) || t('emailInvalid')]"
              />
            </div>

            <div class="tw-grid tw-gap-1">
              <VLabel>{{ $t('phone') }}</VLabel>
              <VTextField
                v-model="newCustomerData.phone"
                :placeholder="t('enterPhone')"
              />
            </div>

            <div class="tw-col-span-2 tw-grid tw-gap-1">
              <VLabel>{{ $t('address') }}</VLabel>
              <VTextarea
                v-model="newCustomerData.address"
                :placeholder="t('enterAddress')"
                rows="2"
              />
            </div>

            <div class="tw-grid tw-gap-1">
              <VLabel>{{ $t('city') }}</VLabel>
              <VTextField
                v-model="newCustomerData.city"
                :placeholder="t('enterCity')"
              />
            </div>

            <div class="tw-grid tw-gap-1">
              <VLabel>{{ $t('state') }}</VLabel>
              <VTextField
                v-model="newCustomerData.state"
                :placeholder="t('enterState')"
              />
            </div>

            <div class="tw-grid tw-gap-1">
              <VLabel>{{ $t('zip') }}</VLabel>
              <VTextField
                v-model="newCustomerData.zip"
                :placeholder="t('enterZip')"
              />
            </div>

            <div class="tw-grid tw-gap-1">
              <VLabel>{{ $t('gstNumber') }}</VLabel>
              <VTextField
                v-model="newCustomerData.gstNumber"
                :placeholder="t('enterGstNumber')"
              />
            </div>
          </div>
        </VForm>
      </VCardText>

      <VDivider />

      <VCardActions class="tw-px-6 tw-py-4 tw-justify-end tw-gap-2">
        <VBtn
          variant="outlined"
          color="secondary"
          :disabled="customerSaving"
          @click="createCustomerDialog = false"
        >
          {{ $t('cancel') }}
        </VBtn>
        <VBtn
          color="primary"
          :loading="customerSaving"
          :disabled="customerSaving"
          @click="saveNewCustomer"
        >
          {{ $t('save') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
