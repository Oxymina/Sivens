<template>
  <div class="image-editor">
    <v-card outlined>
      <!-- Image Preview or Upload Prompt -->
      <v-img
        v-if="localData.url"
        :src="localData.url"
        height="250"
        contain
        class="grey lighten-2"
      >
        <template v-slot:placeholder>
          <v-row class="fill-height ma-0" align="center" justify="center">
            <v-progress-circular
              indeterminate
              color="primary"
            ></v-progress-circular>
          </v-row>
        </template>
      </v-img>
      <div v-else class="pa-8 text-center grey lighten-3">
        <v-icon large class="mb-2">mdi-image-area</v-icon>
        <div>No image selected</div>
      </div>

      <!-- Loading Overlay -->
      <v-overlay :value="uploading" absolute>
        <v-progress-circular indeterminate size="64"></v-progress-circular>
        <div class="mt-2">Uploading...</div>
      </v-overlay>

      <!-- Controls -->
      <v-card-text>
        <v-file-input
          v-model="selectedFile"
          label="Select or replace image"
          accept="image/png, image/jpeg, image/gif"
          outlined
          dense
          prepend-icon="mdi-camera"
          hide-details="auto"
          class="mb-3"
          :error-messages="uploadError"
          :disabled="uploading"
          @change="handleFileSelect"
        ></v-file-input>

        <v-text-field
          v-model="localData.caption"
          label="Caption (optional)"
          outlined
          dense
          hide-details="auto"
          class="mb-3"
          @input="updateParent"
        ></v-text-field>

        <v-text-field
          v-model="localData.alt"
          label="Alt text (important for accessibility)"
          outlined
          dense
          hide-details="auto"
          @input="updateParent"
        ></v-text-field>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Object,
      required: true,
      default: () => ({ url: '', caption: '', alt: '' }),
    },
    blockId: { type: [String, Number], required: true }, // Pass ID for context if needed
  },
  data() {
    return {
      localData: { ...this.value }, // Local copy of data {url, caption, alt}
      selectedFile: null,
      uploading: false,
      uploadError: null,
    }
  },
  watch: {
    value: {
      // Sync external data changes to local data
      deep: true,
      handler(newValue) {
        if (JSON.stringify(newValue) !== JSON.stringify(this.localData)) {
          this.localData = { ...newValue }
        }
      },
    },
  },
  methods: {
    updateParent() {
      // Emit the whole localData object when inputs change
      this.$emit('input', { ...this.localData })
    },
    async handleFileSelect() {
      if (!this.selectedFile) {
        return
      }
      this.uploading = true
      this.uploadError = null
      const formData = new FormData()
      formData.append('image', this.selectedFile)
      // Optionally add context, like post ID if needed by backend for organization
      // formData.append('post_id', this.postId); // Requires postId prop
      // formData.append('block_id', this.blockId); // Pass block ID if useful

      try {
        // Adjust API endpoint as needed
        const response = await this.$axios.post(
          '/admin/images/upload',
          formData,
          {
            headers: { 'Content-Type': 'multipart/form-data' },
            // Optional: Add progress tracking using onUploadProgress axios config
          }
        )
        // Assuming backend returns { url: 'path/to/image.jpg' }
        this.localData.url = response.data.url
        this.selectedFile = null // Clear file input
        this.updateParent() // Emit update with new URL
      } catch (error) {
        console.error('Image upload failed:', error)
        this.uploadError =
          error.response?.data?.message || 'Image upload failed.'
        this.selectedFile = null // Clear file input on error too
      } finally {
        this.uploading = false
      }
    },
  },
}
</script>
