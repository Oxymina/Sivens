<!-- components/sections/BlogPostContent.vue -->
<template>
  <v-fade-transition>
    <!-- Subtle fade-in for the content -->
    <article v-if="post" class="blog-post-article">
      <!-- Hero Image with Overlay and Title -->
      <v-parallax
        :src="post.post_image || defaultImage"
        height="50vh"
        class="mb-8"
        dark
      >
        <v-row align="center" justify="center" class="fill-height text-center">
          <v-col cols="12" md="10" lg="8">
            <h1
              :class="[
                $vuetify.breakpoint.mdAndUp ? 'display-2' : 'display-1',
                'font-weight-bold',
                'mb-4',
                'post-title-shadow',
              ]"
            >
              {{ post.title }}
            </h1>
            <div class="subheading font-weight-regular post-meta-shadow">
              By
              <span class="font-weight-medium primary--text">{{
                post.author?.name || 'Anonymous'
              }}</span>
              <span class="mx-2">•</span>
              <span>{{ formatDate(post.created_at) || 'Some time ago' }}</span>
              <template v-if="post.category">
                <span class="mx-2">•</span>
                In
                <v-chip
                  small
                  color="primary"
                  outlined
                  :to="`/blog?category=${post.category.id}`"
                  >{{ post.category.name }}</v-chip
                >
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
      </v-parallax>

      <!-- Main Content Area -->
      <v-container>
        <v-row justify="center">
          <v-col cols="12" md="10" lg="8">
            <!-- Article Content -->
            <section class="post-content mt-6 mb-12"></section>

            <!-- Tags (Optional) -->
            <div v-if="post.tags && post.tags.length" class="mb-8">
              <v-chip
                v-for="tag in post.tags"
                :key="tag.id"
                class="mr-2 mb-2"
                small
                outlined
                color="secondary"
                :to="`/blog?tag=${tag.id}`"
              >
                #{{ tag.name }}
              </v-chip>
            </div>

            <!-- Author Bio (Optional, if you have author details) -->
            <v-card v-if="post.author" outlined class="mb-12 pa-4 author-bio">
              <v-list-item>
                <v-list-item-avatar color="grey">
                  <!-- Add real avatar if available: <v-img :src="post.author.userPFP ? 'storage_path/' + post.author.userPFP : defaultAvatar"></v-img> -->
                  <v-icon dark>mdi-account-circle</v-icon>
                </v-list-item-avatar>
                <v-list-item-content>
                  <v-list-item-title class="title">{{
                    post.author.name || 'Blog Author'
                  }}</v-list-item-title>
                  <!-- Add bio if available -->
                  <!-- <v-list-item-subtitle>{{ post.author.bio || 'Loves writing...' }}</v-list-item-subtitle> -->
                </v-list-item-content>
              </v-list-item>
            </v-card>

            <!-- Social Share / Actions -->
            <v-card flat class="mb-12 pa-0">
              <v-card-text
                class="d-flex justify-space-between align-center pa-0"
              >
                <!-- Left Side: Social Share Buttons -->
                <div>
                  <v-btn icon title="Share on Twitter">
                    <v-icon>mdi-twitter</v-icon>
                  </v-btn>
                  <v-btn icon title="Share on Facebook">
                    <v-icon>mdi-facebook</v-icon>
                  </v-btn>
                  <v-btn icon title="Copy Link">
                    <v-icon>mdi-link-variant</v-icon>
                  </v-btn>
                </div>

                <!-- Right Side: Likes and Views -->
                <div class="text--secondary d-flex align-center">
                  <v-tooltip bottom>
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn
                        icon
                        :color="localIsLiked ? 'pink' : 'grey'"
                        :loading="likingInProgress"
                        :disabled="likingInProgress"
                        v-bind="attrs"
                        v-on="on"
                        @click="toggleLike"
                      >
                        <v-icon>{{
                          localIsLiked ? 'mdi-heart' : 'mdi-heart-outline'
                        }}</v-icon>
                      </v-btn>
                    </template>
                    <span>{{ localIsLiked ? 'Unlike' : 'Like' }}</span>
                  </v-tooltip>
                  <span class="ml-1 mr-3">{{ localLikesCount }}</span>

                  <v-icon small class="mr-1">mdi-eye-outline</v-icon>
                  {{ post.views_count || 0 }}
                </div>
              </v-card-text>
              <!-- Optional: Error message for like action -->
              <v-snackbar
                v-model="likeErrorSnackbar"
                color="error"
                timeout="4000"
                bottom
                left
              >
                {{ likeErrorMessage }}
                <template v-slot:action="{ attrs }">
                  <v-btn text v-bind="attrs" @click="likeErrorSnackbar = false"
                    >Close</v-btn
                  >
                </template>
              </v-snackbar>
            </v-card>

            <!-- Comments Section -->
            <section id="comments" class="comments-section">
              <h2 class="headline mb-6">Comments ({{ comments.length }})</h2>
              <v-list
                v-if="comments.length"
                class="mb-8"
                two-line
                color="transparent"
              >
                <template v-for="(comment, index) in comments">
                  <v-list-item :key="comment.id" class="comment-item mb-4 pa-0">
                    <v-list-item-avatar color="primary" class="mt-1">
                      <span class="white--text headline">{{
                        comment.user?.name
                          ? comment.user.name.charAt(0).toUpperCase()
                          : 'A'
                      }}</span>
                    </v-list-item-avatar>
                    <v-list-item-content
                      class="comment-content pa-4 rounded-lg"
                    >
                      <v-list-item-title class="font-weight-bold">{{
                        comment.user?.name || 'Anonymous'
                      }}</v-list-item-title>
                      <v-list-item-subtitle
                        class="text--primary"
                        style="white-space: pre-wrap"
                        >{{ comment.content }}</v-list-item-subtitle
                      >
                      <div class="text-caption text--disabled mt-1">
                        {{ formatDate(comment.created_at) }}
                      </div>
                    </v-list-item-content>
                  </v-list-item>
                  <v-divider
                    v-if="index < comments.length - 1"
                    :key="'divider-' + comment.id"
                    class="my-2"
                  ></v-divider>
                </template>
              </v-list>
              <div v-else class="text-center text--disabled mb-8">
                Be the first to comment!
              </div>

              <!-- Add Comment Form -->
              <v-card flat class="add-comment-form">
                <v-card-title class="subtitle-1 font-weight-medium"
                  >Leave a Reply</v-card-title
                >
                <v-card-text>
                  <div
                    v-if="!isAuthenticated"
                    class="mb-4 text-center text--secondary"
                  >
                    You must be <nuxt-link to="/login">logged in</nuxt-link> to
                    comment.
                  </div>
                  <v-textarea
                    v-model="newCommentText"
                    label="Your insightful comment..."
                    outlined
                    rows="3"
                    auto-grow
                    no-resize
                    :disabled="submittingComment || !isAuthenticated"
                  ></v-textarea>
                  <v-btn
                    color="primary"
                    depressed
                    large
                    :loading="submittingComment"
                    :disabled="
                      !newCommentText.trim() ||
                      submittingComment ||
                      !isAuthenticated
                    "
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
    </article>
  </v-fade-transition>
</template>

<script>
import { mapGetters } from 'vuex'
// NO direct import: import DOMPurify from 'dompurify';

export default {
  props: {
    post: {
      type: Object,
      required: true,
      default: () => ({}),
    },
    comments: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      newCommentText: '',
      submittingComment: false,
      commentError: null,
      defaultImage: 'https://cdn.vuetifyjs.com/images/parallax/material.jpg', // Placeholder
      sanitizedContent: '', // Initialize empty
      rawContent: '', // Store raw content from prop
      // Like state
      localIsLiked: false,
      localLikesCount: 0,
      likingInProgress: false,
      likeErrorSnackbar: false,
      likeErrorMessage: '',
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated', 'getUser']),
  },
  watch: {
    // Watcher now just stores the raw content and triggers client-side sanitize if needed
    post: {
      immediate: true,
      deep: true, // Watch nested properties like content, likes_count, is_liked
      handler(newPost) {
        if (newPost) {
          // Update like state from prop
          this.localIsLiked = newPost.is_liked || false
          this.localLikesCount = newPost.likes_count || 0

          // Update raw content for sanitization watcher
          this.rawContent = newPost.content || ''
          // If component is already mounted (client-side), sanitize now
          if (this.$isServer === false) {
            // $isServer is available in Nuxt context
            this.sanitizeOnClient()
          }
        }
      },
    },
  },
  mounted() {
    // Sanitize content when component mounts on the client
    this.sanitizeOnClient()
  },
  methods: {
    // Method to Sanitize Content on Client using Dynamic Import
    async sanitizeOnClient() {
      if (process.client && this.rawContent) {
        try {
          const DOMPurify = (await import('dompurify')).default
          this.sanitizedContent = DOMPurify.sanitize(this.rawContent, {
            USE_PROFILES: { html: true },
          })
        } catch (error) {
          console.error('Error loading or using DOMPurify:', error)
          this.sanitizedContent = 'Error displaying content.' // Fallback
        }
      } else if (this.rawContent) {
        this.sanitizedContent = this.rawContent // Fallback for SSR
      } else {
        this.sanitizedContent = ''
      }
    },

    formatDate(dateString) {
      if (!dateString) return ''
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      try {
        return new Date(dateString).toLocaleDateString(undefined, options)
      } catch (e) {
        console.error('Error formatting date:', dateString, e)
        return dateString
      }
    },

    async submitComment() {
      if (!this.newCommentText.trim()) return
      if (!this.isAuthenticated) {
        this.commentError = 'You must be logged in to comment.'
        return
      }

      this.submittingComment = true
      this.commentError = null
      try {
        await this.$axios.post(`/posts/${this.post.id}/comments`, {
          content: this.newCommentText,
          // Backend should get user_id from the authenticated user via Auth::id()
        })
        this.newCommentText = ''
        this.$emit('comment-submitted') // Notify parent (_id.vue) to refresh comments
      } catch (err) {
        console.error('Error posting comment:', err)
        this.commentError =
          err.response?.data?.message || 'Failed to post comment.'
      } finally {
        this.submittingComment = false
      }
    },

    // Method to Toggle Like Status
    async toggleLike() {
      if (!this.isAuthenticated) {
        this.likeErrorMessage = 'Please log in to like posts.'
        this.likeErrorSnackbar = true
        return
      }
      if (this.likingInProgress) return

      this.likingInProgress = true
      this.likeErrorSnackbar = false

      // Optimistic Update
      const originalIsLiked = this.localIsLiked
      const originalLikesCount = this.localLikesCount
      this.localIsLiked = !this.localIsLiked
      this.localLikesCount += this.localIsLiked ? 1 : -1

      try {
        const response = await this.$axios.post(`/posts/${this.post.id}/like`)
        // Update with actual response from API
        this.localIsLiked = response.data.is_liked
        this.localLikesCount = response.data.likes_count
      } catch (error) {
        console.error('Error toggling like:', error)
        this.likeErrorMessage =
          error.response?.data?.message || 'Could not update like status.'
        this.likeErrorSnackbar = true
        // Revert Optimistic Update on Error
        this.localIsLiked = originalIsLiked
        this.localLikesCount = originalLikesCount
      } finally {
        this.likingInProgress = false
      }
    },
  },
}
</script>

<style scoped lang="scss">
/* --- Styles remain the same as the previous 'revamped' version --- */
//.blog-post-article {
//}
.post-content {
  font-size: 1.1rem;
  line-height: 1.9;
  color: var(--v-text-primary-base);
  ::v-deep p {
    margin-bottom: 1.5em;
  }
  ::v-deep h2 {
    font-size: 1.8em;
    margin-top: 2em;
    margin-bottom: 0.8em;
    font-weight: 600;
  }
  ::v-deep h3 {
    font-size: 1.5em;
    margin-top: 1.8em;
    margin-bottom: 0.7em;
    font-weight: 600;
  }
  ::v-deep blockquote {
    border-left: 4px solid var(--v-primary-base);
    padding-left: 1em;
    margin-left: 0;
    margin-right: 0;
    font-style: italic;
    color: var(--v-text-secondary-base);
  }
  ::v-deep ul,
  ::v-deep ol {
    padding-left: 1.5em;
    margin-bottom: 1.5em;
  }
  ::v-deep li {
    margin-bottom: 0.5em;
  }
  ::v-deep a {
    color: var(--v-primary-base);
    text-decoration: none;
    &:hover {
      text-decoration: underline;
    }
  }
  ::v-deep img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin-top: 1em;
    margin-bottom: 1em;
  }
}
.post-title-shadow {
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
}
.post-meta-shadow {
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
}
.author-bio {
  background-color: rgba(var(--v-primary-base-rgb), 0.05);
}
.comments-section {
  margin-top: 4rem;
}
.comment-content {
  background-color: var(--v-background-lighten1, #f5f5f5);
  border: 1px solid rgba(0, 0, 0, 0.08);
  &.theme--dark {
    background-color: var(--v-background-lighten1, #333);
    border: 1px solid rgba(255, 255, 255, 0.1);
  }
}
</style>
