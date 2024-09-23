<template>
  <section
    id="sponsors"
    :class="$vuetify.theme.dark ? 'grey darken-4' : 'grey lighten-4'"
  >
    <v-container>
      <v-row>
        <v-col cols="12" sm="4" align-self="center">
          <h3
            :class="$vuetify.theme.dark ? 'grey--text' : null"
            class="text-h4 text-center font-weight-light mb-xs-4"
          >
            OUR SPONSORS
          </h3>
        </v-col>
        <v-col cols="12" sm="8">
          <v-slide-group show-arrows draggable="true">
            <template v-for="(l, ii) in brandsLogo">
              <v-slide-item :key="l" v-model="brandsLogo">
                <v-card
                  :class="$vuetify.theme.dark ? 'white' : 'grey lighten-4'"
                  class="mx-4 pa-0"
                  height="90"
                  width="140"
                  flat
                >
                  <v-img height="90" contain :src="`/brands/${l}`"></v-img>
                </v-card>
              </v-slide-item>
              <v-responsive
                v-if="ii < brandsLogo.length - 1"
                :key="ii"
                height="50"
                class="my-auto"
              >
                <v-divider vertical></v-divider>
              </v-responsive>
            </template>
          </v-slide-group>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
export default {
  data() {
    return {
      brandsLogo: [
        'Google_2015_logo.svg.webp',
        'liels-noklusejuma-logotips_3_0.png',
        'SHIELDpng-e1712144852524.png',
        'borisa-un-inaras1-300x138.png',
        'cropped-RVT-100-gadi-logo-e1616052812909.png',
        'Latvia_First_1!_logo.png',
        'indian-army-logo-9D07F8822D-seeklogo.com.png',
        'cat.png',
      ],
    }
  },
}
</script>
