<template>
  <div class="map-location-editor pa-2">
    <v-select
      v-model="localType"
      :items="mapTypes"
      label="Map Type"
      outlined
      dense
      class="mb-3"
      @change="onTypeChange"
    ></v-select>

    <!-- Embed URL Input -->
    <v-text-field
      v-if="localType === 'embed'"
      v-model="localEmbedUrl"
      label="Google Maps Embed URL"
      placeholder="Paste <iframe> src (e.g., https://www.google.com/maps/embed/...)"
      outlined
      dense
      hide-details="auto"
      class="mb-2"
      @blur="handleEmbedUrlChange"
    ></v-text-field>

    <!-- Address Inputs for Static Map -->
    <template v-if="localType === 'address'">
      <v-text-field
        v-model="localAddress"
        label="Full Address or Place Name"
        placeholder="e.g., 1 Eiffel Tower, Paris, France"
        outlined
        dense
        hide-details="auto"
        class="mb-3"
        @input="debouncedGenerateStaticMap"
      ></v-text-field>
      <v-text-field
        v-model="localStaticMapApiKey"
        label="Google Static Maps API Key (Recommended)"
        placeholder="Your Google Cloud API Key for Static Maps"
        outlined
        dense
        hide-details="auto"
        class="mb-3"
        @input="debouncedGenerateStaticMap"
      ></v-text-field>
    </template>

    <!-- Preview Area -->
    <v-card v-if="displayPreview" outlined class="mt-4 preview-card">
      <v-subheader class="font-weight-medium">Map Preview</v-subheader>
      <div
        v-if="localType === 'embed' && embedPreviewUrl"
        class="map-preview embed-responsive embed-responsive-16by9"
      >
        <iframe
          :src="embedPreviewUrl"
          class="embed-responsive-item"
          frameborder="0"
          allowfullscreen
          title="Map Preview"
          sandbox="allow-scripts allow-same-origin allow-popups"
        ></iframe>
      </div>
      <v-img
        v-else-if="localType === 'address' && staticMapPreviewUrl"
        :src="staticMapPreviewUrl"
        aspect-ratio="1.77"
        contain
        class="grey lighten-3"
        height="250px"
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
          <v-row class="fill-height ma-0" align="center" justify="center">
            <v-icon color="error" large>mdi-image-broken-variant</v-icon>
            <span class="ml-2">Preview unavailable</span>
          </v-row>
        </template>
      </v-img>
      <div v-if="validationError" class="pa-3 text-caption error--text">
        {{ validationError }}
      </div>
      <div
        v-if="localType === 'address' && !staticMapPreviewUrl && localAddress"
        class="pa-3 text-caption text--secondary"
      >
        Enter address details to generate static map preview. An API key is
        recommended.
      </div>
    </v-card>
  </div>
</template>

<script>
import _ from 'lodash' // For debounce: npm install lodash

export default {
  name: 'MapLocationBlockEditor',
  props: {
    value: {
      // v-model: receives the 'data' object for this block
      type: Object,
      required: true,
      default: () => ({
        type: 'embed', // 'embed' or 'address'
        embedUrl: '', // For iframe src
        address: '', // For static map
        staticMapUrl: '', // Generated static map URL to store
        staticMapApiKey: '', // User's API key
      }),
    },
  },
  data() {
    return {
      localType: 'embed',
      localEmbedUrl: '',
      localAddress: '',
      localStaticMapUrl: '', // This is what we store for static maps
      localStaticMapApiKey: '',
      mapTypes: [
        { text: 'Embed Code URL (iframe src)', value: 'embed' },
        { text: 'Static Map Image (from Address)', value: 'address' },
      ],
      embedPreviewUrl: null, // Separate state for the actual embeddable URL for preview
      staticMapPreviewUrl: null, // For the <v-img src> preview
      validationError: null,
      debouncedGenerateStaticMap: null,
    }
  },
  computed: {
    displayPreview() {
      if (this.localType === 'embed') return !!this.localEmbedUrl.trim()
      if (this.localType === 'address') return !!this.localAddress.trim()
      return false
    },
  },
  watch: {
    // Sync incoming prop changes to local data
    value: {
      immediate: true,
      deep: true,
      handler(newValue) {
        // Avoid resetting local state if it's already the same (prevents loops if parent uses same object reference)
        if (
          JSON.stringify(newValue) === JSON.stringify(this.getCurrentData())
        ) {
          return
        }

        this.localType = newValue?.type || 'embed'
        this.localEmbedUrl = newValue?.embedUrl || ''
        this.localAddress = newValue?.address || ''
        this.localStaticMapUrl = newValue?.staticMapUrl || '' // This should be the value from saved data
        this.localStaticMapApiKey = newValue?.staticMapApiKey || ''

        this.updatePreviewState() // Update previews based on loaded data
      },
    },
  },
  created() {
    this.debouncedGenerateStaticMap = _.debounce(
      this.generateAndEmitStaticMapUrl,
      600
    )
  },
  mounted() {
    this.updatePreviewState() // Initial preview setup on mount
  },
  methods: {
    /**
     * Emits the current local data to the parent component.
     * This should be called when any logically complete change occurs.
     */
    emitUpdate() {
      this.$emit('input', this.getCurrentData())
    },
    /**
     * Gets the current state of data to be emitted.
     */
    getCurrentData() {
      return {
        type: this.localType,
        embedUrl: this.localType === 'embed' ? this.localEmbedUrl : '',
        address: this.localType === 'address' ? this.localAddress : '',
        staticMapUrl:
          this.localType === 'address' ? this.localStaticMapUrl : '', // Only store this if type is address
        staticMapApiKey: this.localStaticMapApiKey,
      }
    },
    onTypeChange() {
      // When type changes, reset URLs/previews and emit update
      this.localEmbedUrl = ''
      this.localAddress = ''
      this.localStaticMapUrl = ''
      this.staticMapPreviewUrl = null
      this.embedPreviewUrl = null
      this.validationError = null
      this.emitUpdate()
    },
    handleEmbedUrlChange() {
      // Validate and update preview for embed URL on blur
      this.updatePreviewState()
      this.emitUpdate() // Emit after updating the local state including potential embedPreviewUrl source for it
    },
    /**
     * Debounced method to generate static map URL and then emit update.
     */
    generateAndEmitStaticMapUrl() {
      this.generateStaticMapLogic() // This updates localStaticMapUrl & staticMapPreviewUrl
      this.emitUpdate()
    },
    /**
     * Core logic to generate the static map URL string based on localAddress and apiKey.
     * It updates localStaticMapUrl and staticMapPreviewUrl but does NOT emit directly.
     */
    generateStaticMapLogic() {
      this.staticMapPreviewUrl = null // Reset preview
      // Don't reset localStaticMapUrl here immediately if we want to clear it only when address is empty
      this.validationError = null

      if (this.localType === 'address' && this.localAddress.trim()) {
        let baseUrl = `https://maps.googleapis.com/maps/api/staticmap?center=${encodeURIComponent(
          this.localAddress
        )}&zoom=15&size=600x300&maptype=roadmap&markers=color:red%7C${encodeURIComponent(
          this.localAddress
        )}`
        if (this.localStaticMapApiKey && this.localStaticMapApiKey.trim()) {
          baseUrl += `&key=${this.localStaticMapApiKey}`
        } else {
          console.warn(
            'Static Maps API: Using without API key. Features will be limited / may not work in production.'
          )
        }
        this.localStaticMapUrl = baseUrl // Store this one
        this.staticMapPreviewUrl = baseUrl // Use for previewing
      } else {
        this.localStaticMapUrl = '' // Clear if address is empty
        // staticMapPreviewUrl will remain null from reset
      }
    },

    /**
     * Updates the relevant preview URL (embed or static) based on local data.
     */
    updatePreviewState() {
      this.validationError = null
      if (this.localType === 'embed') {
        this.staticMapPreviewUrl = null
        if (
          this.localEmbedUrl &&
          this.localEmbedUrl
            .trim()
            .startsWith('https://www.google.com/maps/embed')
        ) {
          this.embedPreviewUrl = this.localEmbedUrl
        } else if (this.localEmbedUrl && this.localEmbedUrl.trim()) {
          this.embedPreviewUrl = null // Clear preview if not a valid start
          this.validationError =
            'Invalid embed URL. Must start with "https://www.google.com/maps/embed".'
        } else {
          this.embedPreviewUrl = null
        }
      } else if (this.localType === 'address') {
        this.embedPreviewUrl = null
        this.generateStaticMapLogic() // This will update staticMapPreviewUrl and localStaticMapUrl
      }
    },
  },
}
</script>

<style scoped>
.map-preview {
  border: 1px solid #e0e0e0;
}
.theme--dark .map-preview {
  border-color: #424242;
}
.embed-responsive {
  position: relative;
  width: 100%;
  padding-top: 75%; /* 4:3 Aspect Ratio, adjust if needed for your map previews */
  overflow: hidden;
  background-color: #f0f0f0; /* Placeholder background */
}
.embed-responsive-16by9::before {
  /* More common for videos/maps if using specific aspect ratio */
  padding-top: 56.25%;
}
.embed-responsive-item {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 0;
}
.preview-card .v-subheader {
  font-size: 0.9rem;
  padding-left: 12px;
}
</style>
