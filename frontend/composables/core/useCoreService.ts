import type { VerticalNavItems } from '@/@layouts/types'
import { API } from '@/composables/core/useApi'

/**
 * Core domain service — eQMS qualitycenter-spa `composables/coreService.ts` pattern.
 * Menu comes from Laravel `GET /api/core/menus` (role-filtered, Core + Hotel only).
 */

interface MenuResponse {
  status?: boolean
  data?: {
    menu?: VerticalNavItems
    pinned_item_arr?: Array<string>
  }
}

export const useCoreService = () => {
  const getVerticalMenuData = async (): Promise<{
    menu: VerticalNavItems
    pinned_item_arr: Array<string>
  }> => {
    const response = await API.get<MenuResponse>('core/menus')

    if (response?.status && response.data?.menu) {
      return {
        menu: response.data.menu,
        pinned_item_arr: response.data.pinned_item_arr ?? [],
      }
    }

    // Fallback if menu API is unreachable: static Core + Hotel nav.
    return {
      menu: appNavigation,
      pinned_item_arr: [],
    }
  }

  return {
    getVerticalMenuData,
  }
}
