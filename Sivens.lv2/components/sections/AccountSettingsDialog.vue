<template>
  <v-dialog
    :value="dialog"
    persistent
    max-width="650px"
    scrollable
    eager
    @input="closeDialog"
  >
    <v-card v-if="user">
      <v-card-title class="text-h5 primary white--text py-3">
        Account Settings
        <v-spacer></v-spacer>
        <v-btn icon dark title="Close dialog" @click="closeDialog">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-title>

      <v-form
        ref="settingsForm"
        v-model="settingsFormValid"
        @submit.prevent="saveAllSettings_Combined"
      >
        <!-- Optional: Combined save -->
        <v-tabs v-model="activeTab" background-color="transparent" grow>
          <v-tab>Profile Details</v-tab>
          <v-tab>Change Password</v-tab>
        </v-tabs>

        <v-tabs-items v-model="activeTab">
          <v-tab-item>
            <v-card-text class="pt-6 pb-2">
              <h3 class="text-subtitle-1 font-weight-medium mb-1">
                Edit Your Profile
              </h3>
              <v-divider class="mb-5"></v-divider>
              <v-text-field
                v-model="form.name"
                label="Full Name"
                outlined
                dense
                :rules="[rules.required, rules.maxLength(255)]"
                prepend-inner-icon="mdi-account"
                required
                counter="255"
                class="mb-3"
                :disabled="savingDetails"
                :error-messages="fieldErrors.name"
                @input="clearFieldError('name')"
              ></v-text-field>

              <v-text-field
                v-model="form.email"
                label="Email Address"
                type="email"
                outlined
                dense
                :rules="[rules.required, rules.emailFormat]"
                prepend-inner-icon="mdi-email"
                required
                :disabled="savingDetails"
                :error-messages="fieldErrors.email"
                @input="clearFieldError('email')"
              ></v-text-field>
              <div class="text-right mt-1 mb-3">
                <v-btn
                  small
                  color="primary"
                  depressed
                  :loading="savingDetails"
                  :disabled="
                    !isDetailsChanged || savingDetails || !detailsFormValid
                  "
                  @click="saveProfileDetails"
                >
                  <v-icon left small>mdi-content-save-outline</v-icon>
                  Save Details
                </v-btn>
              </div>
            </v-card-text>
          </v-tab-item>

          <v-tab-item>
            <v-card-text class="pt-6 pb-2">
              <h3 class="text-subtitle-1 font-weight-medium mb-1">
                Change Your Password
              </h3>
              <v-divider class="mb-5"></v-divider>

              <v-alert
                v-if="!supportsPasswordChange"
                type="info"
                text
                dense
                class="mb-3"
              >
                Password changes might be handled by your identity provider or a
                separate "Forgot Password" flow.
              </v-alert>

              <template v-if="supportsPasswordChange">
                <v-text-field
                  v-model="form.current_password"
                  label="Current Password"
                  :append-icon="showCurrentPass ? 'mdi-eye' : 'mdi-eye-off'"
                  :type="showCurrentPass ? 'text' : 'password'"
                  outlined
                  dense
                  prepend-inner-icon="mdi-lock-outline"
                  :rules="
                    passwordChangeAttempted
                      ? [rules.requiredPassword('Current password')]
                      : []
                  "
                  class="mb-3"
                  autocomplete="current-password"
                  hint="Required to set a new password"
                  persistent-hint
                  :disabled="savingPassword"
                  :error-messages="fieldErrors.current_password"
                  @click:append="showCurrentPass = !showCurrentPass"
                  @input="clearFieldError('current_password')"
                ></v-text-field>
                <v-text-field
                  v-model="form.new_password"
                  label="New Password"
                  :append-icon="showNewPass ? 'mdi-eye' : 'mdi-eye-off'"
                  :type="showNewPass ? 'text' : 'password'"
                  outlined
                  dense
                  prepend-inner-icon="mdi-lock-plus-outline"
                  :rules="
                    form.new_password || form.new_password_confirmation
                      ? [rules.minLength(8, 'New password')]
                      : []
                  "
                  hint="Min 8 characters. Leave blank to keep current password."
                  persistent-hint
                  class="mb-3"
                  autocomplete="new-password"
                  :disabled="savingPassword"
                  :error-messages="fieldErrors.password"
                  @click:append="showNewPass = !showNewPass"
                  @input="clearFieldError('password')"
                ></v-text-field>
                <v-text-field
                  v-model="form.new_password_confirmation"
                  label="Confirm New Password"
                  :type="showNewPass ? 'text' : 'password'"
                  outlined
                  dense
                  prepend-inner-icon="mdi-lock-check-outline"
                  :rules="
                    form.new_password
                      ? [
                          rules.requiredPassword('Password confirmation'),
                          (v) =>
                            v === form.new_password ||
                            'New passwords do not match',
                        ]
                      : []
                  "
                  autocomplete="new-password"
                  :disabled="savingPassword"
                ></v-text-field>
                <div class="text-right mt-1 mb-3">
                  <v-btn
                    small
                    color="primary"
                    depressed
                    :loading="savingPassword"
                    :disabled="
                      !isPasswordFormValidAndAttempted || savingPassword
                    "
                    @click="savePassword"
                  >
                    <v-icon left small>mdi-lock-reset</v-icon>
                    Update Password
                  </v-btn>
                </div>
              </template>
            </v-card-text>
          </v-tab-item>
        </v-tabs-items>

        <!-- General form error display -->
        <v-alert v-if="generalFormError" type="error" dense text class="ma-4">
          {{ generalFormError }}
        </v-alert>

        <v-divider></v-divider>
        <v-card-actions class="pa-4 grey lighten-4">
          <v-spacer></v-spacer>
          <v-btn
            text
            :disabled="savingDetails || savingPassword"
            @click="closeDialog"
          >
            Done
          </v-btn>
          <!-- Removed combined save button as per UX with separate save buttons per section
          <v-btn
            color="primary"
            type="submit"
            depressed
            :loading="savingDetails || savingPassword"
            :disabled="!settingsFormValid || savingDetails || savingPassword || (!isDetailsChanged && !passwordChangeAttempted)"
          >
            <v-icon left>mdi-content-save-settings-outline</v-icon>
            Save All Changes
          </v-btn>
          -->
        </v-card-actions>
      </v-form>
    </v-card>
    <v-card v-else>
      <v-card-text class="text-center pa-8">
        <p>Loading user data...</p>
        <v-progress-circular
          indeterminate
          color="primary"
        ></v-progress-circular>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapGetters } from 'vuex' // If needed to compare against current logged-in user ID

export default {
  name: 'AccountSettingsDialog',
  props: {
    dialog: Boolean, // Controls dialog visibility, synced with parent
    user: {
      // The user object whose settings are being edited
      type: Object,
      default: null,
    },
  },
  data() {
    return {
      activeTab: 0, // 0 for Profile Details, 1 for Change Password
      settingsFormValid: true, // Vuetify's overall form validity
      savingDetails: false,
      savingPassword: false,
      generalFormError: null, // For non-field specific API errors
      fieldErrors: {}, // For field-specific errors: { name: ['Error'], email: ['Error'] }

      form: {
        // Local form data, initialized from props
        name: '',
        email: '',
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
      },
      initialDetails: { name: '', email: '' }, // To track if name/email actually changed

      showCurrentPass: false,
      showNewPass: false,
      supportsPasswordChange: true, // Set to false if your auth setup doesn't support direct password changes via API

      rules: {
        required: (v) => !!(v || '').trim() || 'This field is required.',
        requiredPassword: (fieldName) => (v) =>
          !!v || `${fieldName} is required.`,
        maxLength:
          (len, fieldName = 'This field') =>
          (v) =>
            (v && v.length <= len) ||
            `${fieldName} must be at most ${len} characters.`,
        emailFormat: (v) => /.+@.+\..+/.test(v) || 'E-mail must be valid.',
        minLength:
          (len, fieldName = 'Password') =>
          (v) =>
            v === '' ||
            (v && v.length >= len) ||
            `${fieldName} must be at least ${len} characters.`,
      },
    }
  },
  computed: {
    ...mapGetters('auth', { currentAuthUser: 'getUser' }), // To get current logged-in user ID if needed for checks
    editableUser() {
      // Primarily uses the prop, but used to initialize form
      return this.user
    },
    isDetailsChanged() {
      if (!this.editableUser) return false
      return (
        this.form.name.trim() !== this.initialDetails.name.trim() ||
        this.form.email.trim() !== this.initialDetails.email.trim()
      )
    },
    detailsFormValid() {
      // Specific validity for details section
      if (!this.form.name || !this.form.email) return false
      return (
        !this.rules.required(this.form.name) &&
        !this.rules.maxLength(255, 'Name')(this.form.name) &&
        !this.rules.required(this.form.email) &&
        !this.rules.emailFormat(this.form.email)
      )
    },
    passwordChangeAttempted() {
      // User is attempting to change password if new_password or its confirmation is filled
      return !!this.form.new_password || !!this.form.new_password_confirmation
    },
    isPasswordFormValidAndAttempted() {
      if (!this.passwordChangeAttempted) return false // Not trying to change password
      // All fields related to password change must be valid if new_password is set
      return (
        this.form.current_password &&
        this.form.new_password &&
        this.form.new_password.length >= 8 &&
        this.form.new_password_confirmation &&
        this.form.new_password === this.form.new_password_confirmation
      )
    },
  },
  watch: {
    dialog(isOpen) {
      if (isOpen && this.user) {
        // When dialog opens, populate form with user data
        this.form.name = this.user.name || ''
        this.form.email = this.user.email || ''
        this.initialDetails = {
          name: this.user.name || '',
          email: this.user.email || '',
        } // Store initial for change detection

        // Reset password fields and errors
        this.form.current_password = ''
        this.form.new_password = ''
        this.form.new_password_confirmation = ''
        this.generalFormError = null
        this.fieldErrors = {}
        this.activeTab = 0 // Default to first tab
        this.$nextTick(() => {
          if (this.$refs.settingsForm) this.$refs.settingsForm.resetValidation()
        })
      } else if (!isOpen) {
        // When dialog closes, clear fields (optional, but good for next open)
        // this.resetFormFields(); // Could call a reset method
      }
    },
  },
  methods: {
    closeDialog() {
      this.$emit('update:dialog', false)
    },
    clearFieldError(fieldName) {
      if (this.fieldErrors[fieldName]) {
        this.$set(this.fieldErrors, fieldName, undefined) // Use Vue.set for reactivity
      }
      this.generalFormError = null // Clear general error when user types
    },
    async saveProfileDetails() {
      // Explicitly trigger validation for relevant parts if using separate form sections
      // For simplicity, this just checks computed and then full form ref
      if (!this.$refs.settingsForm.validate()) {
        // Or check specific rules for name/email
        this.showParentSnackbar(
          'Please correct errors in your profile details.',
          'warning'
        )
        return
      }
      if (!this.isDetailsChanged) {
        this.showParentSnackbar('No changes made to profile details.', 'info')
        return
      }

      this.savingDetails = true
      this.generalFormError = null
      this.fieldErrors = {}

      try {
        const payload = { name: this.form.name, email: this.form.email }
        const response = await this.$axios.put('/user/profile-details', payload)
        this.$emit('user-updated', response.data.user) // Emit the updated user from response
        this.showParentSnackbar(
          response.data.message || 'Profile details updated successfully!',
          'success'
        )
        this.initialDetails = {
          name: response.data.user.name,
          email: response.data.user.email,
        } // Update initial state
        // Dialog stays open
      } catch (error) {
        console.error(
          'Failed to save profile details:',
          error.response?.data || error
        )
        if (
          error.response &&
          error.response.status === 422 &&
          error.response.data.errors
        ) {
          this.fieldErrors = error.response.data.errors
          this.generalFormError = 'Please correct the highlighted errors.'
        } else {
          this.generalFormError =
            error.response?.data?.message || 'Failed to save profile details.'
        }
        this.showParentSnackbar(this.generalFormError, 'error')
      } finally {
        this.savingDetails = false
      }
    },
    async savePassword() {
      if (!this.$refs.settingsForm.validate()) {
        // Validate all form fields including password rules
        this.showParentSnackbar(
          'Please correct password fields before submitting.',
          'warning'
        )
        return
      }
      if (!this.passwordChangeAttempted) {
        // Double check, though button disabled should cover this
        this.showParentSnackbar('No new password entered.', 'info')
        return
      }
      if (!this.form.current_password) {
        // Ensure current password is provided
        this.fieldErrors = {
          ...this.fieldErrors,
          current_password: [
            'Current password is required to change password.',
          ],
        }
        this.showParentSnackbar(
          'Current password is required to set a new one.',
          'error'
        )
        return
      }

      this.savingPassword = true
      this.generalFormError = null
      this.fieldErrors = {}
      try {
        const payload = {
          current_password: this.form.current_password,
          password: this.form.new_password,
          password_confirmation: this.form.new_password_confirmation,
        }
        // API endpoint: PUT /api/user/password
        const response = await this.$axios.put('/user/password', payload)
        this.showParentSnackbar(
          response.data.message || 'Password updated successfully!',
          'success'
        )
        // Reset password fields after successful change
        this.form.current_password = ''
        this.form.new_password = ''
        this.form.new_password_confirmation = ''
        this.$nextTick(() => {
          if (this.$refs.settingsForm) this.$refs.settingsForm.resetValidation()
        })
      } catch (error) {
        console.error(
          'Failed to update password:',
          error.response?.data || error
        )
        if (
          error.response &&
          error.response.status === 422 &&
          error.response.data.errors
        ) {
          this.fieldErrors = error.response.data.errors // This will populate specific password errors
          this.generalFormError =
            'Please correct the highlighted password errors.'
        } else {
          this.generalFormError =
            error.response?.data?.message || 'Password update failed.'
        }
        this.showParentSnackbar(this.generalFormError, 'error')
      } finally {
        this.savingPassword = false
      }
    },
    /**
     * Not used currently as saves are per-section.
     * If you had one "Save All" button, this would be its handler.
     */
    async saveAllSettings_Combined() {
      // Add a dummy await to satisfy eslint's require-await rule
      await Promise.resolve()

      if (!this.$refs.settingsForm.validate()) return

      let detailsPayload = null
      let passwordPayload = null

      if (this.isDetailsChanged) {
        detailsPayload = { name: this.form.name, email: this.form.email }
      }
      if (
        this.passwordChangeAttempted &&
        this.isPasswordFormValidAndAttempted
      ) {
        passwordPayload = {
          current_password: this.form.current_password,
          password: this.form.new_password,
          password_confirmation: this.form.new_password_confirmation,
        }
      }

      if (!detailsPayload && !passwordPayload) {
        this.showParentSnackbar('No changes to save.', 'info')
      }

      // ... (combine saving logic if using a single save button) ...
      // For now, we use separate save buttons for each section.
    },
    showParentSnackbar(text, color = 'info') {
      // Emit an event for the parent page to show a snackbar
      this.$emit('snackbar', { text, color })
    },
  },
}
</script>

<style scoped>
/* Minor styling adjustments for the dialog */
.v-card__title {
  word-break: break-word;
}
.v-tabs-items {
  background-color: transparent !important; /* Ensure tab items background is consistent */
}
.theme--dark .v-tabs-items {
  background-color: var(--v-background-base) !important;
}
.v-card__actions.grey.lighten-4 {
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}
.theme--dark .v-card__actions.grey.lighten-4 {
  /* Adjust for dark theme actions bar */
  background-color: var(--v-sheet-lighten1, #272727) !important;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
}
</style>
