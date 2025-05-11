<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12" md="8">
        <h1 class="text-h4 my-8 text-center">🚧 Temporary Testing Links 🚧</h1>
        <p class="text-center mb-8">ATCERIES SHITO IZDZEST PLS</p>

        <v-card outlined>
          <v-card-title>Quick Links</v-card-title>
          <v-list dense>
            <v-list-item to="/blogeditortest">
              <!-- Example -->
              <v-list-item-icon
                ><v-icon>mdi-pencil-box-outline</v-icon></v-list-item-icon
              >
              <v-list-item-content
                ><v-list-item-title
                  >Block Editor Test Page</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>

            <v-list-item to="/UserProfile">
              <!-- Example -->
              <v-list-item-icon><v-icon>mdi-account</v-icon></v-list-item-icon>
              <v-list-item-content
                ><v-list-item-title
                  >User Profile Page</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>

            <v-list-item to="/postpost">
              <!-- Example -->
              <v-list-item-icon><v-icon>mdi-account</v-icon></v-list-item-icon>
              <v-list-item-content
                ><v-list-item-title
                  >old post creation page</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>

            <v-list-item to="/blogeditortest">
              <!-- Example -->
              <v-list-item-icon><v-icon>mdi-account</v-icon></v-list-item-icon>
              <v-list-item-content
                ><v-list-item-title
                  >new post creator</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>

            <v-list-item to="/admin">
              <!-- Example -->
              <v-list-item-icon><v-icon>mdi-account</v-icon></v-list-item-icon>
              <v-list-item-content
                ><v-list-item-title
                  >admin</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  middleware: 'admin',
  name: 'TempTestingLinks',
  head() {
    return {
      title: 'Dev Testing Links',
      meta: [
        // Prevent search engines from indexing this test page
        { hid: 'robots', name: 'robots', content: 'noindex, nofollow' },
      ],
    }
  },
}
</script>

<style scoped>
/* Add any specific styles if needed */
</style>
