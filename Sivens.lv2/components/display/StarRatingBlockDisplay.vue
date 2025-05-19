<template>
  <div class="star-rating-display-block my-4">
    <div
      v-if="blockData.label && blockData.label.trim()"
      class="text-subtitle-1 font-weight-medium mb-2"
    >
      {{ blockData.label }}
    </div>
    <div class="d-flex align-center">
      <v-rating
        :value="ratingValue"
        :length="maxRatingValue"
        color="amber"
        background-color="grey lighten-2"
        empty-icon="mdi-star-outline"
        full-icon="mdi-star"
        half-icon="mdi-star-half-full"
        half-increments
        readonly
        dense
        :size="iconSize"
      ></v-rating>
      <span
        v-if="showRatingNumber"
        class="ml-3 text-subtitle-1 font-weight-medium text--secondary"
      >
        ({{ ratingValue.toFixed(1) }} / {{ maxRatingValue }})
      </span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StarRatingBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({ rating: 0, maxRating: 5, label: '' }),
    },
    showRatingNumber: {
      // Optional prop to show "4.5 / 5"
      type: Boolean,
      default: true,
    },
  },
  computed: {
    ratingValue() {
      const val = parseFloat(this.blockData?.rating)
      return isNaN(val) ? 0 : Math.max(0, Math.min(val, this.maxRatingValue)) // Ensure within bounds
    },
    maxRatingValue() {
      const val = parseInt(this.blockData?.maxRating, 10)
      return isNaN(val) || val <= 0 ? 5 : val // Default to 5 if invalid
    },
    iconSize() {
      // Adjust icon size based on context or props if needed
      return this.$vuetify.breakpoint.xsOnly ? 28 : 32
    },
  },
}
</script>

<style scoped></style>
