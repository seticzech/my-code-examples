<template>
  <div class="animated fadeIn">
    <b-row align="end">
      <!--empty column to align buttons right-->
      <b-col class="col-auto mr-auto"></b-col>
      <b-col class="col-auto">
        <b-button variant="primary" @click="itemsRefresh">
          <i class="fa fa-refresh mr-1"></i>
          {{ $t('button.refresh') }}
        </b-button>
        <b-button class="ml-3" variant="stack-overflow" @click="addItem">
          <i class="fa fa-plus-circle mr-1"></i>
          {{ $t('button.add') }}
        </b-button>
        <!--<b-button v-if="action !== null" class="ml-3" variant="secondary" @click="cancelAction">-->
          <!--<i class="fa fa-arrow-circle-left mr-1"></i>-->
          <!--{{ $t('button.back') }}-->
        <!--</b-button>-->
      </b-col>
    </b-row>

    <div class="container-fluid bg-white no-padding margin-top">
      <div class="table-responsive">
        <b-table class="mb-0"
                 v-bind:items="items"
                 v-bind:busy="loading"
                 hover
                 show-empty
                 striped
                 stacked="md"
                 :empty-text="emptyText"
                 :fields="fields"
        >

          <div slot="table-busy" class="text-center text-danger my-2">
            <strong>{{ $t('message.loading') }}</strong>
          </div>

          <template slot="name" slot-scope="data">
            {{ data.item.lastName }}, {{ data.item.firstName }}
          </template>

          <template slot="actions" slot-scope="data">
            <div v-if="action === null" class="no-wrap">
              <b-button variant="outline-primary" v-bind:title="$t('button.edit')" @click="editItem(data.item)"><i class="fa fa-edit"></i></b-button>
              <!--<b-button disabled class="ml-2" variant="outline-danger" v-bind:title="$t('button.archive')"><i class="fa fa-archive"></i></b-button>-->
            </div>
            <div v-if="action === 'select'" class="no-wrap">
              <b-button variant="outline-primary" v-bind:title="$t('button.select')" @click="selectItem(data.item)" v-bind:disabled="!selectableItem(data.item)"><i class="fa fa-check"></i></b-button>
            </div>
          </template>

        </b-table>
      </div>
    </div>

    <UserForm @save="onUserFormSave"/>

  </div>
</template>

<script>
  import { call, get } from 'vuex-pathify'
  import { store } from '@/config'

  import dynamicTranslate from '@/mixins/dynamic-translate'

  import UserForm from '@/components/modals/forms/UserForm'

  export default {
    name: "Users",
    components: { UserForm },
    mixins: [ dynamicTranslate ],
    props: {
      action: {
        type: String,
        required: false,
        default: null
      },
      redir: {
        type: String,
        required: false,
        default: null
      }
    },
    data () {
      return {
        editedItem: null,
        emptyText: this.$i18n.t('message.no_items_to_display'),
        fields: [
          {
            key: 'name',
            label: this.$i18n.t('label.name')
          },
          {
            key: 'email',
            label: this.$i18n.t('label.email')
          },
          {
            key: 'username',
            label: this.$i18n.t('label.username')
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
      ...get(store.namespace.users, ['items', 'loading'])
    },
    methods: {
      ...call(store.namespace.users, ['itemAppend', 'itemsLoad', 'itemsRefresh', 'itemUpdate']),
      ...call(store.namespace.userForm, ['showUserAdd', 'showUserEdit']),
      addItem () {
        this.editedItem = null;
        this.showUserAdd();
      },
      editItem (item) {
        this.editedItem = item;
        this.showUserEdit(item.id);
      },
      onUserFormSave (data) {
        if (this.editedItem) {
          this.itemUpdate(data);
        } else {
          this.itemAppend(data);
        }
      }
    },
    mounted() {
      this.itemsLoad();
    }
  }
</script>

<style scoped>

</style>
