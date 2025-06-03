<template>
  <AdminPageWrapper page-title="Admin Dashboard">
    <h1 class="text-h4 font-weight-bold mb-8">Dashboard Overview</h1>
    <v-row v-if="loadingStats">
      <v-col v-for="n in 3" :key="`skel-${n}`" cols="12" md="4">
        <v-skeleton-loader
          type="card-heading, list-item-three-line"
        ></v-skeleton-loader>
      </v-col>
    </v-row>
    <v-alert v-else-if="statsError" type="error" dense border="left" prominent>
      {{ statsError }}
    </v-alert>
    <v-row v-else>
      <v-col cols="12" sm="6" md="4">
        <v-card outlined class="fill-height d-flex flex-column">
          <v-card-title class="text-h6 font-weight-medium"
            >Total Posts</v-card-title
          >
          <v-card-text
            class="text-h2 primary--text text-center font-weight-bold flex-grow-1 d-flex align-center justify-center"
          >
            {{ stats.posts_count != null ? stats.posts_count : 'N/A' }}
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions class="justify-center pa-3">
            <v-btn text color="primary" to="/admin/posts">
              <v-icon left>mdi-newspaper-variant-multiple-outline</v-icon>
              Manage Posts
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="4">
        <v-card outlined class="fill-height d-flex flex-column">
          <v-card-title class="text-h6 font-weight-medium"
            >Registered Users</v-card-title
          >
          <v-card-text
            class="text-h2 primary--text text-center font-weight-bold flex-grow-1 d-flex align-center justify-center"
          >
            {{ stats.users_count != null ? stats.users_count : 'N/A' }}
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions class="justify-center pa-3">
            <v-btn text color="primary" to="/admin/users">
              <v-icon left>mdi-account-group-outline</v-icon>
              Manage Users
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
      <v-col cols="12" sm="6" md="4">
        <v-card outlined class="fill-height d-flex flex-column">
          <v-card-title class="text-h6 font-weight-medium"
            >Total Categories</v-card-title
          >
          <v-card-text
            class="text-h2 primary--text text-center font-weight-bold flex-grow-1 d-flex align-center justify-center"
          >
            {{
              stats.categories_count != null ? stats.categories_count : 'N/A'
            }}
          </v-card-text>
          <v-divider></v-divider>
          <v-card-actions class="justify-center pa-3">
            <v-btn text color="primary" to="/admin/posts/categories">
              <v-icon left>mdi-tag-multiple-outline</v-icon>
              Manage Categories
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
      <!-- Add more stat cards: Tags, Comments, Messages -->
    </v-row>

    <!-- Example: Quick Links Section -->
    <v-card outlined class="mt-8">
      <v-card-title>Quick Actions</v-card-title>
      <v-list dense two-line>
        <v-list-item v-if="isWriter" to="/writer/review_creator">
          <v-list-item-icon
            ><v-icon color="green">mdi-plus-circle</v-icon></v-list-item-icon
          >
          <v-list-item-content>
            <v-list-item-title>Create New Review Post</v-list-item-title>
            <v-list-item-subtitle
              >Start writing and share your content.</v-list-item-subtitle
            >
          </v-list-item-content>
        </v-list-item>
        <v-list-item to="/admin/users">
          <v-list-item-icon
            ><v-icon color="blue">mdi-account-search</v-icon></v-list-item-icon
          >
          <v-list-item-content>
            <v-list-item-title>View All Users</v-list-item-title>
            <v-list-item-subtitle
              >Manage user accounts and roles.</v-list-item-subtitle
            >
          </v-list-item-content>
        </v-list-item>
        <v-list-item to="/admin/messages">
          <v-list-item-icon
            ><v-icon color="orange"
              >mdi-email-check-outline</v-icon
            ></v-list-item-icon
          >
          <v-list-item-content>
            <v-list-item-title>View Contact Messages</v-list-item-title>
            <v-list-item-subtitle
              >Respond to user inquiries.</v-list-item-subtitle
            >
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </v-card>

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
import { mapGetters } from 'vuex' // To check if admin is also a writer
import AdminPageWrapper from '~/components/admin/AdminPageWrapper.vue'

export default {
  name: 'AdminDashboardPage',
  components: {
    AdminPageWrapper,
  },
  middleware: 'admin',
  layout: 'empty',
  data() {
    return {
      stats: {
        // Initialize with defaults or nulls
        posts_count: null,
        users_count: null,
        categories_count: null,
        // tags_count: null,
        // unread_messages_count: null,
      },
      loadingStats: true,
      statsError: null,
      snackbar: { show: false, text: '', color: '' },
    }
  },
  computed: {
    ...mapGetters('auth', ['isWriter']), // If admin can also be writer
  },
  async mounted() {
    // Use mounted for client-side fetch
    await this.fetchAdminStats()
  },
  methods: {
    async fetchAdminStats() {
      this.loadingStats = true
      this.statsError = null
      try {
        const response = await this.$axios.get('/admin/stats') // Dedicated API for stats
        this.stats = response.data
      } catch (error) {
        console.error('Error fetching admin stats:', error)
        this.statsError =
          error.response?.data?.message ||
          'Could not load dashboard statistics.'
        this.showSnackbar(this.statsError, 'error')
      } finally {
        this.loadingStats = false
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
  },
  head() {
    return {
      title: 'Admin Dashboard',
    }
  },
}
</script>

<style scoped>
/* Make stat cards look a bit nicer */
.v-card .text-h2 {
  line-height: 1.2; /* Adjust line height of the big number */
}
.v-card .v-card-actions .v-btn {
  font-size: 0.8rem;
}
</style>
