<script setup lang="ts">
import { VForm } from 'vuetify/components/VForm'

/**
 * Staff create / edit — eQMS users/form layout (breadcrumbs + card form),
 * fields trimmed to Mountain tenant Staff (email, role, password, active).
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
const refUserForm = ref<VForm>()
const roles = ref<Array<{ id: string; name: string; roleName: string }>>([])

const userForm = ref({
  uuid: '',
  email: '',
  password: '',
  role: 'front_desk',
  is_active: true,
})

const rules = {
  required: (v: unknown) => !!v || t('required'),
  email: (v: string) => /.+@.+\..+/.test(v) || t('email'),
  passwordCreate: (v: string) => (!isEdit && (!v || String(v).length < 8) ? t('min8') || 'Min 8 characters' : true),
  passwordEdit: (v: string) => (!v || String(v).length >= 8 ? true : 'Min 8 characters'),
}

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
  const initial = await formInitaldata()
  roles.value = initial?.roles ?? []

  if (isEdit && uuid) {
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

    <template v-if="loading">
      <VSkeletonLoader type="article, actions" />
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
            <AppTextField
              v-model="userForm.email"
              :label="$t('email')"
              type="email"
              :rules="[rules.required, rules.email]"
              required
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppSelect
              v-model="userForm.role"
              :items="roles"
              item-title="roleName"
              item-value="name"
              :label="$t('role')"
              :rules="[rules.required]"
              required
            />
          </VCol>

          <VCol
            cols="12"
            md="6"
          >
            <AppTextField
              v-model="userForm.password"
              :label="$t('password')"
              type="password"
              autocomplete="new-password"
              :hint="isEdit ? 'Leave blank to keep current password' : 'Min 8 characters'"
              persistent-hint
              :rules="isEdit ? [rules.passwordEdit] : [rules.required, rules.passwordCreate]"
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
  </div>
</template>
