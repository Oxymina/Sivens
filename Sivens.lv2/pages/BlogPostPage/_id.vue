<template>
  <section :class="$vuetify.theme.dark ? 'grey darken-4' : 'background-light'">
    <v-container fluid class="pa-0">
      <!-- Loading State -->
      <v-row v-if="loading" justify="center" class="my-16">
        <v-col cols="12" md="8" class="text-center">
          <v-progress-circular
            indeterminate
            size="64"
            color="primary"
          ></v-progress-circular>
          <p class="mt-4 subtitle-1">Loading post...</p>
        </v-col>
      </v-row>

      <!-- Error State -->
      <v-row v-else-if="error" justify="center" class="my-16">
        <v-col cols="12" md="8" class="text-center">
          <v-alert type="error" prominent border="left">
            <div class="title">Oops! Something went wrong.</div>
            {{ error }}
          </v-alert>
          <v-btn color="primary" to="/blog" class="mt-4">
            <v-icon left>mdi-arrow-left</v-icon>
            Back to Blog
          </v-btn>
        </v-col>
      </v-row>

      <!-- Content Display -->
      <template v-else-if="post">
        <BlogPostContent
          :post="post"
          :comments="comments"
          @comment-submitted="fetchComments"
        />
        <!-- We can add related posts or other sections below -->
      </template>

      <!-- Post Not Found -->
      <v-row v-else justify="center" class="my-16">
        <v-col cols="12" md="8" class="text-center">
          <v-alert type="warning" prominent border="left">
            <div class="title">Post Not Found</div>
            The blog post you are looking for could not be found.
          </v-alert>
          <v-btn color="primary" to="/blog" class="mt-4">
            <v-icon left>mdi-arrow-left</v-icon>
            Back to Blog
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
import { mapGetters } from 'vuex'
import BlogPostContent from '~/components/sections/BlogPostContent.vue'
// Assuming Vuex store for auth/user data

export default {
  components: {
    BlogPostContent,
  },
  async fetch() {
    // Nuxt's fetch hook, runs on server and client, good for data fetching
    // https://nuxtjs.org/docs/features/data-fetching/#the-fetch-hook
    this.loading = true
    this.error = null
    try {
      // Fetch post and comments concurrently
      const [postResponse, commentsResponse] = await Promise.all([
        this.$axios.get(`/posts/${this.postId}`), // Using Nuxt $axios
        this.$axios.get(`/posts/${this.postId}/comments`),
      ])

      this.post = postResponse.data
      this.comments = commentsResponse.data.data

      if (!this.post) {
        // You might want to throw a specific error to show the "Post Not Found" state
        // For Nuxt error page:
        // return this.$nuxt.error({ statusCode: 404, message: 'Post not found' });
        // For in-page message:
        console.warn('Post data is null after fetch')
      }
    } catch (err) {
      console.error('Error fetching blog post data:', err)
      this.error =
        err.response?.data?.message ||
        err.message ||
        'Failed to load the blog post.'
      // Optionally trigger Nuxt error page for severe errors:
      // if (err.response && err.response.status) {
      //    this.$nuxt.error({ statusCode: err.response.status, message: this.error });
      // }
    } finally {
      this.loading = false
    }
  },
  data() {
    return {
      post: null,
      comments: [],
      loading: true,
      error: null,
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated', 'getUser']), // If you need user info
    postId() {
      return this.$route.params.id
    },
  },
  methods: {
    // This method will be called by BlogPostContent when a new comment is added, to refresh the list
    async fetchComments(page = 1) {
      // Optionally allow fetching a specific page
      try {
        const commentsResponse = await this.$axios.get(
          `/posts/${this.postId}/comments`,
          {
            params: { page }, // Send page param if your backend supports it for comments
          }
        )
        // --- CORRECTED ASSIGNMENT ---
        if (
          commentsResponse.data &&
          Array.isArray(commentsResponse.data.data)
        ) {
          this.comments = commentsResponse.data.data
          // Optionally update commentsPagination info here too if you have it
          // this.commentsPagination = commentsResponse.data;
        } else {
          // Handle unexpected response, maybe it's not paginated or error
          console.error(
            'Unexpected comments response structure:',
            commentsResponse.data
          )
          this.comments = [] // Fallback to empty array
        }
        // --- END CORRECTION ---
      } catch (err) {
        console.error('Error refreshing comments:', err)
        // Optionally show a toast/snackbar to the user
      }
    },
  },
  head() {
    // Dynamic head for SEO
    if (this.post) {
      return {
        title: this.post.title,
        meta: [
          {
            hid: 'description',
            name: 'description',
            content:
              this.post.content?.substring(0, 155) ||
              'Read this exciting blog post.',
          },
          // Add Open Graph tags, etc.
        ],
      }
    }
    return {
      title: 'Blog Post',
    }
  },
}
</script>

<style scoped>
.background-light {
  background-color: #f9f9f9; /* Lighter than grey lighten-4 */
}
.headline {
  /* Already in BlogPostContent, maybe remove if only used there */
  font-weight: 600; /* Bolder title */
}
</style>
