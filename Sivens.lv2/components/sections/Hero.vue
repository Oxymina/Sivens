<template>
  <section id="hero">
    <v-carousel
      height="calc(100vh - 64px)"
      dark
      cycle
      show-arrows-on-hover
      draggable="true"
      hide-delimiter-background
    >
      <v-carousel-item
        v-for="(carousel, carindex) in carouselsData"
        :key="carindex"
        :src="carousel.src"
        class="gradient-fill"
      >
        <v-container fill-height>
          <div style="max-width: 600px">
            <div
              class="text-md-h2 mb-3 text-sm-h3 text-h5 font-weight-black white--text"
            >
              {{ carousel.heading.toUpperCase() }}
            </div>
            <p class="mb-5 white--text">{{ carousel.subHeading }}</p>
            <v-btn
              :x-large="$vuetify.breakpoint.smAndUp"
              class="my-3 black white--text"
            >
              Read more</v-btn
            >
            <span class="mx-2 my-4"></span>
          </div>
        </v-container>
      </v-carousel-item>
    </v-carousel>
  </section>
</template>

<script>
export default {
  data() {
    return {
      carouselsData: [
        {
          src: '1619051476780.jpg',
          heading: ' We try so you can enjoy! ',
          subHeading:
            'Sivēns.lv tester team tries all sorts of restaurants and eateries to provide you - the coustumer with the best options!',
        },
        {
          src: 'McDonalds_in_Riga_center.jpg',
          heading: 'McDonalds - Ruk Ruk or Nom Nom?',
          subHeading:
            'Is Latvian McDonalds worth it? The maistro burger being the Latvian special - too pricey?',
        },
      ],
    }
  },
}
</script>

<style>
.gradient-fill .v-responsive__content {
  background: rgb(0, 0, 0);
  background: linear-gradient(
    to right,
    rgba(3, 12, 41, 0.75),
    rgba(5, 11, 31, 0.65)
  );
}
</style>
