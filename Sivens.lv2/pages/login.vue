<template>
  <v-container class="fill-height d-flex align-center justify-center">
    <v-card class="pa-6" max-width="400">
      <v-card-title>
        <span class="headline">Login</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" v-model="valid" @submit.prevent="submitForm">
          <v-text-field
            v-model="email"
            label="Email"
            :rules="emailRules"
            required
          ></v-text-field>
          <v-text-field
            v-model="password"
            label="Password"
            :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
            :type="showPassword ? 'text' : 'password'"
            :rules="passwordRules"
            required
            @click:append="showPassword = !showPassword"
          ></v-text-field>
          <v-btn type="submit" color="primary" :disabled="!valid" class="mr-4">
            Login
          </v-btn>
          <v-btn type="reset" @click="clearForm">Clear</v-btn>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="goToRegister">
          Don't have an account? Register
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      valid: false,
      email: '',
      password: '',
      showPassword: false,
      emailRules: [
        (v) => !!v || 'Email is required',
        (v) => /.+@.+\..+/.test(v) || 'E-mail must be valid',
      ],
      passwordRules: [
        (v) => !!v || 'Password is required',
        (v) => v.length >= 6 || 'Password must be at least 6 characters',
      ],
    }
  },
  methods: {
    submitForm() {
      if (this.$refs.form.validate()) {
        const formData = {
          email: this.email,
          password: this.password,
        }

        import('axios')
          .then(({ default: axios }) => {
            axios
              .post('http://localhost:8000/api/login', formData)
              .then((response) => {
                alert('Login successful')
                this.$router.push('/UserProfile')
              })
              .catch((error) => {
                alert('Error logging in: ' + error.message)
              })
          })
          .catch((error) => {
            alert('Error loading Axios: ' + error.message)
          })
      }
    },
    clearForm() {
      this.$refs.form.reset()
    },
    goToRegister() {
      // Redirect to registration page
      this.$router.push('/register')
    },
  },
}
</script>

<style scoped>
.fill-height {
  height: 100vh;
}
</style>
