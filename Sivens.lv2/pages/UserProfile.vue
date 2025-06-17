// Sivens.lv2/pages/UserProfile.vue
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
    <v-row v-else-if="user" justify="start" class="main-content-row">
      <!-- Left Column: User Profile Card -->
      <v-col cols="12" md="5" lg="4" xl="3" class="profile-column mb-6 mb-md-0">
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

      <v-col cols="12" md="7" lg="8" xl="9" class="posts-column">
        <v-card class="elevation-3">
          <v-card-title class="grey lighten-4">
            <v-icon left color="pink">mdi-heart</v-icon>
            <span class="text-h6 font-weight-medium">My Liked Reviews</span>
          </v-card-title>
          <v-divider></v-divider>

          <v-card-text class="pa-0">
            <div
              v-if="
                loadingLikedPosts && (!likedPosts || likedPosts.length === 0)
              "
              class="text-center py-12"
            >
              <v-progress-circular
                indeterminate
                color="primary"
                size="40"
              ></v-progress-circular>
              <p class="mt-4 text--secondary">Loading your liked reviews...</p>
            </div>
            <v-alert
              v-else-if="likedPostsError"
              type="warning"
              dense
              text
              class="ma-4"
              border="left"
            >
              {{ likedPostsError }}
            </v-alert>

            <v-list
              v-else-if="likedPosts && likedPosts.length > 0"
              two-line
              class="py-0"
            >
              <template v-for="(post, index) in likedPosts">
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
                      By {{ post.author ? post.author.name : 'Unknown' }} in
                      {{ post.category ? post.category.name : 'Uncategorized' }}
                    </v-list-item-subtitle>
                    <v-list-item-subtitle
                      class="text-caption mt-1 text--disabled"
                    >
                      Liked on: {{ formatDate(post.pivot.created_at) }}
                    </v-list-item-subtitle>
                  </v-list-item-content>

                  <v-list-item-action class="ml-2 align-self-center">
                    <v-tooltip bottom>
                      <template v-slot:activator="{ on, attrs }">
                        <v-btn
                          icon
                          small
                          color="pink lighten-1"
                          v-bind="attrs"
                          :loading="unlikingPostId === post.id"
                          :disabled="!!unlikingPostId"
                          @click.stop="unlikePost(post)"
                          v-on="on"
                        >
                          <v-icon small>mdi-heart-off</v-icon>
                        </v-btn>
                      </template>
                      <span>Unlike Post</span>
                    </v-tooltip>
                  </v-list-item-action>
                </v-list-item>
                <v-divider
                  v-if="index < likedPosts.length - 1"
                  :key="'divider-' + post.id"
                  inset
                ></v-divider>
              </template>
            </v-list>
            <div
              v-else-if="!loadingLikedPosts"
              class="text-subtitle-1 pa-6 text-center grey--text"
            >
              You haven't liked any reviews yet.
              <v-btn
                text
                color="primary"
                to="/reviews"
                class="mt-2 d-block mx-auto"
                >Explore Reviews</v-btn
              >
            </div>
          </v-card-text>

          <div
            v-if="likedPostsPagination && likedPostsPagination.last_page > 1"
            class="text-center py-3 grey lighten-4"
          >
            <v-pagination
              v-model="likedPostsPagination.current_page"
              :length="likedPostsPagination.last_page"
              circle
              :total-visible="7"
              class="my-0"
              @input="handleLikedPostsPageChange"
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

    <AccountSettingsDialog
      :dialog.sync="settingsDialog"
      :user="user"
      @user-updated="handleUserUpdatedInDialog"
      @snackbar="showSnackbarFromDialog"
    />

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
import AccountSettingsDialog from '~/components/sections/AccountSettingsDialog.vue'

const BACKEND_URL = process.env.NUXT_ENV_BACKEND_URL || 'http://localhost:8000'
const DEFAULT_PFP_PATH = '/storage/default_pfp.png'

export default {
  name: 'UserProfilePage',
  components: {
    AccountSettingsDialog,
  },
  middleware: 'auth',
  async fetch() {
    this.loadingUser = true
    this.loadingLikedPosts = true
    this.userError = null
    this.likedPostsError = null
    this.user = null
    this.likedPosts = []
    this.likedPostsPagination = null

    if (!this.$store.getters['auth/isAuthenticated']) {
      this.loadingUser = false
      this.loadingLikedPosts = false
      return
    }

    try {
      let currentUserInStore = this.$store.getters['auth/getUser']
      if (
        !currentUserInStore ||
        !currentUserInStore.id ||
        !currentUserInStore.role_name
      ) {
        await this.$store.dispatch('auth/fetchUser', { $axios: this.$axios })
        currentUserInStore = this.$store.getters['auth/getUser']
      }
      if (!currentUserInStore || !currentUserInStore.id) {
        throw new Error('User data could not be loaded.')
      }
      this.user = { ...currentUserInStore }
      this.loadingUser = false
      await this.fetchLikedPosts(1)
    } catch (err) {
      console.error(
        '[Fetch UserProfile] Critical error during initial data load:',
        err.response?.data || err.message || err
      )
      this.userError =
        err.response?.data?.message ||
        err.message ||
        'Failed to load your profile information.'
      this.loadingUser = false
      this.loadingLikedPosts = false
    }
  },
  data() {
    return {
      user: null,
      loadingUser: true,
      userError: null,

      likedPosts: [],
      likedPostsPagination: null,
      loadingLikedPosts: true,
      likedPostsError: null,

      unlikingPostId: null,
      settingsDialog: false,
      snackbar: { show: false, text: '', color: '' },
      defaultCoverImage:
        'https://images.ctfassets.net/rric2f17v78a/18hJAlNuCZk7SK4jRQvgdS/5d701e6e6391a9d554c192ede405e587/carlos-lindner-sjBYA8dAw54-unsplash.jpg',
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated', 'getUser']),
    profilePictureUrl() {
      const pfpPath = this.user?.userPFP
      if (pfpPath) {
        if (pfpPath.startsWith('http://') || pfpPath.startsWith('https://')) {
          return pfpPath
        }
        return `${BACKEND_URL}/storage/${pfpPath}`
      }
      return `${BACKEND_URL}${DEFAULT_PFP_PATH}`
    },
  },
  watch: {
    getUser: {
      deep: true,
      handler(newUserFromStore) {
        if (newUserFromStore && newUserFromStore.id) {
          if (
            !this.user ||
            newUserFromStore.id !== this.user.id ||
            newUserFromStore.updated_at !== this.user.updated_at
          ) {
            this.user = { ...newUserFromStore }
            this.userError = null
            if (this.loadingUser) this.loadingUser = false
          }
        } else if (!this.isAuthenticated) {
          this.user = null
          this.loadingUser = false
        }
      },
    },
  },
  methods: {
    refreshAllData() {
      this.$fetch()
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
    async fetchLikedPosts(page = 1) {
      this.loadingLikedPosts = true
      this.likedPostsError = null
      try {
        const response = await this.$axios.get('/user/liked-posts', {
          params: { page },
        })
        this.likedPosts = response.data.data || []
        this.likedPostsPagination = response.data
      } catch (error) {
        console.error(
          'Failed to fetch liked posts:',
          error.response?.data || error
        )
        this.likedPostsError =
          error.response?.data?.message || 'Failed to load your liked posts.'
      } finally {
        this.loadingLikedPosts = false
      }
    },
    async unlikePost(postToUnlike) {
      if (!postToUnlike || this.unlikingPostId) return
      this.unlikingPostId = postToUnlike.id
      const originalPosts = [...this.likedPosts]
      this.likedPosts = this.likedPosts.filter((p) => p.id !== postToUnlike.id)
      try {
        await this.$axios.post(`/posts/${postToUnlike.id}/like`)
        this.showSnackbar('Post unliked.', 'success')
        const currentPage = this.likedPostsPagination?.current_page || 1
        if (this.likedPosts.length === 0 && currentPage > 1) {
          this.fetchLikedPosts(currentPage - 1)
        } else {
          this.fetchLikedPosts(currentPage)
        }
      } catch (error) {
        this.showSnackbar('Failed to unlike post. Please try again.', 'error')
        this.likedPosts = originalPosts
      } finally {
        this.unlikingPostId = null
      }
    },
    handleLikedPostsPageChange(newPage) {
      if (this.likedPostsPagination) {
        this.likedPostsPagination.current_page = newPage
        this.fetchLikedPosts(newPage)
      }
    },
    goToPost(postId) {
      this.$router.push(`/ReviewPostPage/${postId}`)
    },
    openSettingsDialog() {
      if (this.user && this.user.id) {
        this.settingsDialog = true
      } else {
        this.showSnackbar('Loading user data, please wait...', 'info')
      }
    },
    async handleUserUpdatedInDialog(updatedUserFromDialog) {
      this.showSnackbar('Account settings saved successfully!', 'success')
      if (updatedUserFromDialog && updatedUserFromDialog.id) {
        this.$store.commit('auth/SET_USER', { ...updatedUserFromDialog })
      } else {
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
  .profile-column .v-card {
    position: sticky;
    top: 80px;
  }
}
.profile-avatar {
  border: 4px solid white;
}
.theme--dark .profile-avatar {
  border-color: var(--v-sheet-base, #1e1e1e);
}
</style>
