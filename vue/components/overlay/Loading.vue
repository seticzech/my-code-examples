<template>
  <div :class="{ 'loading-cover-fullscreen': fullScreen, 'loading-cover': !fullScreen }" v-if="active">
    <div class="loading-indicator" role="dialog" aria-modal="true">
      <div class="text-center">
        <b-spinner variant="dark"></b-spinner>
        <h5 class="mt-2">{{ $t(message) }}</h5>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'OverlayLoading',
    props: {
      active: {
        type: Boolean,
        required: true,
        default: false
      },
      fullScreen: {
        type: Boolean,
        required: false,
        default: false
      },
      message: {
        type: String,
        required: false,
        default: ''
      }
    }
  }
</script>

<style scoped>
  .loading-indicator {
    display: flex;
    overflow: hidden;
    flex-direction: column;
  }
  .loading-cover {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.5);
    z-index: 5000;
    display: flex;
    justify-content: center;
    align-items: center;
    /*opacity: 0.2;*/
  }
  .loading-cover-fullscreen {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.2);
    z-index: 5000;
    display: flex;
    justify-content: center;
    align-items: center;
    /*opacity: 0.2;*/
  }
</style>
