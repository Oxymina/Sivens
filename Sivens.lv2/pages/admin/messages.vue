<template>
  <AdminPageWrapper page-title="Manage Messages">
    <div class="d-flex justify-space-between align-center mb-6 flex-wrap">
      <h1 class="text-h4 font-weight-medium">Messages</h1>
      <v-spacer class="d-md-none"></v-spacer>
      <div class="d-flex align-center mt-4 mt-sm-0">
        <v-select
          v-model="filterReadStatus"
          :items="readStatusOptions"
          label="Status"
          dense
          outlined
          hide-details
          clearable
          style="max-width: 180px"
          class="mr-2"
          :disabled="$fetchState.pending || localLoading"
          @change="handleFilterChange"
        ></v-select>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search..."
          single-line
          hide-details
          dense
          outlined
          clearable
          style="max-width: 250px"
          class="mr-2"
          :disabled="$fetchState.pending || localLoading"
          @input="debouncedFetchOnFilterChange"
        ></v-text-field>
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              icon
              :loading="$fetchState.pending || localLoading"
              v-bind="attrs"
              @click="refreshData"
              v-on="on"
            >
              <v-icon>mdi-refresh</v-icon>
            </v-btn>
          </template>
          <span>Refresh Messages</span>
        </v-tooltip>
      </div>
    </div>

    <!-- Initial Page Loading or Fetch Error -->
    <div v-if="$fetchState.pending" class="text-center pa-8">
      <v-progress-circular
        indeterminate
        color="primary"
        size="50"
      ></v-progress-circular>
      <p class="mt-3 text-subtitle-1 grey--text">Loading messages...</p>
    </div>
    <v-alert
      v-else-if="$fetchState.error"
      type="error"
      dense
      border="left"
      prominent
      class="ma-4"
    >
      Error loading messages:
      {{
        $fetchState.error.message ||
        'An unknown error occurred during initial load.'
      }}
      <v-btn small text color="error" class="ml-2" @click="$fetch">Retry</v-btn>
    </v-alert>

    <v-card v-else outlined>
      <!-- v-data-table handles its own internal loading via :loading prop -->
      <v-data-table
        v-model="selectedMessages"
        :headers="headers"
        :items="messages"
        :options.sync="options"
        :server-items-length="totalMessages"
        :loading="localLoading"
        :footer-props="{
          'items-per-page-options': [10, 15, 25, 50],
          'show-first-last-page': true,
        }"
        class="elevation-0"
        item-key="id"
        :expanded.sync="expanded"
        show-expand
        show-select
        @item-expanded="onItemExpanded"
      >
        <template v-slot:top>
          <!-- For bulk actions toolbar -->
          <v-toolbar
            v-if="
              selectedMessages.length > 0 &&
              !($fetchState.pending || localLoading)
            "
            flat
            dense
            color="transparent"
          >
            <v-toolbar-title class="text-caption">
              {{ selectedMessages.length }} selected
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  v-bind="attrs"
                  :disabled="actionInProgress"
                  @click="bulkMarkAsReadOrUnread(true)"
                  v-on="on"
                >
                  <v-icon>mdi-email-check-outline</v-icon>
                </v-btn>
              </template>
              <span>Mark Selected as Read</span>
            </v-tooltip>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  v-bind="attrs"
                  :disabled="actionInProgress"
                  @click="bulkMarkAsReadOrUnread(false)"
                  v-on="on"
                >
                  <v-icon>mdi-email-remove-outline</v-icon>
                </v-btn>
              </template>
              <span>Mark Selected as Unread</span>
            </v-tooltip>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="error"
                  v-bind="attrs"
                  :disabled="actionInProgress"
                  @click="confirmDeleteMessage(null, true)"
                  v-on="on"
                >
                  <v-icon>mdi-delete-sweep-outline</v-icon>
                </v-btn>
              </template>
              <span>Delete Selected</span>
            </v-tooltip>
          </v-toolbar>
          <v-divider
            v-if="
              selectedMessages.length > 0 &&
              !($fetchState.pending || localLoading)
            "
          ></v-divider>
        </template>

        <template v-slot:item.is_read="{ item }">
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                :color="item.is_read ? 'grey lighten-1' : 'blue accent-3'"
                v-bind="attrs"
                class="cursor-pointer"
                @click.stop="toggleReadStatus(item)"
                v-on="on"
              >
                {{
                  item.is_read
                    ? 'mdi-email-open-outline'
                    : 'mdi-email-mark-as-unread'
                }}
                <!-- Changed unread icon slightly -->
              </v-icon>
            </template>
            <span>{{ item.is_read ? 'Mark as Unread' : 'Mark as Read' }}</span>
          </v-tooltip>
        </template>

        <template v-slot:item.subject="{ item }">
          <span
            :class="{
              'font-weight-bold primary--text': !item.is_read,
              'grey--text text--darken-1': item.is_read,
            }"
            class="clickable-subject"
            @click="expandRow(item)"
          >
            {{ item.subject }}
          </span>
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
                color="error"
                v-bind="attrs"
                @click.stop="confirmDeleteMessage(item, false)"
                v-on="on"
              >
                <v-icon small>mdi-delete-outline</v-icon>
              </v-btn>
            </template>
            <span>Delete Message</span>
          </v-tooltip>
        </template>

        <template v-slot:expanded-item="{ headers: slotHeaders, item }">
          <td
            :colspan="slotHeaders.length"
            class="pa-4 expanded-content"
            :class="
              $vuetify.theme.dark ? 'grey darken-3' : 'blue-grey lighten-5'
            "
          >
            <div class="d-flex justify-space-between align-start mb-2">
              <div>
                <p class="mb-1">
                  <strong>From:</strong> {{ item.name }}
                  <a :href="'mailto:' + item.email">{{ item.email }}</a>
                </p>
                <p class="mb-0"><strong>Subject:</strong> {{ item.subject }}</p>
              </div>
              <div class="text-caption text--disabled">
                <strong>Received:</strong>
                {{ formatDate(item.created_at, true) }}
              </div>
            </div>
            <v-divider class="my-3"></v-divider>
            <h4 class="subtitle-1 mb-1 font-weight-medium">Message:</h4>
            <p style="white-space: pre-wrap" class="body-1">
              {{ item.message }}
            </p>
          </td>
        </template>

        <template v-slot:no-data>
          <div
            v-if="!($fetchState.pending || localLoading)"
            class="text-center pa-6 grey--text"
          >
            No messages found. Try adjusting your filters or refreshing.
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog.show" persistent max-width="450px">
      <v-card>
        <v-card-title class="text-h5 warning--text"
          ><v-icon left color="warning">mdi-alert-circle-outline</v-icon>Confirm
          Deletion</v-card-title
        >
        <v-card-text v-if="deleteDialog.isBulk" class="pt-4">
          Are you sure you want to delete
          <strong>{{ selectedMessages.length }}</strong> selected message(s)?
          This action cannot be undone.
        </v-card-text>
        <v-card-text v-else class="pt-4">
          Are you sure you want to delete this message from
          <strong>{{
            deleteDialog.message ? deleteDialog.message.name : ''
          }}</strong>
          regarding "<em>{{
            deleteDialog.message
              ? truncateText(deleteDialog.message.subject, 70)
              : ''
          }}</em
          >"? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text :disabled="isDeleting" @click="closeDeleteDialog"
            >Cancel</v-btn
          >
          <v-btn
            color="warning"
            depressed
            :loading="isDeleting"
            @click="deleteMessageConfirmed"
            >Delete</v-btn
          >
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      bottom
      right
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
import AdminPageWrapper from '~/components/admin/AdminPageWrapper.vue'

export default {
  name: 'AdminMessagesPage',
  components: { AdminPageWrapper },
  middleware: 'admin',
  layout: 'empty',

  async fetch() {
    const { sortBy, sortDesc, page, itemsPerPage } = this.options
    const params = {
      page, // Use initial page from options
      perPage: itemsPerPage,
      search: this.search, // Initial search is empty
      sortBy: sortBy.length ? sortBy[0] : 'created_at',
      sortDesc: sortDesc.length ? (sortDesc[0] ? 'desc' : 'asc') : 'desc',
    }
    if (this.filterReadStatus !== null) {
      params.is_read = this.filterReadStatus === '1'
    }

    try {
      const response = await this.$axios.get('/admin/messages', { params })
      if (response.data && Array.isArray(response.data.data)) {
        this.messages = response.data.data
        this.totalMessages = response.data.total
        // Update options page based on server response to keep pagination sync
        this.options.page = response.data.current_page
        this.options.itemsPerPage = Number(response.data.per_page) // Ensure it's a number
        // console.log('[FETCH AdminMessagesPage] Initial messages fetched:', this.messages.length);
      } else {
        throw new Error('Invalid data structure from API for messages.')
      }
    } catch (error) {
      console.error(
        '[FETCH Hook AdminMessagesPage] Error fetching initial messages:',
        error.response?.data || error
      )
      // To display error within the component using $fetchState.error:
      // This will be caught by <v-alert v-else-if="$fetchState.error">
      throw new Error(
        error.response?.data?.message || 'Failed to load messages initially.'
      )
    }
    // $fetchState.pending becomes false automatically
  },
  data() {
    return {
      messages: [],
      expanded: [],
      totalMessages: 0,
      localLoading: false, // For loading state during pagination/filter changes, NOT initial $fetch
      search: '',
      options: {
        page: 1,
        itemsPerPage: 15,
        sortBy: ['created_at'],
        sortDesc: [true],
      },
      filterReadStatus: null,
      readStatusOptions: [
        { text: 'All Messages', value: null },
        { text: 'Unread Messages', value: '0' },
        { text: 'Read Messages', value: '1' },
      ],
      headers: [
        { text: 'Subject', value: 'subject', sortable: true, width: '30%' },
        { text: 'From', value: 'name', sortable: true, width: '20%' },
        {
          text: 'Status',
          value: 'is_read',
          sortable: true,
          align: 'center',
          width: '100px',
        },
        { text: 'Received', value: 'created_at', sortable: true },
        {
          text: 'Actions',
          value: 'actions',
          sortable: false,
          align: 'end',
          width: '80px',
        }, // Adjusted width
        {
          text: '',
          value: 'data-table-expand',
          width: '50px',
          sortable: false,
        },
      ],
      selectedMessages: [],
      deleteDialog: { show: false, message: null, isBulk: false },
      isDeleting: false,
      actionInProgress: false, // General flag for bulk actions or toggles
      snackbar: { show: false, text: '', color: '' },
      debouncedFetchOnFilterChange: null,
    }
  },
  fetchOnServer: true, // Default Nuxt 2 behavior, run on server for SSR

  watch: {
    options: {
      handler(newOptions, oldOptions) {
        // Avoid refetching if only the items array changed (which options does for some reason)
        // Only refetch if page, itemsPerPage, sortBy, or sortDesc actually changed
        const relevantOptionsChanged =
          newOptions.page !== oldOptions.page ||
          newOptions.itemsPerPage !== oldOptions.itemsPerPage ||
          JSON.stringify(newOptions.sortBy) !==
            JSON.stringify(oldOptions.sortBy) ||
          JSON.stringify(newOptions.sortDesc) !==
            JSON.stringify(oldOptions.sortDesc)

        if (!this.$fetchState.pending && relevantOptionsChanged) {
          this.fetchMessagesAndUpdateFromOptions()
        }
      },
      deep: true,
    },
  },
  created() {
    // Debounce for search input primarily
    this.debouncedFetchOnFilterChange = _.debounce(this.handleFilterChange, 500)
  },
  methods: {
    async fetchMessagesAndUpdateFromOptions() {
      // Used by watch on options
      if (this.localLoading) return
      this.localLoading = true
      // fetchError is for the $fetch hook. Use another for this method's errors if needed
      try {
        const { sortBy, sortDesc, page, itemsPerPage } = this.options
        const params = {
          page,
          perPage: itemsPerPage,
          search: this.search,
          sortBy: sortBy.length ? sortBy[0] : 'created_at',
          sortDesc: sortDesc.length ? (sortDesc[0] ? 'desc' : 'asc') : 'desc',
        }
        if (this.filterReadStatus !== null) {
          params.is_read = this.filterReadStatus === '1'
        }
        const response = await this.$axios.get('/admin/messages', { params })
        this.messages = response.data.data
        this.totalMessages = response.data.total
        // options.page will be updated by v-data-table binding
      } catch (error) {
        console.error(
          'Error updating messages from options:',
          error.response?.data || error
        )
        this.showSnackbar('Failed to update messages list.', 'error')
      } finally {
        this.localLoading = false
      }
    },
    handleFilterChange() {
      // Called by status filter change and debounced search
      this.options.page = 1 // Reset to first page
      // The watch on 'options' will trigger fetchMessagesAndUpdateFromOptions
      // or you can call it directly if watch is not firing reliably enough due to page not being exactly 1.
      this.fetchMessagesAndUpdateFromOptions()
    },
    refreshData() {
      // Explicit refresh button
      this.$fetch() // Re-runs the Nuxt fetch() hook
      // Alternatively, call this.fetchMessagesAndUpdateFromOptions(true); if fetch() isn't enough
    },
    formatDate(dateString, includeTime = false) {
      if (!dateString) return 'N/A'
      const options = { day: '2-digit', month: 'short', year: 'numeric' }
      if (includeTime) {
        options.hour = '2-digit'
        options.minute = '2-digit'
      }
      try {
        return new Date(dateString).toLocaleString(undefined, options)
      } catch (e) {
        return dateString
      }
    },
    onItemExpanded({ item, value }) {
      if (value && item && !item.is_read) {
        this.markMessageAndUpdateState(item, true) // true for 'read'
      }
    },
    async toggleReadStatus(message) {
      const newStatus = !message.is_read
      await this.markMessageAndUpdateState(message, newStatus)
    },
    async markMessageAndUpdateState(message, markAsRead) {
      if (this.actionInProgress) return
      this.actionInProgress = true

      const originalStatus = message.is_read
      const action = markAsRead ? 'read' : 'unread'
      const endpoint = `/admin/messages/${message.id}/${action}`

      // Optimistic UI update for better UX
      const messageIndex = this.messages.findIndex((m) => m.id === message.id)
      if (messageIndex !== -1) {
        this.$set(this.messages[messageIndex], 'is_read', markAsRead)
      }

      try {
        const response = await this.$axios.patch(endpoint)
        // Update the item in the local array with the full response for consistency
        if (messageIndex !== -1 && response.data && response.data.data) {
          this.$set(this.messages, messageIndex, response.data.data)
        }
        // this.showSnackbar(`Message marked as ${action}.`, 'info');
      } catch (error) {
        console.error(`Error marking message as ${action}:`, error)
        this.showSnackbar(`Could not mark message as ${action}.`, 'error')
        // Revert optimistic update
        if (messageIndex !== -1) {
          this.$set(this.messages[messageIndex], 'is_read', originalStatus)
        }
      } finally {
        this.actionInProgress = false
      }
    },
    confirmDeleteMessage(message = null, isBulk = false) {
      if (isBulk) {
        if (this.selectedMessages.length === 0) {
          this.showSnackbar('No messages selected for deletion.', 'info')
          return
        }
      } else if (!message) {
        return
      }
      this.deleteDialog = { show: true, message, isBulk }
    },
    async deleteMessageConfirmed() {
      this.isDeleting = true
      let messageIdsToDelete = []
      let operationText = ''

      if (this.deleteDialog.isBulk) {
        messageIdsToDelete = this.selectedMessages.map((msg) => msg.id)
        operationText = `${messageIdsToDelete.length} selected message(s)`
      } else if (this.deleteDialog.message) {
        messageIdsToDelete.push(this.deleteDialog.message.id)
        operationText = `message from "${this.deleteDialog.message.name}"`
      }

      if (messageIdsToDelete.length === 0) {
        this.closeDeleteDialog()
        return
      }

      try {
        if (this.deleteDialog.isBulk) {
          await this.$axios.post(`/admin/messages/bulk-delete`, {
            ids: messageIdsToDelete,
          })
        } else {
          await this.$axios.delete(`/admin/messages/${messageIdsToDelete[0]}`)
        }
        this.showSnackbar(`Successfully deleted ${operationText}.`, 'success')
        this.selectedMessages = [] // Clear selection
        // Fetch needs to account for potential page change after deletion
        if (
          this.messages.length === messageIdsToDelete.length &&
          this.options.page > 1 &&
          this.deleteDialog.isBulk
        ) {
          this.options.page -= 1 // Go to previous page if all items on current page were deleted
        }
        this.fetchMessagesAndUpdateFromOptions() // Refresh list
      } catch (error) {
        console.error(
          'Error deleting message(s):',
          error.response?.data || error
        )
        this.showSnackbar(
          error.response?.data?.message || `Failed to delete ${operationText}.`,
          'error'
        )
      } finally {
        this.closeDeleteDialog()
      }
    },
    closeDeleteDialog() {
      this.deleteDialog = { show: false, message: null, isBulk: false }
      this.isDeleting = false
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
    truncateText(text = '', length = 50) {
      // Ensure text has a default
      return text.length > length ? text.substring(0, length) + '...' : text
    },
    expandRow(item) {
      const index = this.expanded.findIndex(
        (expandedItem) => expandedItem.id === item.id
      )
      if (index > -1) {
        this.expanded.splice(index, 1)
      } else {
        this.expanded = [item] // Auto-collapse others, expand current
        if (!item.is_read) {
          this.markMessageAndUpdateState(item, true) // Mark as read on expand
        }
      }
    },
    async bulkMarkAsReadOrUnread(markAsRead) {
      if (this.selectedMessages.length === 0) {
        this.showSnackbar(
          `No messages selected to mark as ${markAsRead ? 'read' : 'unread'}.`,
          'info'
        )
        return
      }
      if (this.actionInProgress) return
      this.actionInProgress = true

      const actionText = markAsRead ? 'read' : 'unread'
      // Optimistic update for UI
      this.selectedMessages.forEach((selectedMsg) => {
        const msgInTable = this.messages.find((m) => m.id === selectedMsg.id)
        if (msgInTable) msgInTable.is_read = markAsRead
      })

      try {
        // Backend: Consider a single bulk update endpoint for efficiency
        // For now, making individual calls
        for (const message of this.selectedMessages) {
          if (message.is_read !== markAsRead) {
            // Only update if status needs to change
            await this.$axios.patch(
              `/admin/messages/${message.id}/${actionText}`
            )
          }
        }
        this.showSnackbar(
          `${this.selectedMessages.length} message(s) marked as ${actionText}.`,
          'success'
        )
        this.selectedMessages = [] // Clear selection
        // this.fetchMessagesAndUpdateFromOptions(); // Optionally refresh data from server, or rely on optimistic
      } catch (error) {
        console.error(`Error bulk marking messages as ${actionText}:`, error)
        this.showSnackbar(
          `Failed to mark selected messages as ${actionText}. Some may not have updated.`,
          'error'
        )
        // Consider reverting optimistic updates or refreshing full list on error
        this.fetchMessagesAndUpdateFromOptions() // Refresh to get actual states
      } finally {
        this.actionInProgress = false
      }
    },
  },
  head() {
    return { title: 'Messages - Admin' }
  },
}
</script>

<style scoped>
.expanded-content strong {
  display: inline-block;
  min-width: 80px;
}
.expanded-content {
  font-size: 0.95rem;
}
.clickable-subject {
  cursor: pointer;
  transition: color 0.15s ease-in-out;
}
.clickable-subject:hover {
  color: var(
    --v-primary-base
  ) !important; /* Important to override default link colors potentially */
}
.cursor-pointer {
  cursor: pointer;
}
</style>
