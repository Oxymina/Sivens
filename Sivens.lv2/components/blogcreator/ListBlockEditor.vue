<template>
  <div class="list-editor">
    <v-radio-group
      v-model="localStyle"
      row
      dense
      hide-details
      class="mb-2"
      @change="updateData"
    >
      <v-radio label="Bulleted List (ul)" value="unordered"></v-radio>
      <v-radio label="Numbered List (ol)" value="ordered"></v-radio>
    </v-radio-group>

    <div
      v-for="(item, index) in localItems"
      :key="index"
      class="list-item d-flex align-center mb-1"
    >
      <v-icon small class="mr-1 handle">{{
        localStyle === 'ordered'
          ? `mdi-numeric-${index + 1}-box-outline`
          : 'mdi-circle-small'
      }}</v-icon>
      <v-text-field
        :value="item"
        placeholder="List item..."
        dense
        solo
        flat
        hide-details
        class="list-item-input body-1 flex-grow-1"
        @input="updateItem(index, $event)"
      ></v-text-field>
      <v-btn
        icon
        small
        color="error"
        title="Remove item"
        class="ml-1"
        @click="removeItem(index)"
      >
        <v-icon small>mdi-close</v-icon>
      </v-btn>
    </div>

    <v-btn text small color="primary" class="mt-1" @click="addItem">
      <v-icon left small>mdi-plus</v-icon> Add Item
    </v-btn>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: Object,
      required: true,
      default: () => ({ style: 'unordered', items: [''] }),
    },
  },
  data() {
    return {
      localStyle: this.value.style || 'unordered',
      localItems: [...(this.value.items || [''])], // Create a copy
    }
  },
  watch: {
    // Update local if parent value changes
    value: {
      deep: true,
      handler(newValue) {
        const newStyle = newValue?.style || 'unordered'
        const newItems = Array.isArray(newValue?.items)
          ? [...newValue.items]
          : ['']
        if (
          newStyle !== this.localStyle ||
          JSON.stringify(newItems) !== JSON.stringify(this.localItems)
        ) {
          this.localStyle = newStyle
          this.localItems = newItems
        }
      },
    },
  },
  methods: {
    updateData() {
      this.$emit('input', { style: this.localStyle, items: this.localItems })
    },
    updateItem(index, text) {
      // Use $set for array reactivity
      this.$set(this.localItems, index, text)
      // Debounce could be useful here if emitting many updates is slow
      this.updateData()
    },
    addItem() {
      this.localItems.push('')
      // Optionally focus the new input field next tick
      this.$nextTick(() => {
        const inputs = this.$el.querySelectorAll('.list-item-input input')
        if (inputs.length > 0) {
          inputs[inputs.length - 1].focus()
        }
      })
      // Don't necessarily need to emit updateData immediately, maybe on blur/change
    },
    removeItem(index) {
      if (this.localItems.length <= 1) {
        // Prevent removing the last item
        this.$set(this.localItems, 0, '') // Just clear it instead
      } else {
        this.localItems.splice(index, 1)
      }
      this.updateData()
    },
  },
}
</script>

<style scoped>
/* Add styles as needed, e.g., targeting .list-item-input */
.handle {
  cursor: default;
  opacity: 0.6;
}
::v-deep .list-item-input input {
  padding-top: 4px !important;
  padding-bottom: 4px !important;
}
</style>
