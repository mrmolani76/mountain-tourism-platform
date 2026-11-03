import { defineStore } from 'pinia'
import { useAuthService } from '@/composables/auth/useAuthService'

/**
 * Tenant staff session store.
 * EQMS name alias: `useAuthStore` (same store id `auth`).
 */
export const authStore = defineStore('auth', () => {
  const { getUserDetails, logout } = useAuthService()
  const user: Ref<User | undefined> = ref()
  const authenticated = ref<boolean | undefined>()
  const userImage: Ref<string> = ref('')
  const userPermissions: Ref<Array<string>> = ref([])
  const loginDialog = ref(false)

  async function saveUser() {
    const response = await getUserDetails()

    if (!response) {
      unauthenticate()

      return null
    }

    user.value = response.data.user as User
    userImage.value = response.data.userImage || ''
    userPermissions.value = response.data.userPermissions || []
    authenticate()

    return user.value
  }

  async function logoutUser() {
    await logout()

    user.value = undefined
    userImage.value = ''
    userPermissions.value = []
    unauthenticate()
  }

  function authenticate() {
    authenticated.value = true
  }

  function unauthenticate() {
    authenticated.value = false
  }

  function openLoginDialog() {
    loginDialog.value = true
  }

  function closeLoginDialog() {
    loginDialog.value = false
  }

  const getUser = computed(() => user.value as User)
  const isAuthenticated = computed(() => authenticated.value)
  const getUserPermissions = computed(() => userPermissions.value)
  const getUserImage = computed(() => userImage.value)
  const logDialogState = computed(() => loginDialog.value)

  return {
    user,
    authenticated,
    getUser,
    saveUser,
    logoutUser,
    getUserImage,
    authenticate,
    unauthenticate,
    getUserPermissions,
    isAuthenticated,
    openLoginDialog,
    closeLoginDialog,
    logDialogState,
  }
})

/** eQMS qualitycenter-spa naming. */
export const useAuthStore = authStore
