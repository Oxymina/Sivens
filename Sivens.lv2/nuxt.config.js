import colors from 'vuetify/es5/util/colors'

export default {
  // Target (https://go.nuxtjs.dev/config-target)
  target: 'static',

  // Global page headers (https://go.nuxtjs.dev/config-head)
  head: {
    titleTemplate: '%s - Sivēns.lv',
    title: 'Home',
    htmlAttrs: {
      lang: 'en',
    },
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      {
        hid: 'description',
        name: 'description',
        content:
          'Sivēns.lv - neēd kā cūka, ēd kā cilvēks un uzzini kur var ēst!',
      },
    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/favicon/favicon.ico' },
      { rel: 'manifest', href: '/site.webmanifest' },
    ],
  },

  // Global CSS (https://go.nuxtjs.dev/config-css)
  css: [],

  // Plugins to run before rendering page (https://go.nuxtjs.dev/config-plugins)
  plugins: [{ src: '~/plugins/auth-init.js', mode: 'client' }],

  // Auto import components (https://go.nuxtjs.dev/config-components)
  // components: true,
  components: [
    '~/components', // shortcut to { path: '~/components' }
    { path: '~/components/sections/', prefix: 'sections' },
  ],

  // Modules for dev and build (recommended) (https://go.nuxtjs.dev/config-modules)
  buildModules: [
    // https://go.nuxtjs.dev/eslint
    '@nuxtjs/eslint-module',
    // https://go.nuxtjs.dev/vuetify
    '@nuxtjs/vuetify',
  ],

  // Modules (https://go.nuxtjs.dev/config-modules)
  modules: ['@nuxtjs/axios'],

  axios: {
    baseURL: 'http://localhost:8000/api',
  },

  // Vuetify module configuration (https://go.nuxtjs.dev/config-vuetify)
  vuetify: {
    customVariables: ['~/assets/variables.scss'],
    theme: {
      dark: false,
      themes: {
        dark: {
          primary: colors.blue.darken2,
          accent: colors.grey.darken3,
          secondary: colors.amber.darken3,
          info: colors.teal.lighten1,
          warning: colors.amber.base,
          error: colors.deepOrange.accent4,
          success: colors.green.accent3,
        },
        light: {
          primary: colors.blue.darken2,
          accent: colors.grey.darken3,
          secondary: colors.amber.darken3,
          info: colors.teal.lighten1,
          warning: colors.amber.base,
          error: colors.deepOrange.accent4,
          success: colors.green.accent3,
        },
      },
    },
  },

  // Build Configuration (https://go.nuxtjs.dev/config-build)

  build: {
    transpile: ['uuid'],
    babel: {
      presets({ isServer }, [preset, options]) {
        // Keep 'isServer'
        // --- USE isServer ---
        // Example: Different targets
        if (isServer) {
          options.targets = { node: '14' } // Target for Node.js environment
        } else {
          options.targets = { ie: '11' } // Target for browser environment
        }
        // Example: Different options (less common)
        // options.someOtherOption = isServer ? 'serverValue' : 'clientValue';

        // --- End USE isServer ---
        return [[preset, options]]
      },
      plugins: [
        // Required for the ?. optional chaining operator
        '@babel/plugin-proposal-optional-chaining',
        // Required for the ?? and ??= nullish coalescing operators
        '@babel/plugin-proposal-nullish-coalescing-operator',
        // You might have other Babel plugins here already
      ],
      // Optional: You might need to configure presets if you have a complex custom Babel setup
      // but often just adding the plugins is enough for node_modules issues.
      // presets(env, [preset, options]) {
      //    // modify presets...
      // }
    },
    extend(config, ctx) {
      config.module.rules.push({
        enforce: 'pre',
        test: /\.(js|vue)$/,
        loader: 'eslint-loader',
        exclude: /(node_modules)/,
        options: {
          fix: true,
        },
      })
    },
  },
}
