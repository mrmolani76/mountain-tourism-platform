<script setup lang="ts">
const props = defineProps({
  entityData: {
    type: Object,
    default: () => ({}),
  },
})

const { t } = useI18n()

const formatCurrency = (val: number) =>
  `₹${Number(val ?? 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`

const items = computed(() => props.entityData?.items ?? [])

const subTotal = computed(() =>
  items.value.reduce((sum: number, item: any) => sum + (Number(item.quantity) * Number(item.unit_price ?? item.unitPrice ?? 0)), 0),
)

const taxTotal = computed(() =>
  items.value.reduce((sum: number, item: any) => sum + Number(item.tax_amount ?? item.taxAmount ?? 0), 0),
)

const discountTotal = computed(() =>
  items.value.reduce((sum: number, item: any) => sum + Number(item.discount ?? 0), 0),
)

const grandTotal = computed(() =>
  Number(props.entityData?.total_amount ?? props.entityData?.totalAmount ?? 0),
)
</script>

<template>
  <div>
    <!-- Invoice Info -->
    <VCard class="tw-mb-4">
      <VCardText>
        <div class="tw-grid tw-grid-cols-2 tw-gap-x-8 tw-gap-y-4">
          <div>
            <VLabel class="tw-text-xs tw-text-gray-500">{{ t('customer') }}</VLabel>
            <div class="tw-font-medium tw-mt-1">
              {{ displayOrFallback(entityData?.customer_name ?? entityData?.customerName) }}
            </div>
          </div>

          <div>
            <VLabel class="tw-text-xs tw-text-gray-500">{{ t('invoiceDate') }}</VLabel>
            <div class="tw-font-medium tw-mt-1">
              {{ displayOrFallback(entityData?.invoice_date ?? entityData?.invoiceDate) }}
            </div>
          </div>

          <div>
            <VLabel class="tw-text-xs tw-text-gray-500">{{ t('dueDate') }}</VLabel>
            <div class="tw-font-medium tw-mt-1">
              {{ displayOrFallback(entityData?.due_date ?? entityData?.dueDate) }}
            </div>
          </div>

          <div>
            <VLabel class="tw-text-xs tw-text-gray-500">{{ t('totalAmount') }}</VLabel>
            <div class="tw-font-bold tw-mt-1 tw-text-base">
              {{ formatCurrency(grandTotal) }}
            </div>
          </div>

          <div class="tw-col-span-2">
            <VLabel class="tw-text-xs tw-text-gray-500">{{ t('notes') }}</VLabel>
            <div class="tw-font-medium tw-mt-1">
              {{ displayOrFallback(entityData?.notes) }}
            </div>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- Items Table -->
    <VCard>
      <VCardText>
        <div class="tw-text-base tw-font-semibold tw-mb-3">{{ t('invoiceItems') }}</div>
        <div class="tw-overflow-x-auto">
          <table class="tw-w-full tw-text-sm">
            <thead>
              <tr class="tw-border-b tw-bg-gray-50">
                <th class="tw-text-left tw-py-2 tw-px-2 tw-w-10">#</th>
                <th class="tw-text-left tw-py-2 tw-px-2">{{ t('product') }}</th>
                <th class="tw-text-right tw-py-2 tw-px-2 tw-w-16">{{ t('qty') }}</th>
                <th class="tw-text-right tw-py-2 tw-px-2 tw-w-28">{{ t('unitPrice') }}</th>
                <th class="tw-text-right tw-py-2 tw-px-2 tw-w-20">{{ t('taxRate') }} %</th>
                <th class="tw-text-right tw-py-2 tw-px-2 tw-w-28">{{ t('taxAmount') }}</th>
                <th class="tw-text-right tw-py-2 tw-px-2 tw-w-28">{{ t('discount') }}</th>
                <th class="tw-text-right tw-py-2 tw-px-2 tw-w-28">{{ t('total') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, index) in items"
                :key="item.id ?? index"
                class="tw-border-b"
              >
                <td class="tw-py-2 tw-px-2 tw-text-gray-500">{{ index + 1 }}</td>
                <td class="tw-py-2 tw-px-2">{{ item.product_title ?? item.productTitle }}</td>
                <td class="tw-py-2 tw-px-2 tw-text-right">{{ item.quantity }}</td>
                <td class="tw-py-2 tw-px-2 tw-text-right">{{ formatCurrency(Number(item.unit_price ?? item.unitPrice ?? 0)) }}</td>
                <td class="tw-py-2 tw-px-2 tw-text-right">{{ item.tax_rate ?? item.taxRate ?? 0 }}%</td>
                <td class="tw-py-2 tw-px-2 tw-text-right">{{ formatCurrency(Number(item.tax_amount ?? item.taxAmount ?? 0)) }}</td>
                <td class="tw-py-2 tw-px-2 tw-text-right">{{ formatCurrency(Number(item.discount ?? 0)) }}</td>
                <td class="tw-py-2 tw-px-2 tw-text-right tw-font-medium">{{ formatCurrency(Number(item.total ?? 0)) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="tw-border-t tw-bg-gray-50">
                <td
                  colspan="7"
                  class="tw-py-2 tw-px-2 tw-text-right tw-font-semibold"
                >
                  {{ t('subTotal') }}
                </td>
                <td class="tw-py-2 tw-px-2 tw-text-right tw-font-semibold">
                  {{ formatCurrency(subTotal) }}
                </td>
              </tr>
              <tr class="tw-border-t">
                <td
                  colspan="7"
                  class="tw-py-2 tw-px-2 tw-text-right tw-font-semibold"
                >
                  {{ t('taxTotal') }}
                </td>
                <td class="tw-py-2 tw-px-2 tw-text-right tw-font-semibold">
                  {{ formatCurrency(taxTotal) }}
                </td>
              </tr>
              <tr class="tw-border-t">
                <td
                  colspan="7"
                  class="tw-py-2 tw-px-2 tw-text-right tw-font-semibold"
                >
                  {{ t('discount') }}
                </td>
                <td class="tw-py-2 tw-px-2 tw-text-right tw-font-semibold">
                  {{ formatCurrency(discountTotal) }}
                </td>
              </tr>
              <tr class="tw-border-t tw-bg-gray-100">
                <td
                  colspan="7"
                  class="tw-py-2 tw-px-2 tw-text-right tw-font-bold tw-text-base"
                >
                  {{ t('grandTotal') }}
                </td>
                <td class="tw-py-2 tw-px-2 tw-text-right tw-font-bold tw-text-base">
                  {{ formatCurrency(grandTotal) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </VCardText>
    </VCard>
  </div>
</template>
