// middleware/admin-auth.js
export default function ({ store, redirect, route }) {
  if (!store.getters['auth/isAuthenticated']) {
    return redirect(`/login?redirect=${route.fullPath}`)
  }
  // Use the new Vuex getter
  if (!store.getters['auth/isAdmin']) {
    console.log('[Admin Auth] User is not an admin. Redirecting.')
    return redirect('/')
  }
  console.log('[Admin Auth] Admin user OK.')
}
