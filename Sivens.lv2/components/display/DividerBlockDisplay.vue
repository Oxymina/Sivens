<template>
  <div class="divider-display-block">
    <v-divider
      v-if="currentStyle === 'hr'"
      class="my-8"
      :style="hrStyle"
    ></v-divider>

    <div
      v-else-if="currentStyle === 'dots'"
      class="my-10 text-center dot-separator"
    >
      <span class="dot"></span><span class="dot"></span
      ><span class="dot"></span>
    </div>

    <div
      v-else-if="currentStyle === 'asterisks'"
      class="my-10 text-center asterisks-separator"
    >
      * * *
    </div>

    <div
      v-else-if="currentStyle === 'wave'"
      class="my-10 text-center wave-separator"
    >
      〰️
    </div>

    <div
      v-else-if="currentStyle === 'short_line_center'"
      class="my-10 short-line-center-separator"
    >
      <div></div>
    </div>

    <!-- Fallback or if no style is matched / blockData.style is undefined -->
    <v-divider v-else class="my-8" :style="hrStyle"></v-divider>
  </div>
</template>

<script>
export default {
  name: 'DividerBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({ style: 'hr' }), // e.g., { style: 'hr', color: '#cccccc', thickness: '1px' }
    },
  },
  computed: {
    currentStyle() {
      return this.blockData?.style || 'hr' // Default to hr if style not specified
    },
    // Optional: Make HR style more configurable
    hrStyle() {
      return {
        // borderColor: this.blockData?.color || (this.$vuetify.theme.dark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.12)'),
        // borderTopWidth: this.blockData?.thickness || '1px'
      }
    },
  },
}
</script>

<style scoped>
/* Base margin applied to the .divider-display-block by BlogPostContent.vue (class="content-block") */
/* Styles here are specific to the internals of *this* component */

/* .my-8 and .my-10 classes are from Vuetify for vertical margin */

.dot-separator {
  line-height: 1;
  letter-spacing: 0.6em;
  color: var(--v-text-disabled-base, #bdbdbd);
}
.dot {
  display: inline-block;
  font-size: 1.6em;
  line-height: 0;
}
.dot::before {
  content: '\2022'; /* Bullet character • */
}
.theme--dark .dot-separator,
.theme--dark .dot {
  color: #757575;
}

.asterisks-separator {
  color: var(--v-text-disabled-base, #bdbdbd);
  font-size: 1.6em;
  letter-spacing: 0.4em;
  font-weight: normal;
}
.theme--dark .asterisks-separator {
  color: #757575;
}

.wave-separator {
  color: var(--v-text-disabled-base, #bdbdbd);
  font-size: 2em;
  font-weight: normal;
  line-height: 0.5;
  letter-spacing: -0.1em; /* Bring waves closer */
}
.theme--dark .wave-separator {
  color: #757575;
}

.short-line-center-separator {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}
.short-line-center-separator div {
  width: 80px; /* Width of the short line */
  height: 1px; /* Thickness of the line */
  /* Vuetify's default divider color handling */
  background-color: var(--v-divider-theme-dark, rgba(0, 0, 0, 0.12));
}
.theme--dark .short-line-center-separator div {
  background-color: var(--v-divider-theme-light, rgba(255, 255, 255, 0.12));
}
</style>
