<template>
  <figure
    v-if="blockData && blockData.url"
    class="post-image-block my-6 text-center"
  >
    <v-img
      :src="blockData.url"
      :alt="blockData.alt || 'Review post image'"
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
    (Image block: No image URL provided)
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
      // A generic placeholder for lazy-src
      placeholderImage:
        'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', // 1x1 transparent gif
      aspectRatio: 16 / 9, // Default aspect ratio, can be made dynamic if data supports it
    }
  },
  // You could add logic here to determine aspectRatio based on image dimensions if available
  // e.g., in a mounted hook after the image loads, but it's complex.
  // Or your backend could provide image dimensions.
}
</script>

<style scoped>
/*.post-image-block {*/
/* No specific styles needed if Vuetify classes are sufficient */
/* max-width: 750px; */ /* Example: if you want to constrain max image width within the block */
/* margin-left: auto; */
/* margin-right: auto; */
/*}*/
/* For consistent bottom margin */
.post-image-block:last-child {
  margin-bottom: 0 !important;
}
</style>
