<template>
  <div class="opening-hours-editor pa-2">
    <v-card outlined>
      <v-card-text>
        <div
          v-for="(day, index) in localHours"
          :key="index"
          class="day-entry d-flex align-center mb-2"
        >
          <v-checkbox
            v-model="day.isOpen"
            hide-details
            dense
            class="mr-2 flex-shrink-0 mt-0 pt-0"
            @change="updateData"
          ></v-checkbox>
          <span class="font-weight-medium mr-3" style="width: 90px"
            >{{ day.day }}:</span
          >
          <v-text-field
            v-if="day.isOpen"
            v-model="day.times"
            placeholder="e.g., 9:00 AM - 5:00 PM or 24 Hours"
            dense
            outlined
            hide-details
            class="flex-grow-1"
            @input="updateData"
          ></v-text-field>
          <v-chip
            v-else
            small
            dark
            color="grey darken-1"
            class="ml-2 flex-grow-1 justify-center"
            >Closed</v-chip
          >
        </div>
        <v-textarea
          v-model="localNotes"
          label="Optional Notes (e.g., Kitchen closes early)"
          outlined
          dense
          rows="2"
          auto-grow
          hide-details
          class="mt-4"
          @input="updateData"
        ></v-textarea>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Object,
      required: true,
      default: () => ({
        hours: [
          { day: 'Monday', times: '10:00 AM - 9:00 PM', isOpen: true },
          { day: 'Tuesday', times: '10:00 AM - 9:00 PM', isOpen: true },
          { day: 'Wednesday', times: '10:00 AM - 9:00 PM', isOpen: true },
          { day: 'Thursday', times: '10:00 AM - 9:00 PM', isOpen: true },
          { day: 'Friday', times: '10:00 AM - 10:00 PM', isOpen: true },
          { day: 'Saturday', times: '11:00 AM - 10:00 PM', isOpen: true },
          { day: 'Sunday', times: '11:00 AM - 8:00 PM', isOpen: false },
        ],
        notes: '',
      }),
    },
  },
  data() {
    return {
      // Deep copy for local editing
      localHours: JSON.parse(
        JSON.stringify(this.value.hours || this.defaultHours())
      ),
      localNotes: this.value.notes || '',
    }
  },
  watch: {
    value: {
      deep: true,
      handler(newValue) {
        this.localHours = JSON.parse(
          JSON.stringify(newValue?.hours || this.defaultHours())
        )
        this.localNotes = newValue?.notes || ''
      },
    },
  },
  methods: {
    defaultHours() {
      return [
        { day: 'Monday', times: '', isOpen: true },
        { day: 'Tuesday', times: '', isOpen: true },
        { day: 'Wednesday', times: '', isOpen: true },
        { day: 'Thursday', times: '', isOpen: true },
        { day: 'Friday', times: '', isOpen: true },
        { day: 'Saturday', times: '', isOpen: true },
        { day: 'Sunday', times: '', isOpen: false },
      ]
    },
    updateData() {
      this.$emit('input', {
        hours: this.localHours,
        notes: this.localNotes,
      })
    },
  },
}
</script>
