<template>
  <div
    class="block-editor pa-2"
    style="border: 1px solid #ccc; border-radius: 4px"
  >
    <p
      v-if="!localBlocks || localBlocks.length === 0"
      class="text--secondary text-center pa-4"
    >
      Click "Add Block" to start creating content.
    </p>
    <draggable
      v-model="localBlocks"
      handle=".drag-handle"
      animation="150"
      tag="div"
      class="block-list"
      @end="onDragEnd"
    >
      <transition-group
        v-if="localBlocks && Array.isArray(localBlocks)"
        type="transition"
        name="block-list-anim"
      >
        <!-- Wrapper for each block -->
        <div
          v-for="(block, index) in localBlocks"
          :key="block.id"
          class="block-wrapper my-3 pa-2 grey lighten-4 position-relative"
          @mouseover="hoveredBlock = block.id"
          @mouseleave="hoveredBlock = null"
        >
          <!-- Block Controls -->
          <div
            class="block-controls d-flex align-center"
            :class="{ visible: hoveredBlock === block.id }"
          >
            <v-btn icon small class="drag-handle mr-1" title="Drag to reorder">
              <v-icon small>mdi-drag-vertical</v-icon>
            </v-btn>
            <span class="text-caption text--disabled text-uppercase">{{
              block.type.replace('divider_', '')
            }}</span>
            <!-- Clean up type name -->
            <v-spacer></v-spacer>
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  small
                  v-bind="attrs"
                  @click="removeBlock(index)"
                  v-on="on"
                >
                  <v-icon small color="error">mdi-delete-outline</v-icon>
                </v-btn>
              </template>
              <span>Remove Block</span>
            </v-tooltip>
          </div>

          <!-- Dynamic Component for the specific block editor -->
          <div class="block-content pt-1">
            <component
              :is="getBlockEditorComponent(block.type)"
              :value="block.data"
              :block-id="block.id"
              @input="updateBlockData(index, $event)"
            />
          </div>
        </div>
      </transition-group>
    </draggable>

    <!-- Add New Block Button/Menu -->
    <div class="text-center mt-4">
      <v-menu offset-y>
        <template v-slot:activator="{ on, attrs }">
          <v-btn color="secondary" v-bind="attrs" v-on="on">
            <v-icon left>mdi-plus</v-icon> Add Block
          </v-btn>
        </template>
        <v-list dense>
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
          <v-list-item @click="addBlock('image')">
            <v-list-item-icon
              ><v-icon>mdi-image-outline</v-icon></v-list-item-icon
            >
            <v-list-item-title>Image</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('quote')">
            <v-list-item-icon
              ><v-icon>mdi-format-quote-close</v-icon></v-list-item-icon
            >
            <v-list-item-title>Quote</v-list-item-title>
          </v-list-item>
          <v-divider class="my-1"></v-divider>
          <v-list-item @click="addBlock('list')">
            <v-list-item-icon
              ><v-icon>mdi-format-list-bulleted</v-icon></v-list-item-icon
            >
            <v-list-item-title>List (Bulleted/Numbered)</v-list-item-title>
          </v-list-item>
          <v-list-item @click="addBlock('video')">
            <v-list-item-icon><v-icon>mdi-youtube</v-icon></v-list-item-icon>
            <v-list-item-title>Video (YouTube/Vimeo)</v-list-item-title>
          </v-list-item>
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
        </v-list>
      </v-menu>
    </div>
  </div>
</template>

<script>
import { v4 as uuidv4 } from 'uuid'
import draggable from 'vuedraggable'
// Import all the necessary block editor components
import ParagraphBlockEditor from './ParagraphBlockEditor.vue'
import HeadingBlockEditor from './HeadingBlockEditor.vue'
import ImageBlockEditor from './ImageBlockEditor.vue'
import QuoteBlockEditor from './QuoteBlockEditor.vue'
import ListBlockEditor from './ListBlockEditor.vue'
import VideoBlockEditor from './VideoBlockEditor.vue'
import DividerBlockEditor from './DividerBlockEditor.vue'

export default {
  name: 'BlockEditor', // It's good practice to name components
  components: {
    // Register all block editor components
    draggable,
    ParagraphBlockEditor,
    HeadingBlockEditor,
    ImageBlockEditor,
    QuoteBlockEditor,
    ListBlockEditor,
    VideoBlockEditor,
    DividerBlockEditor,
  },
  props: {
    // The array of blocks passed via v-model from the parent page
    value: {
      type: Array,
      required: true,
      default: () => [],
    },
  },
  data() {
    return {
      // Local copy of the blocks array to work with draggable and avoid direct prop mutation
      localBlocks: [],
      // Tracks which block is currently hovered over to show controls
      hoveredBlock: null,
    }
  },
  watch: {
    // Watch for external changes to the 'value' prop (e.g., when loading data into the editor)
    value: {
      immediate: true, // Run immediately when the component mounts
      deep: true, // Watch for nested changes within the array/objects
      handler(newValue) {
        // Prevent infinite loops if the parent passes the same array reference back after an emit
        if (newValue !== this.localBlocks) {
          // More robust check to ensure actual data changed before updating local state
          if (JSON.stringify(newValue) !== JSON.stringify(this.localBlocks)) {
            // Deep copy the incoming array; ensure it's always an array
            this.localBlocks = JSON.parse(JSON.stringify(newValue || []))
            // console.log('BlockEditor: Prop updated, localBlocks synced.');
          }
        }
      },
    },
    // Watch for internal changes to localBlocks (e.g., adding, removing, reordering)
    localBlocks: {
      deep: true,
      handler(newValue) {
        // Emit the 'input' event ONLY if the change originated locally, not from the parent 'value' prop watch.
        if (JSON.stringify(newValue) !== JSON.stringify(this.value)) {
          // console.log('BlockEditor: Emitting input due to localBlocks change.');
          this.$emit('input', newValue) // Emit event for v-model binding
        }
      },
    },
  },
  methods: {
    /**
     * Determines which editor component to render based on the block's type.
     * @param {string} type - The type of the block (e.g., 'paragraph', 'image').
     * @returns {string|null} The name of the Vue component or null.
     */
    getBlockEditorComponent(type) {
      const componentMap = {
        paragraph: 'ParagraphBlockEditor',
        heading: 'HeadingBlockEditor',
        image: 'ImageBlockEditor',
        quote: 'QuoteBlockEditor',
        list: 'ListBlockEditor',
        video: 'VideoBlockEditor',
        divider_hr: 'DividerBlockEditor',
        divider_dots: 'DividerBlockEditor',
        // Add mappings for any other block types you create
      }
      return componentMap[type] || 'ParagraphBlockEditor' // Fallback to paragraph if type is unknown
    },
    /**
     * Adds a new block of the specified type to the editor.
     * @param {string} type - The type of block to add.
     */
    addBlock(type) {
      const newBlock = {
        id: uuidv4(), // Generate a unique ID for the key and tracking
        type,
        data: this.getDefaultBlockData(type), // Get initial data structure for the type
      }
      this.localBlocks.push(newBlock)
      // The watcher for localBlocks will automatically emit the update
    },
    /**
     * Provides the default data structure for a new block based on its type.
     * @param {string} type - The type of block.
     * @returns {object} The default data object.
     */
    getDefaultBlockData(type) {
      switch (type) {
        case 'paragraph':
          return { text: '' }
        case 'heading':
          return { text: '', level: 2 } // Default to H2
        case 'image':
          return { url: '', caption: '', alt: '' }
        case 'quote':
          return { text: '', attribution: '' }
        case 'list':
          return { style: 'unordered', items: [''] } // Start with one empty item
        case 'video':
          return { url: '', caption: '', source: null }
        case 'divider_hr':
          return { style: 'hr' } // Store style for clarity
        case 'divider_dots':
          return { style: 'dots' }
        default:
          return {} // Empty object for unknown types
      }
    },
    /**
     * Removes a block from the editor at the specified index.
     * @param {number} index - The index of the block to remove.
     */
    removeBlock(index) {
      if (confirm('Are you sure you want to remove this content block?')) {
        this.localBlocks.splice(index, 1)
        // The watcher for localBlocks will emit the update
      }
    },
    /**
     * Updates the data of a specific block. Called when a child block editor emits 'input'.
     * @param {number} index - The index of the block to update.
     * @param {object} newData - The new data object for the block.
     */
    updateBlockData(index, newData) {
      // Ensure reactivity when updating an object within the array
      if (this.localBlocks[index]) {
        // Create a new block object by merging existing id/type with new data
        const updatedBlock = {
          ...this.localBlocks[index], // Keep id, type etc.
          data: newData, // Update the data payload
        }
        this.$set(this.localBlocks, index, updatedBlock) // Use Vue.set for reactivity
      } else {
        console.warn(`Attempted to update non-existent block at index ${index}`)
      }
      // The watcher for localBlocks will emit the update
    },
    /**
     * Handler for the end of a drag-and-drop operation.
     * The localBlocks array is already updated by vuedraggable via v-model.
     * The watcher handles emitting the updated order.
     */
    onDragEnd() {
      // Log or perform actions after reordering if needed
      console.log('Block order updated via drag and drop.')
    },
  },
}
</script>

<style scoped>
.block-editor {
  min-height: 200px; /* Ensure it has some initial height */
  background-color: #fff; /* Default background */
}
.theme--dark .block-editor {
  background-color: #212121; /* Darker background for dark theme */
  border: 1px solid #555 !important;
}

.block-list-anim-move {
  transition: transform 0.3s ease; /* Smooth animation for reordering */
}
/* Fade in/out transition (can be added if needed) */
/* .block-list-anim-enter-active, .block-list-anim-leave-active { transition: opacity 0.5s; } */
/* .block-list-anim-enter, .block-list-anim-leave-to { opacity: 0; } */

.block-wrapper {
  background-color: #f5f5f5;
  border: 1px dashed transparent;
  transition: border-color 0.2s ease-in-out, background-color 0.2s ease-in-out;
  /* Add padding inside wrapper if block content doesn't have its own */
  /* padding: 8px; */
}
.theme--dark .block-wrapper {
  background-color: #3a3a3a;
  border: 1px dashed transparent;
}
.block-wrapper:hover {
  border-color: #bdbdbd;
  /* Slightly darker hover background */
  /* background-color: #eeeeee; */
}
.theme--dark .block-wrapper:hover {
  border-color: #616161;
  /* background-color: #424242; */
}

.block-controls {
  height: 30px;
  padding: 2px 4px;
  opacity: 0; /* Hide by default */
  transition: opacity 0.2s ease-in-out;
  /* Example of placing controls absolutely - adjust positioning as needed */
  /* position: absolute; */
  /* top: -10px; */
  /* right: 5px; */
  /* background-color: rgba(220, 220, 220, 0.9); */
  /* border-radius: 4px; */
  /* box-shadow: 0 1px 3px rgba(0,0,0,0.1); */
}
.block-controls.visible,
.block-wrapper:hover .block-controls {
  opacity: 1; /* Show controls on hover or when focus is within */
}
/* Ensure dark theme controls are visible */
.theme--dark .block-controls {
  background-color: rgba(66, 66, 66, 0.9);
}

.drag-handle {
  cursor: grab;
  color: #757575;
}
.drag-handle:active {
  cursor: grabbing;
}
.theme--dark .drag-handle {
  color: #bdbdbd;
}

.block-content {
  margin-top: 4px; /* Add some space below controls */
}
</style>
