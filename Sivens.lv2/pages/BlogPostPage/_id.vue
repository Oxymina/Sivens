<template>
  <section
    :class="
      $vuetify.theme.dark
        ? 'grey darken-4 page-background'
        : 'grey lighten-5 page-background'
    "
  >
    <!-- No `fluid` and `pa-0` on the outer container if you want normal page padding later -->
    <v-container>
      <!--
        The v-rows for loading, error, and post-not-found states are fine as they
        use justify="center" and a limited md/lg column for their content.
        The main issue will be the v-row containing BlogPostContent if it's not structured correctly.
      -->

      <!-- Overall Loading State -->
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
          <p class="mt-6 subtitle-1 text--secondary">Loading post content...</p>
        </v-col>
      </v-row>

      <!-- Error State -->
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
            class="text-left mx-auto"
            max-width="600"
          >
            <div class="text-h6 font-weight-medium">
              Oops! Something Went Wrong
            </div>
            <p class="mb-0 mt-2">{{ error }}</p>
          </v-alert>
          <v-btn color="primary" to="/blog" class="mt-8">
            <v-icon left>mdi-arrow-left</v-icon> Back to Blog List
          </v-btn>
        </v-col>
      </v-row>
      <v-row v-else-if="post" justify="center">
        <v-col cols="12">
          <BlogPostContent
            :post="post"
            :comments="comments"
            :parsed-content-blocks-prop="parsedContentBlocks"
            :is-loading-content-prop="false"
            @comment-submitted="fetchCommentsAndUpdate"
            @like-toggled="handleLikeToggleFeedback"
          />
        </v-col>
      </v-row>

      <!-- Post Not Found -->
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
            class="text-left mx-auto"
            max-width="600"
          >
            <div class="text-h6 font-weight-medium">Post Not Found</div>
            <p class="mb-0">
              The blog post you are looking for could not be found or may have
              been removed.
            </p>
          </v-alert>
          <v-btn color="primary" to="/blog" class="mt-8">
            <v-icon left>mdi-arrow-left</v-icon> Back to Blog List
          </v-btn>
        </v-col>
      </v-row>

      <v-snackbar
        v-model="snackbar.show"
        :color="snackbar.color"
        bottom
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
import BlogPostContent from '~/components/sections/BlogPostContent.vue'

export default {
  name: 'BlogPostDynamicPage',
  components: {
    BlogPostContent,
  },
  async fetch() {
    this.loading = true
    this.error = null
    this.post = null
    this.comments = []
    this.commentsPagination = null

    const currentPostId = this.$route.params.id

    if (!currentPostId) {
      this.error = 'Post ID is missing. Cannot load post.'
      this.loading = false
      return
    }

    try {
      console.log(
        `[FETCH _id.vue] Attempting to fetch post ID: ${currentPostId}`
      )

      const [postResponse, commentsResponse] = await Promise.all([
        this.$axios.get(`/posts/${currentPostId}`),
        this.$axios.get(`/posts/${currentPostId}/comments`, {
          params: { page: 1 },
        }),
      ])

      if (postResponse.data) {
        this.post = postResponse.data
        console.log(
          `[FETCH _id.vue] Post data received for ID: ${currentPostId}`
        )
      } else {
        console.warn(
          `[FETCH _id.vue] No post data returned from API for ID ${currentPostId}.`
        )
        this.error = `Post with ID ${currentPostId} not found (API returned no data).`
      }

      if (commentsResponse.data && Array.isArray(commentsResponse.data.data)) {
        this.comments = commentsResponse.data.data
        this.commentsPagination = commentsResponse.data
        console.log(
          `[FETCH _id.vue] Comments received for post ${currentPostId}: ${this.comments.length}`
        )
      } else {
        console.warn(
          `[FETCH _id.vue] Comments data not in expected paginated format for post ${currentPostId}.`,
          commentsResponse.data
        )
        this.comments = []
      }

      if (!this.post && !this.error) {
        this.error = `The blog post (ID: ${currentPostId}) could not be found or is unavailable.`
      }
    } catch (err) {
      console.error(
        '[FETCH _id.vue] CRITICAL Error fetching blog post data:',
        err.response || err
      )
      this.error =
        err.response?.data?.message ||
        err.message ||
        'An unexpected error occurred while loading the blog post.'
      if (
        err.response?.status === 404 &&
        (!this.error || !this.error.toLowerCase().includes('not found'))
      ) {
        this.error = `The requested blog post (ID: ${currentPostId}) was not found on the server.`
      }
    } finally {
      this.loading = false
      console.log(
        `[FETCH _id.vue] Fetch processing complete. Loading: ${this.loading}`
      )
    }
  },
  data() {
    return {
      post: null,
      comments: [],
      commentsPagination: null,
      loading: true,
      error: null,
      snackbar: { show: false, text: '', color: '' },
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated', 'getUser']),
    postId() {
      return this.$route.params.id
    },
    parsedContentBlocks() {
      if (!this.post?.content) {
        return []
      }
      try {
        let blocks = []
        if (typeof this.post.content === 'string') {
          blocks = JSON.parse(this.post.content)
        } else if (Array.isArray(this.post.content)) {
          blocks = this.post.content
        } else {
          console.warn(
            'Post content is neither a string nor an array, cannot parse blocks:',
            this.post.content
          )
        }
        return Array.isArray(blocks)
          ? blocks.filter(
              (b) =>
                b && typeof b === 'object' && b.type && b.data !== undefined
            )
          : []
      } catch (e) {
        console.error(
          "Error parsing post content JSON in computed 'parsedContentBlocks':",
          e,
          '\nContent was:',
          this.post.content
        )
        return [
          {
            type: 'paragraph',
            data: {
              text: 'Error: The content of this post is malformed and cannot be displayed correctly.',
            },
            id: 'content-parse-error-block-' + Date.now(),
          },
        ]
      }
    },
  },
  methods: {
    async fetchCommentsAndUpdate(
      page = this.commentsPagination?.current_page || 1
    ) {
      if (!this.postId) {
        console.warn('fetchCommentsAndUpdate: No postId available.')
        return
      }
      console.log(
        `[METHODS _id.vue] Refreshing comments for post ${this.postId}, page ${page}`
      )
      try {
        const commentsResponse = await this.$axios.get(
          `/posts/${this.postId}/comments`,
          { params: { page } }
        )
        if (
          commentsResponse.data &&
          Array.isArray(commentsResponse.data.data)
        ) {
          this.comments = commentsResponse.data.data
          this.commentsPagination = commentsResponse.data
        } else {
          console.error(
            'Unexpected comments response structure on refresh:',
            commentsResponse.data
          )
          this.comments = []
          this.commentsPagination = null
        }
      } catch (err) {
        console.error('Error refreshing comments:', err.response?.data || err)
        this.showSnackbar(
          'Could not refresh comments. Please try again.',
          'error'
        )
      }
    },
    handleLikeToggleFeedback({ message, color }) {
      this.showSnackbar(message, color)
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color, timeout: 4000 }
    },
  },
  head() {
    let pageTitle = 'Blog Post'
    let pageDescription = 'Read this interesting blog post from Sivēns.lv.'
    let ogImage = 'YOUR_DEFAULT_OG_IMAGE_URL.jpg' // Remember to set a real default

    if (this.loading) {
      pageTitle = 'Loading Post...'
    } else if (this.error || !this.post) {
      pageTitle = 'Post Not Found or Error'
      pageDescription =
        this.error || 'The content you are looking for is unavailable.'
    } else if (this.post && this.post.title) {
      pageTitle = this.post.title
      ogImage = this.post.post_image || ogImage

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
      title: pageTitle,
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
    100vh - 64px - 36px
  ); /* Approx. view height - app-bar - footer */
}
</style>
