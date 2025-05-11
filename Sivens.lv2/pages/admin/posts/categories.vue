<template>
  <AdminPageWrapper page-title="Manage Categories">
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4 font-weight-medium">Manage Post Categories</h1>
      <v-btn color="primary" depressed @click="openCategoryDialog(null)">
        <v-icon left>mdi-plus</v-icon>
        New Category
      </v-btn>
    </div>

    <v-card outlined>
      <v-card-title>
        All Categories
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search Categories..."
          single-line
          hide-details
          dense
          clearable
          style="max-width: 300px"
          @input="debouncedFetchCategories"
        ></v-text-field>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="categories"
        :options.sync="options"
        :server-items-length="totalCategories"
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
                @click="openCategoryDialog(item)"
                v-on="on"
              >
                <v-icon small>mdi-pencil-outline</v-icon>
              </v-btn>
            </template>
            <span>Edit Category</span>
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
            <span>Delete Category</span>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>

    <!-- Category Create/Edit Dialog -->
    <v-dialog v-model="categoryDialog.show" persistent max-width="500px">
      <v-card>
        <v-card-title>
          <span class="text-h5"
            >{{ categoryDialog.isEdit ? 'Edit' : 'Create' }} Category</span
          >
        </v-card-title>
        <v-card-text>
          <v-form ref="categoryForm" v-model="categoryDialog.valid">
            <v-text-field
              v-model="categoryDialog.category.name"
              label="Category Name"
              :rules="[(v) => !!v || 'Name is required']"
              required
              outlined
              dense
              :error-messages="categoryDialog.errors.name"
            ></v-text-field>
          </v-form>
          <v-alert
            v-if="categoryDialog.formError"
            dense
            text
            type="error"
            class="mt-2"
          >
            {{ categoryDialog.formError }}
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="closeCategoryDialog">Cancel</v-btn>
          <v-btn
            color="primary"
            depressed
            :loading="categoryDialog.loading"
            :disabled="!categoryDialog.valid"
            @click="saveCategory"
          >
            Save
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
          >Are you sure you want to delete the category: "{{
            deleteDialog.category ? deleteDialog.category.name : ''
          }}"? This may affect existing posts.</v-card-text
        >
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog.show = false">Cancel</v-btn>
          <v-btn color="error" depressed @click="deleteCategoryConfirmed"
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
    </v-snackbar>
  </AdminPageWrapper>
</template>

<script>
import _ from 'lodash'
import AdminPageWrapper from '~/components/admin/AdminPageWrapper.vue'

export default {
  name: 'AdminCategoriesManagamentPage', // Unique Name
  components: { AdminPageWrapper },
  middleware: 'admin',
  data() {
    // Same data as your previous category management
    return {
      categories: [],
      totalCategories: 0,
      loading: true,
      search: '',
      options: {
        page: 1,
        itemsPerPage: 10,
        sortBy: ['name'],
        sortDesc: [false],
      },
      headers: [
        { text: 'ID', value: 'id', sortable: true, width: '80px' },
        { text: 'Name', value: 'name', sortable: true },
        { text: 'Posts Count', value: 'posts_count', sortable: true },
        { text: 'Created At', value: 'created_at', sortable: true },
        {
          text: 'Actions',
          value: 'actions',
          sortable: false,
          align: 'end',
          width: '120px',
        },
      ],
      categoryDialog: {
        show: false,
        isEdit: false,
        valid: false,
        loading: false,
        formError: null,
        errors: {},
        category: { id: null, name: '' },
      },
      deleteDialog: { show: false, category: null },
      snackbar: { show: false, text: '', color: '' },
      debouncedFetchCategories: null,
    }
  },
  watch: {
    options: {
      handler() {
        this.fetchCategories()
      },
      deep: true,
    },
  },
  created() {
    this.debouncedFetchCategories = _.debounce(this.fetchCategories, 500)
  },
  async mounted() {
    // Use mounted or asyncData/fetch
    await this.fetchCategories()
  },
  methods: {
    async fetchCategories() {
      this.loading = true
      try {
        const { sortBy, sortDesc, page, itemsPerPage } = this.options
        // IMPORTANT: Use consistent API endpoint name like /admin/categories
        const response = await this.$axios.get('/admin/categories', {
          params: {
            page,
            perPage: itemsPerPage,
            search: this.search,
            sortBy: sortBy.length ? sortBy[0] : 'name',
            sortDesc: sortDesc.length ? sortDesc[0] : false,
          },
        })
        this.categories = response.data.data
        this.totalCategories = response.data.total
      } catch (error) {
        console.error('Error fetching categories:', error)
        this.showSnackbar('Failed to load categories.', 'error')
      } finally {
        this.loading = false
      }
    },
    formatDate(dateString) {
      return dateString ? new Date(dateString).toLocaleDateString() : 'N/A'
    },
    openCategoryDialog(category = null) {
      this.categoryDialog.formError = null
      this.categoryDialog.errors = {}
      if (category) {
        this.categoryDialog.isEdit = true
        this.categoryDialog.category = { ...category }
      } else {
        this.categoryDialog.isEdit = false
        this.categoryDialog.category = { id: null, name: '' }
      }
      this.categoryDialog.show = true
      this.$nextTick(() => {
        if (this.$refs.categoryForm) this.$refs.categoryForm.resetValidation()
      })
    },
    closeCategoryDialog() {
      this.categoryDialog.show = false
    },
    async saveCategory() {
      if (!this.$refs.categoryForm.validate()) return

      this.categoryDialog.loading = true
      this.categoryDialog.formError = null
      this.categoryDialog.errors = {}
      const apiPath = this.categoryDialog.isEdit
        ? `/admin/categories/${this.categoryDialog.category.id}`
        : '/admin/categories'
      const method = this.categoryDialog.isEdit ? '$put' : '$post'

      try {
        await this.$axios[method](apiPath, {
          name: this.categoryDialog.category.name,
        })
        this.showSnackbar(
          `Category ${
            this.categoryDialog.isEdit ? 'updated' : 'created'
          } successfully.`,
          'success'
        )
        this.fetchCategories()
        this.closeCategoryDialog()
      } catch (error) {
        console.error('Error saving category:', error)
        if (error.response && error.response.status === 422) {
          this.categoryDialog.errors = error.response.data.errors || {}
          this.categoryDialog.formError =
            'Please correct the highlighted errors.'
        } else {
          this.categoryDialog.formError =
            error.response?.data?.message || 'Failed to save category.'
        }
      } finally {
        this.categoryDialog.loading = false
      }
    },
    confirmDelete(category) {
      this.deleteDialog = { show: true, category }
    },
    async deleteCategoryConfirmed() {
      if (!this.deleteDialog.category) return
      try {
        await this.$axios.delete(
          `/admin/categories/${this.deleteDialog.category.id}`
        )
        this.showSnackbar('Category deleted successfully.', 'success')
        this.fetchCategories()
      } catch (error) {
        console.error('Error deleting category:', error)
        this.showSnackbar(
          error.response?.data?.message || 'Failed to delete category.',
          'error'
        )
      } finally {
        this.deleteDialog.show = false
        this.deleteDialog.category = null
      }
    },
    showSnackbar(text, color = 'info') {
      this.snackbar = { show: true, text, color }
    },
  },
  head() {
    return { title: 'Manage Categories' }
  },
}
</script>
