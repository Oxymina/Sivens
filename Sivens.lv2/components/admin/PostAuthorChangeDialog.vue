<template>
  <v-dialog
    :value="dialog"
    persistent
    max-width="500px"
    @input="$emit('update:dialog', $event)"
  >
    <v-card v-if="editablePost">
      <v-card-title>
        <span class="text-h5"
          >Change Author for: "{{ editablePost.title }}"</span
        >
      </v-card-title>
      <v-card-text>
        <v-select
          v-model="selectedAuthorId"
          :items="users"
          item-text="name"
          item-value="id"
          label="Assign New Author"
          outlined
          dense
          :disabled="loading"
          :error-messages="error"
          clearable
        ></v-select>
        <div v-if="originalAuthor" class="text-caption grey--text">
          Current Author: {{ originalAuthor.name }}
        </div>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text :disabled="loading" @click="$emit('update:dialog', false)"
          >Cancel</v-btn
        >
        <v-btn
          color="primary"
          depressed
          :loading="loading"
          :disabled="selectedAuthorId === (originalAuthor && originalAuthor.id)"
          @click="saveNewAuthor"
        >
          Update Author
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'PostAuthorChangeDialog',
  props: {
    dialog: Boolean,
    post: { type: Object, default: null },
    users: { type: Array, default: () => [] }, // List of all users: [{id:1, name:'User A'}, ...]
  },
  data() {
    return {
      editablePost: null,
      selectedAuthorId: null,
      originalAuthor: null,
      loading: false,
      error: null,
    }
  },
  watch: {
    dialog(val) {
      if (val && this.post) {
        this.editablePost = { ...this.post }
        this.originalAuthor = this.post.author ? { ...this.post.author } : null // Store original for display and comparison
        this.selectedAuthorId = this.post.author ? this.post.author.id : null
        this.error = null
      } else {
        this.editablePost = null
        this.originalAuthor = null
        this.selectedAuthorId = null
      }
    },
  },
  methods: {
    async saveNewAuthor() {
      if (!this.editablePost || this.selectedAuthorId === null) {
        this.error = 'Please select a new author.'
        return
      }
      if (
        this.selectedAuthorId ===
        (this.originalAuthor && this.originalAuthor.id)
      ) {
        this.error = 'New author is the same as the current author.'
        return
      }
      this.loading = true
      this.error = null
      try {
        // API endpoint: PUT /api/admin/posts/{postId}/author
        // Body: { author_id: X }
        await this.$axios.put(`/admin/posts/${this.editablePost.id}/author`, {
          author_id: this.selectedAuthorId,
        })
        this.$emit('author-changed') // Notify parent
        this.$emit('update:dialog', false)
      } catch (err) {
        console.error('Error updating post author:', err)
        this.error = err.response?.data?.message || 'Failed to update author.'
      } finally {
        this.loading = false
      }
    },
  },
}
</script>
