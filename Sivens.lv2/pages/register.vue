<template>
  <v-container class="fill-height d-flex align-center justify-center">
    <v-card class="pa-6" max-width="400">
      <v-card-title>
        <span class="headline">Register</span>
      </v-card-title>
      <v-card-text>
        <!-- General Error Display (not field specific) -->
        <v-alert
          v-if="formError"
          type="error"
          dense
          text
          dismissible
          class="mb-4"
        >
          {{ formError }}
        </v-alert>

        <v-form ref="form" v-model="valid" @submit.prevent="submitRegistration">
          <v-text-field
            v-model="formData.name"
            label="Name"
            :rules="rules.name"
            required
            :error-messages="fieldErrors.name"
            :disabled="loading"
            @input="clearFieldError('name')"
          ></v-text-field>
          <v-text-field
            v-model="formData.email"
            label="Email"
            :rules="rules.email"
            type="email"
            required
            :error-messages="fieldErrors.email"
            :disabled="loading"
            @input="clearFieldError('email')"
          ></v-text-field>
          <v-text-field
            v-model="formData.password"
            label="Password"
            :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
            :type="showPassword ? 'text' : 'password'"
            :rules="rules.password"
            required
            :error-messages="fieldErrors.password"
            :disabled="loading"
            @input="clearFieldError('password')"
            @click:append="showPassword = !showPassword"
          ></v-text-field>
          <v-text-field
            v-model="formData.password_confirmation"
            label="Confirm Password"
            :type="showPassword ? 'text' : 'password'"
            :rules="rules.password_confirmation"
            required
            :error-messages="fieldErrors.password_confirmation"
            :disabled="loading"
            @input="clearFieldError('password_confirmation')"
          ></v-text-field>

          <v-btn
            type="submit"
            color="primary"
            :disabled="!valid || loading"
            :loading="loading"
            class="mr-4 mt-4"
          >
            Register
          </v-btn>
          <!-- <v-btn type="reset" :disabled="loading" @click="clearForm"
            >Clear</v-btn
          > -->
        </v-form>
      </v-card-text>
      <v-card-text class="mt-n6">
        By registering you agree to our
        <router-link
          :to="{ path: '/terms', hash: 'terms' }"
          class="text-decoration-none primary--text font-weight-medium"
        >
          Terms and Conditions </router-link
        >.
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn class="mr-4" text :disabled="loading" @click="goToLogin">
          Already have an account? Login
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script>
// No need to mapActions if we're calling axios directly or through a service
// If you have a specific 'register' Vuex action, map that instead.

export default {
  middleware: 'guest', // Redirect if already logged in
  data() {
    return {
      valid: false,
      showPassword: false,
      loading: false,
      formError: null, // For general form errors
      fieldErrors: {}, // For individual field errors: { email: ['Error1'], password: ['Error1'] }
      formData: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      },
      rules: {
        // Basic client-side rules, server-side validation is key
        name: [(v) => !!v || 'Name is required'],
        email: [
          (v) => !!v || 'E-mail is required',
          (v) => /.+@.+\..+/.test(v) || 'E-mail must be valid',
        ],
        password: [
          (v) => !!v || 'Password is required',
          (v) =>
            (v && v.length >= 8) || 'Password must be at least 8 characters',
        ],
        password_confirmation: [
          (v) => !!v || 'Password confirmation is required',
          (v) => v === this.formData.password || 'Passwords do not match',
        ],
      },
    }
  },
  methods: {
    async submitRegistration() {
      this.formError = null
      this.fieldErrors = {} // Clear previous field errors
      if (this.$refs.form.validate()) {
        // Validate client-side rules
        this.loading = true
        try {
          // Use this.$axios (from @nuxtjs/axios)
          const response = await this.$axios.post('/register', this.formData)

          // Assuming successful registration logs user in and returns token + user data
          // If you have a Vuex 'login' or 'setUserAndToken' action:
          if (response.data && response.data.token && response.data.user) {
            this.$store.commit('auth/SET_TOKEN', response.data.token)
            this.$store.commit('auth/SET_USER', response.data.user)
            if (process.client) {
              this.$axios.setToken(response.data.token, 'Bearer')
            }
            this.$router.push('/UserProfile') // Or dashboard
          } else {
            // Handle unexpected success response
            this.formError =
              'Registration succeeded but received unexpected data.'
          }
        } catch (error) {
          if (error.response && error.response.status === 422) {
            // Handle Validation Errors
            if (error.response.data && error.response.data.errors) {
              // Vue.set is needed to make fieldErrors reactive for new keys
              // Or initialize fieldErrors with all possible keys if preferred
              this.fieldErrors = { ...error.response.data.errors } // Spread into a new object for reactivity
              this.formError = 'Please correct the errors below.'
            } else {
              this.formError =
                'Validation failed, but no specific errors were provided.'
            }
          } else if (error.response) {
            // Handle other API errors (e.g., 500, 401 if something went wrong with default role)
            this.formError =
              error.response.data.message ||
              'An error occurred during registration.'
          } else {
            // Handle network errors or other issues
            this.formError =
              'Registration failed. Please check your connection and try again.'
            console.error('Registration error:', error)
          }
        } finally {
          this.loading = false
        }
      }
    },
    clearFieldError(fieldName) {
      // Clear individual field error when user types
      if (this.fieldErrors[fieldName]) {
        // Vue.delete(this.fieldErrors, fieldName) also works if you need to remove the key
        this.fieldErrors = { ...this.fieldErrors, [fieldName]: undefined }
      }
    },
    // clearForm() {
    //   this.formError = null
    //   this.fieldErrors = {}
    //   this.$refs.form.reset() // Reset Vuetify form validation state too
    // },
    goToLogin() {
      this.$router.push('/login')
    },
  },
  head() {
    return {
      title: 'Register',
    }
  },
}
</script>

<style scoped>
.fill-height {
  min-height: 80vh;
}
</style>
