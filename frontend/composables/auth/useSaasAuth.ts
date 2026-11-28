import { API } from '@/composables/core/useApi'

interface SaasUser {
  id: number
  email: string
  is_active: boolean
}

export const useSaasAuth = () => {
  const user = useState<SaasUser | null>('saas-user', () => null)
  const authenticated = useState<boolean | undefined>('saas-authenticated', () => undefined)

  const fetchUser = async (): Promise<SaasUser | null> => {
    const response = await API.get<SaasUser>('saas/auth/me')

    user.value = response
    authenticated.value = Boolean(response?.is_active)

    return user.value
  }

  const login = async (email: string, password: string): Promise<SaasUser | null> => {
    const response = await API.post<SaasUser>('saas/auth/login', { email, password })

    user.value = response
    authenticated.value = Boolean(response?.is_active)

    return user.value
  }

  const logout = async (): Promise<void> => {
    try {
      await API.post('saas/auth/logout')
    }
    finally {
      user.value = null
      authenticated.value = false
    }
  }

  return {
    user,
    authenticated,
    isAuthenticated: computed(() => authenticated.value === true),
    fetchUser,
    login,
    logout,
  }
}
