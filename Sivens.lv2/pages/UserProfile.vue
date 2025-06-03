<template>
  <v-container
    fluid
    class="user-profile-page pa-4 pa-md-6"
    :class="
      $vuetify.theme.dark
        ? 'grey darken-4 page-background'
        : 'grey lighten-5 page-background'
    "
  >
    <!-- Overall Page Loading State -->
    <div
      v-if="loadingUser && !user"
      class="text-center py-16 fill-height d-flex flex-column justify-center align-center"
    >
      <v-progress-circular
        indeterminate
        color="primary"
        size="64"
        width="4"
      ></v-progress-circular>
      <p class="mt-6 text-h6">Loading Your Profile...</p>
    </div>

    <!-- Page Error State -->
    <v-alert
      v-else-if="userError"
      type="error"
      prominent
      border="left"
      class="my-8 mx-auto elevation-3"
      max-width="700px"
    >
      <div class="text-h6 font-weight-medium">Error Loading Profile</div>
      <p class="mb-1 body-1">{{ userError }}</p>
      <div class="mt-3">
        <v-btn
          text
          small
          color="error darken-2"
          class="mr-2"
          @click="refreshAllData"
        >
          <v-icon left small>mdi-refresh</v-icon>Try Again
        </v-btn>
        <v-btn text small to="/">
          <v-icon left small>mdi-home</v-icon>Go Home
        </v-btn>
      </div>
    </v-alert>

    <!-- Main Content if User Loaded -->
    <v-row
      v-else-if="user"
      :justify="isWriterOrAdminComputed ? 'start' : 'center'"
      class="main-content-row"
    >
      <!-- Left Column: User Profile Card -->
      <v-col
        cols="12"
        :md="isWriterOrAdminComputed ? 5 : 10"
        :lg="isWriterOrAdminComputed ? 4 : 8"
        :xl="isWriterOrAdminComputed ? 3 : 6"
        class="profile-column mb-6 mb-md-0"
      >
        <v-card class="elevation-3">
          <v-img
            height="200px"
            :src="user.profile_cover || defaultCoverImage"
            alt="Profile cover image"
            class="white--text align-end"
            gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.7)"
          >
          </v-img>

          <div class="d-flex justify-center" style="margin-top: -48px">
            <v-avatar
              size="96"
              class="profile-avatar elevation-6"
              color="grey lighten-2"
            >
              <v-img
                :src="profilePictureUrl"
                :alt="`${user.name || 'User'}'s Avatar`"
                cover
              />
            </v-avatar>
          </div>

          <v-card-text class="text-center pt-4">
            <div class="text-h6 mt-2 mb-1">{{ user.name }}</div>
            <div class="body-1 text--secondary mb-3">{{ user.email }}</div>
            <v-chip
              small
              :color="getRoleColor(user.role_name)"
              dark
              class="font-weight-medium px-3 py-1"
              label
            >
              <v-icon left small>{{ getRoleIcon(user.role_name) }}</v-icon>
              {{ user.role_name ? user.role_name.toUpperCase() : 'N/A' }}
            </v-chip>
          </v-card-text>

          <v-divider class="my-0 mx-4"></v-divider>

          <v-card-actions class="justify-center pa-4 pt-0">
            <v-btn
              color="blue-grey darken-1"
              text
              class="font-weight-medium"
              @click="openSettingsDialog"
            >
              <v-icon left>mdi-cog-outline</v-icon>
              Account Settings
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>

      <!-- Right Column: User's Authored Posts (ONLY if writer/admin) -->
      <v-col
        v-if="isWriterOrAdminComputed"
        cols="12"
        md="7"
        lg="8"
        xl="9"
        class="posts-column"
      >
        <v-card class="elevation-3">
          <v-card-title class="grey lighten-4">
            <v-icon left color="primary"
              >mdi-newspaper-variant-multiple-outline</v-icon
            >
            <span class="text-h6 font-weight-medium">My Authored Posts</span>
            <v-spacer></v-spacer>
            <v-btn
              icon
              :loading="loadingPosts"
              title="Refresh posts"
              @click="fetchUserPosts(userPostsPagination?.current_page || 1)"
            >
              <v-icon>mdi-refresh</v-icon>
            </v-btn>
          </v-card-title>
          <v-divider></v-divider>

          <v-card-text class="pa-0">
            <div
              v-if="loadingPosts && (!postItems || postItems.length === 0)"
              class="text-center py-12"
            >
              <v-progress-circular
                indeterminate
                color="primary"
                size="40"
              ></v-progress-circular>
              <p class="mt-4 text--secondary">Loading your posts...</p>
            </div>
            <v-alert
              v-else-if="postsError"
              type="warning"
              dense
              text
              class="ma-4"
              border="left"
            >
              {{ postsError }}
            </v-alert>

            <v-list
              v-else-if="postItems && postItems.length > 0"
              two-line
              class="py-0"
            >
              <template v-for="(post, index) in postItems">
                <v-list-item
                  :key="post.id"
                  link
                  @click.stop.prevent="goToPost(post.id)"
                >
                  <v-list-item-avatar
                    v-if="post.post_image"
                    tile
                    color="grey lighten-3"
                    size="60"
                    class="rounded"
                  >
                    <v-img
                      :src="storageUrl(post.post_image, true)"
                      :alt="post.title || 'Post image'"
                    ></v-img>
                  </v-list-item-avatar>
                  <v-list-item-avatar
                    v-else
                    tile
                    color="primary lighten-4"
                    size="60"
                    class="rounded"
                  >
                    <v-icon color="primary">mdi-image-area</v-icon>
                  </v-list-item-avatar>

                  <v-list-item-content class="ml-2">
                    <v-list-item-title
                      class="font-weight-medium mb-1 text-subtitle-1 primary--text"
                    >
                      {{ post.title }}
                    </v-list-item-title>
                    <v-list-item-subtitle class="text--secondary text-body-2">
                      {{ generateContentPreview(post.content) }}
                    </v-list-item-subtitle>
                    <v-list-item-subtitle
                      class="text-caption mt-1 text--disabled"
                    >
                      Published: {{ formatDate(post.created_at) }}
                      <span v-if="post.category" class="ml-2"
                        >• In {{ post.category.name }}</span
                      >
                    </v-list-item-subtitle>
                  </v-list-item-content>

                  <v-list-item-action class="ml-2 align-self-center">
                    <div class="d-flex flex-column flex-sm-row">
                      <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                            icon
                            small
                            color="blue lighten-1"
                            v-bind="attrs"
                            class="mx-1"
                            @click.stop="editPost(post.id)"
                            v-on="on"
                          >
                            <v-icon small>mdi-pencil</v-icon>
                          </v-btn>
                        </template>
                        <span>Edit Post</span>
                      </v-tooltip>
                      <v-tooltip bottom>
                        <template v-slot:activator="{ on, attrs }">
                          <v-btn
                            icon
                            small
                            color="error lighten-1"
                            v-bind="attrs"
                            class="mx-1"
                            @click.stop="confirmDeletePost(post)"
                            v-on="on"
                          >
                            <v-icon small>mdi-delete</v-icon>
                          </v-btn>
                        </template>
                        <span>Delete Post</span>
                      </v-tooltip>
                    </div>
                  </v-list-item-action>
                </v-list-item>
                <v-divider
                  v-if="index < postItems.length - 1"
                  :key="'divider-' + post.id"
                  inset
                ></v-divider>
              </template>
            </v-list>
            <div
              v-else-if="!loadingPosts"
              class="text-subtitle-1 pa-6 text-center grey--text"
            >
              You haven't authored any posts yet.
              <v-btn
                v-if="isWriterOrAdminComputed"
                text
                color="primary"
                to="/posts/create"
                class="mt-2 d-block mx-auto"
                >Create Your First Post</v-btn
              >
            </div>
          </v-card-text>

          <div
            v-if="userPostsPagination && userPostsPagination.last_page > 1"
            class="text-center py-3 grey lighten-4"
          >
            <v-pagination
              v-model="userPostsPagination.current_page"
              :length="userPostsPagination.last_page"
              circle
              :total-visible="7"
              class="my-0"
              @input="handleUserPostsPageChange"
            ></v-pagination>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Fallback if user is not loaded AND there's no error (initial edge case) -->
    <div
      v-else-if="!loadingUser && !userError && !user"
      class="text-center py-16 fill-height d-flex flex-column justify-center align-center"
    >
      <v-icon size="64" color="grey">mdi-account-alert-outline</v-icon>
      <p class="text-h6 grey--text mt-4">User profile could not be loaded.</p>
      <p>
        Please ensure you are logged in. If the problem persists, contact
        support.
      </p>
      <v-btn color="primary" to="/login" class="mt-4">Go to Login</v-btn>
    </div>

    <!-- Account Settings Dialog Placeholder/Component -->
    <AccountSettingsDialog
      :dialog.sync="settingsDialog"
      :user="user"
      @user-updated="handleUserUpdatedInDialog"
      @snackbar="showSnackbarFromDialog"
    />

    <!-- Delete Post Confirmation Dialog -->
    <v-dialog v-model="deleteDialog.show" persistent max-width="450px">
      <v-card>
        <v-card-title class="text-h5 error--text">
          <v-icon left color="error">mdi-alert-circle-outline</v-icon>Confirm
          Deletion
        </v-card-title>
        <v-card-text class="body-1 pt-3"
          >Are you sure you want to permanently delete the post:
          <strong class="d-block mt-1"
            >"{{ deleteDialog.post ? deleteDialog.post.title : '' }}"</strong
          >? This action cannot be undone.</v-card-text
        >
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog.show = false">Cancel</v-btn>
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
      app
    >
      {{ snackbar.text }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.show = false">Close</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script>
import { mapGetters } from 'vuex'
// Assume AccountSettingsDialog.vue exists in components/sections or a similar path
import AccountSettingsDialog from '~/components/sections/AccountSettingsDialog.vue'

const BACKEND_URL = process.env.NUXT_ENV_BACKEND_URL || 'http://localhost:8000' // Use environment variable for backend URL
const DEFAULT_PFP_PATH = '/storage/app/public/default_pfp.png' // Ensure this is the correct path relative to public/storage/

export default {
  name: 'UserProfilePage',
  components: {
    AccountSettingsDialog,
  },
  middleware: 'auth',
  async fetch() {
    this.loadingUser = true
    this.loadingPosts = true
    this.userError = null
    this.postsError = null
    this.user = null // Reset to trigger loading states and re-evaluation of computed props
    this.postItems = []
    this.userPostsPagination = null

    if (!this.$store.getters['auth/isAuthenticated']) {
      this.loadingUser = false
      this.loadingPosts = false
      return // Middleware should ideally redirect before this point
    }

    try {
      // Attempt to get user from Vuex store first. If incomplete (e.g., missing role), fetch fresh.
      let currentUserInStore = this.$store.getters['auth/getUser']
      if (
        currentUserInStore &&
        currentUserInStore.id &&
        currentUserInStore.role_name
      ) {
        this.user = { ...currentUserInStore }
        // console.log('[Fetch UserProfile] Used user data from Vuex store.');
      } else {
        // console.log('[Fetch UserProfile] Fetching fresh user data from API.');
        await this.$store.dispatch('auth/fetchUser', { $axios: this.$axios })
        currentUserInStore = this.$store.getters['auth/getUser'] // Re-get from store after dispatch
        if (currentUserInStore && currentUserInStore.id) {
          this.user = { ...currentUserInStore }
        } else {
          throw new Error(
            'User data could not be loaded or is incomplete after API fetch.'
          )
        }
      }
      this.loadingUser = false // User profile part is now considered loaded or errored

      // Fetch posts only if the user is a writer or admin
      if (
        this.user &&
        (this.user.role_name === 'writer' || this.user.role_name === 'admin')
      ) {
        await this.fetchUserPosts(1) // Fetch first page
      } else {
        this.loadingPosts = false // Not a writer/admin, so no "authored posts" to load
      }
    } catch (err) {
      console.error(
        '[Fetch UserProfile] Critical error during initial data load:',
        err.response?.data || err.message || err
      )
      this.userError =
        err.response?.data?.message ||
        err.message ||
        'Failed to load your profile information.'
      this.postsError =
        'Could not load posts because profile data failed to load.' // Cascading error
      this.loadingUser = false
      this.loadingPosts = false
    }
  },
  data() {
    return {
      user: null, // Populated by fetch or watcher
      userPFPFile: null, // For v-file-input
      updatingPFP: false, // Loading state for PFP update
      loadingUser: true, // Loading state for the user profile section
      userError: null,

      postItems: [], // Array of post objects for "My Posts"
      userPostsPagination: null, // Full pagination object for user's posts
      loadingPosts: true, // Loading state for the "My Posts" list
      postsError: null,

      settingsDialog: false,
      deleteDialog: { show: false, post: null },
      deletingPost: false, // Loading state for post deletion
      snackbar: { show: false, text: '', color: '' },
      // defaultCoverImage:
      //   'https://cdn.vuetifyjs.com/images/parallax/material.jpg', // Generic default
    }
  },
  computed: {
    ...mapGetters('auth', [
      'isAuthenticated',
      'isAdmin',
      'isWriter',
      'getUser',
    ]), // getUser is used by the watcher

    isWriterOrAdminComputed() {
      const u = this.user // Use the local 'user' data property
      return !!(
        u &&
        u.id &&
        (u.role_name === 'writer' || u.role_name === 'admin')
      )
    },
    profilePictureUrl() {
      // Default to backend's default PFP if no user data yet or no PFP set
      const pfpPath = this.user?.userPFP
      if (pfpPath) {
        // If it's already a full URL (e.g., from a social provider that was stored)
        if (pfpPath.startsWith('http://') || pfpPath.startsWith('https://')) {
          return pfpPath
        }
        // Construct URL for images stored relative to Laravel's public/storage
        return `${BACKEND_URL}/storage/${pfpPath}`
      }
      return `${BACKEND_URL}${DEFAULT_PFP_PATH}` // Default from backend
    },
  },
  watch: {
    // Watch the Vuex getter. If it changes (e.g. after login/logout in another tab),
    // update the local 'user' data property for this component.
    getUser: {
      immediate: true, // Check once when the component is created as well
      deep: true,
      handler(newUserFromStore) {
        if (newUserFromStore && newUserFromStore.id) {
          // Update local user if it's different from store or not yet set
          if (
            !this.user ||
            newUserFromStore.id !== this.user.id ||
            newUserFromStore.updated_at !== this.user.updated_at
          ) {
            console.log(
              '[Watch getUser] Updating local user data from Vuex store.',
              newUserFromStore.name
            )
            this.user = { ...newUserFromStore } // Create a new object reference
            this.userError = null // Clear any previous error from fetch

            // If role allows post viewing and posts haven't been fetched yet for this user instance
            if (
              (this.user.role_name === 'writer' ||
                this.user.role_name === 'admin') &&
              !this.userPostsPagination &&
              !this.postsError &&
              !this.loadingPosts
            ) {
              console.log(
                '[Watch getUser] User role valid and posts not yet loaded, fetching posts.'
              )
              this.fetchUserPosts()
            } else if (
              !(
                this.user.role_name === 'writer' ||
                this.user.role_name === 'admin'
              )
            ) {
              // console.log('[Watch getUser] User role does not permit viewing own posts section or has changed.');
              this.postItems = [] // Clear posts if role doesn't allow
              this.userPostsPagination = null
              this.loadingPosts = false
            }
          }
          if (this.loadingUser) this.loadingUser = false // Stop initial page load for user section
        } else if (!this.isAuthenticated) {
          // If user logs out from elsewhere, clear local user
          // console.log('[Watch getUser] User no longer authenticated, clearing local user.');
          this.user = null
          this.loadingUser = false
        }
        // If not authenticated and not loadingUser, means data fetch in 'fetch' already failed.
      },
    },
  },
  methods: {
    refreshAllData() {
      this.$fetch() // Re-runs the component's async fetch() hook
    },
    storageUrl(filePath, isPostImage = false) {
      const defaultImg = isPostImage
        ? 'https://cdn.vuetifyjs.com/images/cards/server-room.jpg'
        : `${BACKEND_URL}${DEFAULT_PFP_PATH}`
      if (!filePath) return defaultImg
      if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
        return filePath
      }
      return `${BACKEND_URL}/storage/${filePath}`
    },
    getRoleColor(roleName) {
      if (roleName === 'admin') return 'error darken-1'
      if (roleName === 'writer') return 'blue darken-1'
      return 'grey darken-1'
    },
    getRoleIcon(roleName) {
      if (roleName === 'admin') return 'mdi-shield-crown-outline'
      if (roleName === 'writer') return 'mdi-pencil-outline'
      return 'mdi-account-outline'
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
    // fetchUserProfile is now handled by the async fetch and watcher on getUser
    async fetchUserPosts(page = 1) {
      if (!this.user || !this.user.id || !this.isWriterOrAdminComputed) {
        this.loadingPosts = false
        this.postItems = []
        this.userPostsPagination = null
        return
      }
      this.loadingPosts = true
      this.postsError = null
      try {
        const response = await this.$axios.get('/user-posts', {
          params: { page },
        })
        this.postItems = response.data.data || []
        this.userPostsPagination = response.data
      } catch (error) {
        console.error(
          'Failed to fetch user posts:',
          error.response?.data || error
        )
        this.postsError =
          error.response?.data?.message || 'Failed to load your authored posts.'
        this.postItems = []
        this.userPostsPagination = null
      } finally {
        this.loadingPosts = false
      }
    },
    handleUserPostsPageChange(newPage) {
      if (this.userPostsPagination) {
        // Check if pagination object exists
        this.userPostsPagination.current_page = newPage // Optimistically set for v-model
        this.fetchUserPosts(newPage)
      }
    },
    onFileChange(file) {
      this.userPFPFile = file
    },
    generateContentPreview(contentJson, maxLength = 75) {
      if (!contentJson) return 'No preview available.'
      let previewText = ''
      try {
        const blocks =
          typeof contentJson === 'string'
            ? JSON.parse(contentJson)
            : Array.isArray(contentJson)
            ? contentJson
            : []
        const firstTextBlock = blocks.find(
          (b) =>
            (b.type === 'paragraph' || b.type === 'heading') &&
            b.data?.text &&
            typeof b.data.text === 'string'
        )
        if (firstTextBlock)
          previewText = firstTextBlock.data.text.replace(/<[^>]*>?/gm, '')
        else return 'View post for content.'
      } catch (e) {
        if (
          typeof contentJson === 'string' &&
          !contentJson.startsWith('[{"id":')
        ) {
          // If it's a plain string non-JSON
          previewText = contentJson.replace(/<[^>]*>?/gm, '')
        } else {
          return 'Content preview error.'
        }
      }
      return previewText.length > maxLength
        ? previewText.substring(0, maxLength).trim() + '...'
        : previewText.trim()
    },
    goToPost(postId) {
      this.$router.push(`/ReviewPostPage/${postId}`)
    },
    editPost(postId) {
      this.$router.push(`/posts/${postId}/edit`)
    }, // To writer edit page
    confirmDeletePost(post) {
      this.deleteDialog = { show: true, post }
    },
    async deletePostConfirmed() {
      if (!this.deleteDialog.post) return
      this.deletingPost = true // Set loading for delete button
      const postId = this.deleteDialog.post.id
      const currentPage = this.userPostsPagination?.current_page || 1
      try {
        await this.$axios.delete(`/posts/${postId}`)
        this.showSnackbar('Post deleted successfully.', 'success')
        if (this.postItems.length === 1 && currentPage > 1) {
          await this.fetchUserPosts(currentPage - 1)
        } else {
          await this.fetchUserPosts(currentPage)
        }
      } catch (error) {
        this.showSnackbar(
          error.response?.data?.message || 'Failed to delete post.',
          'error'
        )
      } finally {
        this.deletingPost = false
        this.deleteDialog = { show: false, post: null }
      }
    },
    openSettingsDialog() {
      // Ensure user data is loaded before opening, or dialog handles it
      if (this.user && this.user.id) {
        this.settingsDialog = true
      } else {
        // Optionally, show a loading message or prevent opening
        this.showSnackbar('Loading user data, please wait...', 'info')
        // You could try fetching user again if it's null
        // if (!this.loadingUser) { this.$fetch(); }
      }
    },
    async handleUserUpdatedInDialog(updatedUserFromDialog) {
      // Changed parameter name
      this.showSnackbar('Account settings saved successfully!', 'success')
      if (updatedUserFromDialog && updatedUserFromDialog.id) {
        this.$store.commit('auth/SET_USER', { ...updatedUserFromDialog })
      } else {
        // Re-fetch from API if dialog doesn't return full updated user
        await this.$store.dispatch('auth/fetchUser', { $axios: this.$axios })
      }
    },
    showSnackbarFromDialog({ text, color }) {
      this.showSnackbar(text, color)
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
  },
  head() {
    const userName =
      this.user?.name || (this.loadingUser ? 'Loading Profile' : 'User')
    return { title: `${userName}'s Profile` }
  },
}
</script>

<style scoped>
.user-profile-page {
  min-height: 100vh;
}
.fill-height {
  min-height: calc(
    100vh - 64px - 36px - 64px
  ); /* VP - appbar - page y-pad - footer approx */
}

@media (min-width: 960px) {
  /* Vuetify 'md' breakpoint */
  .profile-column .v-card {
    position: sticky;
    top: 80px; /* app-bar height (64px) + some margin (16px) */
  }
}

.profile-avatar {
  border: 4px solid white;
  /* No margin-top here if placed with `div class="d-flex justify-center" style="margin-top: -48px;"` */
}
.theme--dark .profile-avatar {
  border-color: var(
    --v-sheet-base,
    #1e1e1e
  ); /* Use Vuetify sheet base color for dark theme */
}

.profile-name-on-cover {
  /* position: absolute; */ /* No longer needed if just v-card-title inside v-img */
  /* bottom: 8px; left: 16px; right: 16px; */
  text-align: center;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.9);
}
/*
.v-list-item__content {
  text-align: center; Removed this for more natural list item layout
}
*/
</style>
