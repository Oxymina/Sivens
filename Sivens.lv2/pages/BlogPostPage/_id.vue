<!-- BlogPostPage.vue -->
<template>
  <section :class="$vuetify.theme.dark ? 'grey darken-4' : 'grey lighten-4'">
    <v-container>
      <v-row>
        <v-col cols="12" class="py-16">
          <BlogPostContent :post="post" :comments="comments" />
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script>
import BlogPostContent from '~/components/sections/BlogPostContent.vue'

export default {
  components: {
    BlogPostContent,
  },
  data() {
    return {
      post: null,
      comments: [],
    }
  },
  created() {
    this.fetchPostData()
    this.fetchComments()
  },
  methods: {
    fetchPostData() {
      const postId = this.$route.params.id
      import('axios')
        .then((axios) => {
          return axios.default.get(`http://localhost:8000/api/posts/${postId}`)
        })
        .then((response) => {
          this.post = response.data
        })
        .catch((error) => {
          alert('Error fetching post data: ' + error.message)
        })
    },
    fetchComments() {
      const postId = this.$route.params.id
      import('axios')
        .then((axios) => {
          return axios.default.get(
            `http://localhost:8000/api/posts/${postId}/comments`
          )
        })
        .then((response) => {
          this.comments = response.data
        })
        .catch((error) => {
          alert('Error fetching comments: ' + error.message)
        })
    },
  },
}
</script>

<style scoped></style>
