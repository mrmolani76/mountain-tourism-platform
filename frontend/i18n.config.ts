export default {
  langDir: 'locales',
  defaultLocale: 'en',
  strategy: 'no_prefix',
  locales: [
    { code: 'en', name: 'english', file: 'en.json', isRTL: false },

    { code: 'ms', name: 'malay', file: 'ms.json', isRTL: false },

    // { code: 'fr', name: 'Français', file: 'fr.json', isRTL: false },

    // { code: 'ar', name: 'Arabic', file: 'ar.json', isRTL: true },
  ],
  lazy: true,
  bundle: {
    optimizeTranslationDirective: false,
  },
};
