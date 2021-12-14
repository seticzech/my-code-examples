<template>
  <div class="animated fadeIn">
    <b-row align="end">
      <!--empty column to align buttons right-->
      <b-col class="col-auto mr-auto"></b-col>
<!--      <b-col class="col-auto mr-auto">-->
<!--        <b-checkbox id="cb-countries-deleted" v-model="deleted" switch>-->
<!--          {{ $t('label.show_deleted_items') }}-->
<!--        </b-checkbox>-->
<!--      </b-col>-->
      <b-col class="col-auto">
        <b-button variant="primary" @click="itemsRefresh">
          <i class="fa fa-refresh mr-1"></i>
          {{ $t('button.refresh') }}
        </b-button>
        <b-button class="ml-3" variant="stack-overflow" @click="addItem" :disabled="denied('post', 'countries')">
          <i class="fa fa-plus-circle mr-1"></i>
          {{ $t('button.new_country') }}
        </b-button>
      </b-col>
    </b-row>

    <div class="container-fluid bg-white no-padding margin-top">
      <b-table
        :busy="loading"
        :empty-text="emptyText"
        :fields="fields"
        :items="items"
        hover
        outlined
        show-empty
        striped
        stacked="md"
      >

        <div slot="table-busy" class="text-center text-danger my-2">
          <strong>{{ $t('message.loading') }}</strong>
        </div>

        <template slot="actions" slot-scope="data">
          <div v-if="data.item.deletedAt" class="no-wrap">
            <b-button
              variant="outline-success"
              class="ml-2"
              v-bind:title="$t('button.restore')"
              @click="itemRestore(data.item)"
              :disabled="denied('restore', 'counties')"
            >
              <i class="fa fa-rotate-left"></i>
            </b-button>
          </div>

          <div v-else class="no-wrap">
            <b-button
              variant="outline-primary"
              v-bind:title="$t('button.edit')"
              @click="editItem(data.item)"
              :disabled="entityDenied('put', data.item, true)"
            >
              <i class="fa fa-edit"></i>
            </b-button>
          </div>
        </template>
      </b-table>
    </div>

    <CountryForm @save="onFormSave"></CountryForm>

  </div>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import CountryForm from '@/components/modals/forms/CountryForm'

  import permissions from '@/mixins/permissions'

  export default {
    name: "Countries",
    components: {
      CountryForm
    },
    mixins: [ permissions ],
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
            key: 'isoCode2',
            label: this.$i18n.t('label.iso_code_2')
          },
          {
            key: 'isoCode3',
            label: this.$i18n.t('label.iso_code_3')
          },
          {
            key: 'regionsCount',
            label: this.$i18n.t('label.count_of_regions')
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
      ...get(store.namespace.countries, ['items', 'loading']),
      ...sync(store.namespace.countries, ['deleted'])
    },
    methods: {
      ...call(store.namespace.countryForm, ['showCountryAdd', 'showCountryEdit']),
      ...call(store.namespace.countries, ['itemAppend', 'itemsLoad', 'itemsRefresh', 'itemUpdate']),
      addItem () {
        this.editedItem = null;
        this.showCountryAdd();
      },
      editItem (item) {
        this.editedItem = item;
        this.showCountryEdit(item.id);
      },
      onFormSave (data) {
        if (this.editedItem) {
          this.itemUpdate(data);
          this.editedItem = null;
        } else {
          this.itemAppend(data);
        }
      }
    },
    mounted () {
      this.itemsLoad();
    }
  }
</script>

<style scoped>

</style>
