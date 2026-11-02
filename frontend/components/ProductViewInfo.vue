<script setup lang="ts">
defineProps({
  entityData: {
    type: Object,
    required: false,
  },
})
</script>

<template>
  <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-2">
    <div>
      <VCard>
        <div class="tw-mb-4 tw-text-lg tw-mt-4 tw-ml-4">
          {{ $t('basicDetails') }}
        </div>
        <VDivider
          :thickness="1"
          class="border-opacity-100"
          color="secondary"
        />
        <VList width="100%">
          <VListItem>
            <LabelValueDisplay
              label="title"
              :value="entityData?.title"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="slug"
              :value="entityData?.slug"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="sku"
              :value="entityData?.sku"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="category"
              :value="entityData?.category"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="barcode"
              :value="entityData?.barcode"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="hsnCode"
              :value="entityData?.hsn_code"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="description"
              :value="entityData?.description"
            />
          </VListItem>
        </VList>
      </VCard>
    </div>

    <div>
      <VCard>
        <div class="tw-mb-4 tw-text-lg tw-mt-4 tw-ml-4">
          {{ $t('pricingDetails') }}
        </div>
        <VDivider
          :thickness="1"
          class="border-opacity-100"
          color="secondary"
        />
        <VList width="100%">
          <VListItem>
            <LabelValueDisplay
              label="sellingPrice"
              :value="entityData?.selling_price ? `₹${Number(entityData.selling_price).toLocaleString('en-IN', { minimumFractionDigits: 2 })}` : null"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="purchasePrice"
              :value="entityData?.purchase_price ? `₹${Number(entityData.purchase_price).toLocaleString('en-IN', { minimumFractionDigits: 2 })}` : null"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="taxRate"
              :value="entityData?.tax_rate ? `${entityData.tax_rate}%` : null"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="discount"
              :value="entityData?.discount ? `₹${Number(entityData.discount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}` : null"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="quantity"
              :value="entityData?.quantity"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="alertQuantity"
              :value="entityData?.alert_quantity"
            />
          </VListItem>
          <VListItem>
            <LabelValueDisplay
              label="unit"
              :value="entityData?.unit"
            />
          </VListItem>
        </VList>
      </VCard>
    </div>
  </div>
</template>
