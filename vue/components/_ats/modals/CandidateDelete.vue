<template>
  <b-modal
    class="modal-info"
    header-bg-variant="info"
    :visible="visible"
    :title="title"
    @hide="onHide"
    centered
  >

    <div v-if="message && message.length" class="d-block" v-html="message"></div>

    <b-form-checkbox
      id="anonymize"
      v-model="anonymize"
      name="anonymize"
    >
      <span class="ml-2">{{ $t('label.anonymize_personal_data') }}</span>
    </b-form-checkbox>

    <b-form-checkbox
      class="mt-2"
      id="deleteAttachments"
      v-model="deleteAttachments"
      name="deleteAttachments"
    >
      <span class="ml-2">{{ $t('label.delete_attachments') }}</span>
    </b-form-checkbox>

    <div slot="modal-footer">
      <b-button variant="secondary" @click="cancel">{{ $t('button.cancel') }}</b-button>
      <b-button class="ml-3" variant="primary" @click="confirm">{{ $t('button.confirm') }}</b-button>
    </div>

  </b-modal>
</template>

<script>
  import { get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  export default {
    name: "CandidateDelete",
    data () {
      return {
        onHideEmitEvent: 'cancel'
      }
    },
    computed: {
      ...get(store.namespace.ats.modalCandidateDelete, ['data', 'message', 'title']),
      ...sync(store.namespace.ats.modalCandidateDelete, ['anonymize', 'deleteAttachments', 'visible']),
    },
    methods: {
      cancel () {
        this.onHideEmitEvent = 'cancel';
        this.visible = false;
      },
      confirm () {
        this.onHideEmitEvent = 'confirm';
        this.visible = false;
      },
      onHide () {
        this.$emit(this.onHideEmitEvent, this.data);
        this.visible = false;
      },

    }
  }
</script>

<style scoped>

</style>
