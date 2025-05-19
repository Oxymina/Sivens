<template>
  <div class="star-rating-editor pa-2">
    <v-row align="center" no-gutters>
      <v-col cols="12" sm="8">
        <v-rating
          v-model="localRating"
          color="yellow darken-3"
          background-color="grey lighten-1"
          empty-icon="mdi-star-outline"
          full-icon="mdi-star"
          half-icon="mdi-star-half-full"
          hover
          half-increments
          length="5"
          :size="iconSize"
          @input="updateData"
        ></v-rating>
      </v-col>
      <v-col cols="12" sm="4" class="pl-sm-3">
        <v-text-field
          v-model.number="localRating"
          type="number"
          label="Rating"
          dense
          outlined
          hide-details
          min="0"
          :max="localMaxRating"
          step="0.5"
          @input="updateData"
        ></v-text-field>
      </v-col>
    </v-row>
    <v-text-field
      v-model="localLabel"
      label="Optional Label (e.g., Ambiance, Service)"
      dense
      outlined
      hide-details="auto"
      class="mt-3"
      @input="updateData"
    ></v-text-field>
    <!-- Max Rating could be configurable too -->
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Object,
      required: true,
      default: () => ({ rating: 3, maxRating: 5, label: '' }),
    },
  },
  data() {
    return {
      localRating: parseFloat(this.value.rating) || 3,
      localMaxRating: parseInt(this.value.maxRating) || 5,
      localLabel: this.value.label || '',
    }
  },
  computed: {
    iconSize() {
      return this.$vuetify.breakpoint.xsOnly ? 32 : 40
    },
  },
  watch: {
    value: {
      deep: true,
      handler(newValue) {
        this.localRating = parseFloat(newValue?.rating) || 3
        this.localMaxRating = parseInt(newValue?.maxRating) || 5
        this.localLabel = newValue?.label || ''
      },
    },
  },
  methods: {
    updateData() {
      this.$emit('input', {
        rating: parseFloat(this.localRating),
        maxRating: parseInt(this.localMaxRating),
        label: this.localLabel,
      })
    },
  },
}
</script>
