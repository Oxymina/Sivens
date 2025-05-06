// middleware/auth.js
export default function ({ store, redirect, route }) {
  // If the user is not authenticated and not already on the login page
  if (!store.getters['auth/isAuthenticated']) {
    console.log(
      `[Auth Middleware] User not authenticated. Current route: ${route.path}. Redirecting to /login.`
    )
    return redirect('/login') // Adjust the login path if needed
  }
  console.log(
    `[Auth Middleware] User authenticated. Allowing access to: ${route.path}.`
  )
}
