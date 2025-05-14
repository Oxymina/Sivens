<template>
  <!-- eslint-disable-next-line vue/no-v-html -->
  <div class="post-paragraph-block" v-html="finalHtmlOutput"></div>
  <!--
    Alternative for plain text only (no HTML from editor):
    <p class="post-paragraph-block">{{ blockData.text || '' }}</p>
  -->
</template>

<script>
// DOMPurify will be imported dynamically on the client-side

export default {
  name: 'ParagraphBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({ text: '' }),
    },
  },
  data() {
    return {
      // This will hold the HTML string after client-side sanitization
      clientSanitizedHtml: null,
      // A flag to indicate if client-side sanitization has occurred
      // This helps prevent re-sanitizing unnecessarily if using only `finalHtmlOutput`
      // and helps to initially show server-rendered (escaped) content.
      hasClientSanitized: false,
    }
  },
  computed: {
    /**
     * Provides the HTML to be rendered.
     * During SSR, it returns the raw text (which Vue's v-html will escape).
     * On the client, after sanitization, it returns the clientSanitizedHtml.
     */
    finalHtmlOutput() {
      if (
        process.client &&
        this.hasClientSanitized &&
        this.clientSanitizedHtml !== null
      ) {
        return this.clientSanitizedHtml
      }
      // For SSR or before client sanitization, return the raw text.
      // Vue's v-html on server or client (before our sanitize) will HTML-escape this,
      // so it's safe from XSS at this stage.
      return this.blockData?.text || ''
    },
  },
  watch: {
    // If the input blockData.text prop changes after initial mount,
    // re-sanitize it on the client side.
    'blockData.text': {
      // Don't run immediately, `mounted` will handle the initial sanitization
      immediate: false,
      handler(newText, oldText) {
        if (newText !== oldText && process.client) {
          // console.log(`Paragraph prop 'blockData.text' changed, re-sanitizing.`);
          this.doSanitize(newText)
        }
      },
    },
  },
  mounted() {
    // Perform the initial sanitization when the component mounts on the client-side.
    // This will "upgrade" the server-rendered (escaped) text to potentially rich HTML.
    // console.log(`Paragraph mounted on client. Initial text: "${this.blockData?.text}"`);
    this.doSanitize(this.blockData?.text)
  },
  methods: {
    /**
     * Asynchronously imports DOMPurify and sanitizes the provided text content.
     * This method is intended to be called only on the client-side.
     * @param {string} rawText - The raw HTML or text string to sanitize.
     */
    async doSanitize(rawText) {
      // Should already be guarded by process.client check in callers, but double-check
      if (!process.client) return

      const textToSanitize = typeof rawText === 'string' ? rawText : ''

      try {
        // Dynamically import DOMPurify to keep it out of the server bundle
        // and ensure it's only loaded when needed on the client.
        const DOMPurify = (await import('dompurify')).default

        // Configure DOMPurify:
        // USE_PROFILES: { html: true } is a good starting point for common HTML formatting.
        // You might want to be more restrictive based on what your block editor actually produces.
        const sanitized = DOMPurify.sanitize(textToSanitize, {
          USE_PROFILES: { html: true },
          // Example: Allow only specific tags and attributes
          // ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
          // ALLOWED_ATTR: ['href', 'target', 'rel', 'title'],
          // FORBID_TAGS: ['script', 'iframe', 'style'], // Ensure dangerous tags are always forbidden
          // FORBID_ATTR: ['style', 'onclick', 'onerror', 'onload'], // Ensure dangerous attributes are forbidden
        })
        this.clientSanitizedHtml = sanitized
        this.hasClientSanitized = true // Mark that sanitization has occurred
        // console.log(`Paragraph content sanitized. Raw: "${rawText}", Sanitized: "${sanitized}"`);
      } catch (error) {
        console.error(
          'Error loading or using DOMPurify in ParagraphBlockDisplay:',
          error
        )
        // Fallback in case DOMPurify fails to load or run
        // Displaying plain text version as a safe fallback (HTML tags will be visible)
        this.clientSanitizedHtml = `Error: Could not display content safely. Raw content: ${this.escapeHtml(
          textToSanitize
        )}`
        this.hasClientSanitized = true
      }
    },
    /**
     * Helper to escape HTML entities for safe display as plain text if sanitization fails.
     * Not strictly necessary if v-html's default server-side escaping is relied upon,
     * but can be used as a client-side fallback.
     */
    escapeHtml(unsafeText) {
      if (!unsafeText) return ''
      return unsafeText
        .replace(/&/g, '&')
        .replace(/</g, '<')
        .replace(/>/g, '>')
        .replace(/"/g, '')
        .replace(/'/g, "'")
    },
  },
}
</script>

<style scoped>
.post-paragraph-block {
  font-size: 1.1rem; /* Or your theme's body-1 equivalent from Vuetify for consistency */
  line-height: 1.8; /* Good for readability */
  color: var(
    --v-text-primary-base
  ); /* Uses Vuetify's theme color for primary text */
  margin-bottom: 1.25em; /* Standard paragraph spacing, adjust as needed */
  word-wrap: break-word; /* Prevent long unbroken strings from breaking layout */
  white-space: pre-wrap; /* Respects newlines from textarea if your editor saves them, and wraps text */
}

/*
  If you allow specific HTML tags via DOMPurify and want to style them.
  Use ::v-deep because v-html content is not scoped by default.
*/
.post-paragraph-block ::v-deep p {
  /* If your editor itself wraps paragraphs in <p>, this styles them. */
  /* Many rich text editors might just give you inline elements within the blockData.text */
  margin-bottom: 1em; /* Example spacing if using <p> tags within the block text */
}
.post-paragraph-block ::v-deep p:last-child {
  margin-bottom: 0; /* Avoid double margin if the whole block already has margin-bottom */
}

.post-paragraph-block ::v-deep a {
  color: var(--v-primary-base); /* Vuetify primary color for links */
  text-decoration: none; /* Personal preference, or underline */
  font-weight: 500; /* Slightly bolder links */
  transition: color 0.2s ease-in-out;
}
.post-paragraph-block ::v-deep a:hover {
  text-decoration: underline;
  color: var(--v-primary-darken1, var(--v-primary-base)); /* Darken on hover */
}

.post-paragraph-block ::v-deep strong,
.post-paragraph-block ::v-deep b {
  font-weight: bold; /* Or use Vuetify's font-weight-bold class if applied by editor */
}

.post-paragraph-block ::v-deep em,
.post-paragraph-block ::v-deep i {
  font-style: italic;
}

.post-paragraph-block ::v-deep ul,
.post-paragraph-block ::v-deep ol {
  padding-left: 1.8em; /* Indentation for lists */
  margin-top: 0.5em;
  margin-bottom: 1em;
}
.post-paragraph-block ::v-deep li {
  margin-bottom: 0.3em;
}
</style>
