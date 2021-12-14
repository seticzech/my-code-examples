<template>
  <b-modal
    class="modal-danger"
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
    name: 'ModalError',
    computed: {
      ...get(store.namespace.modalError, ['data']),
      ...sync(store.namespace.modalError, ['message', 'title', 'visible']),
    },
    methods: {
      onHide () {
        this.visible = false;
      }
    },
    created() {
      this.title = this.$i18n.t('dialog.title_error');
    }
  }

</script>
