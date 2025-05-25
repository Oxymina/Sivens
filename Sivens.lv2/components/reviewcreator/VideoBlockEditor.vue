<template>
  <div class="video-editor">
    <v-text-field
      v-model="localUrl"
      label="Video URL (YouTube or Vimeo)"
      placeholder="https://www.youtube.com/watch?v=..."
      outlined
      dense
      hide-details="auto"
      class="mb-2"
      @input="updateData"
      @blur="validateAndEmbed"
    >
      <template v-slot:append>
        <v-progress-circular
          v-if="isValidating"
          indeterminate
          size="20"
          width="2"
        ></v-progress-circular>
      </template>
    </v-text-field>

    <v-text-field
      v-model="localCaption"
      label="Caption (optional)"
      outlined
      dense
      hide-details
      @input="updateData"
    ></v-text-field>

    <!-- Video Preview -->
    <div v-if="embedUrl" class="mt-3 video-preview">
      <iframe
        :src="embedUrl"
        width="100%"
        height="315"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
        title="Video Preview"
      ></iframe>
    </div>
    <v-alert v-if="validationError" dense text type="error" class="mt-2">
      Invalid or unsupported video URL.
    </v-alert>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Object,
      required: true,
      default: () => ({ url: '', caption: '', source: null }),
    },
  },
  data() {
    return {
      localUrl: this.value.url || '',
      localCaption: this.value.caption || '',
      localSource: this.value.source || null, // e.g., 'youtube', 'vimeo'
      isValidating: false,
      validationError: false,
      embedUrl: null, // URL for the iframe preview
    }
  },
  watch: {
    value: {
      deep: true,
      handler(newValue) {
        let changed = false
        if ((newValue?.url || '') !== this.localUrl) {
          this.localUrl = newValue?.url || ''
          changed = true
        }
        if ((newValue?.caption || '') !== this.localCaption) {
          this.localCaption = newValue?.caption || ''
          changed = true
        }
        if ((newValue?.source || null) !== this.localSource) {
          this.localSource = newValue?.source || null
          changed = true
        }
        if (changed) {
          this.generateEmbedUrl() // Re-check embed if URL changes externally
        }
      },
    },
  },
  mounted() {
    // Generate embed URL on load if valid URL exists
    this.generateEmbedUrl()
  },
  methods: {
    updateData() {
      // Update parent with latest input values
      this.$emit('input', {
        url: this.localUrl,
        caption: this.localCaption,
        source: this.localSource, // Source might be updated in validateAndEmbed
      })
    },
    validateAndEmbed() {
      this.generateEmbedUrl()
      // We update the source in generateEmbedUrl, trigger final data update
      this.updateData()
    },
    generateEmbedUrl() {
      this.validationError = false
      this.embedUrl = null
      this.localSource = null

      if (!this.localUrl || !this.localUrl.trim()) {
        return // No URL, nothing to embed
      }

      // YouTube URL variations
      const youtubeRegex =
        /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/
      const youtubeMatch = this.localUrl.match(youtubeRegex)
      if (youtubeMatch && youtubeMatch[1]) {
        this.embedUrl = `https://www.youtube.com/embed/${youtubeMatch[1]}`
        this.localSource = 'youtube'
        // console.log("YouTube URL detected:", this.embedUrl);
        return
      }

      // Vimeo URL variations
      const vimeoRegex =
        /(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(?:video\/)?(\d+)/
      const vimeoMatch = this.localUrl.match(vimeoRegex)
      if (vimeoMatch && vimeoMatch[1]) {
        this.embedUrl = `https://player.vimeo.com/video/${vimeoMatch[1]}`
        this.localSource = 'vimeo'
        // console.log("Vimeo URL detected:", this.embedUrl);
        return
      }

      // If no match
      console.warn("URL doesn't match YouTube or Vimeo pattern:", this.localUrl)
      this.validationError = true
    },
  },
}
</script>

<style scoped>
.video-preview {
  max-width: 560px; /* Limit preview width */
  margin-left: auto;
  margin-right: auto;
  border: 1px solid #eee;
}
.theme--dark .video-preview {
  border: 1px solid #444;
}
</style>
