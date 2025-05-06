<template>
  <v-container class="fill-height d-flex align-center justify-center">
    <v-card class="pa-6" max-width="400">
      <v-card-title>
        <span class="headline">Register</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" v-model="valid" @submit.prevent="submitForm">
          <v-text-field
            v-model="name"
            label="Name"
            :rules="nameRules"
            required
          ></v-text-field>
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
            Register
          </v-btn>
          <v-btn type="reset" @click="clearForm">Clear</v-btn>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="goToLogin">Already have an account? Login</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      valid: false,
      name: '',
      email: '',
      password: '',
      showPassword: false,
      nameRules: [(v) => !!v || 'Name is required'],
      emailRules: [
        (v) => !!v || 'Email is required',
        (v) => /.+@.+\..+/.test(v) || 'E-mail must be valid',
      ],
      passwordRules: [
        (v) => !!v || 'Password is required',
        (v) => v.length >= 8 || 'Password must be at least 8 characters',
        (v) =>
          /[A-Z]/.test(v) ||
          'Password must contain at least one uppercase letter',
        (v) =>
          /[a-z]/.test(v) ||
          'Password must contain at least one lowercase letter',
        (v) => /\d/.test(v) || 'Password must contain at least one number',
        (v) =>
          /[!@#$%^&*]/.test(v) ||
          'Password must contain at least one special character',
      ],
    }
  },
  methods: {
    async submitForm() {
      if (this.$refs.form.validate()) {
        const formData = {
          name: this.name,
          email: this.email,
          password: this.password,
        }

        try {
          // Post registration data to the API
          await this.$axios.post('http://localhost:8000/api/register', formData) // Use this.$axios instead of axios
          alert('Registration successful')
          // Redirect to login page
          this.$router.push('/login')
        } catch (error) {
          if (error.response && error.response.status === 422) {
            const errors = error.response.data // validation errors from backend
            let errorMessages = ''
            for (const field in errors) {
              errorMessages += `${errors[field][0]} \n` // collect error messages
            }
            alert('Error registering: \n' + errorMessages) // show all validation errors
          } else {
            alert(
              'Error registering: ' + (error.message || 'An error occurred')
            )
          }
        }
      }
    },
    clearForm() {
      this.$refs.form.reset()
    },
    goToLogin() {
      this.$router.push('/login')
    },
  },
}
</script>

<style scoped>
.submenubtn {
  cursor: default;
}
</style>
