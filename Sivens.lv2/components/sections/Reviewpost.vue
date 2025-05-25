<template>
  <v-card outlined hover class="mx-auto review-post-card" @click="emitViewPost">
    <v-img
      :src="reviewpost.post_image || defaultListImage"
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
      <v-card-title
        class="text-h6 font-weight-bold"
        style="word-break: break-word; line-height: 1.3"
      >
        {{ reviewpost.title || 'Untitled Post' }}
      </v-card-title>
    </v-img>

    <v-card-subtitle class="pb-2 pt-3">
      <template v-if="reviewpost.author">
        By <span class="font-weight-medium">{{ reviewpost.author.name }}</span>
      </template>
      <template v-else> By Anonymous </template>
      <template v-if="reviewpost.category">
        <span class="mx-1">•</span> In
        <span class="font-weight-medium">{{ reviewpost.category.name }}</span>
      </template>
    </v-card-subtitle>

    <v-card-text class="text--primary py-0 flex-grow-1">
      <p class="mb-0">
        {{ reviewpost.content_preview || 'No preview available.' }}
      </p>
    </v-card-text>

    <v-card-actions class="mt-auto">
      <v-icon small class="mr-1">mdi-heart-outline</v-icon>
      <span class="text-caption mr-3">{{
        reviewpost.likes_count != null ? reviewpost.likes_count : 0
      }}</span>
      <v-spacer></v-spacer>
      <v-btn text small color="primary" @click.stop="emitViewPost">
        Read More
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
export default {
  name: 'SectionsReviewpost',
  props: {
    reviewpost: {
      type: Object,
      required: true,
      default: () => ({
        id: null,
        title: 'Untitled Post',
        content: '[]',
        content_preview: 'No preview available.',
        author: null,
        category: null,
        post_image: null,
        likes_count: 0,
      }),
    },
  },
  data() {
    return {
      defaultListImage:
        'https://media.istockphoto.com/id/1396814518/vector/image-coming-soon-no-photo-no-thumbnail-image-available-vector-illustration.jpg?s=612x612&w=0&k=20&c=hnh2OZgQGhf0b46-J2z7aHbIWwq8HNlSDaNp2wn_iko=',
    }
  },
  methods: {
    emitViewPost() {
      if (this.reviewpost && this.reviewpost.id) {
        this.$emit('view-post', this.reviewpost.id)
      } else {
        console.warn('Cannot navigate: Review post or ID is missing from prop.')
      }
    },
  },
}
</script>

<style scoped>
.review-post-card {
  transition: box-shadow 0.3s ease-in-out;
}
.review-post-card:hover {
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
}
.v-card-text p {
  /* Ensure paragraph within card text has appropriate line clamping or height if needed */
  max-height: 4.5em; /* Approx 3 lines, adjust based on line-height */
  line-height: 1.5em;
  overflow: hidden;
  text-overflow: ellipsis;
  /* For more robust multi-line ellipsis, CSS can be tricky. Consider JS or a library if pure CSS fails. */
  display: -webkit-box;
  -webkit-line-clamp: 3; /* Number of lines to show */
  line-clamp: 3; /* Standard property for compatibility */
  -webkit-box-orient: vertical;
}
</style>
