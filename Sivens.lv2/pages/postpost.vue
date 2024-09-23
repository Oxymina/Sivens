<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12" sm="8" md="6">
        <v-card>
          <v-card-title>
            <span class="headline">Create a New Post</span>
          </v-card-title>
          <v-card-text>
            <v-form ref="form" @submit.prevent="submitPost">
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
              <v-btn type="submit" color="primary" block>Submit</v-btn>
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
    this.fetchCategories()
  },
  methods: {
    fetchCategories() {
      import('axios').then((axios) => {
        axios.default
          .get('http://localhost:8000/api/categories')
          .then((response) => {
            this.categories = response.data
          })
          .catch((error) => {
            alert('Error fetching categories: ' + error.message)
          })
      })
    },
    submitPost() {
      const postData = {
        title: this.title,
        content: this.content,
        post_image: this.postImage,
        category_id: this.selectedCategory,
        author_id: 1, // This should be dynamically set based on the logged-in user
      }

      import('axios').then((axios) => {
        axios.default
          .post('http://localhost:8000/api/posts', postData)
          .then((response) => {
            alert('Post created successfully!')
            this.$router.push('/blog') // Redirect to the blog page after successful post creation
          })
          .catch((error) => {
            alert('Error creating post: ' + error.message)
          })
      })
    },
  },
}
</script>

<style scoped>
.headline {
  font-size: 1.5rem;
  font-weight: 500;
}
</style>
