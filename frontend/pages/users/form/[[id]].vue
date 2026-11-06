<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'

/**
 * Staff create / edit — EQMS form pattern:
 * auto-imported validators, external VLabel + red *, App* fields, skeleton then card.
 */

const nuxtApp: any = useNuxtApp()
const { t } = useI18n()
const route = useRoute()
const { createUser, updateUser, getUserById, formInitaldata } = useUserApiClient()

definePageMeta({
  navActiveLink: 'users',
  checkPermission: 'users.add',
  checkPermissionEdit: 'users.edit',
  form: true,
})

const uuid = route.params.id as string | undefined
const isEdit = Boolean(uuid)

useHead({
  title: isEdit ? t('edit') : t('create'),
})

const isButtonDisabled = ref(false)
const loading = ref(true)
const refUserForm = ref<InstanceType<typeof VForm>>()
const roles = ref<Array<{ id: string; name: string; roleName: string }>>([])

const userForm = ref({
  uuid: '',
  email: '',
  password: '',
  role: 'front_desk',
  is_active: true,
})

const emailRules = computed(() => [
  requiredValidator,
  emailValidator,
])

const passwordRules = computed(() => {
  if (isEdit) {
    return [
      (v: string) => (!v || String(v).length >= 8 ? true : 'Min 8 characters'),
    ]
  }

  return [
    requiredValidator,
    (v: string) => (String(v || '').length >= 8 ? true : 'Min 8 characters'),
  ]
})

const saveUser = async () => {
  const { valid } = await refUserForm.value!.validate()
  if (!valid)
    return

  isButtonDisabled.value = true

  const payload: Record<string, unknown> = {
    email: userForm.value.email,
    role: userForm.value.role,
    is_active: userForm.value.is_active,
  }

  if (userForm.value.password)
    payload.password = userForm.value.password

  if (isEdit) {
    payload.uuid = uuid
    payload.id = uuid
  }

  const response = isEdit
    ? await updateUser(payload)
    : await createUser(payload)

  if (response?.status) {
    nuxtApp.$toast.success(response.message || 'Saved', {
      onClose: () => {
        isButtonDisabled.value = false
        navigateTo(`/users/view/${response.uuid || uuid}`)
      },
    })
  }
  else {
    isButtonDisabled.value = false
  }
}

;(async () => {
  loading.value = true

  const initial = await formInitaldata()

  roles.value = initial?.roles ?? []

  if (!uuid) {
    loading.value = false

    return
  }

  const data = await getUserById(String(uuid))
  const user = data?.user as any

  if (user) {
    userForm.value = {
      uuid: String(user.uuid || user.id || uuid),
      email: user.email || '',
      password: '',
      role: user.roleCode || user.role?.name || 'front_desk',
      is_active: user.is_active ?? user.active ?? true,
    }
  }

  loading.value = false
})()
</script>

<template>
  <div>
    <FormHeaderBreadcrums
      page="users"
      :action="isEdit ? $t('edit') : $t('create')"
      :uuid="uuid ? String(uuid) : null"
    />

    <VCardText class="tw-p-0">
      <template v-if="loading">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4 tw-items-start">
          <div class="tw-col-span-2 tw-mb-5">
            <VSkeletonLoader
              :loading="loading"
              type="text"
            />
          </div>
          <div>
            <VSkeletonLoader
              :loading="loading"
              type="heading@10"
            />
          </div>
          <div>
            <VSkeletonLoader
              :loading="loading"
              type="heading@4"
            />
          </div>
        </div>
      </template>

      <VCard
        v-else
        class="pa-6"
      >
        <VForm
          ref="refUserForm"
          @submit.prevent="saveUser"
        >
          <VRow>
            <VCol
              cols="12"
              md="6"
            >
              <VLabel class="mb-1 text-body-2">
                {{ $t('email') }}
                <span class="text-error font-weight-bold ms-1">*</span>
              </VLabel>
              <AppTextField
                v-model="userForm.email"
                type="email"
                :rules="emailRules"
                required
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VLabel class="mb-1 text-body-2">
                {{ $t('role') }}
                <span class="text-error font-weight-bold ms-1">*</span>
              </VLabel>
              <AppSelect
                v-model="userForm.role"
                :items="roles"
                item-title="roleName"
                item-value="name"
                :rules="[requiredValidator]"
                required
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
            >
              <VLabel class="mb-1 text-body-2">
                {{ $t('password') }}
                <span
                  v-if="!isEdit"
                  class="text-error font-weight-bold ms-1"
                >*</span>
              </VLabel>
              <AppTextField
                v-model="userForm.password"
                type="password"
                autocomplete="new-password"
                :hint="isEdit ? 'Leave blank to keep current password' : 'Min 8 characters'"
                persistent-hint
                :rules="passwordRules"
                :required="!isEdit"
              />
            </VCol>

            <VCol
              cols="12"
              md="6"
              class="d-flex align-center"
            >
              <VCheckbox
                v-model="userForm.is_active"
                :label="$t('enabled') || 'Active'"
                hide-details
              />
            </VCol>
          </VRow>

          <div class="d-flex justify-end gap-3 mt-6">
            <VBtn
              variant="tonal"
              color="secondary"
              @click="navigateTo('/users')"
            >
              {{ $t('cancel') }}
            </VBtn>
            <VBtn
              color="primary"
              type="submit"
              :loading="isButtonDisabled"
              :disabled="isButtonDisabled"
            >
              {{ $t('save') }}
            </VBtn>
          </div>
        </VForm>
      </VCard>
    </VCardText>
  </div>
</template>
