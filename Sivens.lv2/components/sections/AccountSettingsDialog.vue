<template>
  <v-dialog
    :value="dialog"
    persistent
    max-width="650px"
    scrollable
    eager
    @input="
      (value) => {
        if (!value) closeDialog()
      }
    "
    @keydown.esc="closeDialog"
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
        v-model="formOverallValidity"
        lazy-validation
        @submit.prevent
      >
        <v-tabs
          v-model="activeTab"
          background-color="transparent"
          grow
          centered
        >
          <v-tab>Profile Details</v-tab>
          <v-tab>Change Password</v-tab>
          <v-tab>Profile Picture</v-tab>
        </v-tabs>
        <v-divider></v-divider>

        <v-tabs-items v-model="activeTab" class="pt-4">
          <!-- Tab 1: Profile Details (Name & Email) -->
          <v-tab-item eager>
            <v-card-text class="pb-2">
              <h3 class="text-subtitle-1 font-weight-medium mb-1">
                Edit Your Profile
              </h3>
              <p class="text-caption text--secondary mb-4">
                Update your name and email address.
              </p>
              <v-text-field
                v-model="form.name"
                label="Full Name"
                outlined
                dense
                :rules="[
                  rules.required('Full Name'),
                  rules.maxLength(255, 'Full Name'),
                ]"
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
                :rules="[rules.required('Email'), rules.emailFormat]"
                prepend-inner-icon="mdi-email"
                required
                :disabled="savingDetails"
                :error-messages="fieldErrors.email"
                @input="clearFieldError('email')"
              ></v-text-field>
              <div class="text-right mt-3 mb-3">
                <v-btn
                  color="primary"
                  depressed
                  :loading="savingDetails"
                  :disabled="
                    !isDetailsChanged ||
                    savingDetails ||
                    !detailsFieldsArePotentiallyValid
                  "
                  @click="saveProfileDetails"
                >
                  <v-icon left small>mdi-content-save-outline</v-icon>
                  Save Details
                </v-btn>
              </div>
            </v-card-text>
          </v-tab-item>

          <!-- Tab 2: Change Password -->
          <v-tab-item eager>
            <v-card-text class="pb-2">
              <h3 class="text-subtitle-1 font-weight-medium mb-1">
                Change Your Password
              </h3>
              <p class="text-caption text--secondary mb-4">
                Create a new secure password for your account.
              </p>
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
                      ? [rules.required('Current password')]
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
                  v-model="form.password"
                  label="New Password"
                  :append-icon="showNewPass ? 'mdi-eye' : 'mdi-eye-off'"
                  :type="showNewPass ? 'text' : 'password'"
                  outlined
                  dense
                  prepend-inner-icon="mdi-lock-plus-outline"
                  :rules="
                    passwordChangeAttempted
                      ? [
                          rules.minLength(8, 'New password'),
                          rules.passwordComplexity,
                        ]
                      : []
                  "
                  hint="Min 8 chars, with uppercase, lowercase, number, and symbol."
                  persistent-hint
                  class="mb-3"
                  autocomplete="new-password"
                  :disabled="savingPassword"
                  :error-messages="fieldErrors.password"
                  also
                  new_password_confirmation
                  @click:append="showNewPass = !showNewPass"
                  @input="
                    clearFieldError('password')
                    clearFieldError('password_confirmation')
                  "
                ></v-text-field>
                <v-text-field
                  v-model="form.password_confirmation"
                  label="Confirm New Password"
                  :type="showNewPass ? 'text' : 'password'"
                  outlined
                  dense
                  prepend-inner-icon="mdi-lock-check-outline"
                  :rules="
                    passwordChangeAttempted
                      ? [
                          rules.required('Password confirmation'),
                          (v) =>
                            v === form.new_password ||
                            'New passwords do not match',
                        ]
                      : []
                  "
                  autocomplete="new-password"
                  :disabled="savingPassword"
                  :error-messages="fieldErrors.password_confirmation"
                  @input="clearFieldError('password_confirmation')"
                ></v-text-field>
                <div class="text-right mt-3 mb-3">
                  <v-btn
                    color="primary"
                    depressed
                    :loading="savingPassword"
                    :disabled="!canSavePassword || savingPassword"
                    @click="savePassword"
                  >
                    <v-icon left small>mdi-lock-reset</v-icon>
                    Update Password
                  </v-btn>
                </div>
              </template>
              <v-alert v-else type="info" text dense class="mb-3" border="left">
                Password changes are handled via "Forgot Password" or by your
                identity provider.
              </v-alert>
            </v-card-text>
          </v-tab-item>

          <!-- Tab 3: Profile Picture -->
          <v-tab-item eager>
            <v-card-text class="pb-2">
              <h3 class="text-subtitle-1 font-weight-medium mb-1">
                Update Profile Picture
              </h3>
              <p class="text-caption text--secondary mb-4">
                Choose a new image for your profile avatar (max 5MB).
              </p>
              <div class="d-flex justify-center mb-4">
                <v-avatar size="128" color="grey lighten-2" class="elevation-3">
                  <v-img
                    :src="pfpPreviewUrl || initialPfpUrl"
                    :alt="`${form.name || 'User'}'s Avatar`"
                    cover
                  ></v-img>
                </v-avatar>
              </div>
              <v-file-input
                ref="pfpFileInputComponent"
                v-model="form.userPFPFile"
                label="Choose new picture (.jpg, .png, .gif, .webp)"
                accept="image/jpeg, image/png, image/gif, image/webp"
                outlined
                dense
                hide-details="auto"
                prepend-icon="mdi-camera-plus-outline"
                show-size
                :loading="savingPFP"
                clearable
                class="mb-3"
                :rules="pfpRules"
                :error-messages="fieldErrors.userPFP"
                @change="previewPFP"
                @click:clear="clearPFPPreview"
              ></v-file-input>
              <div class="text-right mt-3 mb-3">
                <v-btn
                  color="primary"
                  depressed
                  :loading="savingPFP"
                  :disabled="!form.userPFPFile || savingPFP"
                  @click="updatePFP"
                >
                  <v-icon left small>mdi-image-edit-outline</v-icon>
                  Upload Picture
                </v-btn>
              </div>
            </v-card-text>
          </v-tab-item>
        </v-tabs-items>

        <v-alert
          v-if="generalFormError"
          type="error"
          dense
          text
          class="mx-4 my-2"
        >
          {{ generalFormError }}
        </v-alert>

        <v-divider></v-divider>
        <v-card-actions class="pa-4 grey lighten-4">
          <v-spacer></v-spacer>
          <v-btn
            text
            large
            :disabled="savingDetails || savingPassword || savingPFP"
            @click="closeDialog"
          >
            Done
          </v-btn>
        </v-card-actions>
      </v-form>
    </v-card>
    <v-card v-else>
      <v-card-text class="text-center pa-8">
        <v-progress-circular
          indeterminate
          color="primary"
          size="50"
        ></v-progress-circular>
        <p class="mt-4">Loading user data...</p>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
// No direct BACKEND_URL_SETTINGS needed if Nuxt's $axios.defaults.baseURL is set
// and profilePictureUrl in parent constructs the full URL.
const DEFAULT_PFP_FALLBACK_SETTINGS =
  'https://cdn-icons-png.flaticon.com/512/847/847969.png'

export default {
  name: 'AccountSettingsDialog',
  props: {
    dialog: Boolean,
    user: { type: Object, default: null },
  },
  data() {
    return {
      activeTab: 0,
      formOverallValidity: false,
      savingDetails: false,
      savingPassword: false,
      savingPFP: false,
      generalFormError: null,
      fieldErrors: {}, // { name: ['Error1'], email: ['Error1'], password: [], userPFP: [] }

      form: {
        // Holds current editable data
        name: '',
        email: '',
        current_password: '',
        new_password: '', // For the new password input field
        new_password_confirmation: '', // For new password confirmation
        userPFPFile: null, // Stores the File object for PFP
      },
      initialDetails: { name: '', email: '' }, // To compare if name/email changed
      initialPfpUrl: DEFAULT_PFP_FALLBACK_SETTINGS, // To show current PFP
      pfpPreviewUrl: null, // For local preview of selected new PFP

      showCurrentPass: false,
      showNewPass: false,
      supportsPasswordChange: true, // Assume password can be changed via API

      rules: {
        required: (fieldName) => (v) =>
          !!(v || '').trim() || `${fieldName} is required.`,
        maxLength:
          (len, fieldName = 'Field') =>
          (v) =>
            (v || '').length <= len ||
            `${fieldName} can be at most ${len} characters.`,
        emailFormat: (v) =>
          !v || /.+@.+\..+/.test(v) || 'E-mail must be valid.',
        minLength:
          (len, fieldName = 'Password') =>
          (v) =>
            v === '' ||
            (v && v.length >= len) ||
            `${fieldName} must be at least ${len} characters.`,
        passwordComplexity: (v) =>
          v === '' ||
          /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?~`]).{8,}/.test(
            v
          ) ||
          'Password needs 8+ chars, uppercase, lowercase, number, & symbol.',
      },
      pfpRules: [
        (value) =>
          !value ||
          value.size < 5 * 1024 * 1024 ||
          'Image size should be less than 5 MB!',
        (value) =>
          !value ||
          ['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(
            value.type
          ) ||
          'Invalid image type (allowed: jpg, png, gif, webp).',
      ],
    }
  },
  computed: {
    isDetailsChanged() {
      if (!this.user) return false
      return (
        this.form.name.trim() !== (this.initialDetails.name || '').trim() ||
        this.form.email.trim() !== (this.initialDetails.email || '').trim()
      )
    },
    detailsFieldsArePotentiallyValid() {
      // For enabling "Save Details" button based on minimal input
      if (!this.form.name || !this.form.email) return false
      return (
        this.rules.required('Full Name')(this.form.name) === true &&
        this.rules.maxLength(255, 'Full Name')(this.form.name) === true &&
        this.rules.required('Email')(this.form.email) === true &&
        this.rules.emailFormat(this.form.email) === true
      )
    },
    passwordChangeAttempted() {
      return !!this.form.new_password || !!this.form.new_password_confirmation
    },
    canSavePassword() {
      // For enabling "Update Password" button
      if (!this.passwordChangeAttempted) return false
      // A basic check; full validation is on click with this.$refs.settingsForm.validate()
      return !!(
        this.form.current_password &&
        this.form.new_password &&
        this.form.new_password_confirmation
      )
    },
  },
  watch: {
    dialog: {
      immediate: true,
      handler(isOpen) {
        if (isOpen && this.user) {
          this.populateFormFromUser(this.user)
          this.activeTab = 0
          this.$nextTick(() => {
            if (this.$refs.settingsForm)
              this.$refs.settingsForm.resetValidation()
            this.fieldErrors = {}
          })
        } else if (!isOpen) {
          this.generalFormError = null
        }
      },
    },
    user: {
      deep: true,
      handler(newUser) {
        // Watch the prop
        if (this.dialog && newUser) {
          // Only update form if dialog is open
          this.populateFormFromUser(newUser)
        }
      },
    },
    activeTab() {
      // Reset validation & errors when changing tabs
      this.$nextTick(() => {
        if (this.$refs.settingsForm) this.$refs.settingsForm.resetValidation()
        this.fieldErrors = {}
        this.generalFormError = null
      })
    },
  },
  methods: {
    populateFormFromUser(userData) {
      this.form.name = userData.name || ''
      this.form.email = userData.email || ''
      this.initialDetails = {
        name: userData.name || '',
        email: userData.email || '',
      }

      this.form.current_password = ''
      this.form.new_password = ''
      this.form.new_password_confirmation = ''
      this.form.userPFPFile = null
      this.pfpPreviewUrl = null

      this.initialPfpUrl = userData.userPFP
        ? userData.userPFP.startsWith('http://') ||
          userData.userPFP.startsWith('https://')
          ? userData.userPFP
          : // $axios.defaults.baseURL should provide http://localhost:8000/api
            // so for storage links, we need to construct it without /api
            // Assuming BACKEND_URL_SETTINGS directly points to http://localhost:8000
            `${this.$axios.defaults.baseURL.replace('/api', '')}/storage/${
              userData.userPFP
            }`
        : DEFAULT_PFP_FALLBACK_SETTINGS

      this.generalFormError = null
      this.fieldErrors = {}
    },
    closeDialog() {
      this.$emit('update:dialog', false)
    },
    clearFieldError(fieldName) {
      if (this.fieldErrors[fieldName])
        this.$set(this.fieldErrors, fieldName, undefined)
      if (
        this.generalFormError &&
        !Object.values(this.fieldErrors).some((err) => err && err.length > 0)
      ) {
        this.generalFormError = null
      }
    },
    previewPFP() {
      this.clearFieldError('userPFP')
      if (this.form.userPFPFile) {
        // Client-side rule check example for immediate feedback
        for (const rule of this.pfpRules) {
          const validationResult = rule(this.form.userPFPFile)
          if (validationResult !== true) {
            this.$set(this.fieldErrors, 'userPFP', [validationResult]) // Show rule error
            return // Stop preview if file is invalid by local rules
          }
        }
        const reader = new FileReader()
        reader.onload = (e) => {
          this.pfpPreviewUrl = e.target.result
        }
        reader.readAsDataURL(this.form.userPFPFile)
      } else {
        this.pfpPreviewUrl = null
      }
    },
    clearPFPPreview() {
      this.form.userPFPFile = null
      this.pfpPreviewUrl = null
      this.clearFieldError('userPFP') // Clear validation messages
      if (this.$refs.pfpFileInputComponent) {
        this.$refs.pfpFileInputComponent.reset() // Reset Vuetify's file input internal state
      }
    },
    async updatePFP() {
      this.clearFieldError('userPFP')
      this.generalFormError = null

      if (!this.form.userPFPFile) {
        this.showParentSnackbar('Please select an image to upload.', 'warning')
        return
      }
      // Trigger validation of the specific v-file-input by Vuetify if rules are complex
      // Or use this.$refs.settingsForm.validate() which will validate everything visible
      // Here we assume pfpRules is enough for now.

      this.savingPFP = true
      const formData = new FormData()
      formData.append('userPFP', this.form.userPFPFile)
      try {
        const response = await this.$axios.post(
          '/user/profile-picture',
          formData
        ) // API defined in routes/api.php
        this.$emit('pfp-updated', response.data.user)
        this.showParentSnackbar(
          'Profile picture updated successfully!',
          'success'
        )
        this.clearPFPPreview() // Clear file input and preview on success
      } catch (error) {
        const errData = error.response?.data
        if (errData && errData.errors && errData.errors.userPFP) {
          this.$set(this.fieldErrors, 'userPFP', errData.errors.userPFP)
          this.generalFormError =
            'Error with uploaded image. Please check requirements.'
        } else {
          this.generalFormError =
            errData?.message || 'Profile picture update failed.'
        }
        this.showParentSnackbar(this.generalFormError, 'error')
      } finally {
        this.savingPFP = false
      }
    },
    async saveProfileDetails() {
      this.clearFieldError('name')
      this.clearFieldError('email')
      this.generalFormError = null

      if (!this.isDetailsChanged) {
        this.showParentSnackbar('No changes made to profile details.', 'info')
        return
      }
      if (!this.$refs.settingsForm.validate()) {
        this.showParentSnackbar(
          'Please correct any errors in your profile details.',
          'warning'
        )
        return
      }
      if (this.fieldErrors.name || this.fieldErrors.email) {
        // Check again after validate
        this.showParentSnackbar(
          'Please correct the highlighted errors in profile details.',
          'warning'
        )
        return
      }

      this.savingDetails = true
      try {
        const payload = {
          name: this.form.name.trim(),
          email: this.form.email.trim(),
        }
        const response = await this.$axios.put('/user/profile', payload) // Correct endpoint from prev discussion

        this.$emit('user-updated', response.data.user)
        this.showParentSnackbar(
          response.data.message || 'Profile details updated!',
          'success'
        )
        this.initialDetails = {
          name: response.data.user.name,
          email: response.data.user.email,
        } // Update initial state
      } catch (error) {
        const errData = error.response?.data
        if (errData && errData.errors) {
          this.fieldErrors = { ...this.fieldErrors, ...errData.errors }
          this.generalFormError = 'Please correct the highlighted errors.'
        } else {
          this.generalFormError =
            errData?.message || 'Failed to save profile details.'
        }
        this.showParentSnackbar(this.generalFormError, 'error')
      } finally {
        this.savingDetails = false
      }
    },
    async savePassword() {
      this.clearFieldError('current_password')
      this.clearFieldError('password')
      this.clearFieldError('password_confirmation')
      this.generalFormError = null

      if (!this.passwordChangeAttempted) {
        this.showParentSnackbar('No new password information entered.', 'info')
        return
      }
      if (!this.$refs.settingsForm.validate()) {
        this.showParentSnackbar(
          'Please correct any errors in the password fields.',
          'warning'
        )
        return
      }
      if (
        this.fieldErrors.current_password ||
        this.fieldErrors.password ||
        this.fieldErrors.password_confirmation ||
        !this.form.current_password
      ) {
        this.showParentSnackbar(
          'Please correct the highlighted password errors, current password is required.',
          'warning'
        )
        return
      }

      this.savingPassword = true
      try {
        const payload = {
          current_password: this.form.current_password,
          password: this.form.new_password,
          password_confirmation: this.form.new_password_confirmation,
        }
        const response = await this.$axios.put('/user/password', payload) // Correct endpoint
        this.showParentSnackbar(
          response.data.message || 'Password updated successfully!',
          'success'
        )
        this.resetPasswordFieldsAndErrors()
        this.$nextTick(() => {
          if (this.$refs.settingsForm) this.$refs.settingsForm.resetValidation()
        })
      } catch (error) {
        const errData = error.response?.data
        if (errData && errData.errors) {
          this.fieldErrors = { ...this.fieldErrors, ...errData.errors }
          let firstErrorMsg = 'Please correct the highlighted password errors.'
          for (const key in errData.errors) {
            if (
              Array.isArray(errData.errors[key]) &&
              errData.errors[key].length > 0
            ) {
              firstErrorMsg = errData.errors[key][0]
              break
            }
          }
          this.generalFormError = firstErrorMsg
        } else {
          this.generalFormError = errData?.message || 'Password update failed.'
        }
        this.showParentSnackbar(this.generalFormError, 'error')
      } finally {
        this.savingPassword = false
      }
    },
    showParentSnackbar(text, color = 'info') {
      this.$emit('snackbar', { text, color })
    },
  },
}
</script>

<style scoped>
.v-tabs-items {
  background-color: transparent !important;
}
.theme--dark .v-tabs-items {
  background-color: var(--v-card-base, #2e2e2e) !important;
}
.v-card__actions.grey.lighten-4 {
  border-top: 1px solid rgba(0, 0, 0, 0.12);
}
.theme--dark .v-card__actions.grey.lighten-4 {
  background-color: var(--v-sheet-lighten1, #272727) !important;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
}
</style>
