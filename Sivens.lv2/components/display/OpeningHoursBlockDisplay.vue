<template>
  <div
    v-if="blockData && blockData.hours && blockData.hours.length > 0"
    class="opening-hours-display-block my-6"
  >
    <v-card outlined>
      <v-list dense class="py-0">
        <template v-for="(item, index) in blockData.hours">
          <v-list-item :key="item.day" :class="{ 'closed-day': !item.isOpen }">
            <v-list-item-content>
              <v-list-item-title class="font-weight-medium">
                {{ item.day }}
              </v-list-item-title>
            </v-list-item-content>
            <v-list-item-action-text class="text-right text-subtitle-2">
              <span v-if="item.isOpen">{{ item.times || 'Open' }}</span>
              <v-chip v-else small color="grey darken-1" dark label
                >Closed</v-chip
              >
            </v-list-item-action-text>
          </v-list-item>
          <v-divider
            v-if="index < blockData.hours.length - 1"
            :key="`divider-${item.day}`"
          ></v-divider>
        </template>
      </v-list>
      <v-card-text
        v-if="blockData.notes && blockData.notes.trim()"
        class="pt-3 text-caption text--secondary"
      >
        <v-icon small left>mdi-information-outline</v-icon>
        {{ blockData.notes }}
      </v-card-text>
    </v-card>
  </div>
  <div v-else class="my-6 text-center text--disabled text-caption">
    (Opening hours not available or data malformed.)
  </div>
</template>

<script>
export default {
  name: 'OpeningHoursBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({
        hours: [
          { day: 'Monday', times: '', isOpen: true },
          { day: 'Tuesday', times: '', isOpen: true },
          { day: 'Wednesday', times: '', isOpen: true },
          { day: 'Thursday', times: '', isOpen: true },
          { day: 'Friday', times: '', isOpen: true },
          { day: 'Saturday', times: '', isOpen: true },
          { day: 'Sunday', times: '', isOpen: false },
        ],
        notes: '',
      }),
    },
  },
}
</script>

<style scoped>
.opening-hours-display-block .v-list-item {
  min-height: 40px;
}
.opening-hours-display-block .v-list-item__action-text {
  min-width: 120px;
  font-feature-settings: 'tnum';
}
</style>
