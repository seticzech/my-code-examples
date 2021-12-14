<template>
  <b-modal
    class="modal-danger"
    header-bg-variant="info"
    :visible="visible"
    :title="title"
    @hide="onHide"
    centered
  >

    <div class="d-block" v-html="message"></div>
    <div slot="modal-footer">
      <b-button v-for="(index, button) in buttons" :variant="variants[button]" class="ml-3" @click="callFn(button)" :key="`button_${index}`">{{ $t(`button.${button}`) }}</b-button>
<!--      <b-button variant="secondary" @click="cancel">{{ $t('button.cancel') }}</b-button>-->
<!--      <b-button class="ml-3" variant="primary" @click="confirm">{{ $t('button.confirm') }}</b-button>-->
    </div>

  </b-modal>
</template>

<script>
  import { get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  export default {
    name: "ModalConfirm",
    props: {
      buttons: {
        type: Array,
        required: false,
        default: function () {
          return ['cancel', 'confirm'];
        }
      },
      variants: {
        type: Object,
        required: false,
        default: function () {
          return {
            'cancel': 'secondary',
            'confirm': 'primary',
            'yes': 'success',
            'no': 'danger'
          };
        }
      }
    },
    data () {
      return {
        onHideEmitEvent: 'cancel'
      }
    },
    computed: {
      ...get(store.namespace.modalConfirm, ['callback', 'data']),
      ...sync(store.namespace.modalConfirm, ['message', 'title', 'visible']),
    },
    methods: {
      callFn (name) {
        this[name]();
      },
      cancel () {
        this.onHideEmitEvent = 'cancel';
        this.visible = false;
      },
      confirm () {
        this.onHideEmitEvent = 'confirm';
        this.visible = false;

        if (typeof this.callback === 'function') {
          this.callback(this.data, this.onHideEmitEvent);
        }
      },
      no () {
        this.onHideEmitEvent = 'no';
        this.visible = false;

        if (typeof this.callback === 'function') {
          this.callback(this.data, this.onHideEmitEvent);
        }
      },
      onHide () {
        this.$emit(this.onHideEmitEvent, this.data);
        this.visible = false;
      },
      yes () {
        this.onHideEmitEvent = 'yes';
        this.visible = false;

        if (typeof this.callback === 'function') {
          this.callback(this.data, this.onHideEmitEvent);
        }
      },
    }
  }
</script>

<style scoped>

</style>
