<template>
  <section id="intro" class="py-16">
    <v-container>
      <v-responsive class="max-auto mx-auto text-center" max-width="600">
        <v-avatar color="black" size="70" class="mb-8">
          <v-icon x-large dark>mdi-pasta</v-icon>
        </v-avatar>
        <h2 class="text-h4 text-md-h3 text-center font-weight-black mb-7">
          Discover restaurants selected by the Sivēns.lv team!
        </h2>
        <p class="title font-weight-light">
          A wide range from low-end budget restaurants to high-end restaurants
          rated by a professional team.
        </p>
      </v-responsive>

      <v-row class="pt-12">
        <v-col v-for="card in cards" :key="card.title" cols="12" md="4">
          <v-row no-gutters>
            <v-col :cols="card.callout ? 9 : 12">
              <div class="pr-2">
                <div class="text--disabled" v-text="card.subtitle"></div>
                <h4
                  class="text-uppercase mt-1 mb-4"
                  style="letter-spacing: 0.15em"
                  v-text="card.title"
                ></h4>
                <p v-text="card.text"></p>
              </div>
            </v-col>
            <v-col v-if="card.callout" cols="2">
              <span
                class="text-h3 grey--text font-weight-bold pr-8"
                style="opacity: 0.1"
                >{{ card.callout }}</span
              >
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
export default {
  data() {
    return {
      cards: [
        {
          title: 'Best Reviews',
          subtitle: 'Quality',
          text: 'A thorough and specilised rating system that gurantees quality control.',
          callout: '01',
        },
        {
          title: 'Active Team',
          subtitle: 'Quantity',
          text: 'A team that actively reviews more and more restaurants to provide you with new options!',
          callout: '02',
        },
        {
          title: 'Easy contact',
          subtitle: 'Response',
          text: 'Have a restaurant or a nice place you want us to visit? Just contact us and we will check it out!',
          callout: '03',
        },
      ],
    }
  },
}
</script>
