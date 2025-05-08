<template>
  <section
    :class="$vuetify.theme.dark ? 'grey darken-4' : 'grey lighten-5'"
    class="pa-8"
  >
    <v-container>
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <h1 class="text-h4 font-weight-bold mb-6 text-center">
            Blog Post Editor (Test)
          </h1>

          <v-card outlined>
            <v-card-text class="pa-4 pa-md-6">
              <v-text-field
                v-model="postTitle"
                label="Post Title"
                placeholder="Enter your awesome title here"
                outlined
                dense
                hide-details="auto"
                class="mb-6 title-input"
              ></v-text-field>

              <!-- Block Editor Component -->
              <BlockEditor v-model="editorBlocks" />
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions class="pa-4">
              <v-spacer></v-spacer>
              <v-btn
                color="primary"
                large
                depressed
                :loading="isSaving"
                @click="saveContent"
              >
                <v-icon left>mdi-content-save</v-icon>
                Save Draft (Log to Console)
              </v-btn>
            </v-card-actions>
          </v-card>
          <!-- Display raw data for testing -->
          <v-card outlined class="mt-8">
            <v-card-title>Raw Block Data (for Testing)</v-card-title>
            <v-card-text>
              <pre
                style="
                  white-space: pre-wrap;
                  word-break: break-all;
                  background: #eee;
                  padding: 10px;
                  border-radius: 4px;
                "
                >{{ JSON.stringify(editorBlocks, null, 2) }}</pre
              >
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
import BlockEditor from '~/components/blogcreator/BlockEditor.vue' // Adjusted path assuming editor is in components/admin

export default {
  name: 'BlogEditorTestPage',
  components: {
    BlockEditor,
  },
  // middleware: 'auth', // Protect this page if needed
  data() {
    return {
      postTitle: '',
      // Initialize with some default blocks for testing, or empty array []
      editorBlocks: [
        { id: 'init-1', type: 'heading', data: { text: '', level: 1 } },
        { id: 'init-2', type: 'paragraph', data: { text: '' } },
      ],
      isSaving: false, // For save button loading state
    }
  },
  methods: {
    saveContent() {
      this.isSaving = true
      console.log('--- Saving Post Content ---')
      console.log('Title:', this.postTitle)
      // Usually you'd send this to your backend API
      console.log('Blocks Data:', JSON.stringify(this.editorBlocks)) // Log stringified version for easy viewing
      console.log('Blocks Array:', this.editorBlocks) // Log the actual array object

      // Simulate saving delay
      setTimeout(() => {
        this.isSaving = false
        alert('Content logged to console. Check developer tools!')
      }, 1000)

      // Replace alert and console.log with actual API call using this.$axios
      /*
      async savePostToApi() {
        this.isSaving = true;
        try {
          const payload = {
            title: this.postTitle,
            content: JSON.stringify(this.editorBlocks) // Send as JSON string
            // category_id: ... , tags: ...
          };
          await this.$axios.post('/posts', payload); // Your API endpoint
          // Handle success (e.g., redirect, show snackbar)
        } catch(error) {
          console.error("Failed to save post:", error);
          // Handle error (show message)
        } finally {
          this.isSaving = false;
        }
      }
      */
    },
  },
  head() {
    return {
      title: 'Blog Editor Test',
    }
  },
}
</script>

<style scoped>
/* Optional: Style the title input */
.title-input ::v-deep input {
  font-size: 1.5rem; /* Make title input larger */
  font-weight: bold;
}
</style>
