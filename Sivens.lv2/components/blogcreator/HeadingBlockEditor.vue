<template>
  <div class="d-flex align-center">
    <v-select
      v-model="localLevel"
      :items="headingLevels"
      label="Level"
      dense
      outlined
      hide-details
      class="mr-2 flex-shrink-0"
      style="max-width: 100px"
      @change="updateData"
    ></v-select>
    <v-text-field
      v-model="localText"
      placeholder="Heading text..."
      :class="`text-h${localLevel}`"
      class="heading-input flex-grow-1"
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
      localLevel: this.value.level || 2,
      headingLevels: [1, 2, 3, 4, 5, 6],
    }
  },
  watch: {
    value: {
      deep: true,
      handler(newValue) {
        if (newValue.text !== this.localText)
          this.localText = newValue.text || ''
        if (newValue.level !== this.localLevel)
          this.localLevel = newValue.level || 2
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

<style scoped>
.heading-input input {
  font-weight: bold; /* Match heading styles */
}
/* Add styles for h1, h2 etc. using v-bind(localLevel) or dynamic classes if needed */
.text-h1 {
  font-size: 2.5rem;
}
.text-h2 {
  font-size: 2rem;
}
.text-h3 {
  font-size: 1.75rem;
}
.text-h4 {
  font-size: 1.5rem;
}
/* ...etc */
</style>
