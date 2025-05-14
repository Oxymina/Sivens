<template>
  <component
    :is="listTag"
    v-if="items && items.length > 0"
    class="post-list-block my-4"
  >
    <li v-for="(item, index) in items" :key="index">
      <!--
        If list items can contain very basic HTML from a mini-WYSIWYG in the editor,
        you would need to use v-html and sanitize `item` here.
        For plain text items, direct interpolation is safer.
      -->
      {{ item }}
    </li>
  </component>
</template>

<script>
export default {
  name: 'ListBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({ style: 'unordered', items: [] }),
    },
  },
  computed: {
    listTag() {
      return this.blockData?.style === 'ordered' ? 'ol' : 'ul'
    },
    items() {
      // Filter out any potentially empty string items left by the editor
      if (Array.isArray(this.blockData?.items)) {
        return this.blockData.items.filter(
          (item) => typeof item === 'string' && item.trim() !== ''
        )
      }
      return []
    },
  },
}
</script>

<style scoped>
.post-list-block {
  font-size: 1.1rem; /* Match paragraph font size */
  line-height: 1.8; /* Match paragraph line height */
  color: var(--v-text-primary-base);
  padding-left: 2em; /* Adjust list indentation */
  margin-bottom: 1.5em;
  word-wrap: break-word;
}

.post-list-block li {
  margin-bottom: 0.6em; /* Spacing between list items */
}
</style>
