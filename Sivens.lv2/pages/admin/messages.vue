<template>
  <AdminPageWrapper page-title="Contact Form Messages">
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4 font-weight-medium">Contact Form Submissions</h1>
      <v-btn
        icon
        :loading="loading"
        title="Refresh Messages"
        @click="fetchMessages"
      >
        <v-icon>mdi-refresh</v-icon>
      </v-btn>
    </div>

    <v-card outlined>
      <v-card-title>
        Received Messages
        <v-spacer></v-spacer>
        <!-- Add search/filter if needed by backend -->
        <!--
        <v-text-field
            v-model="search"
            append-icon="mdi-magnify"
            label="Search Messages..."
            single-line
            hide-details
            dense
            clearable
            @input="debouncedFetchMessages"
            style="max-width: 300px;"
        ></v-text-field>
        -->
      </v-card-title>

      <v-alert v-if="fetchError" type="error" dense text class="ma-4">
        {{ fetchError }}
      </v-alert>

      <v-data-table
        :headers="headers"
        :items="messages"
        :options.sync="options"
        :server-items-length="totalMessages"
        :loading="loading"
        :footer-props="{ 'items-per-page-options': [10, 15, 25, 50] }"
        class="elevation-0"
        item-key="id"
        :expanded.sync="expanded"
        show-expand
      >
        <template v-slot:item.subject="{ item }">
          <strong class="text-subtitle-1">{{ item.subject }}</strong>
        </template>
        <template v-slot:item.created_at="{ item }">
          {{ formatDate(item.created_at) }}
        </template>
        <template v-slot:item.actions="{ item }">
          <!-- Currently no actions, but you could add 'Mark as Read' or 'Delete' -->
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                color="error"
                v-bind="attrs"
                @click="confirmDeleteMessage(item)"
                v-on="on"
              >
                <v-icon small>mdi-delete-outline</v-icon>
              </v-btn>
            </template>
            <span>Delete Message</span>
          </v-tooltip>
        </template>

        <template v-slot:expanded-item="{ item }">
          <td
            :colspan="headers.length"
            class="pa-4 grey lighten-4 expanded-content"
          >
            <div class="mb-2">
              <strong>From:</strong> {{ item.name }} &lt;{{ item.email }}&gt;
            </div>
            <div class="mb-2"><strong>Subject:</strong> {{ item.subject }}</div>
            <div class="mb-2">
              <strong>Received:</strong> {{ formatDate(item.created_at, true) }}
            </div>
            <v-divider class="my-3"></v-divider>
            <h4 class="subtitle-2 mb-1">Message:</h4>
            <p style="white-space: pre-wrap" class="body-2">
              {{ item.message_content }}
            </p>
          </td>
        </template>
        <template v-slot:no-data>
          <div v-if="!loading" class="text-center pa-4 grey--text">
            No messages found.
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Delete Message Confirmation Dialog -->
    <v-dialog v-model="deleteDialog.show" persistent max-width="450px">
      <v-card>
        <v-card-title class="text-h5 warning--text"
          >Delete Message</v-card-title
        >
        <v-card-text>
          Are you sure you want to delete this message from
          <strong>{{
            deleteDialog.message ? deleteDialog.message.name : ''
          }}</strong>
          regarding "<em>{{
            deleteDialog.message ? deleteDialog.message.subject : ''
          }}</em
          >"? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog.show = false">Cancel</v-btn>
          <v-btn color="warning" depressed @click="deleteMessageConfirmed"
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
        <v-btn text v-bind="attrs" @click="snackbar.show = false">Close</v-btn>
      </template>
    </v-snackbar>
  </AdminPageWrapper>
</template>

<script>
import AdminPageWrapper from '~/components/admin/AdminPageWrapper.vue'

export default {
  name: 'AdminMessagesPage',
  components: { AdminPageWrapper },
  // layout: 'admin', // AdminPageWrapper acts as the layout now
  middleware: 'admin',
  data() {
    return {
      messages: [],
      expanded: [], // For v-data-table expanded rows
      totalMessages: 0,
      loading: true,
      fetchError: null,
      search: '', // For future search functionality
      options: {
        page: 1,
        itemsPerPage: 15,
        sortBy: ['created_at'],
        sortDesc: [true],
      },
      headers: [
        { text: 'Subject', value: 'subject', sortable: true, width: '30%' },
        { text: 'From', value: 'name', sortable: true },
        { text: 'Email', value: 'email', sortable: true },
        { text: 'Received', value: 'created_at', sortable: true },
        {
          text: 'Actions',
          value: 'actions',
          sortable: false,
          align: 'end',
          width: '100px',
        },
        { text: '', value: 'data-table-expand' }, // Vuetify specific for expansion
      ],
      deleteDialog: { show: false, message: null },
      snackbar: { show: false, text: '', color: '' },
      debouncedFetchMessages: null,
    }
  },
  watch: {
    options: {
      handler() {
        this.fetchMessages()
      },
      deep: true,
    },
  },
  created() {
    // Initialize debounce function for search (if implemented)
    // this.debouncedFetchMessages = _.debounce(this.fetchMessages, 500);
  },
  async mounted() {
    // Using mounted instead of Nuxt 'fetch' for simplicity here
    await this.fetchMessages()
  },
  methods: {
    async fetchMessages() {
      this.loading = true
      this.fetchError = null
      try {
        const { sortBy, sortDesc, page, itemsPerPage } = this.options
        // Backend API endpoint for fetching messages
        const response = await this.$axios.get('/admin/messages', {
          params: {
            page,
            perPage: itemsPerPage,
            search: this.search, // Add backend search if implemented
            sortBy: sortBy.length ? sortBy[0] : 'created_at',
            sortDesc: sortDesc.length ? sortDesc[0] : true,
          },
        })
        this.messages = response.data.data
        this.totalMessages = response.data.total
      } catch (error) {
        console.error('Error fetching messages:', error)
        this.fetchError =
          error.response?.data?.message || 'Failed to load messages.'
        this.showSnackbar('Failed to load messages.', 'error')
      } finally {
        this.loading = false
      }
    },
    formatDate(dateString, includeTime = false) {
      if (!dateString) return 'N/A'
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      if (includeTime) {
        options.hour = '2-digit'
        options.minute = '2-digit'
      }
      try {
        return new Date(dateString).toLocaleDateString(undefined, options)
      } catch (e) {
        return dateString
      }
    },
    viewMessage(item) {
      // For v-data-table expanded row, toggle expansion
      const index = this.expanded.indexOf(item)
      if (index === -1) {
        this.expanded = [item] // Expand one at a time
      } else {
        this.expanded = []
      }
    },
    confirmDeleteMessage(message) {
      this.deleteDialog = { show: true, message }
    },
    async deleteMessageConfirmed() {
      if (!this.deleteDialog.message) return
      try {
        // Backend API endpoint to delete a message
        await this.$axios.delete(
          `/admin/messages/${this.deleteDialog.message.id}`
        )
        this.showSnackbar('Message deleted successfully.', 'success')
        this.fetchMessages() // Refresh list
      } catch (error) {
        console.error('Error deleting message:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Failed to delete message.',
          'error'
        )
      } finally {
        this.deleteDialog.show = false
        this.deleteDialog.message = null
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
      title: 'Contact Messages - Admin',
    }
  },
}
</script>

<style scoped>
.expanded-content strong {
  display: inline-block;
  min-width: 70px; /* Align keys for readability */
}
</style>
