<template>
  <AdminPageWrapper page-title="Manage Users">
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4 font-weight-medium">User Management</h1>
      <!-- <v-btn color="primary" depressed @click="openCreateUserDialog">
        <v-icon left>mdi-account-plus-outline</v-icon>
        New User
      </v-btn> -->
    </div>

    <v-card outlined>
      <v-card-title>
        All Users
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Users (Name or Email)..."
          single-line
          hide-details
          dense
          clearable
          style="max-width: 300px"
          @input="debouncedFetchUsers"
        ></v-text-field>
      </v-card-title>
      <v-data-table
        v-model="selectedUsers"
        :headers="headers"
        :items="users"
        :options.sync="options"
        :server-items-length="totalUsers"
        :loading="loading"
        :footer-props="{ 'items-per-page-options': [10, 25, 50, 100] }"
        class="elevation-0"
        item-key="id"
        show-select
      >
        <template v-slot:item.role="{ item }">
          <v-chip
            :color="getRoleColor(item.role ? item.role.name : '')"
            small
            dark
          >
            {{ item.role ? item.role.name.toUpperCase() : 'N/A' }}
          </v-chip>
        </template>
        <template v-slot:item.userPFP="{ item }">
          <v-avatar size="32" class="my-1">
            <img
              v-if="item.userPFP"
              :src="`http://localhost:8000/storage/${item.userPFP}`"
              :alt="item.name"
            />
            <v-icon v-else>mdi-account-circle</v-icon>
          </v-avatar>
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
                color="primary"
                v-bind="attrs"
                @click="editUser(item)"
                v-on="on"
              >
                <v-icon small>mdi-account-edit-outline</v-icon>
              </v-btn>
            </template>
            <span>Edit Role</span>
          </v-tooltip>
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                icon
                small
                color="error"
                :disabled="item.id === currentUser?.id"
                v-bind="attrs"
                @click="confirmDelete(item)"
                v-on="on"
              >
                <v-icon small>mdi-delete-outline</v-icon>
              </v-btn>
            </template>
            <span>Delete User</span>
          </v-tooltip>
        </template>
      </v-data-table>

      <v-card-actions
        v-if="selectedUsers.length > 0"
        class="grey lighten-3 pa-2"
      >
        <span class="text-caption"
          >{{ selectedUsers.length }} user(s) selected</span
        >
        <v-spacer></v-spacer>
        <v-menu offset-y>
          <template v-slot:activator="{ on, attrs }">
            <v-btn small outlined color="secondary" v-bind="attrs" v-on="on">
              Bulk Actions <v-icon right small>mdi-menu-down</v-icon>
            </v-btn>
          </template>
          <v-list dense>
            <v-list-item @click="bulkChangeRole('reader')">
              <v-list-item-icon
                ><v-icon small>mdi-account-outline</v-icon></v-list-item-icon
              >
              <v-list-item-title>Set as Reader</v-list-item-title>
            </v-list-item>
            <v-list-item @click="bulkChangeRole('writer')">
              <v-list-item-icon
                ><v-icon small>mdi-pencil-outline</v-icon></v-list-item-icon
              >
              <v-list-item-title>Set as Writer</v-list-item-title>
            </v-list-item>
            <v-list-item @click="bulkChangeRole('admin')">
              <v-list-item-icon
                ><v-icon small
                  >mdi-shield-crown-outline</v-icon
                ></v-list-item-icon
              >
              <v-list-item-title>Set as Admin</v-list-item-title>
            </v-list-item>
            <v-divider></v-divider>
            <v-list-item class="error--text" @click="bulkDeleteUsers">
              <v-list-item-icon
                ><v-icon small color="error"
                  >mdi-delete-sweep-outline</v-icon
                ></v-list-item-icon
              >
              <v-list-item-title>Delete Selected</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </v-card-actions>
    </v-card>

    <UserEditDialog
      :dialog.sync="userEditDialog.show"
      :user="userEditDialog.user"
      :roles="availableRoles"
      @user-updated="onUserUpdated"
    />

    <v-dialog v-model="deleteDialog.show" persistent max-width="400px">
      <v-card>
        <v-card-title class="text-h5 error--text"
          >Confirm Deletion</v-card-title
        >
        <v-card-text v-if="deleteDialog.isBulk"
          >Are you sure you want to delete {{ selectedUsers.length }} selected
          user(s)? This action cannot be undone.</v-card-text
        >
        <v-card-text v-else
          >Are you sure you want to delete user: "{{
            deleteDialog.user ? deleteDialog.user.name : ''
          }}"? This action cannot be undone.</v-card-text
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
      :timeout="3000"
    >
      {{ snackbar.text }}
    </v-snackbar>
  </AdminPageWrapper>
</template>

<script>
import _ from 'lodash'
import { mapGetters } from 'vuex'
import AdminPageWrapper from '~/components/admin/AdminPageWrapper.vue'
import UserEditDialog from '~/components/admin/UserEditDialog.vue'

export default {
  name: 'AdminUsersPage',
  components: { AdminPageWrapper, UserEditDialog },
  layout: 'admin',
  middleware: 'admin',
  data() {
    return {
      users: [],
      availableRoles: [], // Will hold roles like [{id:1, name:'reader'}, ...]
      totalUsers: 0,
      loading: true,
      search: '',
      options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['created_at'],
        sortDesc: [true],
      },
      headers: [
        { text: 'Avatar', value: 'userPFP', sortable: false, width: '70px' },
        { text: 'Name', value: 'name', sortable: true },
        { text: 'Email', value: 'email', sortable: true },
        { text: 'Role', value: 'role', sortable: true }, // Will use custom slot
        { text: 'Joined', value: 'created_at', sortable: true },
        {
          text: 'Actions',
          value: 'actions',
          sortable: false,
          align: 'end',
          width: '150px',
        },
      ],
      selectedUsers: [], // For bulk actions
      userEditDialog: { show: false, user: null },
      deleteDialog: { show: false, user: null, isBulk: false },
      snackbar: { show: false, text: '', color: '' },
      debouncedFetchUsers: null,
    }
  },
  computed: {
    ...mapGetters('auth', { currentUser: 'getUser' }), // To prevent deleting self
  },
  watch: {
    options: {
      handler() {
        this.fetchUsers()
      },
      deep: true,
    },
  },
  created() {
    this.debouncedFetchUsers = _.debounce(this.fetchUsers, 500)
  },
  async mounted() {
    await this.fetchRoles() // Fetch roles first for the dialog
    await this.fetchUsers()
  },
  methods: {
    async fetchUsers() {
      this.loading = true
      try {
        const { sortBy, sortDesc, page, itemsPerPage } = this.options
        const response = await this.$axios.get('/admin/users', {
          // Needs new API endpoint
          params: {
            page,
            perPage: itemsPerPage,
            search: this.search,
            sortBy: sortBy.length ? sortBy[0] : 'created_at',
            sortDesc: sortDesc.length ? sortDesc[0] : true,
          },
        })
        this.users = response.data.data
        this.totalUsers = response.data.total
      } catch (error) {
        console.error('Error fetching users:', error)
        this.showSnackbar('Failed to load users.', 'error')
      } finally {
        this.loading = false
      }
    },
    async fetchRoles() {
      try {
        const response = await this.$axios.get('/admin/roles') // New API endpoint to get all roles
        this.availableRoles = response.data
      } catch (error) {
        console.error('Error fetching roles:', error)
        this.showSnackbar('Failed to load roles.', 'error')
      }
    },
    formatDate(dateString) {
      /* ... */ return new Date(dateString).toLocaleDateString()
    },
    getRoleColor(roleName) {
      if (roleName === 'admin') return 'error'
      if (roleName === 'writer') return 'blue'
      return 'grey'
    },
    editUser(user) {
      this.userEditDialog = { show: true, user: { ...user } } // Pass a copy
    },
    onUserUpdated() {
      this.showSnackbar('User role updated successfully.', 'success')
      this.fetchUsers() // Refresh user list
    },
    confirmDelete(user) {
      if (user.id === this.currentUser?.id) {
        this.showSnackbar('You cannot delete your own account.', 'warning')
        return
      }
      this.deleteDialog = { show: true, user, isBulk: false }
    },
    async deleteConfirmed() {
      let usersToDelete = []
      if (this.deleteDialog.isBulk) {
        usersToDelete = this.selectedUsers
          .filter((u) => u.id !== this.currentUser?.id)
          .map((u) => u.id)
        if (this.selectedUsers.some((u) => u.id === this.currentUser?.id)) {
          this.showSnackbar('Cannot delete current user in bulk.', 'warning')
        }
      } else if (this.deleteDialog.user) {
        if (this.deleteDialog.user.id === this.currentUser?.id) {
          this.showSnackbar('You cannot delete your own account.', 'warning')
          this.deleteDialog = { show: false, user: null, isBulk: false }
          return
        }
        usersToDelete.push(this.deleteDialog.user.id)
      }

      if (usersToDelete.length === 0) {
        this.deleteDialog = { show: false, user: null, isBulk: false }
        return
      }

      try {
        // You might need a bulk delete endpoint or loop through
        for (const userId of usersToDelete) {
          await this.$axios.delete(`/admin/users/${userId}`)
        }
        this.showSnackbar(
          `Successfully deleted ${usersToDelete.length} user(s).`,
          'success'
        )
        this.fetchUsers()
        this.selectedUsers = [] // Clear selection
      } catch (error) {
        console.error('Error deleting user(s):', error)
        this.showSnackbar('Failed to delete user(s).', 'error')
      } finally {
        this.deleteDialog = { show: false, user: null, isBulk: false }
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
    async bulkChangeRole(newRoleName) {
      if (this.selectedUsers.length === 0) {
        this.showSnackbar('No users selected.', 'info')
        return
      }
      const roleToSet = this.availableRoles.find((r) => r.name === newRoleName)
      if (!roleToSet) {
        this.showSnackbar('Invalid role selected for bulk action.', 'error')
        return
      }
      if (
        confirm(
          `Set role to "${newRoleName}" for ${this.selectedUsers.length} user(s)?`
        )
      ) {
        try {
          for (const user of this.selectedUsers) {
            if (user.id === this.currentUser?.id && newRoleName !== 'admin') {
              // Prevent admin demoting self
              this.showSnackbar(
                `Admin ${user.name} cannot demote self from Admin role.`,
                'warning'
              )
              continue
            }
            await this.$axios.put(`/admin/users/${user.id}/role`, {
              role_id: roleToSet.id,
            })
          }
          this.showSnackbar(`Roles updated for selected users.`, 'success')
          this.fetchUsers()
          this.selectedUsers = []
        } catch (error) {
          console.error('Bulk role change failed:', error)
          this.showSnackbar('Failed to update roles.', 'error')
        }
      }
    },
    bulkDeleteUsers() {
      if (this.selectedUsers.length === 0) {
        this.showSnackbar('No users selected for deletion.', 'info')
        return
      }
      this.deleteDialog = { show: true, user: null, isBulk: true }
    },
  },
  head() {
    return { title: 'Manage Users' }
  },
}
</script>
