<template>
  <section
    :class="$vuetify.theme.dark ? 'grey darken-4' : 'grey lighten-4'"
    class="py-16"
  >
    <v-card max-width="450" class="mx-auto" elevation="1" @click="goToPost">
      <v-img
        :src="blogpost.post_image"
        class="white--text align-end"
        height="200px"
      >
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
      <v-card-subtitle class="pb-0">
        <v-btn href="#" text small color="primary" class="px-0">
          {{ blogpost.author }}
        </v-btn>
        <v-btn text small disabled class="px-0">
          {{ blogpost.publishedOn }}
        </v-btn>
      </v-card-subtitle>
      <v-card-text
        class="title font-weight-bold mt-3 pb-0 text--primary"
        style="line-height: 1.8rem"
      >
        {{ blogpost.title }}
      </v-card-text>
      <v-card-text class="text--primary">
        {{ blogpost.content }}
        <v-btn
          v-if="isTruncated"
          small
          text
          color="primary"
          @click.stop="readMore(blogpost.id)"
        >
          Read More
        </v-btn>
      </v-card-text>
      <v-card-actions>
        <v-btn icon color="yellow darken-1">
          <v-icon>mdi-comment</v-icon>
        </v-btn>
        <span class="text--disabled">{{ blogpost.likes }}</span>
        <v-spacer></v-spacer>
        <v-btn icon color="orange">
          <v-icon>mdi-heart</v-icon>
        </v-btn>
        <span class="text--disabled mr-2">{{ blogpost.comments }}</span>
        <v-btn icon color="primary">
          <v-icon>mdi-share-variant</v-icon>
        </v-btn>
        <span class="text--disabled">{{ blogpost.shares }}</span>
        <span class="mr-4"></span>
      </v-card-actions>
    </v-card>
  </section>
</template>

<script>
export default {
  props: {
    blogpost: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    isTruncated() {
      return this.blogpost.content.endsWith('...')
    },
  },
  methods: {
    readMore(postId) {
      this.$router.push({ name: 'BlogPostPage', params: { id: postId } })
    },
    goToPost() {
      this.$emit('click')
    },
  },
}
</script>
