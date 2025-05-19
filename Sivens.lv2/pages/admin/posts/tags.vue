<template>
  <AdminPageWrapper page-title="Manage Tags">
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4 font-weight-medium">Manage Post Tags</h1>
      <v-btn color="primary" depressed @click="openTagDialog(null)">
        <v-icon left>mdi-plus-box-outline</v-icon>
        New Tag
      </v-btn>
    </div>

    <v-card outlined>
      <v-card-title>
        All Tags
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Tags..."
          single-line
          hide-details
          dense
          clearable
          style="max-width: 300px"
          @input="debouncedFetchTags"
        ></v-text-field>
      </v-card-title>

      <v-alert v-if="fetchError" type="error" dense text class="mx-4">{{
        fetchError
      }}</v-alert>

      <v-data-table
        :headers="headers"
        :items="tags"
        :options.sync="options"
        :server-items-length="totalTags"
        :loading="loading"
        :footer-props="{ 'items-per-page-options': [10, 25, 50] }"
        class="elevation-0"
        item-key="id"
      >
        <template v-slot:item.posts_count="{ item }">
          {{ item.posts_count != null ? item.posts_count : 0 }}
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
                v-bind="attrs"
                @click="openTagDialog(item)"
                v-on="on"
              >
                <v-icon small>mdi-pencil-outline</v-icon>
              </v-btn>
            </template>
            <span>Edit Tag</span>
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
            <span>Delete Tag</span>
          </v-tooltip>
        </template>
        <template v-slot:no-data>
          <div v-if="!loading" class="text-center pa-4 grey--text">
            No tags found.
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Tag Create/Edit Dialog -->
    <v-dialog v-model="tagDialog.show" persistent max-width="500px">
      <v-card>
        <v-card-title>
          <span class="text-h5"
            >{{ tagDialog.isEdit ? 'Edit' : 'Create' }} Tag</span
          >
        </v-card-title>
        <v-card-text>
          <v-form ref="tagForm" v-model="tagDialog.valid">
            <v-text-field
              v-model="tagDialog.tag.name"
              label="Tag Name"
              :rules="[rules.required, rules.maxLength(50)]"
              required
              outlined
              dense
              counter="50"
              :error-messages="tagDialog.errors.name"
            ></v-text-field>
            <!-- Optional: Add a slug field if your tags have slugs -->
            <!--
            <v-text-field
              v-model="tagDialog.tag.slug"
              label="Tag Slug (optional)"
              placeholder="auto-generated-if-empty"
              outlined
              dense
              :error-messages="tagDialog.errors.slug"
            ></v-text-field>
            -->
          </v-form>
          <v-alert
            v-if="tagDialog.formError"
            dense
            text
            type="error"
            class="mt-2"
          >
            {{ tagDialog.formError }}
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text :disabled="tagDialog.loading" @click="closeTagDialog"
            >Cancel</v-btn
          >
          <v-btn
            color="primary"
            depressed
            :loading="tagDialog.loading"
            :disabled="!tagDialog.valid || tagDialog.loading"
            @click="saveTag"
          >
            Save Tag
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog.show" persistent max-width="400px">
      <v-card>
        <v-card-title class="text-h5 error--text"
          >Confirm Deletion</v-card-title
        >
        <v-card-text
          >Are you sure you want to delete the tag: "<strong>{{
            deleteDialog.tag ? deleteDialog.tag.name : ''
          }}</strong
          >"? This will remove the tag from all associated posts.</v-card-text
        >
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog.show = false">Cancel</v-btn>
          <v-btn color="error" depressed @click="deleteTagConfirmed"
            >Delete</v-btn
          >
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
  name: 'AdminTagsPage',
  components: { AdminPageWrapper },
  middleware: 'admin',
  layout: 'empty',
  data() {
    return {
      tags: [],
      totalTags: 0,
      loading: true,
      fetchError: null,
      search: '',
      options: {
        page: 1,
        itemsPerPage: 15,
        sortBy: ['name'],
        sortDesc: [false],
      },
      headers: [
        { text: 'ID', value: 'id', sortable: true, width: '80px' },
        { text: 'Name', value: 'name', sortable: true },
        { text: 'Posts Count', value: 'posts_count', sortable: true }, // Backend needs to provide this with withCount
        { text: 'Created At', value: 'created_at', sortable: true },
        {
          text: 'Actions',
          value: 'actions',
          sortable: false,
          align: 'end',
          width: '120px',
        },
      ],
      tagDialog: {
        show: false,
        isEdit: false,
        valid: false,
        loading: false,
        formError: null,
        errors: {}, // For field-specific backend validation errors
        tag: { id: null, name: '', slug: '' }, // Add slug if you use it
      },
      deleteDialog: { show: false, tag: null },
      snackbar: { show: false, text: '', color: '' },
      debouncedFetchTags: null,
      rules: {
        required: (v) => !!v || 'This field is required.',
        maxLength: (len) => (v) =>
          (v && v.length <= len) || `Max ${len} characters.`,
      },
    }
  },
  watch: {
    options: {
      handler() {
        this.fetchTags()
      },
      deep: true,
    },
  },
  created() {
    this.debouncedFetchTags = _.debounce(this.fetchTags, 500)
  },
  async mounted() {
    await this.fetchTags()
  },
  methods: {
    async fetchTags() {
      this.loading = true
      this.fetchError = null
      try {
        const { sortBy, sortDesc, page, itemsPerPage } = this.options
        // Ensure API endpoint is /api/admin/tags or similar
        const response = await this.$axios.get('/admin/tags', {
          params: {
            page,
            perPage: itemsPerPage,
            search: this.search,
            sortBy: sortBy.length ? sortBy[0] : 'name',
            sortDesc: sortDesc.length ? (sortDesc[0] ? 'desc' : 'asc') : 'asc',
          },
        })
        this.tags = response.data.data
        this.totalTags = response.data.total
      } catch (error) {
        console.error('Error fetching tags:', error)
        this.fetchError =
          error.response?.data?.message || 'Failed to load tags.'
        this.showSnackbar(this.fetchError, 'error')
      } finally {
        this.loading = false
      }
    },
    formatDate(dateString) {
      return dateString ? new Date(dateString).toLocaleDateString() : 'N/A'
    },
    openTagDialog(tag = null) {
      this.tagDialog.formError = null
      this.tagDialog.errors = {}
      if (tag) {
        this.tagDialog.isEdit = true
        this.tagDialog.tag = { ...tag }
      } else {
        this.tagDialog.isEdit = false
        this.tagDialog.tag = { id: null, name: '', slug: '' }
      }
      this.tagDialog.show = true
      this.$nextTick(() => {
        if (this.$refs.tagForm) this.$refs.tagForm.resetValidation()
      })
    },
    closeTagDialog() {
      this.tagDialog.show = false
    },
    async saveTag() {
      if (!this.$refs.tagForm.validate()) return

      this.tagDialog.loading = true
      this.tagDialog.formError = null
      this.tagDialog.errors = {}

      const payload = { name: this.tagDialog.tag.name }
      // if (this.tagDialog.tag.slug) { // Only send slug if provided, backend can auto-generate
      //     payload.slug = this.tagDialog.tag.slug;
      // }

      const apiPath = this.tagDialog.isEdit
        ? `/admin/tags/${this.tagDialog.tag.id}`
        : '/admin/tags'
      const method = this.tagDialog.isEdit ? '$put' : '$post'

      try {
        await this.$axios[method](apiPath, payload)
        this.showSnackbar(
          `Tag ${this.tagDialog.isEdit ? 'updated' : 'created'} successfully.`,
          'success'
        )
        this.fetchTags()
        this.closeTagDialog()
      } catch (error) {
        console.error('Error saving tag:', error)
        if (
          error.response &&
          error.response.status === 422 &&
          error.response.data.errors
        ) {
          this.tagDialog.errors = error.response.data.errors
          this.tagDialog.formError = 'Please correct the form errors.'
        } else {
          this.tagDialog.formError =
            error.response?.data?.message || 'Failed to save tag.'
        }
      } finally {
        this.tagDialog.loading = false
      }
    },
    confirmDelete(tag) {
      this.deleteDialog = { show: true, tag }
    },
    async deleteTagConfirmed() {
      if (!this.deleteDialog.tag) return
      try {
        await this.$axios.delete(`/admin/tags/${this.deleteDialog.tag.id}`)
        this.showSnackbar('Tag deleted successfully.', 'success')
        this.fetchTags()
      } catch (error) {
        console.error('Error deleting tag:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Failed to delete tag.',
          'error'
        )
      } finally {
        this.deleteDialog.show = false
        this.deleteDialog.tag = null
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar.text = text
      this.snackbar.color = color
      this.snackbar.show = true
    },
  },
  head() {
    return { title: 'Manage Tags - Admin' }
  },
}
</script>

<style scoped>
/* Add any specific styles if needed */
</style>
