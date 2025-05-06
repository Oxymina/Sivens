<template>
  <!-- Removed the outer <section> as this component represents a single card -->
  <v-card
    outlined
    hover
    class="mx-auto blog-post-card"
    style="height: 100%; display: flex; flex-direction: column"
    @click="goToPost"
  >
    <v-img
      :src="blogpost.post_image || defaultListImage"
      height="200px"
      class="white--text align-end flex-grow-0"
      gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
    >
      <template v-slot:placeholder>
        <v-row class="fill-height ma-0" align="center" justify="center">
          <v-progress-circular
            indeterminate
            color="grey lighten-5"
          ></v-progress-circular>
        </v-row>
      </template>
      <template v-slot:error>
        <v-row
          class="fill-height ma-0"
          align="center"
          justify="center"
          style="background-color: rgba(0, 0, 0, 0.1)"
        >
          <v-icon color="grey" large>mdi-image-off-outline</v-icon>
        </v-row>
      </template>
      <!-- Title positioned within the image -->
      <v-card-title
        class="text-h6 font-weight-bold"
        style="word-break: break-word; line-height: 1.3"
      >
        {{ blogpost.title }}
      </v-card-title>
    </v-img>

    <!-- Author and Category Info -->
    <v-card-subtitle class="pb-2 pt-3">
      <template v-if="blogpost.author">
        <!-- Check if author object exists -->
        By <span class="font-weight-medium">{{ blogpost.author.name }}</span>
      </template>
      <template v-else> By Anonymous </template>
      <template v-if="blogpost.category">
        <!-- Check if category object exists -->
        <span class="mx-1">•</span> In
        <span class="font-weight-medium">{{ blogpost.category.name }}</span>
      </template>
    </v-card-subtitle>

    <!-- Optional: Truncated Content -->
    <v-card-text class="text--primary py-0 flex-grow-1">
      <!-- Allow text to grow to fill space -->
      <p class="mb-0">{{ truncatedContent }}</p>
    </v-card-text>

    <!-- Actions: Likes, Comments, Read More -->
    <v-card-actions class="mt-auto">
      <!-- Push actions to the bottom -->
      <!-- Likes Count -->
      <v-icon small class="mr-1">mdi-heart-outline</v-icon>
      <span class="text-caption mr-3">{{
        blogpost.likes_count != null ? blogpost.likes_count : 0
      }}</span>
      <!-- Use likes_count -->

      <!-- Comment Count (Optional - Needs backend support) -->
      <!-- <v-icon small class="mr-1">mdi-comment-outline</v-icon> -->
      <!-- <span class="text-caption mr-2">{{ blogpost.comments_count || 0 }}</span> -->

      <v-spacer></v-spacer>

      <v-btn text small color="primary" @click="goToPost">
        <!-- Use @click.stop if card is also clickable -->
        Read More
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  props: {
    blogpost: {
      type: Object,
      required: true, // Make prop required if it's essential
      default: () => ({
        // Provide default structure if needed
        id: null,
        title: 'Untitled Post',
        content: '',
        author: null,
        category: null,
        post_image: null,
        likes_count: 0,
        // comments_count: 0,
      }),
    },
  },
  data() {
    return {
      // Default image if post_image is null or fails to load
      defaultListImage:
        'https://media.istockphoto.com/id/1396814518/vector/image-coming-soon-no-photo-no-thumbnail-image-available-vector-illustration.jpg?s=612x612&w=0&k=20&c=hnh2OZgQGhf0b46-J2z7aHbIWwq8HNlSDaNp2wn_iko=', // Choose a relevant default
      contentTruncateLength: 120, // Characters to show in the preview
    }
  },
  computed: {
    /**
     * Safely truncates the content for preview display.
     */
    truncatedContent() {
      const content = this.blogpost?.content || '' // Safely access content
      if (content.length > this.contentTruncateLength) {
        return content.substring(0, this.contentTruncateLength) + '...'
      }
      return content
    },
  },
  methods: {
    /**
     * Emits an event for the parent to handle navigation.
     * Prevents direct routing from a potentially reusable component.
     */
    goToPost() {
      if (this.blogpost && this.blogpost.id) {
        // Emit the ID instead of routing directly
        // The parent page (e.g., Blog page) will listen for this
        this.$emit('view-post', this.blogpost.id)

        // OR if you absolutely want routing here (less reusable):
        // this.$router.push(`/BlogPostPage/${this.blogpost.id}`);
      } else {
        console.warn('Cannot navigate: Blog post or ID is missing.')
      }
    },
  },
}
</script>

<style scoped>
.blog-post-card {
  transition: box-shadow 0.3s ease-in-out; /* Add subtle hover effect */
}
.blog-post-card:hover {
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important; /* Vuetify elevation helper might override */
}
/* Ensure consistent card height if needed, e.g., using display: flex; height: 100%; in parent v-col */
</style>
