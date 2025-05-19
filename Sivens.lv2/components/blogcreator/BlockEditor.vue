<template>
  <div
    class="block-editor pa-3"
    :class="$vuetify.theme.dark ? 'grey darken-3' : 'white'"
    style="border: 1px solid; border-radius: 4px"
    :style="borderColor"
  >
    <p
      v-if="!localBlocks || localBlocks.length === 0"
      class="text--secondary text-center pa-4 caption"
    >
      No content yet. Click "Add Block" below to start creating your post.
    </p>

    <draggable
      v-model="localBlocks"
      handle=".drag-handle"
      animation="150"
      tag="div"
      class="block-list"
      ghost-class="block-ghost"
      @end="onDragEnd"
    >
      <transition-group
        v-if="localBlocks && Array.isArray(localBlocks)"
        type="transition"
        name="block-list-anim"
        tag="div"
      >
        <!-- Wrapper for each block -->
        <div
          v-for="(block, index) in localBlocks"
          :key="block.id"
          class="block-wrapper my-3 pa-2 position-relative"
          :class="blockWrapperClass(block.id)"
          @mouseover="hoveredBlockId = block.id"
          @mouseleave="hoveredBlockId = null"
          @focusin="focusedBlockId = block.id"
          @focusout="handleFocusOutBlock(block.id)"
        >
          <!-- Block Controls -->
          <div
            class="block-controls d-flex align-center pa-1"
            :class="{ 'controls-visible': showControls(block.id) }"
          >
            <v-btn
              icon
              small
              class="drag-handle mr-1"
              title="Drag to reorder"
              depressed
              color="grey lighten-2"
              fab
              x-small
            >
              <v-icon small>mdi-drag-vertical</v-icon>
            </v-btn>
            <v-chip small outlined class="text-uppercase block-type-chip" label>
              {{ block.type.replace('divider_', '').replace('_', ' ') }}
            </v-chip>
            <v-spacer></v-spacer>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  color="error lighten-1"
                  v-bind="attrs"
                  depressed
                  fab
                  x-small
                  @click="removeBlock(index)"
                  v-on="on"
                >
                  <v-icon small>mdi-delete-outline</v-icon>
                </v-btn>
              </template>
              <span>Remove Block</span>
            </v-tooltip>
          </div>

          <!-- Dynamic Component for the specific block editor -->
          <div class="block-content pt-2">
            <component
              :is="getBlockEditorComponent(block.type)"
              :value="block.data"
              :block-id="block.id"
              @input="updateBlockData(index, $event)"
              @focus="focusedBlockId = block.id"
            />
          </div>
        </div>
      </transition-group>
    </draggable>

    <!-- Add New Block Button/Menu -->
    <div class="text-center mt-6 mb-2">
      <v-menu top offset-y>
        <template v-slot:activator="{ on, attrs }">
          <v-btn color="primary" large depressed v-bind="attrs" v-on="on">
            <v-icon left>mdi-plus-circle-outline</v-icon> Add Content Block
          </v-btn>
        </template>
        <v-list dense class="add-block-menu">
          <!-- Standard Blocks -->
          <v-list-item @click="addBlock('paragraph')">
            <v-list-item-icon
              ><v-icon>mdi-format-paragraph</v-icon></v-list-item-icon
            >
            <v-list-item-title>Paragraph</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('heading')">
            <v-list-item-icon
              ><v-icon>mdi-format-header-1</v-icon></v-list-item-icon
            >
            <v-list-item-title>Heading</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('list')">
            <v-list-item-icon
              ><v-icon>mdi-format-list-bulleted</v-icon></v-list-item-icon
            >
            <v-list-item-title>List</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('quote')">
            <v-list-item-icon
              ><v-icon>mdi-format-quote-close</v-icon></v-list-item-icon
            >
            <v-list-item-title>Quote</v-list-item-title>
          </v-list-item>

          <!-- Media Blocks -->
          <v-divider class="my-1"></v-divider>
          <v-list-subheader>MEDIA</v-list-subheader>
          <v-list-item @click="addBlock('image')">
            <v-list-item-icon
              ><v-icon>mdi-image-outline</v-icon></v-list-item-icon
            >
            <v-list-item-title>Image</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('video')">
            <v-list-item-icon><v-icon>mdi-youtube</v-icon></v-list-item-icon>
            <v-list-item-title>Video (URL)</v-list-item-title>
          </v-list-item>

          <!-- Food Blog Specific Blocks -->
          <v-divider class="my-1"></v-divider>
          <v-list-subheader>FOOD & LOCATION</v-list-subheader>
          <v-list-item @click="addBlock('star_rating')">
            <v-list-item-icon
              ><v-icon>mdi-star-half-full</v-icon></v-list-item-icon
            >
            <v-list-item-title>Star Rating</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('map_location')">
            <v-list-item-icon
              ><v-icon>mdi-map-marker-outline</v-icon></v-list-item-icon
            >
            <v-list-item-title>Map Location</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('opening_hours')">
            <v-list-item-icon
              ><v-icon>mdi-clock-time-eight-outline</v-icon></v-list-item-icon
            >
            <v-list-item-title>Opening Hours</v-list-item-title>
          </v-list-item>

          <!-- Decorations -->
          <v-divider class="my-1"></v-divider>
          <v-list-subheader>DECORATIONS</v-list-subheader>
          <v-list-item @click="addBlock('divider_hr')">
            <v-list-item-icon><v-icon>mdi-minus</v-icon></v-list-item-icon>
            <v-list-item-title>Horizontal Line</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('divider_dots')">
            <v-list-item-icon
              ><v-icon>mdi-dots-horizontal</v-icon></v-list-item-icon
            >
            <v-list-item-title>Dot Separator</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('divider_asterisks')">
            <v-list-item-icon><v-icon>mdi-asterisk</v-icon></v-list-item-icon>
            <v-list-item-title>Asterisk Separator</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('divider_wave')">
            <v-list-item-icon><v-icon>mdi-wave</v-icon></v-list-item-icon>
            <v-list-item-title>Wave Separator</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('divider_short_line_center')">
            <v-list-item-icon
              ><v-icon>mdi-align-horizontal-center</v-icon></v-list-item-icon
            >
            <v-list-item-title>Short Center Line</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </div>
  </div>
</template>

<script>
import { v4 as uuidv4 } from 'uuid' // npm install uuid
import draggable from 'vuedraggable' // npm install vuedraggable

// Import all the necessary block editor components
// Ensure these paths are correct based on your project structure
// You specified 'components/blogcreator/...'
import ParagraphBlockEditor from './ParagraphBlockEditor.vue'
import HeadingBlockEditor from './HeadingBlockEditor.vue'
import ImageBlockEditor from './ImageBlockEditor.vue'
import QuoteBlockEditor from './QuoteBlockEditor.vue'
import ListBlockEditor from './ListBlockEditor.vue'
import VideoBlockEditor from './VideoBlockEditor.vue'
import DividerBlockEditor from './DividerBlockEditor.vue'
import StarRatingBlockEditor from './StarRatingBlockEditor.vue'
import MapLocationBlockEditor from './MapLocationBlockEditor.vue'
import OpeningHoursBlockEditor from './OpeningHoursBlockEditor.vue'

export default {
  name: 'BlockEditor',
  components: {
    draggable,
    ParagraphBlockEditor,
    HeadingBlockEditor,
    ImageBlockEditor,
    QuoteBlockEditor,
    ListBlockEditor,
    VideoBlockEditor,
    DividerBlockEditor,
    StarRatingBlockEditor,
    MapLocationBlockEditor,
    OpeningHoursBlockEditor,
  },
  props: {
    value: {
      type: Array,
      required: true,
      default: () => [],
    },
  },
  data() {
    return {
      localBlocks: [],
      hoveredBlockId: null,
      focusedBlockId: null,
    }
  },
  computed: {
    borderColor() {
      let color = '#cccccc' // Default fallback border color (light grey)

      if (this.$vuetify && this.$vuetify.theme) {
        if (this.$vuetify.theme.dark) {
          // For dark theme
          color =
            this.$vuetify.theme.themes?.dark?.grey?.darken1 || // Optional chaining
            this.$vuetify.theme.themes?.dark?.['grey-darken-1'] || // Alternative access
            this.$vuetify.theme.themes?.dark?.background || // Fallback to dark background
            '#555555' // Hardcoded dark fallback
        } else {
          // For light theme
          color =
            this.$vuetify.theme.themes?.light?.grey?.lighten1 ||
            this.$vuetify.theme.themes?.light?.['grey-lighten-1'] ||
            this.$vuetify.theme.themes?.light?.surface || // Fallback to light surface
            '#e0e0e0' // Hardcoded light fallback
        }
      }
      return {
        'border-color': `${color} !important`, // Add !important if absolutely necessary
      }
    },
  },
  watch: {
    value: {
      immediate: true,
      deep: true,
      handler(newValue) {
        if (newValue !== this.localBlocks) {
          if (JSON.stringify(newValue) !== JSON.stringify(this.localBlocks)) {
            this.localBlocks = JSON.parse(JSON.stringify(newValue || []))
          }
        }
      },
    },
    localBlocks: {
      deep: true,
      handler(newValue) {
        if (JSON.stringify(newValue) !== JSON.stringify(this.value)) {
          this.$emit('input', newValue)
        }
      },
    },
  },
  methods: {
    getBlockEditorComponent(type) {
      const componentMap = {
        paragraph: 'ParagraphBlockEditor',
        heading: 'HeadingBlockEditor',
        image: 'ImageBlockEditor',
        quote: 'QuoteBlockEditor',
        list: 'ListBlockEditor',
        video: 'VideoBlockEditor',
        star_rating: 'StarRatingBlockEditor',
        map_location: 'MapLocationBlockEditor',
        opening_hours: 'OpeningHoursBlockEditor',
        divider_hr: 'DividerBlockEditor',
        divider_dots: 'DividerBlockEditor',
        divider_asterisks: 'DividerBlockEditor',
        divider_wave: 'DividerBlockEditor',
        divider_short_line_center: 'DividerBlockEditor',
      }
      return componentMap[type] || 'ParagraphBlockEditor' // Fallback
    },
    addBlock(type) {
      const newBlock = {
        id: uuidv4(),
        type,
        data: this.getDefaultBlockData(type),
      }
      this.localBlocks.push(newBlock)
    },
    getDefaultBlockData(type) {
      switch (type) {
        case 'paragraph':
          return { text: '' }
        case 'heading':
          return { text: '', level: 2 }
        case 'image':
          return { url: '', caption: '', alt: '' }
        case 'quote':
          return { text: '', attribution: '' }
        case 'list':
          return { style: 'unordered', items: [''] }
        case 'video':
          return { url: '', caption: '', source: null, type: 'embed' }
        case 'star_rating':
          return { rating: 0, maxRating: 5, label: '' }
        case 'map_location':
          return {
            type: 'embed',
            embedUrl: '',
            address: '',
            staticMapUrl: '',
            staticMapApiKey: '',
          }
        case 'opening_hours':
          return {
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
          }
        case 'divider_hr':
          return { style: 'hr' }
        case 'divider_dots':
          return { style: 'dots' }
        case 'divider_asterisks':
          return { style: 'asterisks' }
        case 'divider_wave':
          return { style: 'wave' }
        case 'divider_short_line_center':
          return { style: 'short_line_center' }
        default:
          return {}
      }
    },
    removeBlock(index) {
      if (
        this.localBlocks[index]?.type === 'heading' &&
        this.localBlocks[index]?.data?.level === 1 &&
        this.localBlocks.filter(
          (b) => b.type === 'heading' && b.data?.level === 1
        ).length <= 1
      ) {
        if (
          !confirm(
            'This is the only H1 heading. Are you sure you want to remove it? Most posts should have one H1.'
          )
        ) {
          return
        }
      } else if (
        !confirm('Are you sure you want to remove this content block?')
      ) {
        return
      }
      this.localBlocks.splice(index, 1)
    },
    updateBlockData(index, newData) {
      if (this.localBlocks[index]) {
        const updatedBlock = {
          ...this.localBlocks[index],
          data: newData,
        }
        this.$set(this.localBlocks, index, updatedBlock)
      } else {
        console.warn(
          `BlockEditor: Attempted to update non-existent block at index ${index}`
        )
      }
    },
    onDragEnd(event) {
      console.log('BlockEditor: Order updated via drag and drop.', event)
      // No need to explicitly emit this.localBlocks, watcher will handle it.
    },
    showControls(blockId) {
      return this.hoveredBlockId === blockId || this.focusedBlockId === blockId
    },
    blockWrapperClass(blockId) {
      const baseClass = this.$vuetify.theme.dark
        ? 'grey darken-3'
        : 'grey lighten-4'
      let elevationClass = ''
      if (this.showControls(blockId)) {
        elevationClass = this.$vuetify.theme.dark
          ? ' elevated-dark'
          : ' elevated-light'
      }
      return `${baseClass}${elevationClass}`
    },
    handleFocusOutBlock() {
      // Use a small timeout to allow click events on controls to register before losing "focused" state
      setTimeout(() => {
        // Check if the new activeElement is still within ANY block's content
        // This is a bit tricky without knowing each block's structure.
        // A simpler approach for now is just not to clear focusedBlockId on blur,
        // as focusin on another block will update it.
        // if (!this.$el.contains(document.activeElement)) {
        //    this.focusedBlockId = null;
        // }
      }, 150)
    },
  },
}
</script>

<style scoped lang="scss">
.block-editor {
  min-height: 300px;
  border-style: solid !important;
  border-width: 1px !important; // Explicitly set border width
  transition: border-color 0.3s ease;
}

/* Dark/Light theme for main editor border - set by computed style now
.theme--dark .block-editor { ... }
.theme--light .block-editor { ... }
*/

.block-list-anim-move,
.block-list-anim-enter-active,
.block-list-anim-leave-active {
  transition: all 0.3s ease;
}
.block-list-anim-enter,
.block-list-anim-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

.block-ghost {
  opacity: 0.4;
  background: #c8ebfb; /* Light blue ghost */
  border: 1px dashed #42a5f5 !important; /* Ensure important for visibility */
  border-radius: 4px;
}
.theme--dark .block-ghost {
  background: #3a4b5a; /* Darker blueish ghost for dark theme */
  border: 1px dashed #5c809b !important;
}

.block-wrapper {
  border: 1px solid transparent; /* Default hidden border */
  border-radius: 4px;
  transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out,
    background-color 0.2s ease-in-out;

  &.theme--dark.grey.darken-3 {
    /* This class combination needs to be very specific */
    background-color: #383838 !important; /* Slightly distinct for dark mode blocks */
  }
  &.theme--light.grey.lighten-4 {
    background-color: #f0f0f0 !important; /* Slightly distinct for light mode blocks */
  }

  &.elevated-light {
    border-color: #bdbdbd !important;
    box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.2),
      0 4px 5px 0 rgba(0, 0, 0, 0.14), 0 1px 10px 0 rgba(0, 0, 0, 0.12) !important;
  }
  &.theme--dark.elevated-dark {
    border-color: #616161 !important;
    box-shadow: 0px 3px 5px -1px rgba(255, 255, 255, 0.05),
      0px 6px 10px 0px rgba(255, 255, 255, 0.03),
      0px 1px 18px 0px rgba(255, 255, 255, 0.02) !important;
  }
}

.block-controls {
  min-height: 32px;
  opacity: 0.1; /* Slightly visible even when not hovered if you prefer */
  transition: opacity 0.2s ease-in-out;
  padding-bottom: 4px;
  border-bottom: 1px solid transparent;
  margin-bottom: 4px;
}
.block-controls.visible, /* This class applied when focused or explicitly made visible */
.block-wrapper:hover .block-controls {
  opacity: 1;
  border-bottom-color: #e0e0e0;
}
.theme--dark .block-controls.visible,
.theme--dark .block-wrapper:hover .block-controls {
  border-bottom-color: #525252;
}

.block-type-chip {
  font-size: 0.65rem !important; /* Smaller chip text */
  height: 18px !important; /* Smaller chip height */
  padding: 0 6px !important;
  pointer-events: none;
  background-color: rgba(0, 0, 0, 0.05) !important;
}
.theme--dark .block-type-chip {
  background-color: rgba(255, 255, 255, 0.08) !important;
}

.drag-handle {
  cursor: grab;
  color: #757575 !important;
}
.drag-handle:active {
  cursor: grabbing;
}
.theme--dark .drag-handle {
  color: #bdbdbd !important;
}

.block-content {
  margin-top: 2px;
}

.add-block-menu .v-list-item__title {
  font-size: 0.9rem;
}
.add-block-menu .v-list-item__icon .v-icon {
  font-size: 1.1rem;
}
</style>
