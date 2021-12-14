<template>
  <div class="app">
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: 'app',
  methods: {
    logout () {

    }
  }
}
</script>

<style lang="scss">
  // CoreUI Icons Set
  @import '../node_modules/@coreui/icons/css/coreui-icons.min.css';
  /* Import Font Awesome Icons Set */
  $fa-font-path: '~font-awesome/fonts/';
  @import '../node_modules/font-awesome/scss/font-awesome';
  /* Import Simple Line Icons Set */
  $simple-line-font-path: '~simple-line-icons/fonts/';
  @import '../node_modules/simple-line-icons/scss/simple-line-icons';
  /* Import Flag Icons Set */
  @import '../node_modules/flag-icon-css/css/flag-icon.min.css';
  /* Import Bootstrap Vue Styles */
  @import '../node_modules/bootstrap-vue/dist/bootstrap-vue.css';
  // Import Main styles for this application
  @import 'assets/scss/style';
</style>
