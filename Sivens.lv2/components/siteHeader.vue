<template>
  <div>
    <!-- Navigation Drawer (Update v-if based on Vuex getter) -->
    <v-navigation-drawer v-model="drawer" fixed app temporary>
      <v-list dense>
        <!-- Static Drawer Items -->
        <template v-for="(item, i) in staticItems">
          <v-list-item :key="'drawer-static-' + i" :to="item.to">
            <v-list-item-action>
              <v-icon>{{ item.icon }}</v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title v-text="item.title.toUpperCase()" />
            </v-list-item-content>
          </v-list-item>
        </template>
        <!-- Conditional Drawer Items -->
        <v-list-item v-if="!isAuthenticated" :key="'drawer-login'" to="/login">
          <v-list-item-action><v-icon>mdi-login</v-icon></v-list-item-action>
          <v-list-item-content
            ><v-list-item-title>LOGIN</v-list-item-title></v-list-item-content
          >
        </v-list-item>
        <v-list-item
          v-if="isAuthenticated"
          :key="'drawer-profile'"
          to="/UserProfile"
        >
          <v-list-item-action
            ><v-icon>mdi-account-circle</v-icon></v-list-item-action
          >
          <v-list-item-content
            ><v-list-item-title>PROFILE</v-list-item-title></v-list-item-content
          >
        </v-list-item>
        <v-list-item
          v-if="isAuthenticated"
          :key="'drawer-logout'"
          @click="logoutUser"
        >
          <!-- Changed method name -->
          <v-list-item-action><v-icon>mdi-logout</v-icon></v-list-item-action>
          <v-list-item-content
            ><v-list-item-title>LOGOUT</v-list-item-title></v-list-item-content
          >
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <!-- App Bar (Update v-if based on Vuex getter) -->
    <v-app-bar fixed app hide-on-scroll height="64" elevate-on-scroll>
      <v-app-bar-nav-icon class="hidden-md-and-up" @click="drawer = true" />
      <nuxt-link to="/" class="d-flex"><Logo /></nuxt-link>

      <!-- Centered nav items container -->
      <div class="nav-center hidden-sm-and-down">
        <template v-for="(item, i) in staticItems">
          <v-btn
            :key="'appbar-static-' + i"
            depressed
            tile
            plain
            class="py-8"
            :to="item.to"
          >
            {{ item.title }}
          </v-btn>
        </template>
      </div>

      <!-- Right-aligned buttons -->

      <div class="nav-right hidden-sm-and-down">
        <!-- **** TEMPORARY TESTING BUTTON - START **** -->
        <v-btn
          color="warning"
          outlined
          class="py-8 ml-4"
          to="/temp-testing-links"
          title="Temporary Page for Dev Links"
        >
          <v-icon left small>mdi-flask-outline</v-icon>
          Test Links
        </v-btn>
        <!-- **** TEMPORARY TESTING BUTTON - END **** -->
        <v-btn
          v-if="!isAuthenticated"
          key="appbar-login"
          depressed
          tile
          plain
          class="py-8"
          to="/login"
        >
          Login
        </v-btn>
        <v-btn
          v-if="isAuthenticated"
          key="appbar-profile"
          depressed
          tile
          plain
          class="py-8"
          to="/UserProfile"
        >
          Profile
        </v-btn>
        <v-btn
          v-if="isAuthenticated"
          key="appbar-logout"
          depressed
          tile
          plain
          class="py-8"
          @click="logoutUser"
        >
          Logout
        </v-btn>
        <v-btn icon @click="changeThemeColor">
          <v-icon>{{
            $vuetify.theme.dark
              ? 'mdi-white-balance-sunny'
              : 'mdi-weather-night'
          }}</v-icon>
        </v-btn>
      </div>
    </v-app-bar>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  data() {
    return {
      drawer: false,
      staticItems: [
        // Items always visible
        { icon: 'mdi-folder-home-outline', title: 'Home', to: '/' },
        { icon: 'mdi-post', title: 'Blog', to: '/blog' },
        { icon: 'mdi-account', title: 'About', to: '/about' },
        { icon: 'mdi-contacts', title: 'Contact', to: '/contact' },
      ],
    }
  },
  computed: {
    // Map the getter directly for reactivity
    ...mapGetters('auth', ['isAuthenticated']),
  },
  methods: {
    // Map the logout action
    ...mapActions('auth', ['logout']),

    changeThemeColor() {
      this.$vuetify.theme.dark = !this.$vuetify.theme.dark
    },
    async logoutUser() {
      console.log('Attempting to logout via Vuex action...')
      try {
        await this.logout({ $axios: this.$axios }) // Dispatch the action
        console.log('Logout action completed.')
        // Redirect after successful logout
        if (this.$route.path !== '/') {
          this.$router.push('/')
        } else {
          // Optional: Refresh if already on home to ensure clean state, though Vuex should handle it
          // window.location.reload();
        }
      } catch (error) {
        console.error('Logout failed:', error)
        // Optionally show error to user
      }
    },
  },
}
</script>

<style scoped>
.submenubtn {
  cursor: default;
}
.d-flex {
  text-decoration: none;
}

.nav-center {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 8px;
}

.nav-right {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
