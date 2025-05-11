// middleware/writer-auth.js
export default function ({ store, redirect, route }) {
  if (!store.getters['auth/isAuthenticated']) {
    return redirect(`/login?redirect=${route.fullPath}`)
  }
  // Use the Vuex getter (remember Admin can also be writer)
  if (!store.getters['auth/isWriter']) {
    console.log('[Writer Auth] User is not a writer or admin. Redirecting.')
    return redirect('/') // Or to a "not authorized" page
  }
  console.log('[Writer Auth] Writer/Admin OK.')
}
