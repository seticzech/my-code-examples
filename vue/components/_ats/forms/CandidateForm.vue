<template>
  <div>
    <b-modal
      size="md"
      :visible="visible"
      :title="title"
      @hide="hide"
      @shown="onShown"
      hide-footer
      no-close-on-backdrop
    >

      <OverlayLoading :active="loading || saving"></OverlayLoading>

      <b-tabs v-model="tabIndex">
        <b-tab title="Osobní">
          <b-form-group
            id="group-first-name"
            :invalid-feedback="$t('validate.select_country')"
            label-cols-sm="3"
            label-cols-lg="4"
            :label="$t('label.first_name')"
            label-for="firstName"
          >
            <b-form-input v-model="firstName" id="firstName" type="text" ref="focused"></b-form-input>
          </b-form-group>

          <b-form-group
            id="group-last-name"
            :invalid-feedback="$t('validate.select_country')"
            label-cols-sm="3"
            label-cols-lg="4"
            :label="$t('label.last_name')"
            label-for="lastName"
          >
            <b-form-input v-model="lastName" id="lastName" type="text"></b-form-input>
          </b-form-group>

          <b-form-group
            id="group-phone"
            :invalid-feedback="$t('validate.select_country')"
            label-cols-sm="3"
            label-cols-lg="4"
            :label="$t('label.phone')"
            label-for="phone"
          >
            <b-form-input v-model="phone" id="phone" type="text"></b-form-input>
          </b-form-group>

          <b-form-group
            id="group-emails"
            :invalid-feedback="$t('validate.select_country')"
            label-cols-sm="3"
            label-cols-lg="4"
            :label="$t('label.email_addresses')"
            label-for="emails"
          >
            <b-form-textarea v-model="emails" id="emails" rows="3"></b-form-textarea>
          </b-form-group>
        </b-tab>

        <b-tab title="Přílohy">
          <b-card
            class="padding-rem-050"
            style="min-height: 7rem;"
            @drop.prevent="dropFiles"
            @dragover.prevent
            no-body
          >
            <h5 class="text-center">{{ $t('label.drop_files_to_upload_here_or') }}</h5>
            <file-uploader
              class="btn btn-primary mb-2"
              :multiple="true"
              ref="upload"
            >
              {{ $t('label.do_select_files') }}
            </file-uploader>

            <file-uploader-list></file-uploader-list>
          </b-card>

          <b-card
            class="padding-rem-050 mb-2"
            :class="{ 'border-danger': attachment.deleted }"
            v-for="attachment in attachments"
            :key="`attachment_${attachment.id}`"
            no-body
          >
            <b-row>
              <b-col cols="1">
                <i class="fa fa-file-o"></i>
              </b-col>
              <b-col class="no-wrap pl-0 pr-0" style="overflow: hidden; text-overflow: ellipsis;">
                <span :id="`attachment_name_${attachment.id}`" class="text-muted">{{ attachment.file.name }}</span>
                <b-tooltip :target="`attachment_name_${attachment.id}`" :title="attachment.file.name" placement="topleft"></b-tooltip>
              </b-col>
              <b-col cols="3" class="pl-0 text-right">
                <div v-if="!attachment.deleted">
                  <b-link :href="attachment.file.url" target="_blank">
                    <b-button variant="link" class="text-primary" @click="">
                      <i class="fa fa-download"></i>
                    </b-button>
                  </b-link>
                  <b-button variant="link" class="text-danger" @click="deleteAttachment(attachment)">
                    <i class="fa fa-trash"></i>
                  </b-button>
                </div>
                <div v-else>
                  <b-button variant="link" class="text-primary" @click="restoreAttachment(attachment)">
                    <i class="fa fa-rotate-left"></i>
                  </b-button>
                </div>
              </b-col>
            </b-row>
          </b-card>
        </b-tab>
      </b-tabs>

      <b-row class="mt-3" align="end">
        <!--empty column to align buttons right-->
        <b-col class="col-auto mr-auto"></b-col>
        <b-col class="col-auto">
          <b-button @click="cancel" :disabled="loading || saving || uploading">
            <i class="fa fa-close mr-1"></i>
            {{ $t('button.cancel') }}
          </b-button>
          <b-button class="ml-3" variant="primary" @click="save" :disabled="loading || saving || uploading">
            <i class="fa fa-save mr-1"></i>
            {{ $t('button.save') }}
          </b-button>
        </b-col>
      </b-row>

    </b-modal>

    <ModalConfirm @confirm="onModalConfirm"></ModalConfirm>

  </div>
</template>

<script>
  import { call, commit, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import FileUploader from "@/components/file-uploader/FileUploader";
  import FileUploaderList from "@/components/file-uploader/FileUploaderList";
  import ModalConfirm from "@/components/modals/Confirm";
  import OverlayLoading from "@/components/overlay/Loading";

  export default {
    name: "CandidateForm",
    components: {
      FileUploader,
      FileUploaderList,
      ModalConfirm,
      OverlayLoading
    },
    data () {
      return {
        forceClose: false,
        onHideEmitData: null,
        onHideEmitEvent: 'cancel',
      }
    },
    computed: {
      ...get(store.namespace.uploads, ['files', 'filesUploaded', 'uploading']),
      ...get(store.namespace.ats.candidateForm, ['attachments', 'editing', 'editedItemId', 'model', 'loading', 'saving']),
      ...sync(store.namespace.ats.candidateForm, ['tabIndex', 'visible']),
      ...sync(store.namespace.ats.candidateForm + '/model@*'),
      title () {
        return this.editing
          ? this.$i18n.t('_ats.candidate_form.title_candidate_edit')
          : this.$i18n.t('_ats.candidate_form.title_candidate_add')
      }
    },
    methods: {
      ...call(store.namespace.ats.candidateForm, ['itemAdd', 'itemUpdate']),
      ...call(store.namespace.modalConfirm, ['showModal']),
      ...call(store.namespace.uploads, ['clearUploads', 'delete', 'deleteUploads']),
      cancel (evt) {
        this.visible = false;
        this.onHideEmitEvent = 'cancel';
      },
      deleteAttachment (attachment) {
        commit(`${store.namespace.ats.candidateForm}/DELETE_ATTACHMENT`, attachment);
      },
      dropFiles (e) {
        if (!e.dataTransfer.files) {
          return;
        }

        this.$refs.upload.dropFiles(e);
      },
      hide (evt) {
        if (this.uploading) {
          return evt.preventDefault();
        }
        if (!this.forceClose && this.files.length && (this.onHideEmitEvent === 'cancel')) {
          this.showModal({
            message: this.$i18n.t('_ats.dialog.candidate_attachments_unassigned_delete_message'),
            title: this.$i18n.t('_ats.dialog.candidate_attachments_unassigned_delete_title')
          });

          return evt.preventDefault();
        }

        this.$emit(this.onHideEmitEvent, this.onHideEmitData);
        this.visible = false;
      },
      isValid () {
        return true;
      },
      onModalConfirm () {
        this.deleteUploads()
          .then(() => {
            this.forceClose = true;
            this.visible = false;
          });
      },
      restoreAttachment (attachment) {
        commit(`${store.namespace.ats.candidateForm}/RESTORE_ATTACHMENT`, attachment);
      },
      save () {
        if (this.isValid()) {
          let fileIds = this.filesUploaded.map( f => f.id );

          if (this.editedItemId) {
            this.itemUpdate(fileIds)
              .then(response => {
                if (response.data) {
                  this.onHideEmitData = response.data;
                  this.onHideEmitEvent = 'save';
                  this.visible = false;
                }
              });
          } else {
            this.itemAdd(fileIds)
              .then(response => {
                if (response.data) {
                  this.onHideEmitData = response.data;
                  this.onHideEmitEvent = 'save';
                  this.visible = false;
                }
              });
          }
        }
      },
      onShown () {
        if (this.$refs.focused) {
          this.$refs.focused.focus();
        }
        this.clearUploads();
        this.forceClose = false;
        this.onHideEmitData = null;
        this.onHideEmitEvent = 'cancel';
      }
    },

  }
</script>
