<template>
  <div class="d-flex align-center">
    <v-select
      v-model="localLevel"
      :items="headingLevels"
      label="H"
      dense
      outlined
      hide-details
      class="mr-2 heading-level-select flex-shrink-0"
      @change="updateData"
    ></v-select>
    <v-text-field
      v-model="localText"
      placeholder="Heading text..."
      class="heading-input flex-grow-1"
      :style="headingInputStyle"
      hide-details
      solo
      flat
      @input="updateData"
    ></v-text-field>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Object,
      required: true,
      default: () => ({ text: '', level: 2 }),
    },
  },
  data() {
    return {
      localText: this.value.text || '',
      localLevel: parseInt(this.value.level) || 2, // Ensure number
      headingLevels: [
        { text: 'H1', value: 1 },
        { text: 'H2', value: 2 },
        { text: 'H3', value: 3 },
        { text: 'H4', value: 4 },
        { text: 'H5', value: 5 },
        { text: 'H6', value: 6 },
      ],
    }
  },
  computed: {
    headingInputStyle() {
      // These should match or be derived from your Vuetify theme's heading styles
      // You can inspect elements in browser to get these values if unsure.
      const styles = {
        1: { fontSize: '2.125rem', fontWeight: 700, lineHeight: '2.5rem' }, // Vuetify text-h1 (approx)
        2: { fontSize: '1.875rem', fontWeight: 700, lineHeight: '2.25rem' }, // Vuetify text-h2 (approx)
        3: { fontSize: '1.5rem', fontWeight: 600, lineHeight: '2rem' }, // Vuetify text-h3 (approx)
        4: { fontSize: '1.25rem', fontWeight: 600, lineHeight: '1.75rem' }, // Vuetify text-h4 (approx)
        5: { fontSize: '1.125rem', fontWeight: 500, lineHeight: '1.5rem' }, // Vuetify text-h5 (approx)
        6: { fontSize: '1rem', fontWeight: 500, lineHeight: '1.375rem' }, // Vuetify text-h6 (approx)
      }
      const selectedStyle = styles[this.localLevel] || styles[2] // Default to H2 style
      return {
        'font-size': selectedStyle.fontSize + ' !important',
        'font-weight': selectedStyle.fontWeight + ' !important',
        'line-height': selectedStyle.lineHeight + ' !important',
        // letterSpacing might also be part of Vuetify heading styles
      }
    },
  },
  watch: {
    value: {
      deep: true,
      immediate: true, // Initialize on mount
      handler(newValue) {
        const newText = newValue?.text || ''
        const newLevel = parseInt(newValue?.level) || 2
        if (newText !== this.localText) this.localText = newText
        if (newLevel !== this.localLevel) this.localLevel = newLevel
      },
    },
  },
  methods: {
    updateData() {
      this.$emit('input', {
        text: this.localText,
        level: parseInt(this.localLevel) || 2,
      })
    },
  },
}
</script>

<style scoped lang="scss">
.heading-level-select {
  max-width: 85px; // Adjust for "H1" label
  flex-shrink: 0;
}

/*
  Target the actual input element within v-text-field.
  Using ::v-deep is necessary because the input is a child component of v-text-field.
*/
.heading-input ::v-deep input {
  padding-top: 8px !important; // Adjust padding as needed for visual balance
  padding-bottom: 8px !important;
}

/* The dynamic :style binding is usually more effective for inputs than these classes */
/*
.heading-input.text-h1 ::v-deep input { font-size: 2.5rem !important; font-weight: bold !important; }
.heading-input.text-h2 ::v-deep input { font-size: 2rem !important; font-weight: bold !important; }
// ... and so on
*/
</style>
