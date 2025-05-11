<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12" md="8">
        <h1 class="text-h4 my-8 text-center">🚧 Temporary Testing Links 🚧</h1>
        <p class="text-center mb-8">ATCERIES SHITO IZDZEST PLS</p>

        <v-card outlined class="mb-6">
          <v-card-text class="text-center">
            <v-btn
              color="warning"
              depressed
              :loading="loginLoading"
              @click="quickLogin"
            >
              <span>Login as John</span>
            </v-btn>
            <v-alert
              v-if="loginError"
              type="error"
              dense
              text
              class="mt-3 text-left"
            >
              {{ loginError }}
            </v-alert>
          </v-card-text>
        </v-card>

        <v-card outlined>
          <v-card-title>Quick Links</v-card-title>
          <v-list dense>
            <v-list-item to="/blogeditortest">
              <!-- Example -->
              <v-list-item-icon
                ><v-icon>mdi-pencil-box-outline</v-icon></v-list-item-icon
              >
              <v-list-item-content
                ><v-list-item-title
                  >Block Editor Test Page</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>

            <v-list-item to="/UserProfile">
              <!-- Example -->
              <v-list-item-icon><v-icon>mdi-account</v-icon></v-list-item-icon>
              <v-list-item-content
                ><v-list-item-title
                  >User Profile Page</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>

            <v-list-item to="/postpost">
              <!-- Example -->
              <v-list-item-icon><v-icon>mdi-account</v-icon></v-list-item-icon>
              <v-list-item-content
                ><v-list-item-title
                  >old post creation page</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>

            <v-list-item to="/blogeditortest">
              <!-- Example -->
              <v-list-item-icon><v-icon>mdi-account</v-icon></v-list-item-icon>
              <v-list-item-content
                ><v-list-item-title
                  >new post creator</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>

            <v-list-item to="/admin">
              <!-- Example -->
              <v-list-item-icon><v-icon>mdi-account</v-icon></v-list-item-icon>
              <v-list-item-content
                ><v-list-item-title
                  >admin</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'TempTestingLinks',
  data() {
    return {
      loginLoading: false,
      loginError: null,
      quickLoginCredentials: {
        // Hardcoded credentials for the button
        email: 'john@john.john',
        password: 'John12345!',
      },
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated']), // Get Vuex auth state
  },
  methods: {
    // We can't directly use ...mapActions here easily with a different payload structure name
    // So we dispatch it manually
    async quickLogin() {
      if (this.isAuthenticated) {
        return
      }
      this.loginLoading = true
      this.loginError = null
      try {
        await this.$store.dispatch('auth/login', {
          $axios: this.$axios, // Pass the Nuxt $axios instance
          credentials: this.quickLoginCredentials, // Pass the hardcoded credentials
        })
      } catch (error) {
        this.loginError = error.message || 'Quick login failed. Check console.'
        console.error('Quick Login Error:', error)
      } finally {
        this.loginLoading = false
      }
    },
  },
  head() {
    return {
      title: 'Dev Testing Links',
      meta: [{ hid: 'robots', name: 'robots', content: 'noindex, nofollow' }],
    }
  },
}
</script>

<style scoped>
/* Add any specific styles if needed */
</style>
