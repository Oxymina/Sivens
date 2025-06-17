<template>
  <section
    :class="$vuetify.theme.dark ? 'grey darken-4' : 'grey lighten-5'"
    class="pa-4 pa-md-8"
  >
    <v-container>
      <v-row justify="center">
        <v-col cols="12" md="10" lg="9">
          <h1 class="text-h4 font-weight-bold mb-8 text-center">
            Create New Review
          </h1>

          <v-card outlined>
            <v-form ref="postForm" v-model="formValid">
              <v-card-text class="pa-4 pa-md-6">
                <v-text-field
                  v-model="post.title"
                  label="Post Title"
                  placeholder="Enter your awesome title here"
                  :rules="[rules.required, rules.maxLength(255)]"
                  required
                  outlined
                  dense
                  class="mb-6 title-input"
                  counter="255"
                ></v-text-field>

                <v-select
                  v-model="post.category_id"
                  :items="categories"
                  item-text="name"
                  item-value="id"
                  label="Category"
                  :rules="[rules.required]"
                  required
                  outlined
                  dense
                  class="mb-6"
                  :loading="loadingCategories"
                ></v-select>

                <v-combobox
                  v-model="post.tags"
                  :items="availableTags"
                  label="Tags (add new or select existing)"
                  multiple
                  chips
                  deletable-chips
                  outlined
                  class="mb-6"
                  hint="Press enter to create a new tag"
                  persistent-hint
                ></v-combobox>

                <v-text-field
                  v-model="post.post_image"
                  label="Featured Image URL (optional)"
                  placeholder="https://example.com/image.jpg"
                  outlined
                  dense
                  class="mb-6"
                ></v-text-field>

                <h3 class="subtitle-1 font-weight-medium mb-3">
                  Content Blocks
                </h3>
                <BlockEditor v-model="post.content_blocks" />
                <div
                  v-if="contentError"
                  class="v-messages theme--light error--text mt-2"
                  role="alert"
                >
                  <div class="v-messages__wrapper">
                    <div class="v-messages__message">{{ contentError }}</div>
                  </div>
                </div>
              </v-card-text>

              <v-divider></v-divider>

              <v-card-actions class="pa-4">
                <v-btn text to="/reviews">Cancel</v-btn>
                <v-spacer></v-spacer>
                <v-btn
                  color="primary"
                  large
                  depressed
                  :loading="isSaving"
                  :disabled="!formValid"
                  @click="submitNewPost"
                >
                  <v-icon left>mdi-publish</v-icon>
                  Publish Post
                </v-btn>
              </v-card-actions>
            </v-form>
          </v-card>
        </v-col>
      </v-row>
      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        bottom
        :timeout="4000"
      >
        {{ snackbar.text }}
        <template v-slot:action="{ attrs }">
          <v-btn text v-bind="attrs" @click="snackbar.show = false"
            >Close</v-btn
          >
        </template>
      </v-snackbar>
    </v-container>
  </section>
</template>

<script>
import { mapGetters } from 'vuex'
import BlockEditor from '~/components/reviewcreator/BlockEditor.vue'

export default {
  name: 'CreateReviewPage',
  components: {
    BlockEditor,
  },
  middleware: 'writer',
  data() {
    return {
      formValid: false,
      post: {
        title: '',
        category_id: null,
        post_image: '',
        content_blocks: [],
        tags: [],
      },
      categories: [],
      availableTags: [],
      loadingCategories: false,
      isSaving: false,
      contentError: null,
      snackbar: { show: false, text: '', color: '' },

      rules: {
        required: (v) => !!v || 'This field is required.',
        maxLength: (len) => (v) =>
          (v && v.length <= len) || `Max ${len} characters.`,
      },
    }
  },
  computed: {
    ...mapGetters('auth', ['isWriter', 'isAdmin', 'getUser']), // Assuming you need getUser for author_id
  },
  async mounted() {
    // Check permissions client-side too for immediate UX
    if (!this.isWriter && !this.isAdmin) {
      this.$nuxt.error({
        statusCode: 403,
        message: 'You do not have permission to create posts.',
      })
    }
    await this.fetchCategoriesForSelect()
    await this.fetchTagsForSelect()
  },
  methods: {
    generateId() {
      // Simple ID generator, use uuid if available
      return Date.now().toString(36) + Math.random().toString(36).substring(2)
    },
    async fetchCategoriesForSelect() {
      this.loadingCategories = true
      try {
        const response = await this.$axios.get('/categories') // Public endpoint to get categories
        // Assuming response.data is an array of categories [{id: 1, name: 'Tech'}, ...]
        // If it's paginated, you'd need to get response.data.data or fetch all
        this.categories = response.data.data || response.data // Adapt if paginated
      } catch (error) {
        console.error('Failed to fetch categories:', error)
        this.showSnackbar('Could not load categories.', 'error')
      } finally {
        this.loadingCategories = false
      }
    },
    async fetchTagsForSelect() {
      this.loadingTags = true
      try {
        const response = await this.$axios.get('/tags')
        this.availableTags = response.data.map((tag) => tag.name)
      } catch (error) {
        console.error('Failed to fetch tags:', error)
        this.showSnackbar('Could not load existing tags.', 'error')
      } finally {
        this.loadingTags = false
      }
    },
    validateContentBlocks() {
      this.contentError = null
      const nonEmptyBlocks = this.post.content_blocks.filter((block) => {
        if (!block.data) return false
        if (
          (block.type === 'paragraph' ||
            block.type === 'heading' ||
            block.type === 'quote') &&
          (!block.data.text || !block.data.text.trim())
        )
          return false
        if (block.type === 'image' && !block.data.url) return false
        if (block.type === 'video' && !block.data.url) return false
        if (
          block.type === 'list' &&
          (!block.data.items ||
            block.data.items.every(
              (item) => !(typeof item === 'string' && item.trim())
            ))
        )
          return false
        return true
      })

      if (nonEmptyBlocks.length === 0) {
        this.contentError =
          'Post content cannot be empty. Please add some content blocks.'
        return false
      }
      return true
    },
    async submitNewPost() {
      if (!this.$refs.postForm.validate() || !this.validateContentBlocks()) {
        this.showSnackbar('Please correct the errors in the form.', 'warning')
        return
      }

      this.isSaving = true
      try {
        const payload = {
          title: this.post.title,
          category_id: this.post.category_id,
          post_image: this.post.post_image || null,
          tags: this.post.tags,
          // Content is now the array of blocks, stringify for backend
          content: JSON.stringify(
            this.post.content_blocks.filter((block) => {
              // Filter out empty/default blocks
              if (!block.data) return false
              if (
                (block.type === 'paragraph' ||
                  block.type === 'heading' ||
                  block.type === 'quote') &&
                (!block.data.text || !block.data.text.trim())
              )
                return false
              if (block.type === 'image' && !block.data.url) return false
              if (block.type === 'video' && !block.data.url) return false
              if (
                block.type === 'list' &&
                (!block.data.items ||
                  block.data.items.every(
                    (item) => !(typeof item === 'string' && item.trim())
                  ))
              )
                return false
              return true
            })
          ),
          // author_id will be set by the backend based on authenticated user
        }

        // API call to POST /api/posts
        const response = await this.$axios.post('/posts', payload)

        this.showSnackbar('Post created successfully!', 'success')
        this.$router.push(`/ReviewPostPage/${response.data.id}`) // Assuming response returns created post with id
      } catch (error) {
        console.error('Error creating post:', error)
        this.showSnackbar(
          error.response?.data?.message ||
            error.response?.data?.errors ||
            'Failed to create post.',
          'error'
        )
      } finally {
        this.isSaving = false
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar.text = text
      this.snackbar.color = color
      this.snackbar.show = true
    },
  },
  head() {
    return {
      title: 'Create New Post',
    }
  },
}
</script>

<style scoped>
.title-input ::v-deep input {
  font-size: 1.75rem !important;
  font-weight: 500;
}
</style>
