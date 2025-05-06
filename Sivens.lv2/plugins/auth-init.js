/* eslint-disable no-console */
// plugins/auth-init.js
export default ({ store }) => {
  // This code runs ONLY on the client-side after the app initializes
  console.log('Auth init plugin running...')
  store.dispatch('auth/initAuth')
}
