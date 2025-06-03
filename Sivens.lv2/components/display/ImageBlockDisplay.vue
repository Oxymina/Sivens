<template>
  <figure v-if="fullImageUrl" class="post-image-block my-6 text-center">
    <v-img
      :src="fullImageUrl"
      :alt="blockData.alt || 'Blog post image'"
      :lazy-src="placeholderImage"
      max-width="100%"
      contain
      class="mx-auto rounded-lg elevation-2"
      :aspect-ratio="aspectRatio"
      style="border: 1px solid rgba(0, 0, 0, 0.05)"
    >
      <template v-slot:placeholder>
        <v-row class="fill-height ma-0" align="center" justify="center">
          <v-progress-circular
            indeterminate
            color="grey lighten-1"
          ></v-progress-circular>
        </v-row>
      </template>
      <template v-slot:error>
        <v-row
          class="fill-height ma-0 grey lighten-3"
          align="center"
          justify="center"
        >
          <v-icon color="grey darken-1" large>mdi-image-broken-variant</v-icon>
          <span class="ml-2 grey--text text--darken-1"
            >Image failed to load</span
          >
        </v-row>
      </template>
    </v-img>

    <figcaption
      v-if="blockData.caption"
      class="text-caption text--secondary mt-3 text-center font-italic"
    >
      {{ blockData.caption }}
    </figcaption>
  </figure>
  <div v-else class="my-6 text-center text--disabled">
    (Image block: No image URL provided or URL is invalid)
  </div>
</template>

<script>
export default {
  name: 'ImageBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({ url: '', caption: '', alt: '' }),
    },
  },
  data() {
    return {
      placeholderImage:
        'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
      aspectRatio: 16 / 9,
    }
  },
  computed: {
    fullImageUrl() {
      if (this.blockData && this.blockData.url) {
        const imageUrl = this.blockData.url

        if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) {
          return imageUrl
        }
        let backendBaseDomain = 'http://localhost:8000' // Hardcoded fallback
        if (
          this.$axios &&
          this.$axios.defaults &&
          this.$axios.defaults.baseURL
        ) {
          try {
            const axiosBaseUrl = new URL(this.$axios.defaults.baseURL)
            backendBaseDomain = `${axiosBaseUrl.protocol}//${axiosBaseUrl.host}`
          } catch (e) {
            console.warn(
              'ImageBlockDisplay: Could not parse $axios.defaults.baseURL to determine backend domain. Falling back to default.',
              this.$axios.defaults.baseURL,
              e
            )
          }
        } else {
          console.warn(
            'ImageBlockDisplay: $axios.defaults.baseURL is not available. Using hardcoded fallback for image path.'
          )
        }
        const relativePath = imageUrl.startsWith('/')
          ? imageUrl.substring(1)
          : imageUrl

        return `${backendBaseDomain}/storage/${relativePath}`
      }
      return null // return placeholderImage
    },
  },
}
</script>

<style scoped>
.post-image-block:last-child {
  margin-bottom: 0 !important;
}

.post-image-block .v-image {
  border-radius: 8px;
}
</style>
