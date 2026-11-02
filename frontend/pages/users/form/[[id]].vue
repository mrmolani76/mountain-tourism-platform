<script setup lang="ts">
import { VForm } from 'vuetify/components/VForm';
import { themeConfig } from '@/themeConfig';

const nuxtApp: any = useNuxtApp();
const { t } = useI18n();
const route = useRoute()
const router = useRouter();
const { getUser } = authStore();
const { createUser, updateUser, getUserById, formInitaldata } = useUserApiClient();

definePageMeta({
  navActiveLink: 'users',
  checkPermission: 'users.add',
  checkPermissionEdit: 'users.edit',
  form: true,
})

const uuid = route.params.id
const userRoles = [ROLE_SUPER_ADMIN];

// Base framework only supports super_admin role

const isButtonDisabled = ref(false);
const loading = ref(false);
const refUserForm = ref()
const roles = ref<Role[]>([])
const refInputEl = ref()
const refEditEl = ref()
const editedUserImage = ref();
const profileUrl = ref();
const userImage = ref<string | null>(null);
const selectedRoleID = ref();

const userForm = ref<UserForm>({
  uuid: '',
  firstName: '',
  lastName: '',
  email: '',
  phoneNo: null,
  city: '',
  address1: '',
  address2: '',
  zip: '',
  lang: '',
  userProfile: null,
  roleId: null,
});

const saveUser = async () => {
  isButtonDisabled.value = true

  const response = uuid ? await updateUser(userForm.value) : await createUser(userForm.value)

  if (response?.status) {
    nuxtApp.$toast.success(response.message, {
      onClose: () => {
        isButtonDisabled.value = false
        navigateTo(`/users/view/${response.uuid}`);
      },
    });
  }
  else {
    isButtonDisabled.value = false
  }
}


const changeAvatar = (files: File[] | File | null) => {
  const file = Array.isArray(files) ? files[0] : files
  if (!file)
    return

  const url = URL.createObjectURL(file)

  profileUrl.value = url
  editedUserImage.value = url

  // bind the actual File for upload
  userForm.value.userProfile = file as any
}

const onSelectedRole = (id: number) => {
  // Role selection handler - can be extended for future role-specific logic
}

const languages = themeConfig.app.i18n.langConfig.map(lang => ({
  ...lang,
  label: t(lang.label),
}));

(async () => {
  useHead({
    title: t('create'),
  })

  const responseData = await formInitaldata()

  roles.value = responseData?.roles.filter(role => !userRoles.includes(role.name))

  if (!uuid) {
    return;
  }

  const response = await getUserById(uuid);
  const user = response?.user

  userImage.value = response?.userImage || null;

  if (!user)
    return;

  userForm.value = {
    uuid: user.uuid,
    email: user.email,
    firstName: user.firstName,
    lastName: user.lastName,
    phoneNo: user.phoneNo,
    city: user.city,
    address1: user.address1,
    address2: user.address2,
    zip: user.zip,
    roleId: user.role.id,
    lang: user.lang,
    userProfile: null,
  }

  selectedRoleID.value = user.role.id;

  useHead({
    title: `${t('update')} ${user?.fullName}`,
  })
})();

// Email is required for all users
const isEmailRequired = computed(() => true)

const emailRules = computed(() => {
  const rules = [emailValidator, textMaxValidator(userForm.value.email, TEXT_MAX_LIMIT)]
  if (isEmailRequired.value)
    rules.unshift(requiredValidator)

  return rules;
});
</script>

<template>
  <FormHeaderBreadcrums
    :uuid="uuid"
    page="users"
    :action="(uuid) ? t('update') : t('create')"
  />
  <div>
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
      <!-- 👉 stepper content -->
      <template v-else>
        <VForm
          ref="refUserForm"
          @submit.prevent="refUserForm.validate().then(function (response: any) {
            response.valid ? saveUser()
            : ''
          })"
        >
          <VRow>
            <VCol
              cols="12"
              sm="6"
              md="6"
            >
              <VCard
                flat
                width="100%"
              >
                <div class="tw-mb-2 tw-text-lg tw-mt-2 tw-ml-4">
                  {{ t('personalInformation') }}
                </div>
                <VDivider
                  :thickness="1"
                  class="border-opacity-100"
                  color="secondary"
                />
                <VCardText>
                  <VRow v-if="uuid">
                    <VCol class="d-flex gap-4 tw-items-end">
                      <VAvatar
                        v-if="editedUserImage?.length || userImage?.length"
                        rounded
                        :size="100"
                      >
                        <VImg
                          alt="Avatar"
                          :src="editedUserImage ?? userImage"
                        >
                          <template #placeholder>
                            <VProgressCircular
                              indeterminate
                              color="primary"
                            />
                          </template>
                        </VImg>
                      </VAvatar>
                      <VAvatar
                        v-else
                        variant="tonal"
                        icon="tabler-user"
                        class="tw-mt-1"
                        rounded
                        :size="100"
                        :image="profileUrl"
                      />
                      <div>
                        <div class="tw-mt-1">
                          <VBtn
                            color="primary"
                            @click="refEditEl?.click()"
                          >
                            <VIcon icon="tabler-photo-edit" />
                            <span class="d-sm-block ">{{ t('editImage') }}</span>
                          </VBtn>

                          <VFileInput
                            ref="refEditEl"
                            v-model="userForm.userProfile"
                            class="tw-hidden"
                            :label="t('fileInput')"
                            showSize
                            accept=".jpeg, .png, .jpg, .GIF"
                            @update:modelValue="changeAvatar"
                          />
                        </div>
                      </div>
                    </VCol>
                  </VRow>

                  <VRow v-else>
                    <VCol class="d-flex tw-items-end gap-4">
                      <VAvatar
                        variant="tonal"
                        icon="tabler-user"
                        :image="profileUrl"
                      />

                      <div>
                        <div class="d-flex flex-wrap gap-2">
                          <VBtn
                            color="primary"
                            @click="refInputEl?.click()"
                          >
                            <VIcon icon="tabler-cloud-upload" />
                            <span class="d-none d-sm-block">{{ t('uploadNewPhoto') }}</span>
                          </VBtn>
                          <VFileInput
                            ref="refInputEl"
                            v-model="userForm.userProfile"
                            :label="t('fileInput')"
                            showSize
                            class="tw-hidden"
                            accept=".jpeg, .png, .jpg, .GIF"
                            @update:modelValue="changeAvatar"
                          />
                        </div>
                      </div>
                    </VCol>
                  </VRow>
                  <VRow>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('firstName') }}
                        <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
                      </VLabel>
                      <AppTextField
                        v-model="userForm.firstName"
                        placeholder=""
                        :rules="[requiredValidator, textMaxValidator(userForm.firstName, FIRST_AND_LAST_NAME_MAX_LIMIT)]"
                        required="true"
                      />
                    </VCol>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('lastName') }}
                        <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
                      </VLabel>
                      <AppTextField
                        v-model="userForm.lastName"
                        placeholder=""
                        required="true"
                        :rules="[requiredValidator, textMaxValidator(userForm.firstName, FIRST_AND_LAST_NAME_MAX_LIMIT)]"
                      />
                    </VCol>
                  </VRow>
                  <VRow>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('email') }}
                        <span
                          v-if="isEmailRequired"
                          class=" tw-text-red-500 tw-font-bold tw-ms-2"
                        >*</span>
                      </VLabel>
                      <AppTextField
                        v-model="userForm.email"
                        placeholder=""
                        :rules="emailRules"
                        :required="isEmailRequired"
                      />
                    </VCol>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('phoneNo') }}
                        <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
                      </VLabel>
                      <AppTextField
                        v-model="userForm.phoneNo"
                        type="tel"
                        prefix="+673"
                        :placeholder="t('enterSevenDigitNumber')"
                        :maxlength="CONTACT_NUMBER_MAX_LIMIT"
                        :rules="[requiredValidator, phoneNumberValidator, maxlengthValidator(userForm.phoneNo, CONTACT_NUMBER_MAX_LIMIT)]"
                      />
                    </VCol>
                  </VRow>
                  <VRow>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('addressLine1') }}
                      </VLabel>
                      <AppTextField v-model="userForm.address1" />
                    </VCol>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('addressLine2') }}
                      </VLabel>
                      <AppTextField v-model="userForm.address2" />
                    </VCol>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('city') }}
                      </VLabel>
                      <AppTextField v-model="userForm.city" />
                    </VCol>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('zipCode') }}
                      </VLabel>
                      <AppTextField
                        v-model="userForm.zip"
                        :rules="[zipCodeValidator]"
                      />
                    </VCol>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('language') }}
                        <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
                      </VLabel>
                      <AppAutocomplete
                        v-model="userForm.lang"
                        :items="languages"
                        itemTitle="label"
                        itemValue="i18nLang"
                        itemKey="i18nLang"
                        :rules="[requiredValidator]"
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
            </VCol>
            <VCol
              cols="12"
              sm="6"
              md="6"
            >
              <VCard
                flat
                width="100%"
              >
                <div class="tw-mb-2 tw-text-lg tw-mt-2 tw-ml-4">
                  {{ t('roleInformation') }}
                </div>
                <VDivider
                  :thickness="1"
                  class="border-opacity-100"
                  color="secondary"
                />
                <VCardText>
                  <VRow>
                    <VCol cols="12">
                      <VLabel>
                        {{ t('role') }}
                        <span class=" tw-text-red-500 tw-font-bold tw-ms-2">*</span>
                      </VLabel>
                      <AppSelect
                        v-model="userForm.roleId"
                        :items="roles"
                        itemTitle="roleName"
                        itemValue="id"
                        :rules="[requiredValidator]"
                        required="true"
                        @update:modelValue="onSelectedRole"
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
          <div class="w-100 sticky-footer mt-5">
            <VCard class="tw-w-full">
              <VCardText class="tw-flex tw-gap-2 tw-justify-start tw-p-2">
                <VBtn
                  :disabled="isButtonDisabled"
                  :loading="isButtonDisabled"
                  color="primary"
                  variant="outlined"
                  @click="router.back()"
                >
                  {{ t('cancel') }}
                </VBtn>
                <VBtn
                  :disabled="isButtonDisabled"
                  :loading="isButtonDisabled"
                  color="primary"
                  type="submit"
                  name="save"
                >
                  {{ userForm?.uuid ? t('update') : t('save') }}
                </VBtn>
              </VCardText>
            </VCard>
          </div>
        </VForm>
      </template>
    </VCardText>
  </div>
</template>
