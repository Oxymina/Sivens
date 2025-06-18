<template>
  <section
    :class="$vuetify.theme.dark ? 'grey darken-4' : 'grey lighten-5'"
    class="pa-4 pa-md-8 page-min-height"
  >
    <v-container>
      <div class="d-flex justify-space-between align-center mb-6">
        <h1 class="text-h4 font-weight-bold">
          {{ isAdmin ? 'All Reviews' : 'My Penned Reviews' }}
        </h1>
        <v-btn
          v-if="isWriterOrAdmin"
          color="primary"
          depressed
          to="/writer/review_creator"
        >
          <v-icon left>mdi-plus-box-outline</v-icon>
          Write New Review
        </v-btn>
      </div>

      <v-card outlined>
        <v-card-title class="pb-0">
          <v-row no-gutters align="center">
            <v-col cols="12" md="4">
              <span class="text-h6">Review List</span>
            </v-col>
            <v-col cols="12" md="4" class="my-2 my-md-0">
              <v-select
                v-if="isAdmin && authors.length > 1"
                v-model="filterAuthorId"
                :items="authorsForFilter"
                item-text="name"
                item-value="id"
                label="Filter by Author"
                outlined
                dense
                hide-details
                clearable
                @change="handleFilterChange"
              ></v-select>
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Search Reviews (Title)..."
                single-line
                hide-details
                dense
                clearable
                outlined
                @input="debouncedFetchMyPosts"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-card-title>
        <v-divider class="mt-2"></v-divider>

        <v-alert v-if="fetchError" type="error" dense text class="mx-4 mt-4">{{
          fetchError
        }}</v-alert>

        <v-data-table
          :headers="headers"
          :items="myPosts"
          :options.sync="options"
          :server-items-length="totalMyPosts"
          :loading="loading"
          :footer-props="{ 'items-per-page-options': [5, 10, 15, 25] }"
          class="elevation-0"
          item-key="id"
        >
          <template v-slot:item.title="{ item }">
            <nuxt-link
              :to="`/ReviewPostPage/${item.id}`"
              target="_blank"
              class="font-weight-medium text-decoration-none"
            >
              {{ item.title }}
            </nuxt-link>
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
                  color="green"
                  :to="`/writer/${item.id}/review_editor`"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
              </template>
              <span>Edit Review</span>
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
              <span>Delete Review</span>
            </v-tooltip>
          </template>
          <template v-slot:no-data>
            <div v-if="!loading" class="text-center pa-6 grey--text">
              <v-icon large class="d-block mx-auto mb-2"
                >mdi-text-box-search-outline</v-icon
              >
              No reviews found matching your criteria.
            </div>
          </template>
        </v-data-table>
      </v-card>

      <!-- Delete Confirmation Dialog -->
      <v-dialog v-model="deleteDialog.show" persistent max-width="450px">
        <v-card>
          <v-card-title class="text-h5 error--text">
            <v-icon left color="error">mdi-alert-circle-outline</v-icon>Confirm
            Deletion
          </v-card-title>
          <v-card-text class="body-1 pt-3"
            >Are you sure you want to permanently delete the review:
            <strong class="d-block mt-1"
              >"{{ deleteDialog.post ? deleteDialog.post.title : '' }}"</strong
            >? This action cannot be undone.
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-spacer></v-spacer>
            <v-btn
              text
              :disabled="deletingPost"
              @click="deleteDialog.show = false"
              >Cancel</v-btn
            >
            <v-btn
              color="error"
              depressed
              :loading="deletingPost"
              @click="deletePostConfirmed"
              >Delete</v-btn
            >
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
          <v-btn text v-bind="attrs" @click="snackbar.show = false"
            >Close</v-btn
          >
        </template>
      </v-snackbar>
    </v-container>
  </section>
</template>

<script>
import _ from 'lodash'
import { mapGetters } from 'vuex'

export default {
  name: 'MyReviewsPage',
  middleware: 'writer',
  data() {
    return {
      myPosts: [],
      totalMyPosts: 0,
      loading: true,
      fetchError: null,
      search: '',
      filterAuthorId: null, // For admin to filter by author
      authors: [], // For admin's author filter dropdown
      options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['created_at'],
        sortDesc: [true],
      },
      headers: [
        { text: 'Title', value: 'title', sortable: true, width: '40%' },
        { text: 'Author', value: 'author.name', sortable: true }, // Requires backend sorting support
        { text: 'Category', value: 'category.name', sortable: true }, // Requires backend sorting support
        { text: 'Published', value: 'created_at', sortable: true },
        {
          text: 'Actions',
          value: 'actions',
          sortable: false,
          align: 'end',
          width: '130px',
        },
      ],
      deleteDialog: { show: false, post: null },
      deletingPost: false,
      snackbar: { show: false, text: '', color: '' },
      debouncedFetchMyPosts: null,
    }
  },
  computed: {
    ...mapGetters('auth', [
      'isAuthenticated',
      'getUser',
      'isAdmin',
      'isWriter',
    ]),
    isWriterOrAdmin() {
      return this.isWriter || this.isAdmin
    },
    authorsForFilter() {
      // Add "All Authors" option if admin
      const options = this.authors.map((author) => ({
        id: author.id,
        name: author.name,
      }))
      if (this.isAdmin) {
        options.unshift({ id: null, name: 'All Authors' })
      }
      return options
    },
  },
  watch: {
    options: {
      handler() {
        this.fetchMyPosts()
      },
      deep: true,
    },
    // If admin changes filterAuthorId, re-fetch
    // filterAuthorId() {
    //    if(this.isAdmin) this.fetchMyPosts();
    // }
  },
  created() {
    this.debouncedFetchMyPosts = _.debounce(this.fetchMyPosts, 500)
    if (!this.isWriterOrAdmin && this.$nuxt) {
      // Extra guard, middleware should handle this
      return this.$nuxt.error({
        statusCode: 403,
        message: "You don't have permission to view or manage posts.",
      })
    }
  },
  async mounted() {
    if (this.isAdmin) {
      await this.fetchAuthorsForFilter()
      // For admin, remove the default author specific sort from headers initially.
      // Let them choose.
    }
    await this.fetchMyPosts()
  },
  methods: {
    async fetchMyPosts() {
      this.loading = true
      this.fetchError = null
      try {
        const { sortBy, sortDesc, page, itemsPerPage } = this.options
        let endpoint = '/user-posts' // Default for writers (their own posts)
        const params = {
          page,
          perPage: itemsPerPage,
          search: this.search,
          sortBy: sortBy.length ? sortBy[0] : 'created_at',
          sortDesc: sortDesc.length ? (sortDesc[0] ? 'desc' : 'asc') : 'desc',
        }

        if (this.isAdmin) {
          endpoint = '/admin/posts' // Admin fetches all posts
          if (this.filterAuthorId) {
            params.author_id = this.filterAuthorId
          }
        }

        const response = await this.$axios.get(endpoint, { params })
        this.myPosts = response.data.data || []
        this.totalMyPosts = response.data.total || 0
      } catch (error) {
        console.error('Error fetching posts:', error.response?.data || error)
        this.fetchError =
          error.response?.data?.message || 'Failed to load your posts.'
        this.myPosts = []
        this.totalMyPosts = 0
      } finally {
        this.loading = false
      }
    },
    async fetchAuthorsForFilter() {
      if (!this.isAdmin) return
      try {
        const response = await this.$axios.get('/admin/users-list') // API to get {id, name} of users
        this.authors = response.data || []
      } catch (error) {
        console.error('Failed to fetch authors for filter:', error)
        this.showSnackbar('Could not load authors list.', 'warning')
      }
    },
    handleFilterChange() {
      // Called by search @input or author filter @change
      this.options.page = 1 // Reset to first page on filter change
      this.fetchMyPosts()
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const options = { year: 'numeric', month: 'short', day: 'numeric' }
      try {
        return new Date(dateString).toLocaleDateString(undefined, options)
      } catch (e) {
        return dateString
      }
    },
    goToPost(postId) {
      this.$router.push(`/ReviewPostPage/${postId}`)
    }, // Adjust your public post view route
    editPost(postId) {
      this.$router.push(`/my-reviews/${postId}/edit`) // Route to the specific edit page
    },
    confirmDelete(post) {
      this.deleteDialog = { show: true, post }
    },
    async deletePostConfirmed() {
      if (!this.deleteDialog.post) return
      this.deletingPost = true
      const postIdToDelete = this.deleteDialog.post.id
      try {
        // Admins use a different endpoint, writers can use their own
        const deleteEndpoint = this.isAdmin
          ? `/admin/posts/${postIdToDelete}`
          : `/posts/${postIdToDelete}`
        await this.$axios.delete(deleteEndpoint)

        this.showSnackbar('Review deleted successfully.', 'success')
        // Check if the deleted item was the last on the page and if not on the first page
        if (this.myPosts.length === 1 && this.options.page > 1) {
          this.options.page -= 1 // Go to previous page
        }
        this.fetchMyPosts() // Refresh list
      } catch (error) {
        console.error('Error deleting post:', error.response?.data || error)
        this.showSnackbar(
          error.response?.data?.message || 'Failed to delete review.',
          'error'
        )
      } finally {
        this.deletingPost = false
        this.deleteDialog = { show: false, post: null }
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
  },
  head() {
    return { title: this.isAdmin ? 'Manage All Reviews' : 'My Penned Reviews' }
  },
}
</script>

<style scoped>
.page-min-height {
  min-height: 100vh;
}
/* Add more styles as needed */
</style>
