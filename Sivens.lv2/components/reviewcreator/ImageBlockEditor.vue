<template>
  <div class="image-editor">
    <v-card outlined class="image-editor-card">
      <!-- Image Preview or Upload Prompt -->
      <v-responsive
        :aspect-ratio="16 / 9"
        class="grey lighten-2 image-preview-container"
      >
        <v-img
          v-if="displayImageUrl"
          :src="displayImageUrl"
          :alt="localData.alt || 'Image preview'"
          height="100%"
          contain
          class="image-preview"
        >
          <template v-slot:placeholder>
            <v-row class="fill-height ma-0" align="center" justify="center">
              <v-progress-circular
                indeterminate
                color="primary"
              ></v-progress-circular>
            </v-row>
          </template>
          <template v-slot:error>
            <v-row
              class="fill-height ma-0"
              align="center"
              justify="center"
              style="background-color: rgba(0, 0, 0, 0.05)"
            >
              <v-icon color="grey darken-1" large
                >mdi-image-broken-variant</v-icon
              >
              <span class="ml-2 grey--text text--darken-1 caption"
                >Preview unavailable</span
              >
            </v-row>
          </template>
        </v-img>
        <div
          v-else
          class="fill-height d-flex flex-column justify-center align-center text-center pa-4"
        >
          <v-icon large class="mb-2 grey--text text--lighten-1"
            >mdi-image-area</v-icon
          >
          <div class="grey--text">No image selected or URL provided.</div>
        </div>
        <!-- Loading Overlay during upload -->
        <v-overlay :value="uploading" absolute color="rgba(0,0,0,0.7)">
          <div class="text-center white--text">
            <v-progress-circular
              indeterminate
              size="50"
              width="3"
              color="white"
            ></v-progress-circular>
            <div class="mt-3 text-subtitle-1">Uploading...</div>
          </div>
        </v-overlay>
      </v-responsive>

      <!-- Controls -->
      <v-card-text class="pt-3">
        <v-file-input
          ref="imageFileInput"
          v-model="selectedFile"
          label="Select or replace image"
          placeholder="Choose an image"
          accept="image/png, image/jpeg, image/gif, image/webp"
          outlined
          dense
          prepend-icon=""
          prepend-inner-icon="mdi-camera-plus-outline"
          hide-details="auto"
          class="mb-3"
          :rules="fileInputRules"
          :error-messages="uploadError"
          :disabled="uploading"
          show-size
          clearable
          @change="handleFileSelect"
          @click:clear="clearImage"
        ></v-file-input>

        <v-text-field
          v-model="localData.caption"
          label="Caption (optional)"
          outlined
          dense
          hide-details="auto"
          class="mb-3"
          @input="updateParentData"
        ></v-text-field>

        <v-text-field
          v-model="localData.alt"
          label="Alt text (for accessibility)"
          placeholder="Describe the image"
          outlined
          dense
          hide-details="auto"
          @input="updateParentData"
        ></v-text-field>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  name: 'ImageBlockEditor',
  props: {
    value: {
      // This is the 'data' object for the image block: { url, caption, alt }
      type: Object,
      required: true,
      default: () => ({ url: '', caption: '', alt: '' }),
    },
    // blockId is passed from BlockEditor.vue, might be useful for unique FormData if backend needs it
    blockId: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      // localData is a copy of the 'value' prop to enable editing within the component
      localData: {
        url: this.value.url || '',
        caption: this.value.caption || '',
        alt: this.value.alt || '',
      },
      selectedFile: null, // Holds the File object from v-file-input
      uploading: false, // Tracks upload status for loading indicator
      uploadError: null, // Stores any upload error messages
      // No need to store backendUrl here if we use this.$axios.defaults.baseURL to derive it
      fileInputRules: [
        // Basic client-side validation for the file input
        // value => !value || value.size < 2000000 || 'Image size should be less than 2MB!', // Example size validation
        // value => !value || ['image/jpeg', 'image/png', 'image/gif'].includes(value.type) || 'Invalid file type!',
      ],
    }
  },
  computed: {
    /**
     * Computes the full displayable URL for the image preview.
     * Handles both externally provided absolute URLs and relative paths from backend uploads.
     */
    displayImageUrl() {
      const url = this.localData.url
      if (!url) return null

      // If it's already a full URL (http:// or https:// or a data: URL from local preview)
      if (
        url.startsWith('http://') ||
        url.startsWith('https://') ||
        url.startsWith('data:image')
      ) {
        return url
      }

      // Construct URL for images stored relative to Laravel's public/storage
      let backendBaseDomain = 'http://localhost:8000' // Hardcoded fallback
      if (this.$axios && this.$axios.defaults && this.$axios.defaults.baseURL) {
        try {
          const axiosBaseUrl = new URL(this.$axios.defaults.baseURL)
          backendBaseDomain = `${axiosBaseUrl.protocol}//${axiosBaseUrl.host}`
        } catch (e) {
          console.warn(
            'ImageBlockEditor: Could not parse $axios.defaults.baseURL. Using fallback.',
            e
          )
        }
      }
      const relativePath = url.startsWith('/') ? url.substring(1) : url
      return `${backendBaseDomain}/storage/${relativePath}`
    },
  },
  watch: {
    // When the parent 'value' prop changes (e.g., loading saved data into editor),
    // update the localData to reflect those changes.
    value: {
      deep: true,
      immediate: true, // Run this watcher also when component is created
      handler(newValue) {
        // Only update if truly different to avoid loops or overwriting ongoing edits
        if (JSON.stringify(newValue) !== JSON.stringify(this.localData)) {
          this.localData.url = newValue?.url || ''
          this.localData.caption = newValue?.caption || ''
          this.localData.alt = newValue?.alt || ''
          // If a URL exists from prop and no file is currently selected for upload, don't clear preview
          if (!this.selectedFile && this.localData.url) {
            // this.pfpPreviewUrl = null; // Not used in this component
          }
        }
      },
    },
  },
  methods: {
    /**
     * Emits the updated localData object to the parent BlockEditor via v-model's 'input' event.
     */
    updateParentData() {
      this.$emit('input', { ...this.localData })
    },
    /**
     * Handles the file selection from v-file-input and initiates the upload process.
     */
    async handleFileSelect() {
      this.uploadError = null // Clear previous errors
      if (!this.selectedFile) {
        // If file was cleared, and an old URL exists, we keep showing old URL
        // If the intent is to remove image, we'd need a separate "Remove Image" button
        return
      }

      // Optional: Client-side validation if not handled by :rules
      if (this.selectedFile.size > 5 * 1024 * 1024) {
        // Example 5MB limit
        this.uploadError = 'File is too large (max 5MB).'
        this.selectedFile = null // Reset file input
        return
      }

      this.uploading = true
      const formData = new FormData()
      formData.append('image', this.selectedFile) // 'image' must match backend expected key

      try {
        // Use the /blog/images/upload endpoint (or /admin/images/upload if appropriate)
        const response = await this.$axios.post('/images/upload', formData, {
          // Content-Type 'multipart/form-data' is usually set automatically by axios for FormData
        })

        // Backend is expected to return { url: 'relative/path/to/image.jpg' }
        this.localData.url = response.data.url // This is the relative path
        this.uploadError = null
        this.updateParentData() // Emit the update to parent (BlockEditor)
      } catch (error) {
        console.error('Image upload failed:', error.response?.data || error)
        this.uploadError = error.response?.data?.errors?.image
          ? error.response.data.errors.image[0] // Show first validation error for image
          : error.response?.data?.message ||
            'Image upload failed. Please try again.'
        // Don't clear localData.url here, as it might still hold a previously valid URL
      } finally {
        this.uploading = false
        this.selectedFile = null // Clear the file input model after attempt
        if (this.$refs.imageFileInput) this.$refs.imageFileInput.reset() // Reset Vuetify file input state
      }
    },
    /**
     * Clears the image URL and associated data when 'clearable' on v-file-input is used,
     * or if a dedicated "remove image" button were added.
     */
    clearImage() {
      this.localData.url = ''
      this.localData.caption = '' // Optionally clear caption and alt
      this.localData.alt = ''
      this.selectedFile = null
      this.uploadError = null
      this.updateParentData()
      if (this.$refs.imageFileInput) this.$refs.imageFileInput.reset()
    },
  },
}
</script>

<style scoped>
.image-editor-card {
  position: relative; /* For the v-overlay */
}
.image-preview-container {
  border-bottom: 1px solid rgba(0, 0, 0, 0.1); /* Separator before controls */
}
.theme--dark .image-preview-container {
  border-bottom-color: rgba(255, 255, 255, 0.1);
}
.image-preview .v-image__image {
  background-position: center center !important; /* Ensure 'contain' shows whole image nicely */
}
/* Ensure file input text is not cut off */
::v-deep .v-file-input .v-file-input__text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
