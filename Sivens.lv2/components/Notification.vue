<template>
  <v-snackbar
    v-model="snackbar"
    :timeout="-1"
    :dark="$vuetify.theme.dark"
    :light="!$vuetify.theme.dark"
  >
    {{ text }}

    <template v-slot:action="{ attrs }">
      <v-btn color="info" small v-bind="attrs" @click="showMoreInfo">
        More info
      </v-btn>
      <v-btn color="primary ml-3" small v-bind="attrs" @click="acceptCookies">
        Accept
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script>
export default {
  data() {
    return {
      snackbar: true,
      text: `We use cookies to give you the best user experience.`,
    }
  },
  created() {
    if (
      typeof localStorage !== 'undefined' &&
      localStorage.getItem('cookiesAccepted')
    ) {
      this.snackbar = false
    }
  },
  methods: {
    acceptCookies() {
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem('cookiesAccepted', 'true')
      }
      this.snackbar = false
    },
    showMoreInfo() {
      // Implement this function if you have a page or modal for more info
      this.$router.push({ path: '/terms', hash: 'cookies' })
    },
  },
}
</script>

<style scoped>
/* Add any custom styles here if necessary */
</style>
