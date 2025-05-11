<template>
  <AdminPageWrapper page-title="Manage All Posts">
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4 font-weight-medium">Post Management</h1>
      <!-- Button to go to public create page if admin is also a writer -->
      <v-btn
        v-if="isWriter"
        color="green lighten-1"
        dark
        depressed
        to="/posts/create"
      >
        <v-icon left>mdi-plus-circle-outline</v-icon> Create New Post (Writer)
      </v-btn>
    </div>

    <v-card outlined>
      <v-card-title>
        All Blog Posts
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Posts (Title)..."
          single-line
          hide-details
          dense
          clearable
          style="max-width: 300px"
          @input="debouncedFetchAdminPosts"
        ></v-text-field>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="adminPosts"
        :options.sync="options"
        :server-items-length="totalAdminPosts"
        :loading="loading"
        :footer-props="{ 'items-per-page-options': [10, 25, 50] }"
        class="elevation-0"
        item-key="id"
      >
        <template v-slot:item.title="{ item }">
          <strong class="blue--text text--darken-2">{{ item.title }}</strong>
        </template>
        <template v-slot:item.author="{ item }">
          {{ item.author ? item.author.name : 'N/A' }}
        </template>
        <template v-slot:item.category="{ item }">
          {{ item.category ? item.category.name : 'N/A' }}
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
                :href="`/BlogPostPage/${item.id}`"
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
            <!-- Link to manage comments for this post -->
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                color="cyan"
                :to="`/admin/posts/comments/${item.id}`"
                v-bind="attrs"
                v-on="on"
              >
                <v-icon small>mdi-comment-multiple-outline</v-icon>
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
                @click="confirmDeletePost(item)"
                v-on="on"
              >
                <v-icon small>mdi-delete-outline</v-icon>
              </v-btn>
            </template>
            <span>Delete Post</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>

    <!-- Post Author Change Dialog -->
    <PostAuthorChangeDialog
      :dialog.sync="authorChangeDialog.show"
      :post="authorChangeDialog.post"
      :users="allUsersList"
      @author-changed="onPostAuthorChanged"
    />

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog.show" persistent max-width="400px">
      <v-card>
        <v-card-title class="text-h5 error--text"
          >Confirm Deletion</v-card-title
        >
        <v-card-text
          >Are you sure you want to delete the post: "{{
            deleteDialog.post ? deleteDialog.post.title : ''
          }}"?</v-card-text
        >
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog.show = false">Cancel</v-btn>
          <v-btn color="error" depressed @click="deletePostConfirmed"
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
import _ from 'lodash'
import { mapGetters } from 'vuex' // For checking if admin is also a writer
import AdminPageWrapper from '~/components/admin/AdminPageWrapper.vue'
import PostAuthorChangeDialog from '~/components/admin/PostAuthorChangeDialog.vue' // New component

export default {
  name: 'AdminPostsManagementPage',
  components: { AdminPageWrapper, PostAuthorChangeDialog },
  middleware: 'admin',
  data() {
    return {
      adminPosts: [],
      totalAdminPosts: 0,
      loading: true,
      search: '',
      options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['created_at'],
        sortDesc: [true],
      },
      headers: [
        { text: 'Title', value: 'title', sortable: true },
        { text: 'Author', value: 'author', sortable: true }, // Sorting by author.name needs backend support
        { text: 'Category', value: 'category', sortable: true }, // Sorting by category.name needs backend
        { text: 'Published', value: 'created_at', sortable: true },
        {
          text: 'Actions',
          value: 'actions',
          sortable: false,
          align: 'end',
          width: '200px',
        }, // Increased width
      ],
      allUsersList: [], // For author change dropdown
      authorChangeDialog: { show: false, post: null },
      deleteDialog: { show: false, post: null },
      snackbar: { show: false, text: '', color: '' },
      debouncedFetchAdminPosts: null,
    }
  },
  computed: {
    ...mapGetters('auth', ['isWriter']), // Check if admin can also write
  },
  watch: {
    options: {
      handler() {
        this.fetchAdminPosts()
      },
      deep: true,
    },
  },
  created() {
    this.debouncedFetchAdminPosts = _.debounce(this.fetchAdminPosts, 500)
  },
  async mounted() {
    await this.fetchAllUsersList() // Fetch users for the dropdown
    await this.fetchAdminPosts()
  },
  methods: {
    async fetchAdminPosts() {
      this.loading = true
      try {
        const { sortBy, sortDesc, page, itemsPerPage } = this.options
        const response = await this.$axios.get('/admin/posts', {
          // Dedicated admin endpoint
          params: {
            page,
            perPage: itemsPerPage,
            search: this.search,
            sortBy: sortBy[0],
            sortDesc: sortDesc[0],
          },
        })
        this.adminPosts = response.data.data
        this.totalAdminPosts = response.data.total
      } catch (error) {
        console.error('Error fetching admin posts:', error)
        this.showSnackbar('Failed to load posts.', 'error')
      } finally {
        this.loading = false
      }
    },
    async fetchAllUsersList() {
      // Fetch a simple list of all users for the author dropdown
      // Needs a simple backend endpoint e.g., /api/admin/users/list or similar that returns {id, name}
      try {
        const response = await this.$axios.get('/admin/users-list') // Assumed new endpoint
        this.allUsersList = response.data
      } catch (error) {
        console.error('Failed to fetch user list for author change:', error)
        this.allUsersList = [] // Fallback
      }
    },
    formatDate(dateString) {
      return dateString ? new Date(dateString).toLocaleDateString() : 'N/A'
    },
    openAuthorChangeDialog(post) {
      this.authorChangeDialog = { show: true, post: { ...post } }
    },
    onPostAuthorChanged() {
      this.showSnackbar('Post author updated successfully.', 'success')
      this.fetchAdminPosts()
    },
    confirmDeletePost(post) {
      this.deleteDialog = { show: true, post }
    },
    async deletePostConfirmed() {
      if (!this.deleteDialog.post) return
      try {
        await this.$axios.delete(`/admin/posts/${this.deleteDialog.post.id}`) // Admin delete endpoint
        this.showSnackbar('Post deleted successfully.', 'success')
        this.fetchAdminPosts()
      } catch (error) {
        console.error('Error deleting post:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Failed to delete post.',
          'error'
        )
      } finally {
        this.deleteDialog = { show: false, post: null }
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
  },
  head() {
    return { title: 'Manage Posts (Admin)' }
  },
}
</script>
