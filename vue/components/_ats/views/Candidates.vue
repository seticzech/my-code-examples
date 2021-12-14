<template>
  <div class="animated fadeIn">
    <b-row align="end">
      <b-col class="col-12 col-xl-8 mr-md-auto">
        <b-row no-gutters class="mb-3">
          <b-col class="text-left mt-2">
            <b-checkbox id="cb-candidates-deleted" v-model="deleted" class="mb-2 mr-sm-5" switch>
              <div class="no-wrap no-margin">{{ $t('label.show_deleted_items') }}</div>
            </b-checkbox>
          </b-col>

          <b-col class="text-left col-12 col-xl-8">
            <div class="input-glyph">
              <span class="glyph-left text-secondary">
                <i class="fa fa-filter"></i>
              </span>
              <b-input
                class="border-secondary"
                v-model="fastFilter"
                :placeholder="$t('label.fast_filter')"
              ></b-input>
            </div>
          </b-col>
        </b-row>
      </b-col>

      <b-col class="col-auto">
        <b-button variant="primary" @click="itemsRefresh">
          <i class="fa fa-refresh mr-1"></i>
          {{ $t('button.refresh') }}
        </b-button>
        <b-button class="ml-3" variant="stack-overflow" @click="addItem" :disabled="denied('post', 'ats.candidates')">
          <i class="fa fa-plus-circle mr-1"></i>
          {{ $t('_ats.button.new_candidate') }}
        </b-button>
        <b-button v-if="action !== null" class="ml-3" variant="secondary" @click="cancelAction">
          <i class="fa fa-arrow-circle-left mr-1"></i>
          {{ $t('button.back') }}
        </b-button>
      </b-col>
    </b-row>

    <div class="container-fluid bg-white no-padding margin-top">
      <div class="table-responsive">
        <b-table
          class="mb-0"
          :busy="loading"
          :empty-text="emptyText"
          :empty-filtered-text="emptyText"
          :fields="fields"
          :filter="fastFilter"
          :items="items"
          hover
          show-empty
          striped
          stacked="md"
        >

          <div slot="table-busy" class="text-center text-danger my-2">
            <strong>{{ $t('message.loading') }}</strong>
          </div>

          <template slot="name" slot-scope="data">
            {{ data.item.lastName }}, {{ data.item.firstName }}
          </template>

          <template slot="emails" slot-scope="data">
            <span v-for="(email, index) in data.item.emails" :key="email.id">

              <b-link v-if="email.isActive" v-bind:href="'mailto:' + email.email">{{ email.email }}</b-link>
              <span v-else>{{ email.email }}</span>

              <span v-if="index < data.item.emails.length - 1">, </span>
            </span>
          </template>

          <template slot="actions" slot-scope="data">
            <div v-if="action === null" class="no-wrap">
              <div v-if="data.item.deletedAt" class="no-wrap">
                <b-button
                  variant="outline-success"
                  class="ml-2"
                  v-bind:title="$t('button.restore')"
                  @click="itemRestore(data.item.id)"
                  :disabled="denied('restore', 'ats.candidates')"
                >
                  <i class="fa fa-rotate-left"></i>
                </b-button>
              </div>
              <div v-else>
                <b-button
                  variant="outline-primary"
                  v-bind:title="$t('button.edit')"
                  @click="editItem(data.item)"
                  :disabled="entityDenied('put', data.item)"
                >
                  <i class="fa fa-edit"></i>
                </b-button>
                <b-button
                  class="ml-2"
                  variant="outline-success"
                  v-bind:title="$t('button.attachments')"
                  @click="editAttachments(data.item)"
                  :disabled="entityDenied('put', data.item)"
                >
                  <i class="fa fa-paperclip"></i>
                </b-button>
                <b-button
                  class="ml-2"
                  variant="outline-danger"
                  v-bind:title="$t('button.delete')"
                  @click="removeCandidate(data.item)"
                  :disabled="denied('delete', 'ats.candidates') || entityDenied('delete', data.item)"
                >
                  <i class="fa fa-trash"></i>
                </b-button>
              </div>
            </div>

            <div v-if="action === 'select'" class="no-wrap">
              <b-button
                variant="outline-primary"
                v-bind:title="$t('button.select')"
                @click="selectItem(data.item)"
                :disabled="!selectableItem(data.item) || !!data.item.deletedAt"
              >
                <i class="fa fa-check"></i>
              </b-button>
            </div>
          </template>

        </b-table>
      </div>
    </div>

    <CandidateDelete @confirm="onCandidateDeleteConfirm"/>
    <CandidateForm @save="onCandidateFormSave"/>

  </div>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import permissions from '@/mixins/permissions'

  import CandidateDelete from "@/components/_ats/modals/CandidateDelete";
  import CandidateForm from '@/components/_ats/forms/CandidateForm'

  export default {
    name: "Candidates",
    components: {
      CandidateDelete,
      CandidateForm
    },
    mixins: [ permissions ],
    data () {
      return {
        editedItem: null,
        emptyText: this.$i18n.t('message.no_items_to_display'),
        fastFilter: null,
        fields: [
          {
            key: 'name',
            label: this.$i18n.t('label.name')
          },
          {
            key: 'phone',
            label: this.$i18n.t('label.phone')
          },
          {
            key: 'emails',
            label: this.$i18n.t('label.email_addresses')
          },
          {
            key: 'actions',
            class: 'text-right',
            label: ''
          }
        ]
      }
    },
    computed: {
      ...get(store.namespace.ats.candidates, ['action', 'actionOptions', 'items', 'loading']),
      ...get(store.namespace.ats.modalCandidateDelete, ['anonymize', 'deleteAttachments']),
      ...sync(store.namespace.ats.candidates, ['deleted']),
    },
    methods: {
      ...call(store.namespace.ats.candidates, [
        'actionCancel',
        'actionPerform',
        'itemAppend',
        'itemDelete',
        'itemsLoad',
        'itemsRefresh',
        'itemRestore',
        'itemUpdate'
      ]),
      ...call(store.namespace.ats.modalCandidateDelete, ['showModal']),
      ...call(store.namespace.ats.candidateForm, ['showCandidateAdd', 'showCandidateEdit']),
      addItem () {
        this.editedItem = null;

        this.showCandidateAdd();
      },
      cancelAction () {
        this.actionCancel();
      },
      backToRedir () {
        let redir = this.$route.query['redir'] || null;

        if (redir) {
          this.clearSelectFilter();
          this.$router.push({ path: redir });
        }
      },
      editAttachments (item) {
        this.editedItem = item;
        this.showCandidateEdit({ id: item.id, tabIndex: 1 });
      },
      editItem (item) {
        this.editedItem = item;
        this.showCandidateEdit({ id: item.id });
      },
      onCandidateDeleteConfirm (data) {
        this.itemDelete({
          id: data.id,
          data: {anonymize: this.anonymize, deleteAttachments: this.deleteAttachments}
        });
      },
      onCandidateFormSave (data) {
        if (this.editedItem) {
          this.itemUpdate(data);
        } else {
          this.itemAppend(data);
        }
      },
      removeCandidate (item) {
        this.showModal({
          data: item,
          title: this.$i18n.t('_ats.dialog.delete_candidate_title'),
        })
      },
      selectableItem (item) {
        return this.actionOptions.filterIds.indexOf(item.id) === -1;
      },
      selectItem (item) {
        this.actionPerform(item);
      }
    },
    mounted() {
      this.itemsLoad();
    }
  }
</script>
