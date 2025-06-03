<template>
  <section
    :class="$vuetify.theme.dark ? 'grey darken-4' : 'grey lighten-5'"
    class="pa-4 pa-md-8 page-min-height"
  >
    <v-container>
      <v-row justify="center">
        <v-col cols="12" md="10" lg="9">
          <!-- Loading and Error States -->
          <div v-if="loadingData" class="text-center py-16">
            <v-progress-circular
              indeterminate
              color="primary"
              size="64"
            ></v-progress-circular>
            <p class="mt-4 text-h6">Loading review for editing...</p>
          </div>
          <v-alert
            v-else-if="fetchError"
            type="error"
            prominent
            border="left"
            class="my-8"
          >
            <div class="text-h6 font-weight-medium">Error Loading Review</div>
            <p>{{ fetchError }}</p>
            <div class="mt-3">
              <v-btn text color="error" @click="$fetch">Try Again</v-btn>
              <v-btn text to="/my-reviews">Back to My Reviews</v-btn>
            </div>
          </v-alert>

          <template v-else-if="post.id">
            <h1 class="text-h4 font-weight-bold mb-8 text-center">
              Edit Review: {{ originalTitle }}
            </h1>
            <v-card outlined>
              <v-form ref="editPostForm" v-model="formValid">
                <v-card-text class="pa-4 pa-md-6">
                  <v-text-field
                    v-model="post.title"
                    label="Review Title"
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
                  <v-btn text to="/writer/review_editor_list">Cancel</v-btn>
                  <v-spacer></v-spacer>
                  <v-btn
                    color="primary"
                    large
                    depressed
                    :loading="isSaving"
                    :disabled="!formValid || !isFormChanged"
                    @click="submitUpdatedPost"
                  >
                    <v-icon left>mdi-content-save-edit-outline</v-icon>
                    Save Changes
                  </v-btn>
                </v-card-actions>
              </v-form>
            </v-card>
          </template>
        </v-col>
      </v-row>
      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        bottom
        :timeout="4000"
      >
        {{ snackbar.text }}
        <template v-slot:action="{ attrs }"
          ><v-btn text v-bind="attrs" @click="snackbar.show = false"
            >Close</v-btn
          ></template
        >
      </v-snackbar>
    </v-container>
  </section>
</template>

<script>
import { mapGetters } from 'vuex'
import BlockEditor from '~/components/reviewcreator/BlockEditor.vue'

export default {
  name: 'EditPostPage',
  components: { BlockEditor },
  middleware: 'writer',
  async asyncData({ $axios, params, error, store }) {
    // Pre-fetch post data and categories
    // Check user permission client-side in mounted/created or with redirecting middleware
    const postId = params.id
    try {
      const [postResponse, categoriesResponse] = await Promise.all([
        $axios.get(`/posts/${postId}`),
        $axios.get('/categories'), // Assuming a public endpoint for categories
      ])

      const fetchedPost = postResponse.data
      let contentBlocks = []
      if (fetchedPost.content) {
        try {
          if (typeof fetchedPost.content === 'string') {
            contentBlocks = JSON.parse(fetchedPost.content)
          } else if (Array.isArray(fetchedPost.content)) {
            contentBlocks = fetchedPost.content
          }
        } catch (e) {
          console.error('Error parsing post content for edit:', e)
        }
      }

      // Permission check (basic, more robust on backend with Gates/Policies)
      const currentUser = store.getters['auth/getUser']
      const isAdmin = store.getters['auth/isAdmin']
      if (!isAdmin && currentUser?.id !== fetchedPost.author_id) {
        error({
          statusCode: 403,
          message: 'You are not authorized to edit this post.',
        })
        return
      }

      return {
        post: {
          // Structure for the form
          id: fetchedPost.id,
          title: fetchedPost.title,
          category_id:
            fetchedPost.category_id ||
            (fetchedPost.category ? fetchedPost.category.id : null),
          post_image: fetchedPost.post_image || '',
          content_blocks: Array.isArray(contentBlocks) ? contentBlocks : [], // Ensure it's an array
          author_id: fetchedPost.author_id, // Keep original author_id
        },
        originalTitle: fetchedPost.title, // To display in header
        categories:
          categoriesResponse.data.data || categoriesResponse.data || [],
        loadingData: false,
        fetchError: null,
      }
    } catch (err) {
      console.error(
        `Error fetching data for post ${postId} edit:`,
        err.response?.data || err
      )
      const statusCode = err.response?.status || 500
      const message =
        err.response?.data?.message ||
        `Failed to load review for editing (ID: ${postId}).`
      error({ statusCode, message }) // Nuxt error page
      return {
        post: { id: null, title: '', content_blocks: [] },
        categories: [],
        loadingData: false,
        fetchError: message,
        originalTitle: 'Error',
      }
    }
  },
  data() {
    return {
      formValid: false,
      // post & categories will be populated by asyncData
      // Need to store initial post state to compare for isFormChanged
      initialPostStateString: '',
      loadingCategories: false, // Redundant if categories are from asyncData
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
    ...mapGetters('auth', ['isAdmin', 'isWriter']),
    isFormChanged() {
      // Compare current post state (excluding IDs) to initial state
      const currentPostToCompare = {
        title: this.post.title,
        category_id: this.post.category_id,
        post_image: this.post.post_image,
        content_blocks: this.post.content_blocks,
      }
      return (
        JSON.stringify(currentPostToCompare) !== this.initialPostStateString
      )
    },
  },
  mounted() {
    // Store initial state of editable fields for 'isFormChanged' computed property
    if (this.post) {
      this.initialPostStateString = JSON.stringify({
        title: this.post.title,
        category_id: this.post.category_id,
        post_image: this.post.post_image,
        content_blocks: this.post.content_blocks,
      })
    }
  },
  methods: {
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
    async submitUpdatedPost() {
      if (
        !this.$refs.editPostForm.validate() ||
        !this.validateContentBlocks()
      ) {
        this.showSnackbar('Please correct the errors in the form.', 'warning')
        return
      }
      if (!this.isFormChanged && this.post.id) {
        // Check if anything changed only for existing posts
        this.showSnackbar('No changes made to save.', 'info')
        return
      }

      this.isSaving = true
      try {
        const payload = {
          title: this.post.title,
          category_id: this.post.category_id,
          post_image: this.post.post_image || null,
          content: JSON.stringify(
            this.post.content_blocks.filter((block) => {
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
        }

        await this.$axios.put(`/posts/${this.post.id}`, payload)

        this.showSnackbar('Review updated successfully!', 'success')
        this.initialPostStateString = JSON.stringify({
          title: this.post.title,
          category_id: this.post.category_id,
          post_image: this.post.post_image,
          content_blocks: this.post.content_blocks,
        })
        this.$router.push('/writer/review_editor_list')
      } catch (error) {
        console.error('Error updating post:', error)
        this.showSnackbar(
          error.response?.data?.message ||
            error.response?.data?.errors ||
            'Failed to update review.',
          'error'
        )
      } finally {
        this.isSaving = false
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
  },
  head() {
    return {
      title:
        this.post && this.post.title
          ? `Edit: ${this.post.title}`
          : this.loadingData
          ? 'Loading Review...'
          : 'Edit Review',
    }
  },
}
</script>

<style scoped>
.title-input ::v-deep input {
  font-size: 1.75rem !important;
  font-weight: 500;
}
.page-min-height {
  min-height: 100vh;
}
</style>
