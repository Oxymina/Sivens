// middleware/guest.js
export default function ({ store, redirect, route }) {
  // If the user IS authenticated
  if (store.getters['auth/isAuthenticated']) {
    console.log(
      `[Guest Middleware] User authenticated. Current route: ${route.path}. Redirecting to /UserProfile.`
    )
    return redirect('/UserProfile') // Redirect them away from guest pages
  }
  console.log(
    `[Guest Middleware] User not authenticated. Allowing access to: ${route.path}.`
  )
}
