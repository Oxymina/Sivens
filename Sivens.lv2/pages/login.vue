<template>
  <v-container class="fill-height d-flex align-center justify-center">
    <v-card class="pa-6" max-width="400">
      <v-card-title>
        <span class="headline">Login</span>
      </v-card-title>
      <v-card-text>
        <!-- Display error messages from Vuex action -->
        <v-alert v-if="error" type="error" dense text dismissible>
          {{ error }}
        </v-alert>

        <v-form ref="form" v-model="valid" @submit.prevent="submitForm">
          <v-text-field
            v-model="email"
            label="Email"
            :rules="emailRules"
            required
            autocomplete="email"
            :disabled="loading"
          ></v-text-field>
          <v-text-field
            v-model="password"
            label="Password"
            :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
            :type="showPassword ? 'text' : 'password'"
            :rules="passwordRules"
            required
            autocomplete="current-password"
            :disabled="loading"
            @click:append="showPassword = !showPassword"
          ></v-text-field>
          <v-btn
            type="submit"
            color="primary"
            :disabled="!valid || loading"
            :loading="loading"
            class="mr-4"
          >
            Login
          </v-btn>
          <v-btn type="reset" :disabled="loading" @click="clearForm"
            >Clear</v-btn
          >
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text :disabled="loading" @click="goToRegister">
          Don't have an account? Register
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script>
import { mapActions } from 'vuex'

export default {
  middleware: 'guest',
  data() {
    return {
      valid: false,
      email: '',
      password: '',
      showPassword: false,
      loading: false, // Add loading state
      error: null, // Add error state
      emailRules: [
        (v) => !!v || 'Email is required',
        (v) => /.+@.+\..+/.test(v) || 'E-mail must be valid',
      ],
      passwordRules: [
        (v) => !!v || 'Password is required',
        (v) => (v && v.length >= 6) || 'Password must be at least 6 characters',
      ],
    }
  },
  methods: {
    ...mapActions('auth', ['login']), // Map the login action

    async submitForm() {
      this.error = null
      if (this.$refs.form.validate()) {
        this.loading = true
        const credentials = {
          email: this.email,
          password: this.password,
        }

        try {
          // --- CORRECTED DISPATCH ---
          // Pass an object that matches the expected payload structure in your action
          await this.login({
            $axios: this.$axios, // Pass the Nuxt-injected $axios instance
            credentials,
          })
          // --- END CORRECTION ---

          console.log('Login action dispatched successfully') // Changed from alert
          this.$router.push('/UserProfile')
        } catch (error) {
          this.error =
            error.message || // Get message from actual Error object now
            'Login failed. Please check your credentials.'
          console.error('Login component caught error:', this.error, error) // Log full error object too
        } finally {
          this.loading = false
        }
      }
    },
    clearForm() {
      this.error = null
      this.$refs.form.reset()
    },
    goToRegister() {
      this.$router.push('/register')
    },
  },
}
</script>

<style scoped>
.fill-height {
  min-height: 80vh;
}
</style>
