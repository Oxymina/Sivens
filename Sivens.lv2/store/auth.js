// store/auth.js

export const state = () => ({
  token: null,
  user: null,
})

export const getters = {
  isAuthenticated: (state) => !!state.token,
  getToken: (state) => state.token,
  getUser: (state) => state.user,
}

export const mutations = {
  SET_TOKEN(state, token) {
    state.token = token
    if (process.client && token) {
      localStorage.setItem('token', token)
      // @nuxtjs/axios will handle setting the default Authorization header
      // if configured correctly in nuxt.config.js or via its setToken method.
      // Avoid setting axios.defaults.headers.common directly here if using the module.
    } else if (process.client) {
      localStorage.removeItem('token')
      // @nuxtjs/axios needs to clear its header too, usually by calling
      // this.$axios.setToken(false) in the logout action or a plugin.
    }
  },

  SET_USER(state, user) {
    state.user = user
  },

  CLEAR_AUTH(state) {
    state.token = null
    state.user = null
    if (process.client) {
      localStorage.removeItem('token')
      // Also inform @nuxtjs/axios to clear its token header.
      // This is typically done in the logout action by calling this.$axios.setToken(false)
      // where 'this.$axios' refers to the instance accessible from the component/plugin context.
    }
  },
}

export const actions = {
  // Action called upon successful login
  // Expects payload: { $axios: nuxtAxiosInstance, credentials: { email, password } }
  async login({ commit, dispatch }, payload) {
    // Destructure $axios and credentials from payload
    const { $axios, credentials } = payload
    if (!$axios) {
      console.error('Login Action: $axios instance not provided in payload.')
      return Promise.reject(new Error('$axios not available in login action.'))
    }

    try {
      // Use the passed $axios instance
      const response = await $axios.post('/login', credentials) // Endpoint relative to baseURL
      const token = response.data.token

      if (!token) {
        throw new Error('Token not received from server')
      }

      commit('SET_TOKEN', token)
      // Now that @nuxtjs/axios knows the token (if set via module methods/interceptors),
      // it should use it for subsequent requests like fetchUser.
      // Or pass $axios along to ensure it's the correctly configured instance.
      // await dispatch('fetchUser', { $axios }) // Pass $axios to fetchUser

      // return Promise.resolve(response.data)
    } catch (error) {
      commit('CLEAR_AUTH')
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

  // Action to fetch user data
  // Expects payload: { $axios: nuxtAxiosInstance }
  async fetchUser({ commit, state }, payload) {
    const { $axios } = payload
    if (!$axios) {
      console.error(
        'FetchUser Action: $axios instance not provided in payload.'
      )
      return Promise.reject(
        new Error('$axios not available in fetchUser action.')
      )
    }
    if (!state.token) {
      return Promise.reject(
        new Error('No token available for fetchUser action.')
      )
    }

    try {
      // @nuxtjs/axios should automatically use the token if set via $axios.setToken()
      // or via default headers after SET_TOKEN mutation (if module is configured to watch the store).
      // Or, you can explicitly pass it, but usually not needed if module handles it.
      const response = await $axios.get('/users') // Endpoint relative to baseURL
      commit('SET_USER', response.data)
      return Promise.resolve(response.data)
    } catch (error) {
      if (error.response && error.response.status === 401) {
        commit('CLEAR_AUTH')
      }
      console.error(
        'fetchUser action failed:',
        error.response?.data?.message || error.message,
        error
      )
      return Promise.reject(
        new Error(
          error.response?.data?.message || error.message || 'User fetch failed'
        )
      )
    }
  },

  // Action to log out
  // Expects payload: { $axios: nuxtAxiosInstance } from where it's dispatched
  logout({ commit }, payload) {
    const { $axios } = payload
    if (!$axios) {
      console.warn(
        'Logout Action: $axios instance not provided in payload. Header might not be cleared automatically by module.'
      )
      // Still proceed with client-side logout.
    }

    console.log('Client-side logout initiated.')
    commit('CLEAR_AUTH')

    // Important: Tell @nuxtjs/axios to clear its token
    // This needs the $axios instance from the context where logout is dispatched
    if (process.client && $axios) {
      $axios.setToken(false) // This removes the 'Authorization' header from future requests by this $axios instance
    }

    // ---- Optional Backend Call Section ----
    // if ($axios && state.token) { // Would need to get token from state if backend needs it for invalidation
    //     try {
    //         await $axios.post('/logout'); // No token in header if cleared above, backend needs other means
    //         console.log("Backend logout API called.");
    //     } catch (error) {
    //         console.error("Backend logout API call failed:", error);
    //     }
    // }
    // --------------------------------------
    return Promise.resolve()
  },

  // Action called by plugin on app load to initialize from localStorage
  // Does not typically require $axios unless you immediately fetchUser
  initAuth({ commit, dispatch }, payload) {
    // 'payload' can be empty or contain $axios if needed
    if (process.client) {
      const token = localStorage.getItem('token')
      if (token) {
        console.log('initAuth: Token found in localStorage, setting in store.')
        commit('SET_TOKEN', token)
        // If @nuxtjs/axios is configured, setting the token in the store (and via $axios.setToken())
        // should make it available for subsequent requests.
        // Example: Immediately fetch user if token exists and $axios is passed by plugin
        // if (payload && payload.$axios) {
        //    dispatch('fetchUser', { $axios: payload.$axios });
        // }
      } else {
        console.log('initAuth: No token found in localStorage.')
        commit('CLEAR_AUTH')
      }
    }
  },
}
