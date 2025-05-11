<template>
  <section :class="$vuetify.theme.dark ? 'grey darken-4' : 'grey lighten-4'">
    <v-row no-gutters>
      <v-col cols="12">
        <SectionsHeroAlt :hero-alt="heroAlt" />
      </v-col>
    </v-row>
    <v-container>
      <v-row>
        <v-col cols="12" xl="10" lg="9" md="8" sm="8" class="py-16">
          <!-- Loading and Error States -->
          <v-row v-if="loadingPosts" justify="center">
            <v-progress-circular
              indeterminate
              color="primary"
            ></v-progress-circular>
          </v-row>
          <v-alert v-else-if="fetchError" type="error">
            {{ fetchError }}
          </v-alert>
          <v-row v-else-if="posts.length === 0 && !loadingPosts">
            <v-col cols="12" class="text-center">
              <p>No posts found matching your criteria.</p>
            </v-col>
          </v-row>
          <!-- Display Posts -->
          <v-row v-else>
            <v-col
              v-for="post in posts"
              :key="post.id"
              cols="12"
              sm="6"
              md="6"
              lg="4"
              xl="3"
            >
              <SectionsBlogpost
                :blogpost="truncateContent(post)"
                @click.native="goToPost(post.id)"
              />
            </v-col>
          </v-row>
          <div class="text-center mt-8">
            <v-container>
              <v-row justify="center">
                <v-col cols="10" md="8">
                  <v-pagination
                    v-model="currentPage"
                    circle
                    class="my-4"
                    :length="totalPages"
                    :total-visible="7"
                    @input="handlePageChange"
                  >
                  </v-pagination>
                </v-col>
              </v-row>
            </v-container>
          </div>
        </v-col>
        <v-col cols="12" xl="2" lg="3" md="4" sm="4" class="py-16">
          <aside>
            <v-text-field
              v-model="search"
              clearable
              dense
              flat
              outlined
              placeholder="Search..."
              append-icon="mdi-magnify"
              class="mb-6"
              hide-details
              @input="debouncedFetchPosts"
            >
            </v-text-field>
            <v-card outlined class="mb-6">
              <div
                class="subtitle font-weight-black text-uppercase text-center mt-4"
              >
                Categories
              </div>
              <v-list dense>
                <v-list-item-group
                  v-model="selectedCategory"
                  color="primary"
                  @change="handleFilterChange"
                >
                  <v-list-item :value="null">
                    <!-- Using null for 'All Categories' -->
                    <v-list-item-content>
                      <v-list-item-title>All Categories</v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                  <v-list-item
                    v-for="category in categories"
                    :key="category.id"
                    :value="category.id"
                  >
                    <v-list-item-content>
                      <v-list-item-title
                        v-text="category.name"
                      ></v-list-item-title>
                    </v-list-item-content>
                  </v-list-item>
                </v-list-item-group>
              </v-list>
            </v-card>
            <v-card outlined class="mb-6">
              <div
                class="subtitle font-weight-black text-uppercase text-center mt-4"
              >
                Tags
              </div>
              <v-card-text>
                <v-chip-group
                  v-model="selectedTag"
                  column
                  @change="handleFilterChange"
                >
                  <!-- Add v-model & change handler for tags -->
                  <v-chip v-for="tag in tags" :key="tag.id" :value="tag.id">
                    {{ tag.name }}
                  </v-chip>
                </v-chip-group>
              </v-card-text>
            </v-card>
          </aside>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
import _ from 'lodash' // For debouncing search input

export default {
  // REMOVED 'paginatedPosts' and old 'totalPages' computed properties

  async fetch() {
    // Nuxt's fetch hook for initial data load (SSR friendly)
    await this.fetchInitialData()
  },
  data() {
    return {
      heroAlt: [
        /* ... */
      ],
      posts: [], // This will hold the posts for the CURRENT page
      categories: [],
      tags: [],
      selectedCategory: null, // Changed from 0
      selectedTag: null, // For tag filtering (optional)
      search: '',

      // Pagination state from backend
      currentPage: 1,
      totalPages: 0, // Will be last_page from API
      totalPosts: 0, // Will be total from API
      postsPerPage: 10, // Matches backend, or from API 'per_page'

      loadingPosts: true,
      fetchError: null,
      debouncedFetchPosts: null, // For search debouncing
    }
  },
  created() {
    // Initialize debounced function
    this.debouncedFetchPosts = _.debounce(this.handleFilterChange, 500)
  },
  methods: {
    async fetchInitialData() {
      this.loadingPosts = true
      this.fetchError = null
      try {
        // Use $axios from Nuxt context
        const [postsData, categoriesData, tagsData] = await Promise.all([
          this.$axios.get('/posts', {
            // Send current page, search, category
            params: {
              page: this.currentPage,
              search: this.search,
              category: this.selectedCategory,
              // tag: this.selectedTag // Add if implementing tag filter
            },
          }),
          this.$axios.get('/categories'),
          this.$axios.get('/tags'),
        ])

        this.posts = postsData.data.data
        this.totalPages = postsData.data.last_page
        this.totalPosts = postsData.data.total
        this.currentPage = postsData.data.current_page
        this.postsPerPage = postsData.data.per_page // Update from API if it can change

        this.categories = categoriesData.data
        this.tags = tagsData.data
      } catch (error) {
        console.error('Error fetching initial data:', error)
        this.fetchError =
          error.response?.data?.message ||
          error.message ||
          'Failed to load data.'
      } finally {
        this.loadingPosts = false
      }
    },
    async fetchPostsForPage(pageNumber) {
      this.loadingPosts = true
      this.fetchError = null
      this.currentPage = pageNumber // Update current page for the request
      try {
        const response = await this.$axios.get('/posts', {
          params: {
            page: this.currentPage,
            search: this.search,
            category: this.selectedCategory,
            // tag: this.selectedTag // Add if implementing tag filter
          },
        })
        this.posts = response.data.data
        this.totalPages = response.data.last_page
        this.totalPosts = response.data.total
        // this.currentPage is already set
        this.postsPerPage = response.data.per_page
      } catch (error) {
        console.error('Error fetching posts:', error)
        this.fetchError =
          error.response?.data?.message ||
          error.message ||
          'Failed to load posts.'
      } finally {
        this.loadingPosts = false
      }
    },
    handlePageChange(newPage) {
      this.fetchPostsForPage(newPage)
    },
    handleFilterChange() {
      // When search or category changes, fetch from page 1
      this.fetchPostsForPage(1)
    },
    truncateContent(post) {
      const maxLength = 200
      if (post && post.content && post.content.length > maxLength) {
        return {
          ...post,
          content: post.content.substring(0, maxLength) + '...',
        }
      }
      return post
    },
    goToPost(postId) {
      this.$router.push(`/BlogPostPage/${postId}`)
    },
  },
  // Watch for route query changes if you want to sync filters with URL
  // watch: {
  //   '$route.query': {
  //     handler() {
  //       this.currentPage = parseInt(this.$route.query.page) || 1;
  //       this.search = this.$route.query.search || '';
  //       this.selectedCategory = parseInt(this.$route.query.category) || null;
  //       this.fetchPostsForPage(this.currentPage);
  //     },
  //     immediate: true // Call handler on mount if query params exist
  //   }
  // }
}
</script>

<style scoped></style>
