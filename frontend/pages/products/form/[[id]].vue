-- Active: 1771068012160@@127.0.0.1@3306@laravel_my_shop
<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  navActiveLink: 'products',
  checkPermission: 'products.add',
})

const route = useRoute()
const productId = computed(() => route.params.id as string)
const isEditMode = computed(() => !!productId.value)
const nuxtApp = useNuxtApp()
const isButtonDisabled = ref(false)
const submitLabel = computed(() => isEditMode.value ? t('update') : t('save'))

const formOptions = ref({
  units: [],
})

useHead({
  title: isEditMode.value ? t('editProduct') : t('addProduct'),
})

const { loading, getProductById, createProduct, updateProduct, formInitialData } = useProductApiClient()

const formData = ref<ProductForm>({
  title: '',
  slug: '',
  sku: '',
  description: '',
  category: '',
  sellingPrice: 0,
  purchasePrice: 0,
  quantity: 0,
  alertQuantity: 10,
  unit: 'pcs',
  hsnCode: '',
  taxRate: 0,
  discount: 0,
  barcode: '',
  status: 'active',
})

const refProductForm = ref()

const loadFormInitialData = async () => {
  const data = await formInitialData()
  if (data)
    formOptions.value.units = data.units || []
}

const loadProduct = async () => {
  if (!productId.value)
    return

  const product = await getProductById(productId.value)

  if (product) {
    let p = ((product as any).data || product) as any
    if (p.product)
      p = p.product

    formData.value = {
      title: p.title,
      slug: p.slug,
      sku: p.sku,
      description: p.description,
      category: p.category,
      sellingPrice: p.sellingPrice ?? p.selling_price ?? 0,
      purchasePrice: p.purchasePrice ?? p.purchase_price ?? 0,
      quantity: p.quantity ?? 0,
      alertQuantity: p.alertQuantity ?? p.alert_quantity ?? 10,
      unit: p.unit || 'pcs',
      hsnCode: p.hsnCode ?? p.hsn_code ?? '',
      taxRate: p.taxRate ?? p.tax_rate ?? 0,
      discount: p.discount ?? 0,
      barcode: p.barcode ?? '',
      status: p.status ?? 'active',
      uuid: p.uuid,
    }
  }
}

const generateSku = () => {
  if (isEditMode.value)
    return

  const prefix = formData.value.title
    ? formData.value.title
      .trim()
      .toUpperCase()
      .replace(/[^A-Z0-9]/g, '')
      .slice(0, 3)
      .padEnd(3, 'X')
    : 'PRD'

  const suffix = Math.random().toString(36).substring(2, 8).toUpperCase()

  formData.value.sku = `${prefix}-${suffix}`
}

const handleTitleChange = () => {
  if (!isEditMode.value && formData.value.title) {
    // Simple slug generation
    formData.value.slug = formData.value.title
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '')

    // Auto-generate SKU from title only once on create
    if (!formData.value.sku)
      generateSku()
  }
}

const saveProduct = async () => {
  isButtonDisabled.value = true

  const payload = {
    ...formData.value,
  }

  let response
  if (isEditMode.value)
    response = await updateProduct(payload)
  else
    response = await createProduct(payload)

  if (response?.status) {
    nuxtApp.$toast.success(response.message)
    navigateTo('/products')
  }
  else {
    isButtonDisabled.value = false
  }
}

const handleCancel = () => {
  navigateTo('/products')
}

onMounted(async () => {
  await loadFormInitialData()
  if (isEditMode.value)
    await loadProduct()
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
      :uuid="productId"
      page="products"
      :action="isEditMode ? t('update') : t('create')"
    />

    <div class="tw-grid tw-grid-cols-2">
      <VCard>
        <VCardItem>
          <VForm
            ref="refProductForm"
            class="tw-pt-5"
            @submit.prevent="
              refProductForm.validate().then(function (response) {
                response.valid ? saveProduct($event) : '';
              })

            "
          >
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("product") }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <VTextField
                v-model="formData.title"
                :placeholder="t('enterProductTitle')"
                :rules="[v => !!v || t('productTitleRequired')]"
                required
                @update:modelValue="handleTitleChange"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("slug") }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <VTextField
                v-model="formData.slug"
                :placeholder="t('enterProductSlug')"
                :rules="[v => !!v || t('productSlugRequired')]"
                required
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("sku") }}
              </VLabel>
              <div class="tw-flex tw-gap-2">
                <VTextField
                  v-model="formData.sku"
                  :placeholder="t('enterSku')"
                  class="tw-flex-1"
                />
                <VBtn
                  v-if="!isEditMode"
                  icon
                  variant="tonal"
                  color="primary"
                  :title="t('generateSku')"
                  @click="generateSku"
                >
                  <VIcon icon="tabler-refresh" />
                </VBtn>
              </div>
            </div>

            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("purchasePrice") }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <VTextField
                v-model.number="formData.purchasePrice"
                :placeholder="t('enterPurchasePrice')"
                type="number"
                step="0.01"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("sellingPrice") }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <VTextField
                v-model.number="formData.sellingPrice"
                :placeholder="t('enterSellingPrice')"
                type="number"
                step="0.01"
                :rules="[v => v >= 0 || t('priceRequired')]"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("quantity") }}
                <span class="tw-text-red-500 tw-font-bold tw-ms-2">*</span>
              </VLabel>
              <VTextField
                v-model.number="formData.quantity"
                :placeholder="t('enterQuantity')"
                type="number"
                step="1"
                :rules="[v => v >= 0 || t('quantityRequired')]"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("alertQuantity") }}
              </VLabel>
              <VTextField
                v-model.number="formData.alertQuantity"
                :placeholder="t('enterAlertQuantity')"
                type="number"
                step="1"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("unit") }}
              </VLabel>
              <VAutocomplete
                v-model="formData.unit"
                :items="formOptions.units"
                :placeholder="t('selectUnit')"
                itemTitle="label"
                itemValue="value"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("category") }}
              </VLabel>
              <VTextField
                v-model="formData.category"
                :placeholder="t('enterCategory')"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("hsnCode") }}
              </VLabel>
              <VTextField
                v-model="formData.hsnCode"
                :placeholder="t('enterHsnCode')"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("taxRate") }}
              </VLabel>
              <VTextField
                v-model.number="formData.taxRate"
                :placeholder="t('enterTaxRate')"
                type="number"
                step="0.01"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("discount") }}
              </VLabel>
              <VTextField
                v-model.number="formData.discount"
                :placeholder="t('enterDiscount')"
                type="number"
                step="0.01"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("barcode") }}
              </VLabel>
              <VTextField
                v-model="formData.barcode"
                :placeholder="t('enterBarcode')"
              />
            </div>
            <div class="tw-mb-4 tw-grid tw-gap-1">
              <VLabel>
                {{ $t("description") }}
              </VLabel>
              <VTextarea
                v-model="formData.description"
                :placeholder="t('enterDescription')"
                rows="3"
              />
            </div>
            <div class="d-flex flex-wrap gap-2  tw-justify-start tw-mt-5">
              <VBtn
                :disabled="isButtonDisabled"
                :loading="isButtonDisabled"
                color="primary"
                variant="outlined"
                @click="handleCancel"
              >
                {{ $t("cancel") }}
              </VBtn>
              <VBtn
                v-if="!isEditMode"
                :disabled="isButtonDisabled"
                :loading="isButtonDisabled"
                color="primary"
                name="save_and_new"
                type="submit"
              >
                {{ $t("save_and_new") }}
              </VBtn>
              <VBtn
                :disabled="isButtonDisabled"
                :loading="isButtonDisabled"
                color="primary"
                name="save"
                type="submit"
              >
                {{ submitLabel }}
              </VBtn>
            </div>
          </VForm>
        </VCardItem>
      </VCard>
    </div>
  </template>
</template>
