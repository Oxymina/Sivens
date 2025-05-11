// store/auth.js

export const state = () => ({
  token: null,
  // Expect user object like: { id: 1, name: 'John', email: '...', role_name: 'admin', /* other user fields */ }
  user: null,
})

export const getters = {
  isAuthenticated: (state) => !!state.token,
  getToken: (state) => state.token,
  getUser: (state) => state.user,

  // Role-based getters - using the 'role_name' appended attribute from Laravel User model
  isAdmin: (state) => state.user && state.user.role_name === 'admin',
  isWriter: (state) =>
    state.user &&
    (state.user.role_name === 'writer' || state.user.role_name === 'admin'), // Admins can also write
  isReader: (state) => state.user && state.user.role_name === 'reader',
}

export const mutations = {
  SET_TOKEN(state, token) {
    state.token = token
    if (process.client) {
      if (token) {
        localStorage.setItem('token', token)
        // console.log('[Vuex Mutation] Token set in localStorage and Vuex state.'); // Keep for debug if needed
      } else {
        localStorage.removeItem('token')
        // console.log('[Vuex Mutation] Token removed from localStorage and Vuex state.');
      }
    }
  },

  SET_USER(state, user) {
    state.user = user
    // console.log('[Vuex Mutation] User set in state:', user);
  },

  CLEAR_AUTH(state) {
    state.token = null
    state.user = null
    if (process.client) {
      localStorage.removeItem('token')
      // console.log('[Vuex Mutation] Auth cleared from localStorage and Vuex state.');
    }
  },
}

export const actions = {
  /**
   * Logs in the user.
   * Expects payload: { $axios: nuxtAxiosInstance, credentials: { email, password } }
   */
  async login({ commit, dispatch }, payload) {
    // Removed dispatch as it's not used within THIS action
    const { $axios, credentials } = payload
    if (!$axios) {
      console.error('Login Action: $axios instance not provided in payload.')
      return Promise.reject(new Error('$axios not available in login action.'))
    }

    try {
      const response = await $axios.post('/login', credentials)
      console.log(
        '[Auth Action - Login] Full API Response Data:',
        JSON.parse(JSON.stringify(response.data))
      )

      const token = response.data.token
      // THIS IS THE LINE WE ARE FOCUSING ON:
      const user = response.data.user // Your backend MUST return this for this line to work

      // Your previous error: "Token not received from login server response."
      // or more accurately "User data not received..." because your 'if (!user)' was triggering.

      if (!token) {
        // This error is thrown if `response.data.token` is undefined/null
        throw new Error('Token not received from login server response.')
      }
      if (!user) {
        // This error is thrown if `response.data.user` is undefined/null
        throw new Error('User data not received from login server response.')
      }

      commit('SET_TOKEN', token)
      commit('SET_USER', user) // Commit the user data received from login

      // Configure the $axios instance to use this token for subsequent requests
      if (process.client) {
        $axios.setToken(token, 'Bearer')
        console.log(
          '[Vuex Action] Login successful, token set on $axios for session.'
        )
      }
      // Since the login response now contains the user object,
      // dispatching 'fetchUser' immediately might be redundant.
      // If you want to be absolutely sure it's the freshest user data,
      // or if '/user' endpoint returns more/different details than the login response,
      // you can keep it, but it might not be necessary for basic auth flow.
      // await dispatch('fetchUser', { $axios }); // Pass $axios here too if called

      return Promise.resolve(response.data) // Return the full response (token + user)
    } catch (error) {
      commit('CLEAR_AUTH')
      if (process.client && $axios) {
        $axios.setToken(false)
      }
      console.error(
        'Login action failed:',
        error.response?.data?.message || error.message,
        error
      )
      return Promise.reject(
        new Error(
          error.response?.data?.message || error.message || 'Login failed'
        )
      )
    }
  },

  /**
   * Fetches the authenticated user's data.
   * Expects payload: { $axios: nuxtAxiosInstance }
   */
  async fetchUser({ commit, state }, payload) {
    const { $axios } = payload
    if (!$axios) {
      console.error('FetchUser Action: $axios instance not provided.')
      return Promise.reject(
        new Error('$axios not available in fetchUser action.')
      )
    }
    // Token check can happen before the API call
    const tokenInState = state.token // Get token from state directly
    if (!tokenInState) {
      console.warn(
        'FetchUser Action: No token in Vuex state. User cannot be fetched.'
      )
      // No need to make API call if no token is available in the first place.
      // It might also be good to clear auth just in case here if we reach this state unexpectedly.
      // commit('CLEAR_AUTH'); // Optionally clear if this state is unexpected
      return Promise.reject(
        new Error('No token available in state for fetchUser action.')
      )
    }

    try {
      // $axios should have token set by login action or auth-init plugin
      const response = await $axios.get('/users')
      commit('SET_USER', response.data) // Assuming /user returns the user object directly
      console.log('[Vuex Action] User data fetched successfully.')
      return Promise.resolve(response.data)
    } catch (error) {
      console.error(
        'fetchUser action failed:',
        error.response?.data?.message || error.message,
        error
      )
      if (error.response && error.response.status === 401) {
        // If 401, token is invalid/expired. Clear auth state.
        commit('CLEAR_AUTH')
        if (process.client && $axios) {
          $axios.setToken(false) // Also clear $axios token
        }
      }
      return Promise.reject(
        new Error(
          error.response?.data?.message ||
            error.message ||
            'Failed to fetch user data'
        )
      )
    }
  },

  /**
   * Logs out the user.
   * Expects payload: { $axios: nuxtAxiosInstance }
   */
  logout({ commit }, payload) {
    const { $axios } = payload

    commit('CLEAR_AUTH')

    if (process.client && $axios) {
      $axios.setToken(false)
      console.log('[Vuex Action] Logout: Token cleared from $axios.')
    } else if (process.client && !$axios) {
      console.warn(
        '[Vuex Action] Logout: $axios instance not provided for logout.'
      )
    }

    // Optional Backend API call for logout
    // if ($axios) {
    //   return $axios.post('/logout').catch(err => console.error("Backend logout call failed:", err));
    // }
    return Promise.resolve()
  },
}
