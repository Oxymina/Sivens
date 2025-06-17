export const state = () => ({
  token: null,
  user: null,
})

export const getters = {
  isAuthenticated: (state) => !!state.token,
  getToken: (state) => state.token,
  getUser: (state) => state.user,
  isAdmin: (state) => state.user && state.user.role_name === 'admin',
  isWriter: (state) =>
    state.user &&
    (state.user.role_name === 'writer' || state.user.role_name === 'admin'),
  isReader: (state) => state.user && state.user.role_name === 'reader',
}

export const mutations = {
  SET_TOKEN(state, token) {
    state.token = token
    if (process.client) {
      if (token) {
        localStorage.setItem('token', token)
      } else {
        localStorage.removeItem('token')
      }
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
    }
  },
}

export const actions = {
  async login({ commit, dispatch }, payload) {
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
      const user = response.data.user

      if (!token) {
        throw new Error('Token not received from login server response.')
      }
      if (!user) {
        throw new Error('User data not received from login server response.')
      }

      commit('SET_TOKEN', token)
      commit('SET_USER', user)

      if (process.client) {
        $axios.setToken(token, 'Bearer')
        console.log(
          '[Vuex Action] Login successful, token set on $axios for session.'
        )
      }

      return Promise.resolve(response.data)
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

  async fetchUser({ commit, state }, payload) {
    const { $axios } = payload
    if (!$axios) {
      console.error('FetchUser Action: $axios instance not provided.')
      return Promise.reject(
        new Error('$axios not available in fetchUser action.')
      )
    }
    const tokenInState = state.token
    if (!tokenInState) {
      console.warn(
        'FetchUser Action: No token in Vuex state. User cannot be fetched.'
      )
      return Promise.reject(
        new Error('No token available in state for fetchUser action.')
      )
    }

    try {
      const response = await $axios.get('/users')
      commit('SET_USER', response.data)
      console.log('[Vuex Action] User data fetched successfully.')
      return Promise.resolve(response.data)
    } catch (error) {
      console.error(
        'fetchUser action failed:',
        error.response?.data?.message || error.message,
        error
      )
      if (error.response && error.response.status === 401) {
        commit('CLEAR_AUTH')
        if (process.client && $axios) {
          $axios.setToken(false)
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
    return Promise.resolve()
  },
}
