<template>
  <v-container>
    <v-row>
      <v-col cols="12" md="4">
        <!-- User Profile Card (no changes needed here based on the error) -->
        <v-card>
          <v-card-title>
            <span class="headline">User Profile</span>
          </v-card-title>
          <v-card-text>
            <v-list-item v-if="user">
              <!-- Add v-if for user -->
              <v-list-item-avatar>
                <v-img :src="profilePictureUrl" />
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title>{{ user.name }}</v-list-item-title>
                <v-list-item-subtitle>{{ user.email }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
            <div v-else class="text-center pa-4">Loading user...</div>
            <v-file-input
              label="Update Profile Picture"
              accept="image/*"
              class="mt-4"
              @change="onFileChange"
            ></v-file-input>
            <v-btn
              color="primary"
              :disabled="!userPFPFile"
              @click="updateProfilePicture"
            >
              Update Picture
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="8">
        <v-card>
          <v-card-title> <span class="headline">My Posts</span> </v-card-title>
          <v-card-text>
            <!-- Loading and Error states for posts -->
            <div v-if="loadingPosts" class="text-center pa-4">
              <v-progress-circular
                indeterminate
                color="primary"
              ></v-progress-circular>
              <p>Loading posts...</p>
            </div>
            <v-alert v-else-if="postsError" type="error" dense text>
              {{ postsError }}
            </v-alert>

            <!-- Use postItems (the actual array) for v-if and v-for -->
            <v-list v-else-if="postItems && postItems.length > 0">
              <v-list-item
                v-for="post in postItems"
                :key="post.id"
                @click="goToPost(post.id)"
              >
                <v-list-item-content>
                  <v-list-item-title>{{ post.title }}</v-list-item-title>
                  <v-list-item-subtitle>
                    <!-- Safe substring usage -->
                    {{ truncatePostContent(post.content) }}
                  </v-list-item-subtitle>
                </v-list-item-content>
                <v-list-item-action>
                  <v-btn icon title="Edit Post" @click.stop="editPost(post.id)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                  <v-btn
                    icon
                    title="Delete Post"
                    @click.stop="confirmDeletePost(post)"
                  >
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </v-list-item-action>
              </v-list-item>
            </v-list>
            <div v-else class="text-subtitle-1 pa-4 text-center">
              You have no posts yet.
            </div>

            <!-- Optional: Pagination for user posts if many -->
            <div
              v-if="userPostsPagination && userPostsPagination.last_page > 1"
              class="text-center mt-4"
            >
              <v-pagination
                v-model="userPostsPagination.current_page"
                :length="userPostsPagination.last_page"
                circle
                @input="handleUserPostsPageChange"
              ></v-pagination>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

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
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" bottom>
      {{ snackbar.text }}
    </v-snackbar>
  </v-container>
</template>

<script>
// No need to import axios here if using this.$axios via @nuxtjs/axios
// import { default as axios } from 'axios';

export default {
  middleware: 'writer',
  // If user object from Vuex store is reliable after login, you could use that
  // computed: {
  //   ...mapGetters('auth', ['getUserFromStore']), // Rename if 'getUser' is used elsewhere
  //   // You can then watch this.getUserFromStore to populate this.user or use it directly
  // },
  async fetch() {
    // Nuxt's fetch hook for initial data loading on server/client
    await this.fetchUserProfile()
    await this.fetchUserPosts() // Fetch initial page of user posts
  }, // Ensures user is logged in to see this page
  data() {
    return {
      // User profile data
      user: null, // Initialize as null
      userPFPFile: null, // For the <v-file-input>
      profilePictureUrl:
        'https://cdn-icons-png.flaticon.com/512/847/847969.png', // Default

      // User's posts data
      postItems: [], // THIS WILL HOLD THE ARRAY OF POSTS
      userPostsPagination: null, // To store the pagination object from backend { current_page, data, last_page, ... }
      loadingPosts: false,
      postsError: null,

      // Dialog and snackbar
      deleteDialog: { show: false, post: null },
      snackbar: { show: false, text: '', color: '' },
    }
  },
  methods: {
    async fetchUserProfile() {
      try {
        // The /api/user route should already be configured to return the authenticated user
        // based on the token which $axios should be sending automatically
        const response = await this.$axios.get('/users')
        this.user = response.data
        this.profilePictureUrl = this.user.userPFP
          ? `http://localhost:8000/storage/${this.user.userPFP}` // Adjust if your storage link is different
          : 'https://cdn-icons-png.flaticon.com/512/847/847969.png'
      } catch (error) {
        console.error('Failed to fetch user profile:', error)
        // this.showSnackbar('Failed to load user profile.', 'error');
        this.error = 'Failed to load user profile.' // Or set general page error
      }
    },

    async fetchUserPosts(page = 1) {
      this.loadingPosts = true
      this.postsError = null
      try {
        const response = await this.$axios.get('/user-posts', {
          params: { page },
          // No need to send Authorization header manually, $axios should handle it
        })
        // --- CORRECTED ASSIGNMENT ---
        this.postItems = response.data.data // Get the array from the 'data' key
        this.userPostsPagination = response.data // Store the whole pagination object
        // --- END CORRECTION ---
      } catch (error) {
        console.error('Failed to fetch user posts:', error)
        this.postsError =
          error.response?.data?.message || 'Failed to load your posts.'
        this.postItems = [] // Ensure it's an array on error
      } finally {
        this.loadingPosts = false
      }
    },
    handleUserPostsPageChange(newPage) {
      this.fetchUserPosts(newPage)
    },
    onFileChange(file) {
      // v-file-input returns the file directly
      this.userPFPFile = file
    },
    async updateProfilePicture() {
      if (!this.userPFPFile) return
      try {
        const formData = new FormData()
        formData.append('userPFP', this.userPFPFile)
        // Route '/users/update' or more specific like '/user/profile-picture'
        await this.$axios.post('/users/update', formData, {
          // 'Content-Type': 'multipart/form-data' is usually set automatically by axios
          // when sending FormData
        })
        this.showSnackbar('Profile picture updated!', 'success')
        await this.fetchUserProfile() // Refresh profile to show new picture
        this.userPFPFile = null // Clear the file input
      } catch (error) {
        console.error('Failed to update profile picture:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Failed to update profile picture.',
          'error'
        )
      }
    },
    truncatePostContent(content, maxLength = 100) {
      if (content && typeof content === 'string') {
        if (content.length > maxLength) {
          return content.substring(0, maxLength) + '...'
        }
        return content
      }
      return '' // Return empty string if content is null/undefined
    },
    goToPost(postId) {
      this.$router.push(`/BlogPostPage/${postId}`)
    },
    editPost(postId) {
      // Assuming you have an admin route or specific edit page
      this.$router.push(`/admin/posts/${postId}/edit`) // Example edit route
    },
    confirmDeletePost(post) {
      this.deleteDialog = { show: true, post }
    },
    async deletePostConfirmed() {
      if (!this.deleteDialog.post) return
      try {
        await this.$axios.delete(`/posts/${this.deleteDialog.post.id}`)
        this.showSnackbar('Post deleted successfully.', 'success')
        this.fetchUserPosts(this.userPostsPagination?.current_page || 1) // Refresh current page of posts
      } catch (error) {
        console.error('Failed to delete post:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Failed to delete post.',
          'error'
        )
      } finally {
        this.deleteDialog = { show: false, post: null }
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar.text = text
      this.snackbar.color = color
      this.snackbar.show = true
    },
  },
  head() {
    return {
      title: this.user ? `${this.user.name}'s Profile` : 'User Profile',
    }
  },
}
</script>

<style scoped>
.text-subtitle-1 {
  padding: 20px;
  color: #777;
  text-align: center;
}
</style>
