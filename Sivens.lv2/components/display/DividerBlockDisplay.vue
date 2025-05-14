<template>
  <div class="divider-block-display">
    <v-divider v-if="isHrStyle" class="my-8"></v-divider>
    <div v-else-if="isDotsStyle" class="my-10 text-center dot-separator">
      <span class="dot"></span>
      <span class="dot"></span>
      <span class="dot"></span>
    </div>
    <!-- Optional: Handle custom spacer style if implemented -->
    <!-- <div v-else-if="blockData.style === 'spacer'" :style="{ height: blockData.height || '20px' }"></div> -->
    <div v-else class="my-4 text--disabled text-center">(Unknown Divider)</div>
  </div>
</template>

<script>
export default {
  name: 'DividerBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({ style: 'hr' }), // 'hr', 'dots', 'spacer' etc.
    },
  },
  computed: {
    isHrStyle() {
      return this.blockData?.style === 'hr'
    },
    isDotsStyle() {
      return this.blockData?.style === 'dots'
    },
  },
}
</script>

<style scoped>
.dot-separator {
  line-height: 1;
  letter-spacing: 0.5em; /* Space out the dots */
  color: var(--v-text-disabled-base, #bdbdbd); /* Vuetify disabled color */
}

.dot {
  display: inline-block;
  font-size: 1.5em; /* Make dots larger */
  /* Use a character that looks like a dot or a custom SVG */
}
.dot::before {
  content: '\2022'; /* Bullet character • */
}

/* More pronounced v-divider if desired */
/*
.divider-block-display .v-divider {
  border-top-width: 2px !important;
  border-color: rgba(0,0,0,0.15) !important;
}
.theme--dark .divider-block-display .v-divider {
  border-color: rgba(255,255,255,0.15) !important;
}
*/
</style>
