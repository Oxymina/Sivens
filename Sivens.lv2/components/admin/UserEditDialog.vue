<template>
  <v-dialog
    :value="dialog"
    persistent
    max-width="500px"
    @input="$emit('update:dialog', $event)"
  >
    <v-card v-if="editableUser">
      <v-card-title>
        <span class="text-h5">Edit User Role: {{ editableUser.name }}</span>
      </v-card-title>
      <v-card-text>
        <v-select
          v-model="selectedRoleId"
          :items="roles"
          item-text="name"
          item-value="id"
          label="Assign Role"
          outlined
          dense
          :disabled="loading"
          :error-messages="error"
        ></v-select>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text :disabled="loading" @click="$emit('update:dialog', false)"
          >Cancel</v-btn
        >
        <v-btn color="primary" depressed :loading="loading" @click="saveRole"
          >Save Role</v-btn
        >
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'UserEditDialog',
  props: {
    dialog: Boolean,
    user: { type: Object, default: null },
    roles: { type: Array, default: () => [] }, // [{id: 1, name: 'reader'}, ...]
  },
  data() {
    return {
      editableUser: null,
      selectedRoleId: null,
      loading: false,
      error: null,
    }
  },
  computed: {
    ...mapGetters('auth', { currentUser: 'getUser' }),
  },
  watch: {
    dialog(val) {
      if (val && this.user) {
        this.editableUser = { ...this.user }
        // user.role might be null if role_id was null and not eager loaded properly
        this.selectedRoleId = this.user.role ? this.user.role.id : null
        this.error = null
      } else {
        this.editableUser = null // Clear when dialog closes
        this.selectedRoleId = null
      }
    },
  },
  methods: {
    async saveRole() {
      if (!this.editableUser || this.selectedRoleId === null) {
        this.error = 'Please select a role.'
        return
      }
      // Prevent admin from demoting themselves if it's their own record and the new role isn't admin
      if (this.currentUser && this.currentUser.id === this.editableUser.id) {
        const selectedRoleObject = this.roles.find(
          (r) => r.id === this.selectedRoleId
        )
        if (selectedRoleObject && selectedRoleObject.name !== 'admin') {
          this.error = "Admins cannot demote their own role from 'admin'."
          return
        }
      }

      this.loading = true
      this.error = null
      try {
        // API endpoint: PUT /api/admin/users/{userId}/role
        // Body: { role_id: X }
        await this.$axios.put(`/admin/users/${this.editableUser.id}/role`, {
          role_id: this.selectedRoleId,
        })
        this.$emit('user-updated')
        this.$emit('update:dialog', false) // Close dialog
      } catch (err) {
        console.error('Error updating user role:', err)
        this.error = err.response?.data?.message || 'Failed to update role.'
      } finally {
        this.loading = false
      }
    },
  },
}
</script>
