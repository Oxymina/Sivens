<template>
  <div class="divider-editor text-center">
    <v-divider v-if="localStyle === 'hr'" class="my-2"></v-divider>
    <div v-else-if="localStyle === 'dots'" class="my-4 dot-separator">
      <span class="dot"></span><span class="dot"></span
      ><span class="dot"></span>
    </div>
    <div v-else class="text-caption text--disabled">
      (Unknown divider style)
    </div>
    <!-- No controls needed - the type defines the style -->
  </div>
</template>

<script>
export default {
  props: {
    value: { type: Object, required: true, default: () => ({ style: 'hr' }) },
    // Block ID is available but not used here
    // blockId: { type: [String, Number], required: true }
  },
  computed: {
    // Get style, default to 'hr'
    localStyle() {
      return this.value?.style || 'hr'
    },
  },
  // No data, watch, or methods needed as it's display-only based on type via BlockEditor map
}
</script>

<style scoped>
.dot-separator {
  line-height: 1; /* Ensure dots stay aligned */
}
.dot {
  display: inline-block;
  width: 6px;
  height: 6px;
  background-color: #bdbdbd; /* Default dot color */
  border-radius: 50%;
  margin: 0 4px;
}
.theme--dark .dot {
  background-color: #757575;
}
</style>
