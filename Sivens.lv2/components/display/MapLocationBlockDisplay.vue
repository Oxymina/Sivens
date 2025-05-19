<template>
  <div class="map-location-display-block my-6 text-center">
    <!-- Embedded Map/Street View -->
    <div
      v-if="blockData.type === 'embed' && blockData.embedUrl"
      class="embed-responsive embed-responsive-16by9 mx-auto elevation-2 rounded"
    >
      <iframe
        :src="blockData.embedUrl"
        class="embed-responsive-item"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
        loading="lazy"
        :title="blockData.address || 'Embedded Map'"
      ></iframe>
    </div>

    <!-- Static Map Image -->
    <v-img
      v-else-if="blockData.type === 'address' && blockData.staticMapUrl"
      :src="blockData.staticMapUrl"
      :alt="blockData.address || 'Map Location'"
      max-width="100%"
      contain
      class="mx-auto rounded-lg elevation-2"
      aspect-ratio="1.77"
      height="300px"
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
          <v-icon color="grey darken-1" large
            >mdi-map-marker-off-outline</v-icon
          >
          <span class="ml-2 grey--text text--darken-1"
            >Map image unavailable</span
          >
        </v-row>
      </template>
    </v-img>

    <!-- Fallback if data is incomplete -->
    <div v-else class="text-caption text--disabled">
      (Map location data is incomplete or not specified)
    </div>

    <!-- Display address if available and type is address-based (even if static map fails) -->
    <div v-if="blockData.address" class="text-body-2 text--secondary mt-3">
      <v-icon small left>mdi-map-marker</v-icon>
      {{ blockData.address }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'MapLocationBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({
        type: 'embed',
        embedUrl: '',
        address: '',
        staticMapUrl: '',
      }),
    },
  },
}
</script>

<style scoped>
/* Styles for responsive iframe copied from VideoBlockDisplay example */
.embed-responsive {
  position: relative;
  display: block;
  width: 100%;
  padding: 0;
  overflow: hidden;
  max-width: 720px; /* Or desired max width for maps */
  background-color: #e0e0e0; /* Placeholder while iframe loads */
}
.embed-responsive::before {
  content: '';
  display: block;
}
.embed-responsive-16by9::before {
  padding-top: 56.25%; /* 16:9 Aspect Ratio for typical map embeds */
}
/* Adjust aspect ratio for square-ish maps if preferred for 'map_view' type */
.embed-responsive-1by1::before {
  padding-top: 100%; /* 1:1 Aspect Ratio */
}
.embed-responsive .embed-responsive-item {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 0;
}
.post-map-block figcaption,
.map-location-display-block > div:last-child {
  /* Style for address display */
  max-width: 720px;
  margin-left: auto;
  margin-right: auto;
}
</style>
