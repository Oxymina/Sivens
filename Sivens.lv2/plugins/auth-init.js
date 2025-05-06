// plugins/auth-init.js
export default ({ store, app }) => {
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

      // 1. Set the token for the Nuxt $axios instance.
      // This makes $axios automatically send the 'Authorization: Bearer <token>' header.
      app.$axios.setToken(token, 'Bearer')
      console.log('[Auth Init Plugin] $axios token set.')

      // 2. Commit the token to the Vuex store so the state is hydrated correctly.
      // It's okay that SET_TOKEN also accesses localStorage, but the main purpose here is Vuex state.
      store.commit('auth/SET_TOKEN', token)

      // 3. OPTIONAL: Fetch user data immediately if needed globally on app load
      // You only need this if components rely on 'getUser' being populated right away.
      // Often, components fetch their own data when needed.
      // If you dispatch this, make sure 'fetchUser' handles receiving $axios correctly.
      // store.dispatch('auth/fetchUser', { $axios: app.$axios });
    } else {
      console.log('[Auth Init Plugin] No token found in localStorage.')
      // Ensure axios doesn't accidentally use an old token if one somehow existed
      app.$axios.setToken(false)
      // Ensure Vuex state is clear (optional if default is null, but good practice)
      store.commit('auth/CLEAR_AUTH')
    }
  }
}
