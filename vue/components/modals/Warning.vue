<template>
  <b-modal
    class="modal-warning"
    :visible="visible"
    :title="title"
    @hide="onHide"
    centered
    ok-only
  >

    <div class="d-block" v-html="message"></div>

  </b-modal>
</template>

<script>
  import { get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  export default {
    name: 'ModalWarning',
    computed: {
      ...get(store.namespace.modalWarning, ['data']),
      ...sync(store.namespace.modalWarning, ['message', 'title', 'visible']),
    },
    methods: {
      onHide () {
        this.visible = false;
      }
    },
    created() {
      this.title = this.$i18n.t('dialog.title_warning');
    }
  };
</script>
