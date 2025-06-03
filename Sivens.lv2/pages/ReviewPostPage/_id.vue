<template>
  <section
    :class="
      $vuetify.theme.dark
        ? 'grey darken-4 page-background'
        : 'grey lighten-5 page-background'
    "
  >
    <v-container>
      <v-row
        v-if="loading"
        justify="center"
        align="center"
        class="fill-height my-16 py-16 text-center"
      >
        <v-col cols="12" md="8">
          <v-progress-circular
            indeterminate
            size="64"
            width="4"
            color="primary"
          ></v-progress-circular>
          <p class="mt-6 subtitle-1 text--secondary">
            Loading review content...
          </p>
        </v-col>
      </v-row>

      <v-row
        v-else-if="error"
        justify="center"
        align="center"
        class="fill-height my-16 py-16 text-center"
      >
        <v-col cols="12" md="8" lg="6">
          <v-alert
            type="error"
            prominent
            border="left"
            class="text-left mx-auto elevation-2"
            max-width="600"
          >
            <div class="text-h6 font-weight-medium">
              Oops! Something Went Wrong
            </div>
            <p class="mb-0 mt-2 body-1">{{ error }}</p>
          </v-alert>
          <v-btn color="primary" to="/reviews" class="mt-8">
            <!-- Or your main blog list page -->
            <v-icon left>mdi-arrow-left</v-icon>
            Back to Reviews List
          </v-btn>
        </v-col>
      </v-row>

      <!-- Content Display (if post is fetched successfully) -->
      <v-row v-else-if="post && post.id" justify="center">
        <!-- Ensure post and post.id exist -->
        <v-col cols="12">
          <ReviewPostContent
            :post="post"
            :comments="comments"
            :parsed-content-blocks-prop="parsedContentBlocks"
            :parent-is-loading="loading"
            @comment-submitted="fetchCommentsAndUpdate"
            @like-toggled="handleLikeToggleFeedback"
          />
        </v-col>
      </v-row>

      <!-- Post Not Found (if post is null after fetch and not loading, and no other error) -->
      <v-row
        v-else-if="!loading && !error"
        justify="center"
        align="center"
        class="fill-height my-16 py-16 text-center"
      >
        <v-col cols="12" md="8" lg="6">
          <v-alert
            type="warning"
            prominent
            border="left"
            icon="mdi-file-question-outline"
            class="text-left mx-auto elevation-2"
            max-width="600"
          >
            <div class="text-h6 font-weight-medium">Post Not Found</div>
            <p class="mb-0 body-1">
              The review post you are looking for could not be found or may have
              been removed.
            </p>
          </v-alert>
          <v-btn color="primary" to="/reviews" class="mt-8">
            <v-icon left>mdi-arrow-left</v-icon>
            Back to Reviews List
          </v-btn>
        </v-col>
      </v-row>

      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        bottom
        right
        :timeout="4000"
        app
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
// Ensure the path to your content display component is correct
import ReviewPostContent from '~/components/sections/ReviewPostContent.vue'

export default {
  name: 'ReviewPostDynamicPage', // Or BlogPostDynamicPage
  components: {
    ReviewPostContent,
  },
  /**
   * Nuxt's fetch hook:
   * - Runs on server for initial load (if SSR/static for this route).
   * - Runs on client for client-side navigation or after hydration on refresh.
   * - Fetches the main post data and initial comments.
   * - The `is_liked` and `likes_count` on the 'post' object here will reflect
   *   the authentication state of THIS request.
   */
  async fetch() {
    this.loading = true
    this.error = null
    this.post = null // Reset for navigation between different post pages
    this.comments = []
    this.commentsPagination = null

    const currentPostId = this.$route.params.id

    if (!currentPostId) {
      this.error = 'Post ID is missing. Cannot load the review.'
      this.loading = false
      if (this.$nuxt && typeof this.$nuxt.error === 'function') {
        this.$nuxt.error({ statusCode: 400, message: this.error })
      }
      return
    }

    try {
      console.log(
        `[FETCH _id.vue] Attempting data for post ID: ${currentPostId}`
      )
      // For SSR, this $axios instance might not have the client's token yet.
      // The `is_liked` status here will be for an unauthenticated user in that case.
      // The client-side watchers will then call `fetchCurrentUserLikeStatus`.

      const [postResponse, commentsResponse] = await Promise.all([
        this.$axios.get(`/posts/${currentPostId}`),
        this.$axios.get(`/posts/${currentPostId}/comments`, {
          params: { page: 1 },
        }),
      ])

      if (postResponse.data && postResponse.data.id) {
        this.post = postResponse.data
        console.log(
          `[FETCH _id.vue] Initial post (ID ${currentPostId}) loaded. is_liked: ${this.post.is_liked}, likes_count: ${this.post.likes_count}`
        )
      } else {
        console.warn(
          `[FETCH _id.vue] No valid post data from API for ID ${currentPostId}.`,
          postResponse.data
        )
        this.error = `Review with ID ${currentPostId} not found or data is invalid.`
      }

      if (commentsResponse.data && Array.isArray(commentsResponse.data.data)) {
        this.comments = commentsResponse.data.data
        this.commentsPagination = commentsResponse.data
      } else {
        this.comments = []
      }

      if (!this.post && !this.error) {
        // If post is still null despite no caught error from axios
        this.error = `The review post (ID: ${currentPostId}) could not be loaded.`
      }
    } catch (err) {
      console.error(
        '[FETCH _id.vue] CRITICAL Error during initial fetch:',
        err.response || err
      )
      this.error =
        err.response?.data?.message ||
        err.message ||
        'An error occurred while loading the review.'
      if (err.response?.status === 404) {
        this.error = `The requested review post (ID: ${currentPostId}) was not found (404).`
      }
      // Optionally trigger Nuxt's error page
      // if (this.$nuxt && (err.response?.status === 404 || err.response?.status >= 500)) {
      //   this.$nuxt.error({ statusCode: err.response.status, message: this.error });
      // }
    } finally {
      this.loading = false
      console.log(
        `[FETCH _id.vue] Fetching process completed. Loading state: ${this.loading}`
      )
    }
  },
  data() {
    return {
      post: null,
      comments: [],
      commentsPagination: null, // To store the full pagination object for comments
      loading: true, // Overall loading state for the page, managed by async fetch
      error: null, // Error state for the async fetch
      snackbar: { show: false, text: '', color: '' },
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated', 'getUser']),
    postIdFromRoute() {
      return this.$route.params.id
    },
    parsedContentBlocks() {
      if (!this.post?.content) {
        return []
      }
      try {
        const blocks =
          typeof this.post.content === 'string'
            ? JSON.parse(this.post.content)
            : Array.isArray(this.post.content)
            ? this.post.content
            : []
        return Array.isArray(blocks)
          ? blocks.filter(
              (b) =>
                b && typeof b === 'object' && b.type && b.data !== undefined
            )
          : []
      } catch (e) {
        console.error('Error parsing post content JSON:', e)
        return [
          {
            type: 'paragraph',
            data: { text: 'Error: Content is malformed.' },
            id: 'content-error-' + Date.now(),
          },
        ]
      }
    },
  },
  watch: {
    isAuthenticated: {
      immediate: true,
      handler(isNowAuthenticated) {
        if (isNowAuthenticated && this.post && this.post.id && !this.loading) {
          // Only if post is loaded
          console.log(
            '[Watch isAuthenticated] Auth state is true, post exists. Fetching like status.'
          )
          this.fetchCurrentUserLikeStatus()
        } else if (!isNowAuthenticated && this.post && this.post.is_liked) {
          this.post = { ...this.post, is_liked: false } // Optimistically update on logout
        }
      },
    },
    // Watch post.id. If it changes AND user is authenticated, refresh like status.
    // This covers cases where initial post data is set from $fetch and auth state is already true.
    'post.id': {
      immediate: false, // Avoid running with initial null post.id unless 'post' object is also truthy
      handler(newPostId) {
        if (newPostId && this.isAuthenticated && !this.loading) {
          // Also ensure page is not in its main loading phase
          console.log(
            `[Watch post.id] Post ID changed to ${newPostId}, user is authenticated. Fetching like status.`
          )
          this.fetchCurrentUserLikeStatus()
        }
      },
    },
  },
  methods: {
    async fetchCurrentUserLikeStatus() {
      if (
        !this.post ||
        !this.post.id ||
        !this.isAuthenticated ||
        !process.client
      ) {
        // console.log('[METHOD fetchCurrentUserLikeStatus] Conditions not met. Post:', !!this.post, 'Auth:', this.isAuthenticated, 'Client:', process.client);
        return
      }
      // Avoid re-fetching if already in the middle of a page load handled by $fetch
      if (this.loading && this.$fetchState?.pending) return

      try {
        console.log(
          `[METHOD fetchCurrentUserLikeStatus] Fetching for post ID: ${
            this.post.id
          }. $axios Auth Header: ${
            this.$axios.defaults.headers.common.Authorization
              ? 'SET'
              : 'NOT_SET'
          }`
        )
        const response = await this.$axios.get(
          `/posts/${this.post.id}/like-status`
        )

        if (this.post && response.data) {
          let updated = false
          const newPostData = { ...this.post } // Start with a copy

          // Corrected hasOwnProperty check for is_liked
          if (Object.prototype.hasOwnProperty.call(response.data, 'is_liked')) {
            if (newPostData.is_liked !== response.data.is_liked) {
              newPostData.is_liked = response.data.is_liked
              updated = true
            }
          }
          // Corrected hasOwnProperty check for likes_count
          if (
            Object.prototype.hasOwnProperty.call(response.data, 'likes_count')
          ) {
            if (newPostData.likes_count !== response.data.likes_count) {
              newPostData.likes_count = response.data.likes_count
              updated = true
            }
          }

          if (updated) {
            this.post = newPostData // Assign the updated object
            console.log(
              `[METHOD fetchCurrentUserLikeStatus] Synced. is_liked=${this.post.is_liked}, likes_count=${this.post.likes_count}`
            )
          }
        }
      } catch (error) {
        console.error(
          "Error fetching user's current like status for post:",
          error.response?.data || error
        )
        // Don't necessarily show an error to the user for this, it's a background sync.
      }
    },
    async fetchCommentsAndUpdate(
      page = this.commentsPagination?.current_page || 1
    ) {
      if (!this.postIdFromRoute) return
      // Add specific loading for comments if needed
      try {
        const commentsResponse = await this.$axios.get(
          `/posts/${this.postIdFromRoute}/comments`,
          { params: { page } }
        )
        if (
          commentsResponse.data &&
          Array.isArray(commentsResponse.data.data)
        ) {
          this.comments = commentsResponse.data.data
          this.commentsPagination = commentsResponse.data
        } else {
          this.comments = []
          this.commentsPagination = null
        }
      } catch (err) {
        this.showSnackbar('Could not refresh comments.', 'error')
      }
    },
    handleLikeToggleFeedback({ message, color, isLiked, likesCount }) {
      this.showSnackbar(message, color)
      // Sync parent post state with what child reported after its API call
      if (
        this.post &&
        this.post.id &&
        isLiked !== undefined &&
        likesCount !== undefined
      ) {
        this.post = { ...this.post, is_liked: isLiked, likes_count: likesCount }
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar.text = text
      this.snackbar.color = color
      this.snackbar.show = true // timeout is set on component
    },
  },
  head() {
    let pageTitle = 'Review Post'
    let pageDescription = 'Read this interesting review post from Sivēns.lv.'
    let ogImage = 'YOUR_FALLBACK_OG_IMAGE.jpg' // IMPORTANT: Set a real default OpenGraph image URL

    if (this.loading) {
      pageTitle = 'Loading Review...'
    } else if (this.error || !this.post || !this.post.id) {
      // Check post.id as well
      pageTitle = 'Review Not Found or Error'
      pageDescription =
        this.error || 'The content you are looking for is unavailable.'
    } else if (this.post && this.post.title) {
      // Now post definitely exists
      pageTitle = this.post.title
      // Use post.post_image and ensure it's a full URL if relative
      const pfpPath = this.post.post_image
      if (pfpPath) {
        if (pfpPath.startsWith('http://') || pfpPath.startsWith('https://')) {
          ogImage = pfpPath
        } else {
          // Construct full URL from relative path
          const backendBaseUrl =
            this.$axios?.defaults?.baseURL?.replace('/api', '') ||
            'http://localhost:8000'
          ogImage = `${backendBaseUrl}/storage/${pfpPath.replace(
            /^public\//,
            ''
          )}`
        }
      }

      if (this.parsedContentBlocks && this.parsedContentBlocks.length > 0) {
        const firstParagraphBlock = this.parsedContentBlocks.find(
          (b) => b.type === 'paragraph' && b.data?.text
        )
        if (
          firstParagraphBlock &&
          typeof firstParagraphBlock.data.text === 'string'
        ) {
          const extractedText = firstParagraphBlock.data.text.replace(
            /<[^>]*>?/gm,
            ''
          )
          pageDescription =
            extractedText.substring(0, 160) +
            (extractedText.length > 160 ? '...' : '')
        }
      } else if (
        this.post.content &&
        typeof this.post.content === 'string' &&
        !this.post.content.startsWith('[{"id":')
      ) {
        const simpleContent = this.post.content.replace(/<[^>]*>?/gm, '')
        pageDescription =
          simpleContent.substring(0, 160) +
          (simpleContent.length > 160 ? '...' : '')
      }
    }

    return {
      title: `${pageTitle} - Sivēns.lv`, // Append site name
      meta: [
        { hid: 'description', name: 'description', content: pageDescription },
        { hid: 'og:title', property: 'og:title', content: pageTitle },
        {
          hid: 'og:description',
          property: 'og:description',
          content: pageDescription,
        },
        { hid: 'og:image', property: 'og:image', content: ogImage },
        { hid: 'og:type', property: 'og:type', content: 'article' },
      ],
    }
  },
}
</script>

<style scoped>
.page-background {
  min-height: 100vh;
}
.fill-height {
  min-height: calc(
    100vh - 64px - 36px - 64px
  ); /* VP - appbar - page y-pad - footer approx */
}
</style>
