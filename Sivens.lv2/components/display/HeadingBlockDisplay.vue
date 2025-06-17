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
      default: () => ({ text: '', level: 2 }),
    },
  },
  computed: {
    level() {
      const lvl = parseInt(this.blockData?.level, 10)
      if (lvl >= 1 && lvl <= 6) {
        return lvl
      }
      return 2
    },
    headingTag() {
      return `h${this.level}`
    },
    headingClass() {
      switch (this.level) {
        case 1:
          return 'text-h4 font-weight-bold'
        case 2:
          return 'text-h5 font-weight-medium'
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
  line-height: 1.4;
  margin-top: 2em;
  margin-bottom: 0.8em;
  word-wrap: break-word;
}
</style>
