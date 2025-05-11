<template>
  <AdminPageWrapper
    :page-title="post ? `Comments for: ${post.title}` : 'Manage Comments'"
  >
    <div v-if="loadingPost" class="text-center pa-8">
      <v-progress-circular
        indeterminate
        color="primary"
        size="50"
      ></v-progress-circular>
      <p class="mt-3">Loading Post Details...</p>
    </div>
    <v-alert v-else-if="postError" type="error" dense text>
      {{ postError }}
      <v-btn small text to="/admin/posts">Back to Posts List</v-btn>
    </v-alert>

    <template v-if="post">
      <div class="d-flex justify-space-between align-center mb-4">
        <h1 class="text-h5 font-weight-medium">
          Comments for "{{ post.title }}"
        </h1>
        <v-btn text to="/admin/posts"
          ><v-icon left>mdi-arrow-left</v-icon> Back to Posts</v-btn
        >
      </div>

      <v-card outlined>
        <v-card-title>
          All Comments ({{ totalComments }})
          <v-spacer></v-spacer>
          <v-btn icon :loading="loadingComments" @click="fetchPostComments">
            <v-icon>mdi-refresh</v-icon>
          </v-btn>
        </v-card-title>

        <v-alert
          v-if="commentFetchError"
          type="warning"
          dense
          text
          class="ma-4"
        >
          {{ commentFetchError }}
        </v-alert>

        <v-list v-if="comments.length > 0" three-line>
          <template v-for="(comment, index) in comments">
            <v-list-item :key="comment.id">
              <v-list-item-avatar color="primary">
                <span class="white--text text-h6">{{
                  comment.user && comment.user.name
                    ? comment.user.name.charAt(0).toUpperCase()
                    : 'A'
                }}</span>
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title class="font-weight-medium">
                  {{ comment.user ? comment.user.name : 'Anonymous User' }}
                  <span class="text--disabled text-caption ml-2"
                    >({{
                      comment.user ? comment.user.email : 'No Email'
                    }})</span
                  >
                </v-list-item-title>
                <v-list-item-subtitle
                  style="white-space: pre-wrap"
                  class="text--primary body-2 my-1"
                  >{{ comment.content }}</v-list-item-subtitle
                >
                <v-list-item-subtitle class="text-caption">{{
                  formatDate(comment.created_at)
                }}</v-list-item-subtitle>
              </v-list-item-content>
              <v-list-item-action>
                <v-tooltip bottom>
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn
                      icon
                      small
                      color="error"
                      v-bind="attrs"
                      @click="confirmDeleteComment(comment)"
                      v-on="on"
                    >
                      <v-icon small>mdi-delete-outline</v-icon>
                    </v-btn>
                  </template>
                  <span>Delete Comment</span>
                </v-tooltip>
              </v-list-item-action>
            </v-list-item>
            <v-divider
              v-if="index < comments.length - 1"
              :key="`divider-${comment.id}`"
              inset
            ></v-divider>
          </template>
        </v-list>
        <div
          v-else-if="!loadingComments && !commentFetchError"
          class="text-center pa-6 text--disabled"
        >
          No comments found for this post.
        </div>
        <div v-if="totalPages > 1" class="text-center pa-4">
          <v-pagination
            v-model="currentPage"
            :length="totalPages"
            circle
            @input="fetchPostComments"
          ></v-pagination>
        </div>
      </v-card>
    </template>

    <!-- Delete Comment Confirmation -->
    <v-dialog v-model="deleteCommentDialog.show" persistent max-width="450px">
      <v-card>
        <v-card-title class="text-h5 warning--text"
          >Delete Comment</v-card-title
        >
        <v-card-text>
          Are you sure you want to delete this comment? <br />
          <blockquote
            class="mt-2 grey lighten-3 pa-2"
            style="border-left: 3px solid orange"
          >
            "{{
              deleteCommentDialog.comment
                ? truncateText(deleteCommentDialog.comment.content, 100)
                : ''
            }}"
          </blockquote>
          by
          {{
            deleteCommentDialog.comment && deleteCommentDialog.comment.user
              ? deleteCommentDialog.comment.user.name
              : 'User'
          }}
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteCommentDialog.show = false">Cancel</v-btn>
          <v-btn color="warning" depressed @click="deleteCommentConfirmed"
            >Delete</v-btn
          >
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      bottom
      :timeout="3000"
    >
      {{ snackbar.text }}
    </v-snackbar>
  </AdminPageWrapper>
</template>

<script>
import AdminPageWrapper from '~/components/admin/AdminPageWrapper.vue'

export default {
  name: 'AdminManagePostCommentsPage',
  components: { AdminPageWrapper },
  layout: 'admin',
  middleware: 'admin',
  async asyncData({ $axios, params, error }) {
    try {
      const postData = await $axios.$get(`/posts/${params.postId}`) // Fetch the post details (title)
      const commentsData = await $axios.$get(
        `/admin/posts/${params.postId}/comments`,
        { params: { page: 1 } }
      ) // Fetch first page of comments via admin endpoint
      return {
        post: postData,
        comments: commentsData.data,
        totalPages: commentsData.last_page,
        totalComments: commentsData.total,
        currentPage: commentsData.current_page,
        loadingPost: false,
        postError: null,
      }
    } catch (err) {
      console.error('Error fetching initial data for comments page:', err)
      error({
        statusCode: err.response?.status || 500,
        message: err.response?.data?.message || 'Failed to load data.',
      })
      return {
        post: null,
        comments: [],
        totalPages: 0,
        totalComments: 0,
        currentPage: 1,
        loadingPost: false,
        postError: 'Failed to load post data.',
      }
    }
  },
  data() {
    return {
      // post, comments, totalPages, totalComments, currentPage populated by asyncData
      loadingComments: false,
      commentFetchError: null,
      deleteCommentDialog: { show: false, comment: null },
      snackbar: { show: false, text: '', color: '' },
    }
  },
  computed: {
    postId() {
      return this.$route.params.postId
    },
  },
  methods: {
    async fetchPostComments() {
      this.loadingComments = true
      this.commentFetchError = null
      try {
        // Needs API: /api/admin/posts/{postId}/comments (paginated)
        const response = await this.$axios.get(
          `/admin/posts/${this.postId}/comments`,
          {
            params: { page: this.currentPage },
          }
        )
        this.comments = response.data.data
        this.totalPages = response.data.last_page
        this.totalComments = response.data.total
        this.currentPage = response.data.current_page
      } catch (error) {
        console.error('Error fetching comments:', error)
        this.commentFetchError =
          error.response?.data?.message || 'Failed to load comments.'
        this.showSnackbar('Failed to load comments.', 'error')
      } finally {
        this.loadingComments = false
      }
    },
    formatDate(dateString) {
      return dateString ? new Date(dateString).toLocaleString() : 'N/A'
    },
    truncateText(text, length = 50) {
      if (!text) return ''
      return text.length > length ? text.substring(0, length) + '...' : text
    },
    confirmDeleteComment(comment) {
      this.deleteCommentDialog = { show: true, comment }
    },
    async deleteCommentConfirmed() {
      if (!this.deleteCommentDialog.comment) return
      try {
        // Needs API: DELETE /api/admin/comments/{commentId}
        await this.$axios.delete(
          `/admin/comments/${this.deleteCommentDialog.comment.id}`
        )
        this.showSnackbar('Comment deleted successfully.', 'success')
        this.fetchPostComments() // Refresh the list
      } catch (error) {
        console.error('Error deleting comment:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Failed to delete comment.',
          'error'
        )
      } finally {
        this.deleteCommentDialog = { show: false, comment: null }
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
  },
  head() {
    return {
      title: this.post ? `Comments for: ${this.post.title}` : 'Manage Comments',
    }
  },
}
</script>
