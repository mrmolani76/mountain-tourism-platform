import toastify, { toast, updateGlobalOptions } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const globalConfig = {
  autoClose: 2000,
  dangerouslyHTMLString: true,
  theme: 'light',
  transition: 'flip',
  position: toast.POSITION.TOP_CENTER,
  pauseOnFocusLoss: false,
}

export default defineNuxtPlugin(nuxtApp => {
  nuxtApp.vueApp.use(toastify, globalConfig)

  const toggleToastTheme = theme => {
    globalConfig['theme'] = theme
    updateGlobalOptions(globalConfig)
  }

  return {
    provide: {
      toast,
      toggleToastTheme,
    },
  }
})
