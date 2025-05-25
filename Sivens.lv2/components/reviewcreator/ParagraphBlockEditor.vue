<template>
  <div class="paragraph-editor">
    <v-textarea
      v-model="localText"
      placeholder="Start typing your paragraph..."
      :rows="initialRows"
      auto-grow
      outlined
      dense
      hide-details="auto"
      class="paragraph-input body-1"
      @input="updateData"
    ></v-textarea>
    <!--
          Alternative: Contenteditable div for basic rich text.
          Requires more complex handling of input events, selections, and sanitization.
          <div
              ref="editorDiv"
              contenteditable="true"
              class="paragraph-input body-1 contenteditable-div pa-2"
              @input="handleContentEditableInput"
              @blur="handleContentEditableBlur"
              v-html="localText">
           </div>
      -->
  </div>
</template>

<script>
export default {
  props: {
    // Receives the 'data' object for this block, e.g., { text: 'Initial content' }
    value: {
      type: Object,
      required: true,
      default: () => ({ text: '' }),
    },
  },
  data() {
    return {
      // Local copy of the text for the v-textarea
      localText: this.value.text || '',
      initialRows: 3, // Starting rows for textarea
    }
  },
  watch: {
    // If the parent component forces an update (e.g., loading saved data),
    // update the local state.
    value: {
      deep: true, // Watch the 'text' property within the value object
      handler(newValue) {
        const newText = newValue?.text || ''
        // Avoid infinite loops by checking if update is necessary
        if (newText !== this.localText) {
          // console.log(`Paragraph ${this.$vnode.key}: Prop updated, setting localText`);
          this.localText = newText
        }
      },
    },
  },
  methods: {
    /**
     * Emits the updated data object back to the parent (BlockEditor).
     * Triggered on @input from v-textarea.
     */
    updateData() {
      // console.log(`Paragraph ${this.$vnode.key}: Emitting input with text:`, this.localText);
      // Emit the full data structure expected by the parent BlockEditor
      this.$emit('input', { text: this.localText })
    },

    /* --- Optional: Methods for Contenteditable approach ---
         handleContentEditableInput(event) {
           // Very basic handling - gets the innerHTML
           // Need robust sanitization here before updating localText / emitting
           const newHtml = event.target.innerHTML;
           // Be VERY careful directly using innerHTML - risk of XSS if not sanitized
           this.localText = newHtml; // Or sanitize first
           this.updateData(); // Emit after sanitization
         },
         handleContentEditableBlur(event) {
            // Could trigger final update on blur
             this.handleContentEditableInput(event);
         },
        --- End Optional --- */
  },
}
</script>

<style scoped>
.paragraph-input {
  width: 100%;
  line-height: 1.7; /* Good readability */
  /* font-family: 'Your Font', sans-serif; /* Set your base font */
}

/* Styling for the v-textarea's internal input field */
/* You might need ::v-deep depending on component structure */
::v-deep .v-textarea textarea {
  padding-top: 10px; /* Adjust internal padding if needed */
  padding-bottom: 10px;
}

/* Styling for optional contenteditable approach */
.contenteditable-div {
  min-height: 60px;
  border: 1px solid #e0e0e0; /* Light border */
  border-radius: 4px;
  background-color: white; /* Or theme background */
  line-height: 1.7;
  outline: none; /* Remove default focus outline */
  transition: border-color 0.2s ease-in-out;
}
.theme--dark .contenteditable-div {
  background-color: #333;
  border: 1px solid #555;
}
.contenteditable-div:focus {
  border-color: var(--v-primary-base); /* Use Vuetify primary color on focus */
  box-shadow: 0 0 0 1px var(--v-primary-base); /* Optional subtle glow */
}
</style>
