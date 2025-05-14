<template>
  <component :is="headingTag" :class="['post-heading-block', headingClass]">
    {{ blockData.text }}
  </component>
</template>

<script>
export default {
  name: 'HeadingBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({ text: '', level: 2 }), // Default to H2
    },
  },
  computed: {
    level() {
      const lvl = parseInt(this.blockData?.level, 10)
      // Ensure level is within valid h1-h6 range
      if (lvl >= 1 && lvl <= 6) {
        return lvl
      }
      return 2 // Default to h2 if level is invalid or missing
    },
    headingTag() {
      return `h${this.level}`
    },
    headingClass() {
      // Apply Vuetify typography classes if desired for consistent theme styling
      // Or use custom CSS as below
      switch (this.level) {
        case 1:
          return 'text-h4 font-weight-bold' // Example: Use Vuetify text-h4 style for H1 blocks
        case 2:
          return 'text-h5 font-weight-medium' // Example: Use Vuetify text-h5 style for H2 blocks
        case 3:
          return 'text-h6 font-weight-medium'
        case 4:
          return 'text-subtitle-1 font-weight-medium'
        case 5:
          return 'text-subtitle-2 font-weight-medium'
        case 6:
          return 'text-caption font-weight-medium'
        default:
          return 'text-h5 font-weight-medium'
      }
    },
  },
}
</script>

<style scoped>
.post-heading-block {
  color: var(--v-text-primary-base);
  line-height: 1.4; /* Adjust line height for headings */
  margin-top: 2em; /* Space above heading */
  margin-bottom: 0.8em; /* Space below heading */
  word-wrap: break-word;
}

/*
  You can add specific styles for h1, h2 etc. here,
  but using Vuetify's typography classes (like in headingClass computed)
  is often preferred for theme consistency. If you do custom styling:
*/
/*
.post-heading-block.h1 { font-size: 2.2rem; font-weight: bold; }
.post-heading-block.h2 { font-size: 1.8rem; font-weight: bold; }
.post-heading-block.h3 { font-size: 1.5rem; font-weight: 500; }
.post-heading-block.h4 { font-size: 1.25rem; font-weight: 500; }
.post-heading-block.h5 { font-size: 1.1rem; font-weight: 500; }
.post-heading-block.h6 { font-size: 1rem; font-weight: 500; text-transform: uppercase; }
*/
</style>
