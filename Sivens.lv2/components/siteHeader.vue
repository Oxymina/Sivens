<template>
  <div>
    <!-- Navigation Drawer -->
    <v-navigation-drawer v-model="drawer" fixed app temporary>
      <v-list dense>
        <!-- Static Drawer Items -->
        <template v-for="(item, i) in staticItems">
          <v-list-item :key="'drawer-static-' + i" :to="item.to">
            <v-list-item-action
              ><v-icon>{{ item.icon }}</v-icon></v-list-item-action
            >
            <v-list-item-content
              ><v-list-item-title v-text="item.title.toUpperCase()"
            /></v-list-item-content>
          </v-list-item>
        </template>

        <!-- TEMP DO NOT FORGET -->
        <v-list-item :key="'drawer-temp-link'" to="/temp-testing-links">
          <v-list-item-action
            ><v-icon color="warning"
              >mdi-flask-outline</v-icon
            ></v-list-item-action
          >
          <v-list-item-content
            ><v-list-item-title
              >TEST LINKS</v-list-item-title
            ></v-list-item-content
          >
        </v-list-item>

        <!-- Conditional Auth Drawer Items -->
        <v-list-item v-if="!isAuthenticated" :key="'drawer-login'" to="/login">
          <v-list-item-action><v-icon>mdi-login</v-icon></v-list-item-action>
          <v-list-item-content
            ><v-list-item-title>LOGIN</v-list-item-title></v-list-item-content
          >
        </v-list-item>

        <!-- Actions for Authenticated Users in Drawer -->
        <template v-if="isAuthenticated">
          <v-list-item :key="'drawer-profile'" to="/UserProfile">
            <v-list-item-action
              ><v-icon>mdi-account-circle</v-icon></v-list-item-action
            >
            <v-list-item-content
              ><v-list-item-title
                >PROFILE</v-list-item-title
              ></v-list-item-content
            >
          </v-list-item>

          <template v-if="isWriterOrAdmin">
            <v-divider :key="'drawer-actions-divider'"></v-divider>
            <v-subheader :key="'drawer-actions-subheader'"
              >CONTENT ACTIONS</v-subheader
            >
            <v-list-item :key="'drawer-create-post'" to="/posts/create">
              <!-- To your public post creation page -->
              <v-list-item-action
                ><v-icon>mdi-plus-box-outline</v-icon></v-list-item-action
              >
              <v-list-item-content
                ><v-list-item-title
                  >CREATE POST</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>
            <v-list-item :key="'drawer-edit-posts'" to="/my-posts/edit-list">
              <!-- Example link to a list of user's posts to edit -->
              <v-list-item-action
                ><v-icon
                  >mdi-pencil-box-multiple-outline</v-icon
                ></v-list-item-action
              >
              <v-list-item-content
                ><v-list-item-title
                  >EDIT MY POSTS</v-list-item-title
                ></v-list-item-content
              >
            </v-list-item>
          </template>

          <v-list-item v-if="isAdmin" :key="'drawer-admin-panel'" to="/admin">
            <v-list-item-action
              ><v-icon color="red lighten-1"
                >mdi-shield-crown</v-icon
              ></v-list-item-action
            >
            <v-list-item-content
              ><v-list-item-title
                >ADMIN PANEL</v-list-item-title
              ></v-list-item-content
            >
          </v-list-item>

          <v-divider :key="'drawer-logout-divider'" class="mt-2"></v-divider>
          <v-list-item :key="'drawer-logout'" @click="logoutUser">
            <v-list-item-action><v-icon>mdi-logout</v-icon></v-list-item-action>
            <v-list-item-content
              ><v-list-item-title
                >LOGOUT</v-list-item-title
              ></v-list-item-content
            >
          </v-list-item>
        </template>
      </v-list>
    </v-navigation-drawer>

    <!-- App Bar -->
    <v-app-bar fixed app hide-on-scroll height="64" elevate-on-scroll>
      <v-app-bar-nav-icon class="d-md-none mr-3" @click="drawer = true" />
      <nuxt-link to="/" class="d-flex align-center mr-2">
        <Logo />
      </nuxt-link>
      <h2 class="font-weight-medium text-no-wrap" style="font-size: 1.4rem">
        Sivēns.lv
      </h2>

      <div class="nav-center d-none d-md-flex">
        <template v-for="(item, i) in staticItems">
          <v-btn
            :key="'appbar-static-' + i"
            depressed
            plain
            class="py-8 mx-1"
            :to="item.to"
            tile="false"
          >
            {{ item.title }}
          </v-btn>
        </template>
      </div>

      <div class="nav-right d-none d-sm-flex align-center">
        <!-- TEMP DELETE LATER -->
        <!-- <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              color="warning"
              icon
              to="/temp-testing-links"
              class="mx-1"
              v-bind="attrs"
              v-on="on"
            >
              <v-icon>mdi-flask-outline</v-icon>
            </v-btn>
          </template>
          <span>Dev Links</span>
        </v-tooltip> -->

        <v-btn
          v-if="!isAuthenticated"
          key="appbar-login"
          depressed
          plain
          class="py-8 mx-1"
          to="/login"
        >
          Login
        </v-btn>
        <v-menu v-if="isAuthenticated && isWriterOrAdmin" offset-y>
          <template v-slot:activator="{ on, attrs }">
            <v-btn text class="py-8 mx-1" v-bind="attrs" v-on="on">
              Actions <v-icon right small>mdi-chevron-down</v-icon>
            </v-btn>
          </template>
          <v-list dense>
            <v-list-item v-if="isWriterOrAdmin" to="/writer/review_creator">
              <v-list-item-icon
                ><v-icon small>mdi-plus-box-outline</v-icon></v-list-item-icon
              >
              <v-list-item-title>Create New Post</v-list-item-title>
            </v-list-item>
            <v-list-item v-if="isWriterOrAdmin" to="/writer/review_editor_list">
              <v-list-item-icon
                ><v-icon small
                  >mdi-pencil-box-multiple-outline</v-icon
                ></v-list-item-icon
              >
              <v-list-item-title>Edit Posts</v-list-item-title>
            </v-list-item>
            <v-divider v-if="isAdmin"></v-divider>
            <v-list-item v-if="isAdmin" to="/admin">
              <v-list-item-icon
                ><v-icon small color="red lighten-1"
                  >mdi-shield-crown</v-icon
                ></v-list-item-icon
              >
              <v-list-item-title>Admin Panel</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
        <!-- **** END NEW ACTIONS DROPDOWN **** -->

        <v-btn
          v-if="isAuthenticated"
          key="appbar-profile"
          depressed
          plain
          class="py-8 mx-1"
          to="/UserProfile"
        >
          Profile
        </v-btn>
        <v-btn
          v-if="isAuthenticated"
          key="appbar-logout"
          depressed
          plain
          class="py-8 mx-1"
          @click="logoutUser"
        >
          Logout
        </v-btn>

        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              icon
              class="ml-1"
              v-bind="attrs"
              @click="changeThemeColor"
              v-on="on"
            >
              <v-icon>{{
                $vuetify.theme.dark
                  ? 'mdi-white-balance-sunny'
                  : 'mdi-weather-night'
              }}</v-icon>
            </v-btn>
          </template>
          <span>Toggle Theme</span>
        </v-tooltip>
      </div>
    </v-app-bar>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'SiteHeader',
  data() {
    return {
      drawer: false,
      staticItems: [
        { icon: 'mdi-folder-home-outline', title: 'Home', to: '/' },
        { icon: 'mdi-post', title: 'Reviews', to: '/reviews' },
        { icon: 'mdi-account', title: 'About', to: '/about' },
      ],
    }
  },
  computed: {
    ...mapGetters('auth', ['isAuthenticated', 'isAdmin', 'isWriter']),
    // Helper computed property
    isWriterOrAdmin() {
      return this.isWriter || this.isAdmin
    },
  },
  methods: {
    // ...mapActions('auth', ['logout']), // Using direct dispatch below

    changeThemeColor() {
      this.$vuetify.theme.dark = !this.$vuetify.theme.dark
    },
    async logoutUser() {
      console.log('Attempting to logout via Vuex action...')
      try {
        await this.$store.dispatch('auth/logout', { $axios: this.$axios })
        console.log('Logout action completed successfully in component.')
        if (this.$route.path !== '/') {
          this.$router.push('/')
        }
      } catch (error) {
        console.error(
          'Logout failed (component):',
          error.message ? error.message : error
        )
      }
    },
  },
}
</script>

<style scoped>
.d-flex {
  text-decoration: none;
}
.nav-center {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
}
.nav-right {
  margin-left: auto;
  display: flex;
  align-items: center;
}
/* Give buttons a bit more explicit spacing if needed */
.nav-center .v-btn,
.nav-right .v-btn,
.nav-right .v-menu {
  /* Target the v-menu to give it similar spacing to buttons */
  margin-left: 4px;
  margin-right: 4px;
}
/* Reduce default padding of a text button to make it align better if it still uses py-8*/
.nav-right .v-btn--text.py-8,
.nav-center .v-btn--text.py-8 {
  padding-top: 0 !important;
  padding-bottom: 0 !important;
  height: 64px; /* Match app bar height */
  display: inline-flex;
  align-items: center;
}
</style>
