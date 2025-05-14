<template>
  <div v-if="embedUrl" class="post-video-block my-6 text-center">
    <div
      class="embed-responsive embed-responsive-16by9 mx-auto elevation-2 rounded"
    >
      <iframe
        :src="embedUrl"
        class="embed-responsive-item"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
        :title="blockData.caption || 'Embedded Video'"
      ></iframe>
    </div>
    <figcaption
      v-if="blockData.caption"
      class="text-caption text--secondary mt-3 text-center font-italic"
    >
      {{ blockData.caption }}
    </figcaption>
  </div>
  <div
    v-else-if="blockData && blockData.url"
    class="my-6 text-center text--disabled"
  >
    (Unsupported or invalid video URL: {{ blockData.url }})
  </div>
  <div v-else class="my-6 text-center text--disabled">
    (Video block: No URL provided)
  </div>
</template>

<script>
export default {
  name: 'VideoBlockDisplay',
  props: {
    blockData: {
      type: Object,
      required: true,
      default: () => ({ url: '', caption: '', source: null }), // 'source' determined by editor
    },
  },
  computed: {
    embedUrl() {
      const url = this.blockData?.url
      const source = this.blockData?.source // Source is set by the VideoBlockEditor

      if (!url) return null

      if (source === 'youtube') {
        // Regex to extract video ID from various YouTube URL formats
        const youtubeRegex =
          /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/
        const youtubeMatch = url.match(youtubeRegex)
        if (youtubeMatch && youtubeMatch[1]) {
          return `https://www.youtube.com/embed/${youtubeMatch[1]}`
        }
      } else if (source === 'vimeo') {
        // Regex to extract video ID from various Vimeo URL formats
        const vimeoRegex =
          /(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(?:video\/)?(\d+)/
        const vimeoMatch = url.match(vimeoRegex)
        if (vimeoMatch && vimeoMatch[1]) {
          return `https://player.vimeo.com/video/${vimeoMatch[1]}`
        }
      }
      // Add support for other video platforms or self-hosted <video> tags if needed

      console.warn(
        'VideoBlockDisplay: Could not determine embed URL for:',
        this.blockData
      )
      return null // Return null if URL is not recognized
    },
  },
}
</script>

<style scoped>
.post-video-block .embed-responsive {
  position: relative;
  display: block;
  width: 100%;
  padding: 0;
  overflow: hidden;
  max-width: 720px; /* Or based on your design preferences */
  margin-left: auto;
  margin-right: auto;
  background-color: #000; /* Black background while loading */
}

.post-video-block .embed-responsive::before {
  content: '';
  display: block;
}

/* Common aspect ratios */
.embed-responsive-16by9::before {
  padding-top: 56.25%; /* 16:9 */
}
.embed-responsive-4by3::before {
  padding-top: 75%; /* 4:3 */
}

.post-video-block .embed-responsive .embed-responsive-item,
.post-video-block .embed-responsive iframe {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 0;
}
.post-video-block figcaption {
  max-width: 720px; /* Match video max width */
  margin-left: auto;
  margin-right: auto;
}
</style>
