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
          <v-row>
            <v-col
              v-for="post in paginatedPosts"
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
          <div class="text-center">
            <v-container>
              <v-row justify="center">
                <v-col cols="8">
                  <v-pagination
                    v-model="page"
                    circle
                    class="my-4"
                    :length="totalPages"
                    @input="fetchPosts"
                  ></v-pagination>
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
              @input="fetchPosts"
            ></v-text-field>
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
                  @change="fetchPosts"
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
                <v-chip-group column>
                  <v-chip v-for="tag in tags" :key="tag.id">
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
export default {
  data() {
    return {
      heroAlt: [
        {
          src: 'pexels-andrea-piacquadio-3884440.jpg',
          heading: ' Blog ',
        },
      ],
      posts: [],
      page: 1,
      postsPerPage: 8,
      search: '',
      selectedCategory: null,
      categories: [],
      tags: [],
    }
  },
  computed: {
    totalPages() {
      return Math.ceil(this.posts.length / this.postsPerPage)
    },
    paginatedPosts() {
      const start = (this.page - 1) * this.postsPerPage
      const end = start + this.postsPerPage
      return this.posts.slice(start, end)
    },
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      Promise.all([
        this.fetchPosts(),
        this.fetchCategories(),
        this.fetchTags(),
      ]).catch((error) => {
        alert('Error fetching data: ' + error.message)
      })
    },
    fetchPosts() {
      return import('axios')
        .then((axios) => {
          return axios.default.get('http://localhost:8000/api/posts', {
            params: {
              search: this.search,
              category: this.selectedCategory,
            },
          })
        })
        .then((response) => {
          this.posts = response.data
        })
    },
    fetchCategories() {
      return import('axios')
        .then((axios) => {
          return axios.default.get('http://localhost:8000/api/categories')
        })
        .then((response) => {
          this.categories = response.data
        })
    },
    fetchTags() {
      return import('axios')
        .then((axios) => {
          return axios.default.get('http://localhost:8000/api/tags')
        })
        .then((response) => {
          this.tags = response.data
        })
    },
    truncateContent(post) {
      const maxLength = 200
      if (post.content.length > maxLength) {
        return {
          ...post,
          content: post.content.substring(0, maxLength) + '...',
        }
      }
      return post
    },
    goToPost(postId) {
      this.$router.push({ name: 'BlogPostPage', params: { id: postId } })
    },
  },
}
</script>

<style scoped></style>
