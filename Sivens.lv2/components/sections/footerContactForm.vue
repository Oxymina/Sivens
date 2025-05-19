<template>
  <v-form ref="contactForm" v-model="valid" @submit.prevent="submitForm">
    <v-text-field
      v-model="formData.name"
      label="Name"
      :rules="[rules.required, rules.maxLength(100)]"
      counter="100"
      dense
      outlined
      required
      class="mb-4"
      :error-messages="formErrors.name"
      :disabled="submitting"
      @input="clearError('name')"
    ></v-text-field>
    <v-text-field
      v-model="formData.email"
      label="Email"
      type="email"
      :rules="[rules.required, rules.email, rules.maxLength(150)]"
      counter="150"
      dense
      outlined
      required
      class="mb-4"
      :error-messages="formErrors.email"
      :disabled="submitting"
      @input="clearError('email')"
    ></v-text-field>
    <v-text-field
      v-model="formData.subject"
      label="Subject"
      :rules="[rules.required, rules.maxLength(200)]"
      counter="200"
      dense
      outlined
      required
      class="mb-4"
      :error-messages="formErrors.subject"
      :disabled="submitting"
      @input="clearError('subject')"
    ></v-text-field>
    <v-textarea
      v-model="formData.message"
      dense
      label="Your Message"
      :rules="[rules.required, rules.maxLength(2000)]"
      counter="2000"
      auto-grow
      outlined
      rows="6"
      row-height="20"
      required
      class="mb-4"
      :error-messages="formErrors.message"
      :disabled="submitting"
      @input="clearError('message')"
    ></v-textarea>
    <v-btn
      type="submit"
      block
      color="primary"
      large
      depressed
      :loading="submitting"
      :disabled="!valid || submitting"
    >
      <v-icon left>mdi-email-send-outline</v-icon>
      SEND MESSAGE
    </v-btn>

    <v-alert
      v-if="submitStatus.message"
      :type="submitStatus.type"
      dense
      text
      class="mt-4"
    >
      {{ submitStatus.message }}
    </v-alert>
  </v-form>
</template>

<script>
export default {
  name: 'FooterContactForm',
  data() {
    return {
      valid: false, // Form validation state
      submitting: false, // For loading state of the button
      formData: {
        name: '',
        email: '',
        subject: '',
        message: '', // Changed from message_content to align with common practice
      },
      formErrors: {}, // For field-specific backend errors e.g. { email: ['The email is invalid.'] }
      submitStatus: {
        // For success/general error messages
        message: '',
        type: '', // 'success' or 'error'
      },
      rules: {
        required: (v) => !!v || 'This field is required.',
        email: (v) => /.+@.+\..+/.test(v) || 'E-mail must be valid.',
        maxLength: (len) => (v) =>
          (v && v.length <= len) || `Max ${len} characters allowed.`,
      },
    }
  },
  methods: {
    async submitForm() {
      this.formErrors = {} // Clear previous field errors
      this.submitStatus = { message: '', type: '' } // Clear previous general status

      if (this.$refs.contactForm.validate()) {
        this.submitting = true
        try {
          // Using this.$axios injected by Nuxt
          // Assuming your API endpoint for messages is POST /api/messages
          await this.$axios.post('/messages', this.formData)

          this.submitStatus = {
            message: 'Message sent successfully! We will get back to you soon.',
            type: 'success',
          }
          this.$refs.contactForm.reset() // Reset form fields
          // this.formData = { name: '', email: '', subject: '', message: '' }; // Manual reset if needed
        } catch (error) {
          console.error(
            'Error submitting contact form:',
            error.response || error
          )
          if (
            error.response &&
            error.response.status === 422 &&
            error.response.data.errors
          ) {
            // Handle Laravel validation errors
            this.formErrors = error.response.data.errors
            this.submitStatus = {
              message: 'Please correct the errors above.',
              type: 'warning',
            }
          } else if (error.response && error.response.data.message) {
            this.submitStatus = {
              message: `Error: ${error.response.data.message}`,
              type: 'error',
            }
          } else {
            this.submitStatus = {
              message: 'An unexpected error occurred. Please try again.',
              type: 'error',
            }
          }
        } finally {
          this.submitting = false
        }
      } else {
        this.submitStatus = {
          message: 'Please fill out all required fields correctly.',
          type: 'warning',
        }
      }
    },
    clearError(fieldName) {
      // Clear individual field error when user types
      if (this.formErrors[fieldName]) {
        this.$set(this.formErrors, fieldName, undefined) // Ensure reactivity for Vuetify
      }
      // Also clear general form error if user starts typing
      if (this.submitStatus.message && this.submitStatus.type !== 'success') {
        this.submitStatus = { message: '', type: '' }
      }
    },
  },
}
</script>

<style scoped>
/* Add any specific styling if desired */
.v-textarea ::v-deep textarea {
  line-height: 1.5; /* Ensure good line height for textarea */
}
</style>
