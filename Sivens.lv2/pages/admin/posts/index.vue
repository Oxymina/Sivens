<template>
  <AdminPageWrapper page-title="Manage Posts">
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4 font-weight-medium">Post Management</h1>
      <v-btn
        v-if="isWriterOrAdmin"
        color="primary"
        depressed
        to="/writer/review_creator"
      >
        <!-- Adjust link if needed -->
        <v-icon left>mdi-plus-box-outline</v-icon>
        Create New Post
      </v-btn>
    </div>

    <v-card outlined>
      <v-card-title>
        All Posts
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Posts (Title, Author)..."
          single-line
          hide-details
          dense
          clearable
          style="max-width: 350px"
          @input="debouncedFetchPosts"
        ></v-text-field>
      </v-card-title>

      <v-alert v-if="fetchError" type="error" dense text class="mx-4">{{
        fetchError
      }}</v-alert>

      <v-data-table
        v-model="selectedPosts"
        :headers="headers"
        :items="posts"
        :options.sync="options"
        :server-items-length="totalPosts"
        :loading="loading"
        :footer-props="{ 'items-per-page-options': [10, 25, 50, 100] }"
        class="elevation-0"
        item-key="id"
        show-select
      >
        <template v-slot:item.title="{ item }">
          <div
            class="font-weight-medium"
            style="
              max-width: 300px;
              overflow: hidden;
              text-overflow: ellipsis;
              white-space: nowrap;
            "
            :title="item.title"
          >
            {{ item.title }}
          </div>
        </template>

        <template v-slot:item.author.name="{ item }">
          {{ item.author ? item.author.name : 'N/A' }}
        </template>

        <template v-slot:item.category.name="{ item }">
          <v-chip
            v-if="item.category"
            small
            :color="
              item.category
                ? 'blue lighten-4 blue--text text--darken-2'
                : 'grey lighten-2'
            "
          >
            {{ item.category.name }}
          </v-chip>
          <span v-else>N/A</span>
        </template>

        <template v-slot:item.created_at="{ item }">
          {{ formatDate(item.created_at) }}
        </template>

        <template v-slot:item.actions="{ item }">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                color="blue"
                :href="`/ReviewPostPage/${item.id}`"
                target="_blank"
                v-bind="attrs"
                v-on="on"
              >
                <v-icon small>mdi-eye-outline</v-icon>
              </v-btn>
            </template>
            <span>View Post</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                color="green"
                :to="`/writer/${item.id}/review_editor`"
                v-bind="attrs"
                v-on="on"
              >
                <v-icon small>mdi-pencil-outline</v-icon>
              </v-btn>
            </template>
            <span>Edit Post Content</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                color="cyan"
                :to="`/admin/posts/comments/${item.id}`"
                v-bind="attrs"
                v-on="on"
              >
                <v-icon small>mdi-comment-text-multiple-outline</v-icon>
              </v-btn>
            </template>
            <span>Manage Comments</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                color="orange"
                v-bind="attrs"
                @click="openAuthorChangeDialog(item)"
                v-on="on"
              >
                <v-icon small>mdi-account-switch-outline</v-icon>
              </v-btn>
            </template>
            <span>Change Author</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                color="error"
                v-bind="attrs"
                @click="confirmDelete(item)"
                v-on="on"
              >
                <v-icon small>mdi-delete-outline</v-icon>
              </v-btn>
            </template>
            <span>Delete Post</span>
          </v-tooltip>
        </template>
        <template v-slot:no-data>
          <div v-if="!loading" class="text-center pa-4 grey--text">
            No posts found.
          </div>
        </template>
      </v-data-table>

      <!-- Bulk Actions Bar -->
      <v-card-actions
        v-if="selectedPosts.length > 0"
        class="grey lighten-3 pa-2 elevation-1"
      >
        <span class="text-caption"
          >{{ selectedPosts.length }} post(s) selected</span
        >
        <v-spacer></v-spacer>
        <!-- Add bulk author change if needed, similar to bulk role change for users -->
        <v-btn small outlined color="error" @click="confirmBulkDelete">
          <v-icon left small>mdi-delete-sweep-outline</v-icon>
          Delete Selected ({{ selectedPosts.length }})
        </v-btn>
      </v-card-actions>
    </v-card>

    <!-- Post Author Change Dialog -->
    <PostAuthorChangeDialog
      :dialog.sync="authorChangeDialog.show"
      :post="authorChangeDialog.post"
      :users="allUsersList"
      @author-changed="onPostAuthorChanged"
    />

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog.show" persistent max-width="450px">
      <v-card>
        <v-card-title class="text-h5 error--text"
          >Confirm Deletion</v-card-title
        >
        <v-card-text v-if="deleteDialog.isBulk"
          >Are you sure you want to delete {{ selectedPosts.length }} selected
          post(s)? This action cannot be undone.</v-card-text
        >
        <v-card-text v-else
          >Are you sure you want to delete the post: "{{
            deleteDialog.post ? deleteDialog.post.title : ''
          }}"? This cannot be undone.</v-card-text
        >
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog.show = false">Cancel</v-btn>
          <v-btn color="error" depressed @click="deleteConfirmed">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      bottom
      :timeout="4000"
    >
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.show = false">Close</v-btn>
      </template>
    </v-snackbar>
  </AdminPageWrapper>
</template>

<script>
import _ from 'lodash'
import { mapGetters } from 'vuex' // For isWriter getter
import AdminPageWrapper from '~/components/admin/AdminPageWrapper.vue'
import PostAuthorChangeDialog from '~/components/admin/PostAuthorChangeDialog.vue' // You created this earlier

export default {
  name: 'AdminPostsPage',
  components: { AdminPageWrapper, PostAuthorChangeDialog },
  middleware: 'admin',
  layout: 'empty',
  data() {
    return {
      posts: [],
      totalPosts: 0,
      loading: true,
      fetchError: null,
      search: '',
      options: {
        // v-data-table options
        page: 1,
        itemsPerPage: 10,
        sortBy: ['created_at'], // Default sort
        sortDesc: [true], // Default sort direction
      },
      headers: [
        // { text: 'ID', value: 'id', sortable: true, width: '70px' }, // Optional
        { text: 'Title', value: 'title', sortable: true, width: '35%' },
        { text: 'Author', value: 'author.name', sortable: true }, // Can sort by author name if backend supports it
        { text: 'Category', value: 'category.name', sortable: true }, // Can sort by category name if backend supports it
        { text: 'Published', value: 'created_at', sortable: true },
        {
          text: 'Actions',
          value: 'actions',
          sortable: false,
          align: 'end',
          width: '220px',
        }, // Increased width
      ],
      selectedPosts: [], // For bulk actions
      allUsersList: [], // To populate the author change dropdown
      authorChangeDialog: { show: false, post: null },
      deleteDialog: { show: false, post: null, isBulk: false },
      snackbar: { show: false, text: '', color: '' },
      debouncedFetchPosts: null,
    }
  },
  computed: {
    ...mapGetters('auth', ['isWriter']), // Get writer status (admin might also be a writer)
    isWriterOrAdmin() {
      // Helper for the create button
      return this.isWriter // Since this page is admin-only, isAdmin is implied.
      // Adjust if 'writer' role is distinctly different and admins AREN'T writers.
    },
  },
  watch: {
    options: {
      // Watch for v-data-table options changes (pagination, sorting)
      handler() {
        this.fetchPosts()
      },
      deep: true,
    },
  },
  created() {
    this.debouncedFetchPosts = _.debounce(this.fetchPosts, 500)
  },
  async mounted() {
    await this.fetchAllUsersSimpleList() // Fetch all users for the author change dropdown
    await this.fetchPosts() // Initial fetch for posts
  },
  methods: {
    async fetchPosts() {
      this.loading = true
      this.fetchError = null
      try {
        const { sortBy, sortDesc, page, itemsPerPage } = this.options
        const params = {
          page,
          perPage: itemsPerPage,
          search: this.search,
          // Backend needs to handle these sort parameters for relationships
          sortBy: sortBy.length ? sortBy[0] : 'created_at',
          sortDesc: sortDesc.length ? (sortDesc[0] ? 'desc' : 'asc') : 'desc', // Send 'asc' or 'desc'
        }

        // Using a dedicated admin endpoint that includes necessary related data for posts
        const response = await this.$axios.get('/admin/posts', { params })
        this.posts = response.data.data
        this.totalPosts = response.data.total
      } catch (error) {
        console.error('Error fetching posts:', error)
        this.fetchError =
          error.response?.data?.message || 'Failed to load posts.'
        this.showSnackbar('Failed to load posts.', 'error')
      } finally {
        this.loading = false
      }
    },
    async fetchAllUsersSimpleList() {
      // This endpoint should return a simple list: [{ id: 1, name: 'John Doe' }, ...]
      try {
        const response = await this.$axios.get('/admin/users-list')
        this.allUsersList = response.data
      } catch (error) {
        console.error('Error fetching users list for author change:', error)
        this.showSnackbar(
          'Could not load users for author selection.',
          'warning'
        )
        this.allUsersList = []
      }
    },
    formatDate(dateString) {
      return dateString ? new Date(dateString).toLocaleDateString() : 'N/A'
    },
    openAuthorChangeDialog(post) {
      if (this.allUsersList.length === 0) {
        this.showSnackbar(
          'User list not loaded. Cannot change author.',
          'warning'
        )
        return
      }
      this.authorChangeDialog = { show: true, post: { ...post } } // Pass a copy
    },
    onPostAuthorChanged() {
      this.showSnackbar('Post author updated successfully.', 'success')
      this.fetchPosts() // Refresh post list
    },
    confirmDelete(post) {
      this.deleteDialog = { show: true, post, isBulk: false }
    },
    confirmBulkDelete() {
      if (this.selectedPosts.length === 0) {
        this.showSnackbar('No posts selected for deletion.', 'info')
        return
      }
      this.deleteDialog = { show: true, post: null, isBulk: true }
    },
    async deleteConfirmed() {
      let postsToDeleteIds = []
      let operationText = ''

      if (this.deleteDialog.isBulk && this.selectedPosts.length > 0) {
        postsToDeleteIds = this.selectedPosts.map((p) => p.id)
        operationText = `${this.selectedPosts.length} post(s)`
      } else if (this.deleteDialog.post) {
        postsToDeleteIds.push(this.deleteDialog.post.id)
        operationText = `post "${this.deleteDialog.post.title}"`
      }

      if (postsToDeleteIds.length === 0) {
        this.deleteDialog.show = false
        return
      }

      this.loading = true // Can use a different loading state for delete action
      try {
        // Backend needs to handle single or bulk delete.
        // For bulk, ideally a single request: DELETE /api/admin/posts?ids[]=1&ids[]=2
        // Or loop if simpler for now:
        for (const postId of postsToDeleteIds) {
          await this.$axios.delete(`/admin/posts/${postId}`) // Admin delete endpoint
        }
        this.showSnackbar(`Successfully deleted ${operationText}.`, 'success')
        this.fetchPosts() // Refresh list
        this.selectedPosts = [] // Clear selection
      } catch (error) {
        console.error(`Error deleting ${operationText}:`, error)
        this.showSnackbar(`Failed to delete ${operationText}.`, 'error')
      } finally {
        this.loading = false
        this.deleteDialog = { show: false, post: null, isBulk: false }
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
  },
  head() {
    return {
      title: 'Manage Posts - Admin',
    }
  },
}
</script>

<style scoped>
/* Ensure table titles don't wrap aggressively and have ellipsis */
.v-data-table td > div.font-weight-medium {
  display: block; /* Or inline-block */
  max-width: 300px; /* Adjust as needed */
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
