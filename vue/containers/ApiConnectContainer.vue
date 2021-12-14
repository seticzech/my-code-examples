<template>
  <main class="main">
    <ModalConnecting/>
    <ModalErrorNetwork/>
  </main>
</template>

<script>
  import { call, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import ConnectApi from '@/services/api/Connect.js'
  import ModalConnecting from '@/components/modals/Connecting'
  import ModalErrorNetwork from '@/components/modals/ErrorNetwork'

  export default {
    name: 'ApiConnectContainer',
    components: {ModalConnecting, ModalErrorNetwork},
    computed: {
      ...sync(store.namespace.app, ['connecting', 'connected'])
    },
    methods: {
      ...call(store.namespace.app, ['redirect']),
      connect () {
        // try to connect to API server
        ConnectApi.connect({
              onNetworkError: (error => { this.$store.commit('modals/error/SET_VISIBLE', true); })
          })
          .then(data => {
            this.connecting = false;
            this.connected = true;

            this.redirect();
          })
          .catch(error => {
            return error;
            //this.showError();
          })
          .finally(() => {
            this.connecting = false;
          });
      },
      setConnectingVisibility(state) {
        this.$store.commit('modals/connecting/SET_VISIBLE', state);
      },
      showError() {
        this.$store.commit('modals/error/SET_VISIBLE', true);
      }
    },
    watch: {
      connecting (to, from) {
        this.setConnectingVisibility(to);
        if (to) {
          this.connect();
        }
      }
    },
    created () {
      this.$store.commit('app/SET_CONNECTING', true);
    }
  }
</script>
