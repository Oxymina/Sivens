<template>
  <v-app :class="{ 'dark-theme-background': $vuetify.theme.dark }">
    <v-navigation-drawer
      v-model="drawer"
      app
      clipped
      :mini-variant.sync="miniVariant"
      expand-on-hover
      mobile-breakpoint="960"
      width="260"
      @mouseenter="drawerIsHovered = true"
      @mouseleave="drawerIsHovered = false"
    >
      <v-list-item
        class="px-2 py-3 admin-drawer-header"
        :class="{
          'white--text': !miniVariantOrHovered && drawer,
          'justify-center': miniVariantOrHovered && !drawer,
          'pl-3': miniVariantOrHovered && !drawer,
        }"
        style="min-height: 64px"
      >
        <v-list-item-avatar
          :size="miniVariantOrHovered && !drawer ? 36 : 40"
          class="my-0"
          :class="{ 'mx-auto': miniVariantOrHovered && !drawer }"
        >
          <v-icon
            :color="
              miniVariantOrHovered && !drawer
                ? $vuetify.theme.dark
                  ? 'white'
                  : 'grey darken-2'
                : 'grey darken-2'
            "
            :small="miniVariantOrHovered && !drawer"
          >
            mdi-shield-crown
          </v-icon>
        </v-list-item-avatar>

        <v-list-item-content v-show="!miniVariantOrHoveredIfClosed">
          <v-list-item-title
            class="text-h6 font-weight-bold"
            :class="{
              [$vuetify.theme.dark
                ? 'white--text'
                : 'grey--text text--darken-4']: !miniVariantOrHovered && drawer,
            }"
          >
            Admin Panel
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <v-divider></v-divider>

      <v-list dense nav shaped>
        <v-list-item
          v-for="item in adminNavItems"
          :key="item.title"
          :to="item.to"
          link
          exact
          active-class="primary--text font-weight-medium"
          class="my-1 nav-item"
        >
          <v-list-item-icon class="mr-4">
            <v-icon :title="item.title">{{ item.icon }}</v-icon>
          </v-list-item-icon>
          <v-list-item-content>
            <v-list-item-title>{{ item.title }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar
      app
      clipped-left
      :color="$vuetify.theme.dark ? 'grey darken-3' : 'primary'"
      dark
      elevate-on-scroll
      short
    >
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <v-toolbar-title class="font-weight-bold">
        {{ currentPageTitle }}
      </v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn icon @click="$vuetify.theme.dark = !$vuetify.theme.dark">
        <v-icon>
          {{
            $vuetify.theme.dark
              ? 'mdi-white-balance-sunny'
              : 'mdi-weather-night'
          }}
        </v-icon>
      </v-btn>
      <v-menu bottom left offset-y transition="slide-y-transition">
        <template v-slot:activator="{ on, attrs }">
          <v-btn icon v-bind="attrs" class="ml-1" v-on="on">
            <v-avatar
              :color="$vuetify.theme.dark ? 'grey darken-1' : 'secondary'"
              size="36"
            >
              <span class="white--text text-button">{{ userInitials }}</span>
            </v-avatar>
          </v-btn>
        </template>
        <v-list dense>
          <v-list-item :to="isAuthenticated ? '/UserProfile' : '/login'">
            <v-list-item-icon>
              <v-icon small>mdi-account-circle-outline</v-icon>
            </v-list-item-icon>
            <v-list-item-title>My Profile</v-list-item-title>
          </v-list-item>
          <v-list-item v-if="isAuthenticated" @click="logoutAdmin">
            <v-list-item-icon
              ><v-icon small>mdi-logout</v-icon></v-list-item-icon
            >
            <v-list-item-title>Logout</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <v-main :class="$vuetify.theme.dark ? 'grey darken-3' : 'grey lighten-3'">
      <v-container fluid class="pa-4 pa-md-6">
        <slot></slot>
      </v-container>
    </v-main>

    <v-footer
      app
      padless
      class="pa-2"
      :color="$vuetify.theme.dark ? 'grey darken-3' : 'grey lighten-3'"
    >
      <v-col
        class="text-center text-caption"
        cols="12"
        :class="{
          'white--text': $vuetify.theme.dark, // White text for dark theme footer
          'black--text': !$vuetify.theme.dark, // Black text for light theme footer
        }"
      >
        © {{ new Date().getFullYear() }} Sivēns.lv Admin
      </v-col>
    </v-footer>
  </v-app>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'AdminPageWrapper',
  props: {
    pageTitle: {
      type: String,
      default: 'Admin Dashboard',
    },
  },
  data() {
    return {
      drawer: true,
      miniVariant: false,
      drawerIsHovered: false,
      adminNavItems: [
        {
          title: 'Dashboard',
          icon: 'mdi-view-dashboard-variant-outline',
          to: '/admin',
        },
        {
          title: 'Posts List',
          icon: 'mdi-newspaper-variant-multiple-outline',
          to: '/admin/posts',
        },
        {
          title: 'Categories',
          icon: 'mdi-tag-multiple-outline',
          to: '/admin/posts/categories',
        },
        {
          title: 'Tags',
          icon: 'mdi-label-multiple-outline',
          to: '/admin/posts/tags',
        },
        {
          title: 'Manage Users',
          icon: 'mdi-account-group-outline',
          to: '/admin/users',
        },
        {
          title: 'Contact Messages',
          icon: 'mdi-email-mark-as-unread',
          to: '/admin/messages',
        },
      ],
    }
  },
  computed: {
    ...mapGetters('auth', ['getUser', 'isAuthenticated']),
    currentPageTitle() {
      return this.$route.meta?.adminTitle || this.pageTitle || 'Admin Panel'
    },
    userInitials() {
      const user = this.getUser
      if (user && user.name) {
        const parts = user.name.split(' ')
        if (parts.length > 1 && parts[0] && parts[parts.length - 1]) {
          return (
            parts[0][0].toUpperCase() + parts[parts.length - 1][0].toUpperCase()
          )
        }
        return user.name.substring(0, 2).toUpperCase()
      }
      return 'AD'
    },
    isTrulyMini() {
      return this.miniVariant && !this.drawerIsHovered && !this.drawer
    },
    showAppendText() {
      return !this.miniVariant || this.drawerIsHovered || this.drawer
    },
    miniVariantOrHovered() {
      return this.miniVariant && !this.drawerIsHovered
    },
    miniVariantOrHoveredIfClosed() {
      return this.miniVariant && !this.drawer && !this.drawerIsHovered
    },
  },
  mounted() {
    this.drawer = !!this.$vuetify.breakpoint.mdAndUp
  },
  methods: {
    async logoutAdmin() {
      try {
        await this.$store.dispatch('auth/logout', { $axios: this.$axios })
        this.$router.push('/login')
      } catch (error) {
        console.error('Admin logout failed:', error)
      }
    },
  },
}
</script>

<style scoped>
.dark-theme-background .v-main {
  background-color: var(--v-background-base, #1e1e1e) !important;
}
.v-main.grey.lighten-3 {
  background-color: #f5f5f5 !important;
}

.admin-drawer-header.justify-center .v-list-item__avatar {
  margin-left: auto !important;
  margin-right: auto !important;
  flex: 0 0 auto;
}
.admin-drawer-header:not(.justify-center) .v-list-item__avatar {
  margin-right: 12px;
}

.v-list-item__avatar,
.v-list-item__content {
  transition: opacity 0.2s ease-in-out, margin 0.2s ease-in-out,
    flex 0.2s ease-in-out;
}

.nav-item:not(.v-list-item--active):hover .v-list-item__icon .v-icon,
.nav-item:not(.v-list-item--active):hover .v-list-item__title {
  color: var(--v-primary-base) !important;
}
.v-navigation-drawer .v-list-item--active .v-list-item__icon .v-icon,
.v-navigation-drawer .v-list-item--active .v-list-item__title {
  color: var(--v-primary-base) !important;
}
.v-list-item--active {
  border-left: 4px solid var(--v-primary-base);
}
</style>
