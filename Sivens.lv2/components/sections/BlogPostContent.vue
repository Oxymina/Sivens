<!-- eslint-disable vue/no-v-html -->
<!-- BlogPostContent.vue -->
<template>
  <v-card max-width="800" class="mx-auto my-5" elevation="2">
    <v-img :src="post?.post_image" class="white--text align-end" height="400px">
      <template v-slot:placeholder>
        <v-row class="fill-height ma-0" align="center" justify="center">
          <v-progress-circular
            indeterminate
            color="primary"
          ></v-progress-circular>
        </v-row>
      </template>
      <template v-slot:error>
        <v-row class="fill-height ma-0" align="center" justify="center">
          <v-icon color="error" large>mdi-alert</v-icon>
          <span>Image failed to load</span>
        </v-row>
      </template>
    </v-img>

    <v-card-title class="headline">{{ post?.title }}</v-card-title>
    <v-card-subtitle class="pb-0">
      <v-btn text small color="primary" class="px-0">{{ post?.author }}</v-btn>
      <v-btn text small disabled class="px-0">{{ post?.publishedOn }}</v-btn>
    </v-card-subtitle>
    <v-card-text
      class="text--primary mt-3"
      style="line-height: 1.8rem"
      v-html="sanitizedContent"
    ></v-card-text>

    <v-card-actions>
      <v-btn icon color="orange">
        <v-icon>mdi-heart</v-icon>
      </v-btn>
      <span class="text--disabled">{{ post?.likes }}</span>
      <v-spacer></v-spacer>
      <v-btn icon color="primary">
        <v-icon>mdi-share-variant</v-icon>
      </v-btn>
      <span class="text--disabled">{{ post?.shares }}</span>
    </v-card-actions>

    <v-divider></v-divider>

    <v-card-title class="headline"
      >Comments ({{ comments.length }})</v-card-title
    >
    <v-card-text>
      <v-list two-line>
        <v-list-item v-for="comment in comments" :key="comment.id">
          <v-list-item-content>
            <v-list-item-title v-text="comment.author"></v-list-item-title>
            <v-list-item-subtitle
              v-text="comment.content"
            ></v-list-item-subtitle>
          </v-list-item-content>
        </v-list-item>
      </v-list>
      <v-text-field
        v-model="newComment"
        label="Add a comment"
        outlined
        dense
      ></v-text-field>
      <v-btn color="primary" @click="addComment">Submit</v-btn>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  props: {
    post: {
      type: Object,
      default: () => ({}),
    },
    comments: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      newComment: '',
      sanitizedContent: '',
    }
  },
  watch: {
    post: {
      immediate: true,
      handler(newValue) {
        if (newValue && newValue.content) {
          this.sanitizeContent(newValue.content)
        }
      },
    },
  },
  methods: {
    sanitizeContent(content) {
      import('dompurify')
        .then((DOMPurify) => {
          this.sanitizedContent = DOMPurify.sanitize(content)
        })
        .catch((error) => {
          alert('Error loading DOMPurify:', error)
        })
    },
    addComment() {
      if (this.newComment.trim() === '') {
        alert('Comment cannot be empty')
        return
      }
      const newComment = {
        id: this.comments.length + 1, // This assumes IDs are simply incremented
        author: 'Current User', // This should be replaced with actual user data if available
        content: this.newComment,
        postId: this.post.id,
      }
      this.comments.push(newComment) // This should ideally make a POST request to the server
      this.newComment = ''
    },
  },
}
</script>

<style scoped>
.v-card-title.headline {
  font-size: 1.5rem;
  font-weight: 500;
}
.v-card-text {
  font-size: 1rem;
  line-height: 1.8rem;
}
</style>
