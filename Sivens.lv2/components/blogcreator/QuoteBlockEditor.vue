<template>
  <div class="quote-editor">
    <v-card outlined class="pa-3 grey lighten-5">
      <blockquote class="blockquote-editor-content">
        <v-textarea
          v-model="localText"
          placeholder="Quote text..."
          :rows="initialQuoteRows"
          auto-grow
          flat
          solo
          hide-details
          background-color="transparent"
          class="quote-text-input body-1 font-italic"
          @input="updateData"
        ></v-textarea>
      </blockquote>
      <v-text-field
        v-model="localAttribution"
        placeholder="Attribution (optional)"
        solo
        flat
        dense
        hide-details
        class="attribution-input mt-2 text-right caption"
        @input="updateData"
      ></v-text-field>
    </v-card>
  </div>
</template>

<script>
export default {
  props: {
    // Receives the 'data' object, e.g., { text: 'Be yourself...', attribution: 'Oscar Wilde' }
    value: {
      type: Object,
      required: true,
      default: () => ({ text: '', attribution: '' }),
    },
  },
  data() {
    return {
      // Local copies for editing
      localText: this.value.text || '',
      localAttribution: this.value.attribution || '',
      initialQuoteRows: 2, // Starting rows for the quote textarea
    }
  },
  watch: {
    // Sync external changes to local state
    value: {
      deep: true, // Watch both 'text' and 'attribution'
      handler(newValue) {
        const newText = newValue?.text || ''
        const newAttribution = newValue?.attribution || ''
        let changed = false
        if (newText !== this.localText) {
          this.localText = newText
          changed = true
        }
        if (newAttribution !== this.localAttribution) {
          this.localAttribution = newAttribution
          changed = true
          if (changed) {
            console.log(
              'QuoteBlockEditor: Local state updated due to prop change.'
            )
          }
        }
        // Only log if something actually changed if needed for debugging
        // if (changed) { console.log(`Quote ${this.$vnode.key}: Prop updated`); }
      },
    },
  },
  methods: {
    /**
     * Emits the updated data object back to the parent (BlockEditor).
     */
    updateData() {
      this.$emit('input', {
        text: this.localText,
        attribution: this.localAttribution,
      })
    },
  },
}
</script>

<style scoped>
.quote-editor .v-card {
  /* Theme specific background */
  background-color: var(
    --v-background-lighten1,
    #fafafa
  ) !important; /* Adjust lightness if needed */
}
.theme--dark .quote-editor .v-card {
  background-color: var(--v-background-lighten1, #424242) !important;
}

.blockquote-editor-content {
  border-left: 4px solid var(--v-primary-base, #1976d2); /* Use Vuetify primary color */
  padding-left: 16px;
  margin: 0; /* Remove default blockquote margin */
}

/* Target the internal textarea of v-textarea */
::v-deep .quote-text-input textarea {
  line-height: 1.7 !important;
  padding-top: 4px !important; /* Adjust padding */
  padding-bottom: 4px !important;
  color: var(--v-text-primary-base);
}
.theme--dark ::v-deep .quote-text-input textarea {
  color: var(--v-text-primary-base); /* Ensure dark theme text color */
}

::v-deep .attribution-input input {
  text-align: right; /* Align attribution to the right */
  font-style: normal; /* Override italic if needed for attribution */
  color: var(--v-text-secondary-base); /* Secondary text color */
}
.theme--dark ::v-deep .attribution-input input {
  color: var(--v-text-secondary-base);
}

/* Remove extra padding/margin from solo/flat fields if necessary */
.v-text-field.v-text-field--solo.v-input--dense > .v-input__control,
.v-text-field.v-text-field--solo.v-input--dense
  > .v-input__control
  > .v-input__slot,
.v-textarea.v-text-field--solo.v-input--dense > .v-input__control,
.v-textarea.v-text-field--solo.v-input--dense
  > .v-input__control
  > .v-input__slot {
  padding: 0 !important;
  margin-bottom: 0 !important;
}
</style>
