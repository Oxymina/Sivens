<!-- components/sections/BlogPostContent.vue -->
<template>
  <v-fade-transition>
    <!-- Subtle fade-in for the content -->
    <article v-if="post && post.id" class="blog-post-article">
      <!-- Ensure post object exists and has an ID -->
      <!-- Hero Image with Overlay and Title -->
      <v-parallax
        :src="post.post_image || defaultImage"
        alt=""
        height="50vh"
        class="mb-8 blog-hero"
        dark
      >
        <v-row align="center" justify="center" class="fill-height text-center">
          <v-col cols="12" md="10" lg="8">
            <h1
              :class="[
                $vuetify.breakpoint.mdAndUp ? 'text-h3' : 'text-h4',
                'font-weight-bold',
                'mb-4',
                'post-title-shadow',
              ]"
            >
              {{ post.title || 'Untitled Post' }}
            </h1>
            <div class="subtitle-1 font-weight-regular post-meta-shadow">
              By
              <nuxt-link
                v-if="post.author"
                :to="`/author/${post.author.id}`"
                class="font-weight-medium primary--text text--lighten-2"
                style="text-decoration: none"
              >
                {{ post.author.name || 'Anonymous' }}
              </nuxt-link>
              <span v-else class="font-weight-medium">Anonymous</span>

              <span class="mx-2">•</span>
              <span>{{
                formatDate(post.created_at) || 'Date not available'
              }}</span>

              <template v-if="post.category">
                <span class="mx-2">•</span>
                In
                <v-chip
                  small
                  color="rgba(255,255,255,0.2)"
                  text-color="white"
                  class="font-weight-medium"
                  :to="`/blog?category=${post.category.id}`"
                >
                  <v-icon left small>mdi-tag-outline</v-icon>
                  {{ post.category.name }}
                </v-chip>
              </template>
            </div>
          </v-col>
        </v-row>
        <template v-slot:placeholder>
          <v-row class="fill-height ma-0" align="center" justify="center">
            <v-progress-circular
              indeterminate
              color="white"
            ></v-progress-circular>
          </v-row>
        </template>
        <template v-slot:error>
          <v-row
            class="fill-height ma-0 grey lighten-2"
            align="center"
            justify="center"
            style="opacity: 0.8"
          >
            <v-icon color="grey darken-2" x-large
              >mdi-image-broken-variant</v-icon
            >
          </v-row>
        </template>
      </v-parallax>

      <!-- Main Content Area -->
      <v-container>
        <v-row justify="center">
          <v-col cols="12" md="10" lg="8">
            <!-- Article Content Blocks -->
            <div class="post-content-blocks mt-2 mb-12">
              <template v-if="isLoadingContentProp">
                <div class="text-center my-8">
                  <v-progress-circular
                    indeterminate
                    color="primary"
                    size="40"
                  ></v-progress-circular>
                  <p class="mt-2 text-caption">Rendering content...</p>
                </div>
              </template>
              <template
                v-else-if="
                  parsedContentBlocksProp && parsedContentBlocksProp.length > 0
                "
              >
                <div
                  v-for="block in parsedContentBlocksProp"
                  :key="block.id"
                  class="content-block"
                >
                  <component
                    :is="getBlockDisplayComponent(block.type)"
                    :block-data="block.data"
                  />
                </div>
              </template>
              <v-alert v-else type="info" text prominent class="mt-8">
                This post appears to be empty or content is still loading.
              </v-alert>
            </div>

            <!-- Tags (Optional) -->
            <div v-if="post.tags && post.tags.length" class="mb-8">
              <v-chip
                v-for="tag in post.tags"
                :key="tag.id"
                class="mr-2 mb-2"
                small
                outlined
                color="secondary"
                :to="`/blog?tag=${tag.slug || tag.id}`"
                label
              >
                <v-icon left small>mdi-tag-multiple-outline</v-icon>
                {{ tag.name }}
              </v-chip>
            </div>

            <!-- Author Bio (Optional) -->
            <v-card
              v-if="post.author"
              outlined
              class="mb-12 pa-4 author-bio d-flex align-center"
            >
              <v-list-item-avatar
                color="primary"
                size="60"
                class="mr-4 elevation-2"
              >
                <v-img
                  v-if="post.author.userPFP"
                  :src="storageUrl(post.author.userPFP)"
                  :alt="post.author.name"
                ></v-img>
                <span v-else class="white--text text-h5">{{
                  post.author.name
                    ? post.author.name.charAt(0).toUpperCase()
                    : 'A'
                }}</span>
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title class="text-h6 font-weight-medium">{{
                  post.author.name || 'Blog Author'
                }}</v-list-item-title>
                <v-list-item-subtitle class="mt-1 body-2">
                  {{
                    post.author.bio || 'The author has not provided a bio yet.'
                  }}
                </v-list-item-subtitle>
              </v-list-item-content>
            </v-card>

            <!-- Social Share / Like Actions -->
            <v-card flat class="mb-12 pa-0">
              <v-card-text
                class="d-flex justify-space-between align-center pa-0"
              >
                <div>
                  <v-tooltip bottom
                    ><template v-slot:activator="{ on, attrs }">
                      <v-btn
                        icon
                        title="Share on Twitter"
                        v-bind="attrs"
                        @click="share('twitter')"
                        v-on="on"
                      >
                        <v-icon>mdi-twitter</v-icon>
                      </v-btn> </template
                    ><span>Share on Twitter</span></v-tooltip
                  >
                  <v-tooltip bottom
                    ><template v-slot:activator="{ on, attrs }">
                      <v-btn
                        icon
                        title="Share on Facebook"
                        v-bind="attrs"
                        @click="share('facebook')"
                        v-on="on"
                      >
                        <v-icon>mdi-facebook</v-icon>
                      </v-btn> </template
                    ><span>Share on Facebook</span></v-tooltip
                  >
                  <v-tooltip bottom
                    ><template v-slot:activator="{ on, attrs }">
                      <v-btn
                        icon
                        title="Copy Link"
                        v-bind="attrs"
                        @click="copyLink"
                        v-on="on"
                      >
                        <v-icon>mdi-link-variant</v-icon>
                      </v-btn> </template
                    ><span>Copy Link</span></v-tooltip
                  >
                </div>
                <div class="text--secondary d-flex align-center">
                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn
                        icon
                        :color="
                          localIsLiked ? 'pink accent-3' : 'grey lighten-1'
                        "
                        :loading="likingInProgress"
                        :disabled="likingInProgress || !isAuthenticated"
                        v-bind="attrs"
                        v-on="on"
                        @click="toggleLike"
                      >
                        <v-icon>{{
                          localIsLiked ? 'mdi-heart' : 'mdi-heart-outline'
                        }}</v-icon>
                      </v-btn>
                    </template>
                    <span>{{
                      localIsLiked
                        ? 'Unlike'
                        : isAuthenticated
                        ? 'Like'
                        : 'Login to like'
                    }}</span>
                  </v-tooltip>
                  <span class="ml-n2 mr-3 font-weight-medium subheading">{{
                    localLikesCount
                  }}</span>

                  <v-icon small class="mr-1 grey--text">mdi-eye-outline</v-icon>
                  <span class="text-caption grey--text">{{
                    post.views_count || 0
                  }}</span>
                </div>
              </v-card-text>
            </v-card>

            <!-- Comments Section -->
            <section id="comments" class="comments-section">
              <h2 class="text-h5 font-weight-medium mb-6">
                Comments ({{ comments.length }})
              </h2>
              <v-list
                v-if="comments.length > 0"
                class="mb-8"
                two-line
                color="transparent"
                flat
              >
                <template v-for="(comment, index) in comments">
                  <v-list-item :key="comment.id" class="comment-item mb-4 pa-0">
                    <v-list-item-avatar
                      color="secondary"
                      size="48"
                      class="mt-1 elevation-1"
                    >
                      <v-img
                        v-if="comment.user && comment.user.userPFP"
                        :src="storageUrl(comment.user.userPFP)"
                        :alt="comment.user.name"
                      ></v-img>
                      <span
                        v-else-if="comment.user && comment.user.name"
                        class="white--text text-h6"
                        >{{ comment.user.name.charAt(0).toUpperCase() }}</span
                      >
                      <v-icon v-else dark>mdi-account</v-icon>
                    </v-list-item-avatar>
                    <v-list-item-content
                      class="comment-content pa-4 rounded-lg elevation-1"
                    >
                      <v-list-item-title
                        class="font-weight-bold subtitle-1 d-flex justify-space-between"
                      >
                        <span>{{ comment.user?.name || 'Anonymous' }}</span>
                        <span class="text-caption text--disabled">{{
                          formatDate(comment.created_at, true)
                        }}</span>
                      </v-list-item-title>
                      <v-list-item-subtitle
                        class="text--primary body-2 mt-1"
                        style="white-space: pre-wrap"
                        >{{ comment.content }}</v-list-item-subtitle
                      >
                    </v-list-item-content>
                  </v-list-item>
                  <v-divider
                    v-if="index < comments.length - 1"
                    :key="`divider-${comment.id}`"
                    class="my-3"
                    light
                  ></v-divider>
                </template>
              </v-list>
              <div
                v-else-if="!parentIsLoading"
                class="text-center text--disabled py-5 body-1"
              >
                <!-- parentIsLoading refers to parent's loading state -->
                Be the first to share your thoughts!
              </div>
              <div v-else class="text-center py-5">
                <v-progress-circular
                  indeterminate
                  color="primary"
                  size="24"
                ></v-progress-circular>
              </div>

              <!-- Add Comment Form -->
              <v-card flat class="add-comment-form mt-6 pa-1">
                <v-card-title class="subtitle-1 font-weight-medium"
                  >Leave a Reply</v-card-title
                >
                <v-card-text>
                  <div v-if="!isAuthenticated" class="mb-4">
                    <v-alert type="info" text dense>
                      Please
                      <nuxt-link to="/login" class="font-weight-medium"
                        >login</nuxt-link
                      >
                      or
                      <nuxt-link to="/register" class="font-weight-medium"
                        >register</nuxt-link
                      >
                      to post a comment.
                    </v-alert>
                  </div>
                  <v-textarea
                    v-model="newCommentText"
                    label="Your comment..."
                    placeholder="Share your thoughts on this post..."
                    outlined
                    rows="4"
                    auto-grow
                    no-resize
                    :disabled="submittingComment || !isAuthenticated"
                    counter
                    maxlength="2000"
                  ></v-textarea>
                  <v-btn
                    color="primary"
                    depressed
                    large
                    :loading="submittingComment"
                    :disabled="
                      !newCommentText.trim() ||
                      !isAuthenticated ||
                      submittingComment
                    "
                    class="mt-2"
                    @click="submitComment"
                  >
                    Post Comment
                  </v-btn>
                  <v-alert
                    v-if="commentError"
                    type="error"
                    dense
                    text
                    class="mt-4"
                  >
                    {{ commentError }}
                  </v-alert>
                </v-card-text>
              </v-card>
            </section>
          </v-col>
        </v-row>
      </v-container>

      <!-- Snackbars for feedback -->
      <v-snackbar
        v-model="actionSnackbar.show"
        :color="actionSnackbar.color"
        bottom
        :timeout="3000"
      >
        {{ actionSnackbar.message }}
        <template v-slot:action="{ attrs }">
          <v-btn text v-bind="attrs" @click="actionSnackbar.show = false"
            >Close</v-btn
          >
        </template>
      </v-snackbar>
    </article>
    <div v-else-if="!post && !parentIsLoading" class="text-center my-16 pa-8">
      <v-icon size="64" color="grey">mdi-file-document-outline</v-icon>
      <p class="text-h6 grey--text mt-4">
        Post details are currently unavailable.
      </p>
    </div>
  </v-fade-transition>
</template>

<script>
import { mapGetters } from 'vuex'

// Import Display Block Components (Adjust paths as necessary)
import ParagraphBlockDisplay from '~/components/display/ParagraphBlockDisplay.vue'
import HeadingBlockDisplay from '~/components/display/HeadingBlockDisplay.vue'
import ImageBlockDisplay from '~/components/display/ImageBlockDisplay.vue'
import QuoteBlockDisplay from '~/components/display/QuoteBlockDisplay.vue'
import ListBlockDisplay from '~/components/display/ListBlockDisplay.vue'
import VideoBlockDisplay from '~/components/display/VideoBlockDisplay.vue'
import DividerBlockDisplay from '~/components/display/DividerBlockDisplay.vue'

export default {
  name: 'BlogPostContent',
  components: {
    ParagraphBlockDisplay,
    HeadingBlockDisplay,
    ImageBlockDisplay,
    QuoteBlockDisplay,
    ListBlockDisplay,
    VideoBlockDisplay,
    DividerBlockDisplay,
  },
  props: {
    post: {
      type: Object,
      required: true,
      default: () => ({
        // More robust default
        id: null,
        title: 'Loading...',
        content: '[]',
        post_image: null,
        author: null,
        category: null,
        tags: [],
        created_at: null,
        likes_count: 0,
        views_count: 0,
        is_liked: false,
      }),
    },
    comments: {
      type: Array,
      default: () => [],
    },
    // This prop now receives the already parsed blocks from the parent (_id.vue)
    parsedContentBlocksProp: {
      type: Array,
      required: true,
      default: () => [],
    },
    // Optional: If the parent (_id.vue) wants to signal its overall loading state
    parentIsLoading: {
      type: Boolean,
      default: false,
    },
    isLoadingContentProp: {
      // From parent's 'isParsingContent' (optional for fine-grained loading UI)
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      newCommentText: '',
      submittingComment: false,
      commentError: null,
      defaultImage: 'https://cdn.vuetifyjs.com/images/parallax/material.jpg',

      // Local state for likes, initialized by prop in watch
      localIsLiked: false,
      localLikesCount: 0,
      likingInProgress: false,

      // Snackbar for all actions (like, comment, copy link)
      actionSnackbar: { show: false, message: '', color: 'info' },
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated', 'getUser']),
    // parsedContentBlocks is now a prop: 'parsedContentBlocksProp'
  },
  watch: {
    // Watch the incoming post prop to initialize local like state
    // and other potentially derived states if necessary.
    post: {
      immediate: true,
      deep: true,
      handler(newPost) {
        if (newPost && newPost.id) {
          // Check if post is valid
          this.localIsLiked = !!newPost.is_liked // Coerce to boolean
          this.localLikesCount = newPost.likes_count || 0
        } else {
          // Reset if post becomes null (e.g., navigating away then back quickly)
          this.localIsLiked = false
          this.localLikesCount = 0
        }
      },
    },
  },
  // No 'mounted' hook needed for sanitization here as blocks are pre-parsed
  methods: {
    formatDate(dateString, includeTime = false) {
      if (!dateString) return 'Date unavailable'
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      if (includeTime) {
        options.hour = '2-digit'
        options.minute = '2-digit'
      }
      try {
        return new Date(dateString).toLocaleDateString(undefined, options)
      } catch (e) {
        console.warn('Error formatting date:', dateString, e)
        return dateString
      }
    },
    storageUrl(filePath) {
      if (!filePath) return null
      // Assuming your Laravel backend is at http://localhost:8000
      // and you have run `php artisan storage:link`
      return `http://localhost:8000/storage/${filePath}`
    },
    getBlockDisplayComponent(type) {
      const componentMap = {
        paragraph: 'ParagraphBlockDisplay',
        heading: 'HeadingBlockDisplay',
        image: 'ImageBlockDisplay',
        quote: 'QuoteBlockDisplay',
        list: 'ListBlockDisplay',
        video: 'VideoBlockDisplay',
        divider_hr: 'DividerBlockDisplay',
        divider_dots: 'DividerBlockDisplay',
        default: 'ParagraphBlockDisplay', // Fallback if type is unknown
      }
      const componentName = componentMap[type] || componentMap.default
      return componentName
    },
    async submitComment() {
      if (!this.newCommentText.trim()) return
      if (!this.isAuthenticated) {
        this.commentError =
          'You must be logged in to comment. Please login or register.'
        return
      }

      this.submittingComment = true
      this.commentError = null
      try {
        await this.$axios.post(`/posts/${this.post.id}/comments`, {
          content: this.newCommentText,
        })
        this.newCommentText = ''
        this.$emit('comment-submitted') // Notify parent page to refresh comments
        this.showActionSnackbar('Comment posted successfully!', 'success')
      } catch (err) {
        console.error('Error posting comment:', err.response || err)
        this.commentError =
          err.response?.data?.message ||
          'Failed to post comment. Please try again.'
      } finally {
        this.submittingComment = false
      }
    },
    async toggleLike() {
      if (!this.isAuthenticated) {
        this.showActionSnackbar('Please log in to like posts.', 'info')
        return
      }
      if (this.likingInProgress || !this.post || this.post.id == null) return // Guard clause

      this.likingInProgress = true

      const originalIsLiked = this.localIsLiked
      const originalLikesCount = this.localLikesCount

      this.localIsLiked = !this.localIsLiked
      this.localLikesCount += this.localIsLiked ? 1 : -1

      try {
        // Use the API endpoint defined in your Laravel routes for liking
        const response = await this.$axios.post(`/posts/${this.post.id}/like`)

        // Update with actual response from API to ensure consistency
        this.localIsLiked = response.data.is_liked
        this.localLikesCount = response.data.likes_count
        this.showActionSnackbar(response.data.message, 'success')

        // Emit an event if parent page needs to know about like changes
        this.$emit('like-toggled', {
          postId: this.post.id,
          isLiked: this.localIsLiked,
          likesCount: this.localLikesCount,
        })
      } catch (error) {
        console.error('Error toggling like:', error.response || error)
        this.showActionSnackbar(
          error.response?.data?.message || 'Could not update like status.',
          'error'
        )
        // Revert optimistic update on error
        this.localIsLiked = originalIsLiked
        this.localLikesCount = originalLikesCount
      } finally {
        this.likingInProgress = false
      }
    },
    showActionSnackbar(message, color = 'info') {
      this.actionSnackbar.message = message
      this.actionSnackbar.color = color
      this.actionSnackbar.show = true
    },
    share(platform) {
      if (!this.post || !this.post.title) return
      const url = process.client ? window.location.href : '' // Get URL only on client
      const title = this.post.title
      let shareUrl = ''

      if (platform === 'twitter') {
        shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(
          url
        )}&text=${encodeURIComponent(title)}`
      } else if (platform === 'facebook') {
        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(
          url
        )}`
      }
      if (shareUrl && process.client) {
        window.open(shareUrl, '_blank', 'noopener,noreferrer')
      } else if (!process.client) {
        console.warn('Share links are generated client-side.')
      }
    },
    async copyLink() {
      if (!process.client) {
        this.showActionSnackbar('Link can be copied from the browser.', 'info')
        return
      }
      try {
        await navigator.clipboard.writeText(window.location.href)
        this.showActionSnackbar('Link copied to clipboard!', 'success')
      } catch (err) {
        console.error('Failed to copy link: ', err)
        this.showActionSnackbar('Could not copy link to clipboard.', 'error')
      }
    },
  },
}
</script>

<style scoped lang="scss">
// Base styles for overall article
.blog-post-article {
  word-wrap: break-word; // Prevent long words from breaking layout
}

// Hero image improvements
.blog-hero .v-parallax__image {
  // Ensure image covers
  object-fit: cover;
}
.post-title-shadow {
  text-shadow: 0px 2px 4px rgba(0, 0, 0, 0.6);
}
.post-meta-shadow {
  text-shadow: 0px 1px 3px rgba(0, 0, 0, 0.8);
}

// General content block spacing within the rendered post
.content-block {
  margin-bottom: 1.75rem; /* Default space between blocks */
  &:last-child {
    margin-bottom: 0;
  }
}

.author-bio {
  background-color: rgba(
    var(--v-primary-base-rgb),
    0.03
  ); // Use RGB version for alpha
  border-left: 4px solid var(--v-primary-base);
  &.theme--dark {
    background-color: rgba(var(--v-primary-base-rgb), 0.1);
    // border-left-color: var(--v-primary-lighten1); // Optional for dark theme
  }
}

.comments-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(0, 0, 0, 0.08);
  .theme--dark & {
    // Apply style if inside a dark themed element
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }
}

.comment-item .comment-content {
  // Use Vuetify elevation or custom shadow for a bit of depth
  // background-color: var(--v-background-base); // Match main background by default
  background-color: var(--v-grey-lighten4, #f5f5f5);
  border: 1px solid var(--v-grey-lighten3, #eee);
  // box-shadow: 0 1px 2px rgba(0,0,0,0.05);

  &.theme--dark {
    background-color: var(--v-grey-darken3, #3a3a3a);
    border: 1px solid var(--v-grey-darken2, #424242);
  }
}

/*.add-comment-form {*/
/* No specific background, blends with page */
/*}*/
</style>
