<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12" sm="8" md="6">
        <v-card>
          <v-card-title>
            <span class="headline">Edit Post</span>
          </v-card-title>
          <v-card-text>
            <v-form ref="form" @submit.prevent="updatePost">
              <v-text-field
                v-model="title"
                label="Title"
                outlined
                required
              ></v-text-field>
              <v-textarea
                v-model="content"
                label="Content"
                outlined
                required
              ></v-textarea>
              <v-text-field
                v-model="postImage"
                label="Image URL"
                outlined
              ></v-text-field>
              <v-select
                v-model="selectedCategory"
                :items="categories"
                item-text="name"
                item-value="id"
                label="Category"
                outlined
                required
              ></v-select>
              <v-btn type="submit" color="primary" block>Update Post</v-btn>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      title: '',
      content: '',
      postImage: '',
      selectedCategory: null,
      categories: [],
    }
  },
  created() {
    this.fetchPostData()
    this.fetchCategories()
  },
  methods: {
    fetchPostData() {
      const postId = this.$route.params.id
      import('axios')
        .then((axios) => {
          return axios.default.get(`http://localhost:8000/api/posts/${postId}`)
        })
        .then((response) => {
          const post = response.data
          this.title = post.title
          this.content = post.content
          this.postImage = post.post_image
          this.selectedCategory = post.category_id
        })
        .catch((error) => {
          alert('Error fetching post data: ' + error.message)
        })
    },
    fetchCategories() {
      import('axios')
        .then((axios) => {
          return axios.default.get('http://localhost:8000/api/categories')
        })
        .then((response) => {
          this.categories = response.data
        })
        .catch((error) => {
          alert('Error fetching categories: ' + error.message)
        })
    },
    updatePost() {
      const postId = this.$route.params.id
      const postData = {
        title: this.title,
        content: this.content,
        post_image: this.postImage,
        category_id: this.selectedCategory,
        author_id: 1,
      }

      import('axios')
        .then((axios) => {
          return axios.default.put(
            `http://localhost:8000/api/posts/${postId}`,
            postData
          )
        })
        .then(() => {
          alert('Post updated successfully!')
          this.$router.push('/UserProfile') // Redirect to the profile page after successful post update
        })
        .catch((error) => {
          alert('Error updating post: ' + error.message)
        })
    },
  },
}
</script>

<style scoped></style>
