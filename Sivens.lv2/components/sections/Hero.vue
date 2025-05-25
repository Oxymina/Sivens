<template>
  <section id="hero" class="hero-section-wrapper">
    <!-- Loading State for Carousel -->
    <div
      v-if="loadingCarousel"
      class="d-flex justify-center align-center fullscreen-child"
      :style="{ backgroundColor: $vuetify.theme.dark ? '#212121' : '#333' }"
    >
      <v-progress-circular
        indeterminate
        size="64"
        width="4"
        color="white"
      ></v-progress-circular>
      <p class="white--text mt-4 ml-4 subheading">Loading Featured Posts...</p>
    </div>

    <!-- Error State for Carousel -->
    <div
      v-else-if="carouselError"
      class="d-flex justify-center align-center pa-8 text-center fullscreen-child"
      :style="{ backgroundColor: $vuetify.theme.dark ? '#1E1E1E' : '#f5f5f5' }"
    >
      <div>
        <v-icon
          size="64"
          :color="$vuetify.theme.dark ? 'grey lighten-1' : 'grey darken-1'"
          class="mb-4"
          >mdi-alert-circle-outline</v-icon
        >
        <p
          class="text-h6"
          :class="
            $vuetify.theme.dark
              ? 'grey--text text--lighten-1'
              : 'grey--text text--darken-2'
          "
        >
          {{ carouselError }}
        </p>
        <v-btn large color="primary" class="mt-4" @click="refreshCarousel">
          <v-icon left>mdi-refresh</v-icon>Try Again
        </v-btn>
      </div>
    </div>

    <!-- Carousel Display -->
    <v-carousel
      v-else-if="carouselItems && carouselItems.length > 0"
      class="fullscreen-child hero-carousel"
      dark
      cycle
      show-arrows="hover"
      draggable="true"
      hide-delimiter-background
      delimiter-icon="mdi-circle-medium"
      interval="7000"
      :continuous="carouselItems.length > 1"
      :show-arrows-on-hover="carouselItems.length > 1"
    >
      <v-carousel-item
        v-for="item in carouselItems"
        :key="item.id"
        #default="scopedSlotProps"
        :src="item.src"
        class="gradient-fill"
      >
        <v-container fluid fill-height class="text-container-for-carousel">
          <!-- Note the 'pl-md-10 pl-lg-16 pl-4' for responsive left padding on the row -->
          <v-row
            align="center"
            justify="start"
            no-gutters
            class="fill-height pa-sm-8 pa-4 pl-md-10 pl-lg-16"
          >
            <v-col cols="12" sm="10" md="8" lg="7" xl="6">
              <div
                :class="{
                  'animate-slide-in': scopedSlotProps && scopedSlotProps.active,
                }"
                class="hero-text-content"
              >
                <h1
                  :class="[
                    $vuetify.breakpoint.mdAndUp
                      ? 'text-h2 font-weight-black'
                      : 'text-h3 font-weight-bold',
                    'mb-4 white--text hero-heading',
                  ]"
                  style="line-height: 1.2"
                >
                  {{ item.heading.toUpperCase() }}
                </h1>
                <p
                  class="mb-8 white--text text-body-1 hero-subheading"
                  style="max-width: 600px; opacity: 0.95; line-height: 1.7"
                >
                  {{ item.subHeading }}
                </p>
                <v-btn
                  x-large
                  depressed
                  class="hero-button"
                  :color="$vuetify.theme.dark ? 'white' : 'black'"
                  :to="`/ReviewPostPage/${item.id}`"
                  nuxt
                  elevation="4"
                >
                  <span
                    :class="$vuetify.theme.dark ? 'black--text' : 'white--text'"
                    >Read More</span
                  >
                  <v-icon right :color="$vuetify.theme.dark ? 'black' : 'white'"
                    >mdi-arrow-right-bold-circle-outline</v-icon
                  >
                </v-btn>
              </div>
            </v-col>
          </v-row>
        </v-container>
      </v-carousel-item>
    </v-carousel>

    <!-- Fallback if no carousel items -->
    <div
      v-else
      class="d-flex justify-center align-center pa-8 text-center fullscreen-child"
      :style="{ backgroundColor: $vuetify.theme.dark ? '#121212' : '#e0e0e0' }"
    >
      <div>
        <v-icon
          size="64"
          :color="$vuetify.theme.dark ? 'grey lighten-1' : 'grey darken-1'"
          class="mb-4"
          >mdi-image-multiple-off-outline</v-icon
        >
        <p
          class="text-h6"
          :class="
            $vuetify.theme.dark
              ? 'grey--text text--lighten-1'
              : 'grey--text text--darken-2'
          "
        >
          No Featured Posts Available
        </p>
        <p class="body-1 grey--text">Check back later for new articles!</p>
      </div>
    </div>
  </section>
</template>

<script>
// Script remains the same
export default {
  name: 'HeroCarouselSection',
  async fetch() {
    await this.loadCarouselData()
  },
  data() {
    return {
      carouselItems: [],
      loadingCarousel: true,
      carouselError: null,
    }
  },
  methods: {
    async loadCarouselData() {
      this.loadingCarousel = true
      this.carouselError = null
      try {
        const response = await this.$axios.get('/get-carousel')
        if (response.data && Array.isArray(response.data)) {
          this.carouselItems = response.data
          if (this.carouselItems.length === 0) {
            console.warn('[Hero] Carousel API returned an empty array.')
          }
        } else {
          console.error(
            '[Hero] Carousel API response is not a valid array:',
            response.data
          )
          this.carouselError =
            'Featured posts data is not in the expected format.'
          this.carouselItems = []
        }
      } catch (error) {
        const errorMessage =
          error.response?.data?.message ||
          error.message ||
          'Unknown error fetching carousel data.'
        console.error(
          '[Hero] Error fetching carousel posts:',
          errorMessage,
          error.response || error
        )
        this.carouselError = `Could not load featured posts. ${
          error.message.includes('Network Error')
            ? 'Please check your connection.'
            : 'Please try refreshing.'
        }`
        this.carouselItems = []
      } finally {
        this.loadingCarousel = false
      }
    },
    refreshCarousel() {
      this.$fetch()
    },
  },
  head() {
    return {
      title: 'Home',
      meta: [
        {
          hid: 'description',
          name: 'description',
          content:
            'Discover the latest delicious food reviews, restaurant guides, and culinary articles on Sivēns.lv. We try so you can enjoy!',
        },
      ],
    }
  },
}
</script>

<style>
body::-webkit-scrollbar {
  display: none;
}

html {
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.scrollable-element-without-scrollbar {
  overflow-y: auto;
}

.scrollable-element-without-scrollbar::-webkit-scrollbar {
  display: none;
}

.scrollable-element-without-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.hero-section-wrapper {
  width: 100%;
  height: calc(100vh - 64px);
  position: relative;
  overflow: hidden;
}

.fullscreen-child,
.hero-carousel.v-carousel.v-window {
  width: 100% !important;
  height: 100% !important;
}

.hero-carousel .v-carousel__item.v-image.v-responsive {
  height: 100% !important;
  width: 100% !important;
}

.gradient-fill .v-responsive__content {
  background: linear-gradient(
    90deg,
    rgba(0, 0, 0, 0.85) 0%,
    rgba(0, 0, 0, 0.75) 30%,
    rgba(0, 0, 0, 0.4) 60%,
    rgba(0, 0, 0, 0) 90%
  );
  display: flex;
  align-items: center;
  justify-content: flex-start;
  width: 100%;
  height: 100%;
  padding: 0 !important;
  position: relative;
  z-index: 1;
}

.hero-carousel
  .v-carousel-item
  .v-responsive__content
  .v-container.text-container-for-carousel {
  height: 100%;
  width: 100%;
  max-width: none !important;
  padding: 0 !important;
  display: flex;
  align-items: center;
}
.hero-carousel
  .v-carousel-item
  .v-responsive__content
  .v-container.text-container-for-carousel
  .v-row {
  margin: 0 !important;
}

.hero-text-content {
  position: relative;
  z-index: 2;
}
</style>
