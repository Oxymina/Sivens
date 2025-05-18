<template>
  <section id="timeline" class="py-16">
    <v-container>
      <h2 class="text-h3 text-center font-weight-black mb-8">Our Journey</h2>
      <template>
        <v-timeline>
          <v-timeline-item
            v-for="(year, i) in years"
            :key="i"
            :color="year.color"
            small
          >
            <template v-slot:opposite>
              <span
                :class="`headline font-weight-bold ${year.color}--text`"
                v-text="year.year"
              ></span>
            </template>
            <div class="py-4">
              <h2
                :class="`headline font-weight-light mb-4 ${year.color}--text`"
              >
                {{ year.title }}
              </h2>
              <div>
                {{ year.description }}
              </div>
            </div>
          </v-timeline-item>
        </v-timeline>
      </template>
    </v-container>
  </section>
</template>

<script>
export default {
  data() {
    return {
      years: [
        {
          color: 'teal',
          year: '2023',
          title: 'Begining of a idea',
          description:
            'The idea of a independent reviewing site for Latvian food begins.',
        },
        {
          color: 'black',
          year: '2024',
          title: 'A beta version of the site "Sivēns.lv"',
          description:
            'The first real beta site of the idea is made. While very basic and not including most features, it is a start to something.',
        },
        {
          color: 'red',
          year: '2025',
          title: 'Alpha version and first review',
          description:
            'The beta version of the site is released. First restaurant recives its review.',
        },
      ],
    }
  },
}
</script>
