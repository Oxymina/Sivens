<template>
  <v-fade-transition>
    <article v-if="post && post.id" class="review-post-article">
      <!-- Hero Image with Overlay and Title -->
      <v-parallax
        :src="post.post_image || defaultImage"
        :alt="post.title || 'Review post image'"
        height="50vh"
        class="mb-8 review-hero"
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
              <template v-if="post.author">
                By
                <nuxt-link
                  :to="`/author/${post.author.id}`"
                  class="font-weight-medium primary--text text--lighten-2 author-link"
                >
                  {{ post.author.name || 'Anonymous' }}
                </nuxt-link>
              </template>
              <span v-else class="font-weight-medium">By Anonymous</span>

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
                  label
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
              <v-alert
                v-else-if="!parentIsLoading"
                type="info"
                text
                prominent
                border="left"
                class="mt-8"
                icon="mdi-information-outline"
              >
                This post currently has no content to display.
              </v-alert>
              <div v-else-if="parentIsLoading" class="text-center my-8">
                <!-- Shown if parent is loading overall post data -->
                <v-progress-circular
                  indeterminate
                  color="primary"
                  size="30"
                ></v-progress-circular>
              </div>
            </div>

            <!-- Tags -->
            <div
              v-if="post.tags && post.tags.length"
              class="mb-10 tags-section"
            >
              <span class="text-subtitle-2 font-weight-medium mr-2">Tags:</span>
              <v-chip
                v-for="tag in post.tags"
                :key="tag.id"
                class="mr-2 mb-2"
                small
                color="blue-grey lighten-4"
                text-color="blue-grey darken-2"
                :to="`/blog?tag=${tag.slug || tag.id}`"
                label
              >
                <v-icon left small>mdi-tag</v-icon>
                {{ tag.name }}
              </v-chip>
            </div>

            <!-- Author Bio -->
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
                  cover
                ></v-img>
                <span
                  v-else-if="post.author.name"
                  class="white--text text-h5 font-weight-light"
                  >{{ post.author.name.charAt(0).toUpperCase() }}</span
                >
                <v-icon v-else dark>mdi-account</v-icon>
              </v-list-item-avatar>
              <v-list-item-content>
                <div class="text-caption text--secondary">Post by</div>
                <v-list-item-title class="text-h6 font-weight-medium">{{
                  post.author.name || 'Review Author'
                }}</v-list-item-title>
              </v-list-item-content>
            </v-card>

            <!-- Social Share / Like / Views Actions -->
            <v-card flat class="mb-12 pa-0 action-bar">
              <v-card-text
                class="d-flex justify-space-between align-center pa-0"
              >
                <div class="share-buttons">
                  <v-tooltip bottom
                    ><template v-slot:activator="{ on, attrs }">
                      <v-btn
                        icon
                        title="Share on Twitter"
                        v-bind="attrs"
                        @click="handleShare('twitter')"
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
                        @click="handleShare('facebook')"
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
                        @click="handleShare('copy')"
                        v-on="on"
                      >
                        <v-icon>mdi-link-variant</v-icon>
                      </v-btn> </template
                    ><span>Copy Link</span></v-tooltip
                  >
                </div>
                <div class="text--secondary d-flex align-center post-stats">
                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn
                        icon
                        :color="
                          localIsLiked
                            ? 'pink accent-3'
                            : $vuetify.theme.dark
                            ? 'grey lighten-1'
                            : 'grey darken-1'
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
                  <span class="ml-n2 mr-3 font-weight-medium text-subtitle-1">{{
                    localLikesCount
                  }}</span>

                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-icon
                        small
                        class="mr-1 grey--text"
                        v-bind="attrs"
                        v-on="on"
                        >mdi-share-variant-outline</v-icon
                      >
                    </template>
                    <span>Shares</span>
                  </v-tooltip>
                  <span class="text-caption grey--text mr-3">{{
                    localSharesCount
                  }}</span>

                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-icon
                        small
                        class="mr-1 grey--text"
                        v-bind="attrs"
                        v-on="on"
                        >mdi-eye-outline</v-icon
                      >
                    </template>
                    <span>Views</span>
                  </v-tooltip>
                  <span class="text-caption grey--text">{{
                    post.views || 0
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
                        class="white--text text-h6 font-weight-light"
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
                Be the first to share your thoughts!
              </div>
              <div v-else class="text-center py-5">
                <v-progress-circular
                  indeterminate
                  color="primary"
                  size="24"
                ></v-progress-circular>
              </div>

              <v-card
                flat
                class="add-comment-form mt-6 pa-1"
                :color="
                  $vuetify.theme.dark ? 'grey darken-3' : 'grey lighten-5'
                "
              >
                <v-card-title class="subtitle-1 font-weight-medium"
                  >Leave a Reply</v-card-title
                >
                <v-card-text>
                  <div v-if="!isAuthenticated" class="mb-4">
                    <v-alert type="info" text dense border="left" prominent>
                      Please
                      <nuxt-link to="/login" class="font-weight-bold"
                        >login</nuxt-link
                      >
                      or
                      <nuxt-link to="/register" class="font-weight-bold"
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
                    :error-messages="commentError ? [commentError] : []"
                    @input="commentError = null"
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
                </v-card-text>
              </v-card>
            </section>
          </v-col>
        </v-row>
      </v-container>

      <v-snackbar
        v-model="actionSnackbar.show"
        :color="actionSnackbar.color"
        bottom
        right
        :timeout="3500"
        app
      >
        {{ actionSnackbar.message }}
        <template v-slot:action="{ attrs }">
          <v-btn text v-bind="attrs" @click="actionSnackbar.show = false"
            >Close</v-btn
          >
        </template>
      </v-snackbar>
    </article>
    <div
      v-else-if="!parentIsLoading && post === null"
      class="text-center my-16 pa-8 fill-height d-flex flex-column justify-center align-center"
    >
      <v-icon size="80" color="grey lighten-1" class="mb-4"
        >mdi-text-box-search-outline</v-icon
      >
      <p class="text-h5 grey--text text--lighten-1">
        This post is currently unavailable.
      </p>
      <p class="text-body-1 grey--text text--lighten-1">
        It might have been removed or the link is incorrect.
      </p>
      <v-btn color="primary" class="mt-4" to="/reviews">
        <v-icon left>mdi-arrow-left</v-icon>Back to Reviews</v-btn
      >
    </div>
    <!-- Else, the parent's loading/error state from _id.vue will be shown -->
  </v-fade-transition>
</template>

<script>
import { mapGetters } from 'vuex'

// Import ALL your display block components
import ParagraphBlockDisplay from '~/components/display/ParagraphBlockDisplay.vue'
import HeadingBlockDisplay from '~/components/display/HeadingBlockDisplay.vue'
import ImageBlockDisplay from '~/components/display/ImageBlockDisplay.vue'
import QuoteBlockDisplay from '~/components/display/QuoteBlockDisplay.vue'
import ListBlockDisplay from '~/components/display/ListBlockDisplay.vue'
import VideoBlockDisplay from '~/components/display/VideoBlockDisplay.vue'
import DividerBlockDisplay from '~/components/display/DividerBlockDisplay.vue'
import StarRatingBlockDisplay from '~/components/display/StarRatingBlockDisplay.vue'
import MapLocationBlockDisplay from '~/components/display/MapLocationBlockDisplay.vue'
import OpeningHoursBlockDisplay from '~/components/display/OpeningHoursBlockDisplay.vue'

// Define a fallback URL in case environment variable isn't set, or this component is used standalone
const BACKEND_PUBLIC_URL =
  process.env.NUXT_ENV_LARAVEL_URL || 'http://localhost:8000'

export default {
  name: 'ReviewPostContent', // Changed from BlogPostContent for consistency if you renamed it
  components: {
    ParagraphBlockDisplay,
    HeadingBlockDisplay,
    ImageBlockDisplay,
    QuoteBlockDisplay,
    ListBlockDisplay,
    VideoBlockDisplay,
    DividerBlockDisplay,
    StarRatingBlockDisplay,
    MapLocationBlockDisplay,
    OpeningHoursBlockDisplay,
  },
  props: {
    post: {
      type: Object,
      required: true,
      default: () => ({
        id: null,
        title: 'Loading Content...', // More indicative default title
        content: '[]',
        post_image: null,
        author: null,
        category: null,
        tags: [],
        created_at: null,
        views: 0, // Changed from views_count to match your latest migration
        shares: 0, // Changed from shares_count to match your latest migration
        likes_count: 0, // This comes from withCount
        is_liked: false, // This comes from accessor/backend calculation
      }),
    },
    comments: {
      type: Array,
      default: () => [],
    },
    parsedContentBlocksProp: {
      type: Array,
      required: true,
      default: () => [],
    },
    parentIsLoading: {
      type: Boolean,
      default: false,
    },
    isLoadingContentProp: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      newCommentText: '',
      submittingComment: false,
      commentError: null,
      defaultImage: 'https://cdn.vuetifyjs.com/images/backgrounds/vbanner.jpg', // Generic placeholder

      localIsLiked: this.post?.is_liked || false,
      localLikesCount: this.post?.likes_count || 0,
      localSharesCount: this.post?.shares || 0,
      likingInProgress: false,
      sharingInProgress: false,

      actionSnackbar: { show: false, message: '', color: 'info' },
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated', 'getUser']),
  },
  watch: {
    post: {
      immediate: true,
      deep: true,
      handler(newPost) {
        if (newPost && newPost.id !== null) {
          this.localIsLiked = !!newPost.is_liked
          this.localLikesCount = newPost.likes_count || 0
          this.localSharesCount = newPost.shares || 0 // Update shares from post prop
        } else {
          // Reset local state if post is nullified
          this.localIsLiked = false
          this.localLikesCount = 0
          this.localSharesCount = 0
        }
      },
    },
  },
  methods: {
    formatDate(dateString, includeTime = false) {
      if (!dateString) return 'Date unavailable'
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      if (includeTime) {
        options.hour = '2-digit'
        options.minute = '2-digit'
        // options.timeZoneName = 'short'; // Optional
      }
      try {
        return new Date(dateString).toLocaleDateString(undefined, options)
      } catch (e) {
        return dateString
      }
    },
    storageUrl(filePath) {
      if (!filePath) return null // Return null to let v-img handle its error/placeholder
      if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
        return filePath
      }
      // Construct URL for images stored relative to Laravel's public/storage
      return `${BACKEND_PUBLIC_URL}/storage/${filePath.replace(
        /^public\//,
        ''
      )}`
    },
    getBlockDisplayComponent(type) {
      const componentMap = {
        paragraph: 'ParagraphBlockDisplay',
        heading: 'HeadingBlockDisplay',
        image: 'ImageBlockDisplay',
        quote: 'QuoteBlockDisplay',
        list: 'ListBlockDisplay',
        video: 'VideoBlockDisplay',
        star_rating: 'StarRatingBlockDisplay',
        map_location: 'MapLocationBlockDisplay',
        opening_hours: 'OpeningHoursBlockDisplay',
        divider_hr: 'DividerBlockDisplay',
        divider_dots: 'DividerBlockDisplay',
        divider_asterisks: 'DividerBlockDisplay',
        divider_wave: 'DividerBlockDisplay',
        divider_short_line_center: 'DividerBlockDisplay',
        default: 'ParagraphBlockDisplay',
      }
      return componentMap[type] || componentMap.default
    },
    async submitComment() {
      if (!this.newCommentText.trim()) {
        this.showActionSnackbar('Comment cannot be empty.', 'warning')
        return
      }
      if (!this.isAuthenticated) {
        this.showActionSnackbar('You must be logged in to comment.', 'info')
        return
      }
      this.submittingComment = true
      this.commentError = null
      try {
        await this.$axios.post(`/posts/${this.post.id}/comments`, {
          content: this.newCommentText,
        })
        this.newCommentText = ''
        this.$emit('comment-submitted') // Notify parent page
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
      if (this.likingInProgress || !this.post || this.post.id == null) return

      this.likingInProgress = true
      const originalIsLiked = this.localIsLiked
      const originalLikesCount = this.localLikesCount

      this.localIsLiked = !this.localIsLiked
      this.localLikesCount += this.localIsLiked ? 1 : -1

      try {
        const response = await this.$axios.post(`/posts/${this.post.id}/like`)
        this.localIsLiked = response.data.is_liked
        this.localLikesCount = response.data.likes_count
        // Parent will show snackbar via emitted event
        this.$emit('like-toggled', {
          message: response.data.message,
          color: 'success',
        })
      } catch (error) {
        console.error('Error toggling like:', error.response || error)
        const errorMessage =
          error.response?.data?.message || 'Could not update like status.'
        this.showActionSnackbar(errorMessage, 'error') // Show error from this component for direct feedback
        this.localIsLiked = originalIsLiked
        this.localLikesCount = originalLikesCount
        this.$emit('like-toggled', { message: errorMessage, color: 'error' }) // Also emit to parent
      } finally {
        this.likingInProgress = false
      }
    },
    async handleShare(platform) {
      if (!this.post || !this.post.id || this.sharingInProgress) return

      const url = process.client
        ? window.location.href
        : this.post.id
        ? `${BACKEND_PUBLIC_URL}/ReviewPostPage/${this.post.id}`
        : ''
      const title = this.post.title || 'Check out this review'
      let shareActionTaken = false

      if (platform === 'twitter') {
        const twShareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(
          url
        )}&text=${encodeURIComponent(title)}`
        if (process.client)
          window.open(twShareUrl, '_blank', 'noopener,noreferrer')
        shareActionTaken = true
      } else if (platform === 'facebook') {
        const fbShareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(
          url
        )}`
        if (process.client)
          window.open(fbShareUrl, '_blank', 'noopener,noreferrer')
        shareActionTaken = true
      } else if (platform === 'copy') {
        await this.copyLinkToClipboard(url) // copyLinkToClipboard shows its own snackbar
        shareActionTaken = true // We count this as a share intent
      }

      if (shareActionTaken) {
        this.incrementShareCountOnBackend()
      }
    },
    async copyLinkToClipboard(urlToCopy) {
      if (!process.client || !navigator.clipboard) {
        this.showActionSnackbar(
          'Could not copy link. Please copy from address bar.',
          'info'
        )
        return
      }
      try {
        await navigator.clipboard.writeText(urlToCopy)
        this.showActionSnackbar('Link copied to clipboard!', 'success')
      } catch (err) {
        console.error('Failed to copy link: ', err)
        this.showActionSnackbar('Could not copy link automatically.', 'warning')
      }
    },
    async incrementShareCountOnBackend() {
      this.sharingInProgress = true // Set loading for this specific action
      this.localSharesCount++ // Optimistic update
      try {
        const response = await this.$axios.post(`/posts/${this.post.id}/share`)
        if (response.data && response.data.shares !== undefined) {
          this.localSharesCount = response.data.shares // Sync with backend
        }
      } catch (error) {
        console.error(
          'Error incrementing share count on backend:',
          error.response?.data || error
        )
        this.localSharesCount-- // Revert optimistic update
        this.showActionSnackbar(
          'Could not update share count on server.',
          'error'
        )
      } finally {
        this.sharingInProgress = false
      }
    },
    showActionSnackbar(message, color = 'info') {
      this.actionSnackbar.message = message
      this.actionSnackbar.color = color
      this.actionSnackbar.show = true
    },
  },
}
</script>

<style scoped lang="scss">
.review-post-article {
  word-wrap: break-word;
}
.review-hero .v-parallax__image {
  object-fit: cover;
}
.post-title-shadow {
  text-shadow: 0px 2px 4px rgba(0, 0, 0, 0.7); /* Increased shadow for better contrast */
}
.post-meta-shadow {
  text-shadow: 0px 1px 3px rgba(0, 0, 0, 0.9);
  .author-link {
    color: #e1f5fe !important; /* Lighter blue for better contrast on dark parallax */
    text-decoration: none;
    &:hover {
      text-decoration: underline;
      color: #b3e5fc !important;
    }
  }
  .v-chip {
    background-color: rgba(
      255,
      255,
      255,
      0.15
    ) !important; /* Semi-transparent white chip */
    color: #fff !important;
    .v-icon {
      color: #fff !important;
    }
    &:hover {
      background-color: rgba(255, 255, 255, 0.25) !important;
    }
  }
}
.content-block {
  margin-bottom: 2rem; /* More generous spacing */
  &:last-child {
    margin-bottom: 0;
  }
}
.author-bio {
  background-color: rgba(var(--v-primary-base-rgb), 0.03);
  border-left: 5px solid var(--v-primary-base);
  border-radius: 4px;
  &.theme--dark {
    background-color: rgba(var(--v-primary-base-rgb), 0.08);
    border-left-color: var(--v-primary-lighten1);
  }
}
.action-bar {
  border-top: 1px solid rgba(0, 0, 0, 0.08);
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  padding-top: 12px !important;
  padding-bottom: 12px !important;
  margin-bottom: 3rem !important;

  .theme--dark & {
    border-color: rgba(255, 255, 255, 0.08);
  }
  .post-stats .v-icon {
    color: var(--v-text-secondary-base);
  }
  .post-stats span {
    color: var(--v-text-secondary-base);
  }
}

.comments-section {
  margin-top: 3rem;
  padding-top: 2rem;
  // border-top: 1px solid rgba(0,0,0,0.08);
  // .theme--dark & {
  //   border-top: 1px solid rgba(255,255,255,0.08);
  // }
}
.comment-item .comment-content {
  background-color: var(--v-grey-lighten4, #f9f9f9);
  border: 1px solid var(--v-grey-lighten3, #ededed);
  &.theme--dark {
    background-color: var(--v-grey-darken3, #3e3e3e);
    border: 1px solid var(--v-grey-darken2, #525252);
  }
}
.add-comment-form {
  background-color: transparent !important; // Remove card background
}
</style>
