// plugins/auth-init.js
export default async ({ store, app }) => {
  // This plugin runs once on the client when the app initializes.
  // 'app' is the Nuxt context which includes the $axios instance.

  if (process.client) {
    // Ensure this only runs client-side where localStorage exists
    console.log('[Auth Init Plugin] Running on client...')
    const token = localStorage.getItem('token')

    if (token) {
      console.log(
        '[Auth Init Plugin] Token found in localStorage. Applying to $axios and Vuex state...'
      )

      // Set the token for the Nuxt $axios instance.
      app.$axios.setToken(token, 'Bearer')
      console.log('[Auth Init Plugin] $axios token set.')

      // Commit the token to the Vuex store so the state is hydrated correctly.
      store.commit('auth/SET_TOKEN', token)

      try {
        await store.dispatch('auth/fetchUser', { $axios: app.$axios }) // Pass $axios
        console.log(
          '[Auth Init Plugin] User data fetched and Vuex state updated.'
        )
      } catch (error) {
        // This can happen if token is expired or invalid
        console.error(
          '[Auth Init Plugin] Error fetching user on init:',
          error.message ? error.message : error
        )
        // Token is invalid, clear everything
        store.dispatch('auth/logout', { $axios: app.$axios }) // Dispatch logout to ensure clean state
      }
    } else {
      console.log('[Auth Init Plugin] No token found in localStorage.')
      // Ensure axios doesn't accidentally use an old token if one somehow existed
      app.$axios.setToken(false)
      // Ensure Vuex state is clear
      store.commit('auth/CLEAR_AUTH')
    }
  }
}
