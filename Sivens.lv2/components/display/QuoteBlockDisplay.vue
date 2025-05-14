<template>
  <div v-if="blockData && blockData.text" class="post-quote-block my-6">
    <blockquote class="blockquote text-h6 font-italic font-weight-regular">
      "{{ blockData.text }}"
      <footer
        v-if="blockData.attribution"
        class="text-subtitle-1 font-weight-light mt-3"
      >
        — {{ blockData.attribution }}
      </footer>
    </blockquote>
  </div>
</template>

<script>
export default {
  name: 'QuoteBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({ text: '', attribution: '' }),
    },
  },
}
</script>

<style scoped>
.post-quote-block {
  /* color: var(--v-text-secondary-base); */
  color: inherit; /* Inherit color, allow blockquote styling to take over */
}

.post-quote-block blockquote {
  border-left: 5px solid var(--v-primary-base); /* Use Vuetify primary color */
  padding-left: 1.5em;
  margin-left: 0;
  margin-right: 0;
  font-style: italic;
  /* background-color: rgba(var(--v-primary-base-rgb), 0.03); */ /* Optional subtle background */
}

.theme--dark .post-quote-block blockquote {
  /* background-color: rgba(var(--v-primary-base-rgb), 0.08); */
  border-left-color: var(--v-primary-lighten1);
}

.post-quote-block blockquote footer {
  font-style: normal; /* Override italic for the footer */
  color: var(--v-text-secondary-base);
}
</style>
