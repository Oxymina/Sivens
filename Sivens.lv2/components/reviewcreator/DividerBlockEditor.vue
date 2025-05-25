<template>
  <div class="divider-editor-selector pa-3">
    <v-select
      v-model="localStyle"
      :items="dividerStyles"
      label="Divider Style"
      outlined
      dense
      hide-details
      @change="updateData"
    ></v-select>

    <!-- Preview of the selected divider -->
    <div class="mt-4 text-center preview-area" :class="themeClass">
      <template v-if="localStyle === 'hr'">
        <v-divider class="my-2"></v-divider>
      </template>
      <template v-else-if="localStyle === 'dots'">
        <div class="my-4 dot-separator">
          <span class="dot"></span><span class="dot"></span
          ><span class="dot"></span>
        </div>
      </template>
      <template v-else-if="localStyle === 'wave'">
        <div class="my-4 wave-separator">〰️〰️〰️</div>
        <!-- Simple text wave -->
      </template>
      <template v-else-if="localStyle === 'short_line_center'">
        <div class="my-4 short-line-center-separator">
          <div></div>
          <!-- CSS will style this -->
        </div>
      </template>
      <template v-else-if="localStyle === 'asterisks'">
        <div class="my-4 asterisks-separator">* * *</div>
      </template>
      <p v-else class="text-caption text--disabled">
        (Select a style to preview)
      </p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    value: { type: Object, required: true, default: () => ({ style: 'hr' }) },
  },
  data() {
    return {
      localStyle: this.value.style || 'hr',
      dividerStyles: [
        { text: 'Horizontal Line (HR)', value: 'hr' },
        { text: 'Three Dots Separator', value: 'dots' },
        { text: 'Asterisks Separator', value: 'asterisks' },
        { text: 'Short Line Center', value: 'short_line_center' },
        { text: 'Wave Separator (Basic)', value: 'wave' },
        // Add more complex SVG/CSS based dividers here
      ],
    }
  },
  computed: {
    themeClass() {
      return this.$vuetify.theme.dark ? 'theme--dark' : 'theme--light'
    },
  },
  watch: {
    value(newValue) {
      if ((newValue?.style || 'hr') !== this.localStyle) {
        this.localStyle = newValue?.style || 'hr'
      }
    },
  },
  methods: {
    updateData() {
      this.$emit('input', { style: this.localStyle })
    },
  },
}
</script>

<style scoped>
.preview-area {
  min-height: 50px;
  padding: 20px;
  border: 1px dashed #ccc;
  border-radius: 4px;
  background-color: rgba(0, 0, 0, 0.02);
}
.theme--dark .preview-area {
  border-color: #555;
  background-color: rgba(255, 255, 255, 0.02);
}

.dot-separator {
  line-height: 1;
  letter-spacing: 0.5em;
  color: #bdbdbd;
}
.dot {
  display: inline-block;
  font-size: 1.5em;
}
.dot::before {
  content: '\2022';
}
.theme--dark .dot {
  color: #757575;
}

.asterisks-separator {
  color: #bdbdbd;
  font-size: 1.5em;
  letter-spacing: 0.3em;
}
.theme--dark .asterisks-separator {
  color: #757575;
}

.wave-separator {
  color: #bdbdbd;
  font-size: 1.5em;
  font-weight: bold;
}
.theme--dark .wave-separator {
  color: #757575;
}

.short-line-center-separator {
  display: flex;
  justify-content: center;
  align-items: center;
}
.short-line-center-separator div {
  width: 60px; /* Width of the short line */
  height: 1px;
  background-color: #bdbdbd;
}
.theme--dark .short-line-center-separator div {
  background-color: #757575;
}
</style>
