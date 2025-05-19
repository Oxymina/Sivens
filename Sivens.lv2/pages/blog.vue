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
          <!-- ... Loading and Error States ... -->
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
              class="d-flex"
            >
              <!-- ***** CORRECTION HERE ***** -->
              <SectionsBlogpost
                :blogpost="truncateContent(post)"
                class="flex-grow-1"
                @view-post="handleGoToPost"
              />
              <!-- ***** END CORRECTION ***** -->
            </v-col>
          </v-row>
          <!-- ... Pagination ... -->
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
          <!-- ... Sidebar (search, categories, tags) ... -->
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
// Assuming SectionsHeroAlt is globally registered or auto-imported
// Assuming SectionsBlogpost is globally registered or auto-imported

export default {
  name: 'BlogListPage',
  // components: { SectionsHeroAlt, SectionsBlogpost }, // If not auto-imported
  async fetch() {
    await this.fetchInitialData()
  },
  data() {
    return {
      heroAlt: [
        { src: 'pexels-andrea-piacquadio-3884440.jpg', heading: ' Blog ' },
      ],
      posts: [],
      categories: [],
      tags: [],
      selectedCategory: null,
      selectedTag: null,
      search: '',
      currentPage: 1,
      totalPages: 0,
      totalPosts: 0,
      postsPerPage: 10,
      loadingPosts: true,
      fetchError: null,
      debouncedFetchPosts: null,
    }
  },
  created() {
    this.debouncedFetchPosts = _.debounce(this.handleFilterChange, 500)
  },
  methods: {
    async fetchInitialData() {
      this.loadingPosts = true
      this.fetchError = null
      try {
        const params = {
          page: this.currentPage,
          search: this.search,
          category: this.selectedCategory,
          tag: this.selectedTag,
        }
        Object.keys(params).forEach(
          (key) =>
            (params[key] == null || params[key] === '') && delete params[key]
        )

        const [postsData, categoriesData, tagsData] = await Promise.all([
          this.$axios.get('/posts', { params }),
          this.$axios.get('/categories'),
          this.$axios.get('/tags'),
        ])

        this.posts = postsData.data.data || []
        this.totalPages = postsData.data.last_page || 0
        this.totalPosts = postsData.data.total || 0
        this.currentPage = postsData.data.current_page || 1
        this.postsPerPage = postsData.data.per_page || 10

        this.categories = categoriesData.data.data || categoriesData.data || []
        this.tags = tagsData.data.data || tagsData.data || []
      } catch (error) {
        console.error(
          'Error fetching initial data for blog list:',
          error.response?.data || error
        )
        this.fetchError =
          error.response?.data?.message ||
          error.message ||
          'Failed to load blog content.'
        this.posts = []
        this.totalPages = 0
      } finally {
        this.loadingPosts = false
      }
    },
    async fetchPostsForPage(pageNumber) {
      this.loadingPosts = true
      this.fetchError = null
      this.currentPage = pageNumber
      try {
        const params = {
          page: this.currentPage,
          search: this.search,
          category: this.selectedCategory,
          tag: this.selectedTag,
        }
        Object.keys(params).forEach(
          (key) =>
            (params[key] == null || params[key] === '') && delete params[key]
        )

        const response = await this.$axios.get('/posts', { params })
        this.posts = response.data.data || []
        this.totalPages = response.data.last_page || 0
        this.totalPosts = response.data.total || 0
        this.postsPerPage = response.data.per_page || 10
      } catch (error) {
        console.error(
          'Error fetching posts for page:',
          error.response?.data || error
        )
        this.fetchError =
          error.response?.data?.message ||
          error.message ||
          'Failed to load posts.'
        this.posts = []
        this.totalPages = 0
      } finally {
        this.loadingPosts = false
      }
    },
    handlePageChange(newPage) {
      this.fetchPostsForPage(newPage)
    },
    handleFilterChange() {
      this.fetchPostsForPage(1)
    },
    truncateContent(post) {
      const maxLength = 150
      let previewText = 'No preview available.'
      if (post && post.content) {
        let blocks = []
        try {
          if (typeof post.content === 'string') {
            blocks = JSON.parse(post.content)
          } else if (Array.isArray(post.content)) {
            blocks = post.content
          }
        } catch (e) {
          if (typeof post.content === 'string') previewText = post.content
        }
        if (Array.isArray(blocks) && blocks.length > 0) {
          const firstParagraph = blocks.find(
            (block) =>
              block &&
              block.type === 'paragraph' &&
              block.data &&
              typeof block.data.text === 'string' &&
              block.data.text.trim() !== ''
          )
          if (firstParagraph) {
            previewText = firstParagraph.data.text
          } else {
            const firstHeading = blocks.find(
              (block) =>
                block &&
                block.type === 'heading' &&
                block.data &&
                typeof block.data.text === 'string' &&
                block.data.text.trim() !== ''
            )
            if (firstHeading) {
              previewText = firstHeading.data.text
            } else {
              const firstTextualBlock = blocks.find(
                (b) =>
                  b &&
                  b.data &&
                  typeof b.data.text === 'string' &&
                  b.data.text.trim() !== ''
              )
              if (firstTextualBlock) previewText = firstTextualBlock.data.text
            }
          }
        } else if (
          typeof post.content === 'string' &&
          !post.content.startsWith('[{"id":')
        ) {
          previewText = post.content
        }
      }
      if (previewText.length > maxLength) {
        return {
          ...post,
          content_preview: previewText.substring(0, maxLength) + '...',
        }
      }
      return { ...post, content_preview: previewText }
    },
    /**
     * This method is called when the SectionsBlogpost child component emits 'view-post'.
     */
    handleGoToPost(postId) {
      if (postId) {
        this.$router.push(`/BlogPostPage/${postId}`)
      } else {
        console.warn('handleGoToPost: postId is missing.')
      }
    },
  },
  head() {
    return {
      title: 'Reviews',
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: 'Read the latest reviews from Sivēns.lv',
        },
      ],
    }
  },
}
</script>

<style scoped>
.v-card {
  display: flex;
  flex-direction: column;
  height: 100%;
}
.v-card__text {
  flex-grow: 1;
}
.v-card__actions {
  margin-top: auto;
}
</style>
