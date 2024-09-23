<template>
  <v-container>
    <v-row>
      <v-col cols="12" md="4">
        <v-card>
          <v-card-title>
            <span class="headline">User Profile</span>
          </v-card-title>
          <v-card-text>
            <v-list-item>
              <v-list-item-avatar>
                <v-img :src="profilePictureUrl" />
              </v-list-item-avatar>
              <v-list-item-content>
                <v-list-item-title>{{ user.name }}</v-list-item-title>
                <v-list-item-subtitle>{{ user.email }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
            <v-file-input
              label="Update Profile Picture"
              accept="image/*"
              @change="onFileChange"
            ></v-file-input>
            <v-btn color="primary" @click="updateProfilePicture">
              Update Picture
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="8">
        <v-card>
          <v-card-title>
            <span class="headline">My Posts</span>
          </v-card-title>
          <v-card-text>
            <v-list>
              <v-list-item
                v-for="post in posts"
                :key="post.id"
                @click="goToPost(post.id)"
              >
                <v-list-item-content>
                  <v-list-item-title>{{ post.title }}</v-list-item-title>
                  <v-list-item-subtitle>
                    {{ post.content.substring(0, 100) }}...
                  </v-list-item-subtitle>
                </v-list-item-content>
                <v-list-item-action>
                  <v-btn icon @click.stop="editPost(post.id)">
                    <v-icon>mdi-pencil</v-icon>
                  </v-btn>
                  <v-btn icon @click.stop="deletePost(post.id)">
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </v-list-item-action>
              </v-list-item>
            </v-list>
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
      user: {
        name: '',
        email: '',
      },
      posts: [],
      userPFP: null,
      profilePictureUrl: '',
    }
  },
  created() {
    this.fetchUserProfile()
    this.fetchUserPosts()
  },
  methods: {
    async fetchUserProfile() {
      try {
        const { default: axios } = await import('axios')
        const response = await axios.get('http://localhost:8000/api/users', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        this.user = response.data
        this.profilePictureUrl = response.data.userPFP
          ? `http://localhost:8000/storage/${response.data.userPFP}`
          : ''
      } catch (error) {
        alert('Error fetching user profile: ' + error.message)
      }
    },
    async fetchUserPosts() {
      try {
        const { default: axios } = await import('axios')
        const response = await axios.get('http://localhost:8000/api/posts', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        this.posts = response.data
      } catch (error) {
        alert('Error fetching posts: ' + error.message)
      }
    },
    onFileChange(event) {
      this.userPFP = event.target.files[0]
    },
    async updateProfilePicture() {
      if (!this.userPFP) return
      try {
        const { default: axios } = await import('axios')
        const formData = new FormData()
        formData.append('userPFP', this.userPFP)
        await axios.post('http://localhost:8000/api/users/update', formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data',
          },
        })
        this.fetchUserProfile()
        alert('Profile picture updated successfully!')
      } catch (error) {
        alert('Error updating profile picture: ' + error.message)
      }
    },
    goToPost(postId) {
      this.$router.push(`/BlogPostPage/${postId}`)
    },
    editPost(postId) {
      this.$router.push({ name: 'editpost', params: { id: postId } })
    },
    async deletePost(postId) {
      try {
        const { default: axios } = await import('axios')
        await axios.delete(`http://localhost:8000/api/posts/${postId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        this.fetchUserPosts()
        alert('Post deleted successfully!')
      } catch (error) {
        alert('Error deleting post: ' + error.message)
      }
    },
  },
}
</script>

<style scoped></style>
