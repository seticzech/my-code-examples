<template>
  <div class="animated fadeIn">
    <b-row align="end">
      <!--empty column to align buttons right-->
      <b-col class="col-auto mr-auto"></b-col>
<!--      <b-col class="col-auto mr-auto">-->
<!--        <b-checkbox id="cb-cities-deleted" v-model="deleted" switch>-->
<!--          {{ $t('label.show_deleted_items') }}-->
<!--        </b-checkbox>-->
<!--      </b-col>-->
      <b-col class="col-auto">
        <b-button variant="primary" @click="itemsRefresh">
          <i class="fa fa-refresh mr-1"></i>
          {{ $t('button.refresh') }}
        </b-button>
        <b-button class="ml-3" variant="stack-overflow" @click="addItem" :disabled="denied('post', 'cities')">
          <i class="fa fa-plus-circle mr-1"></i>
          {{ $t('button.new_city') }}
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
              :disabled="denied('restore', 'cities')"
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

    <CityForm @save="onFormSave"></CityForm>

  </div>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import CityForm from '@/components/modals/forms/CityForm'

  import permissions from '@/mixins/permissions'

  export default {
    name: "Cities",
    components: {
      CityForm
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
            key: 'region.name',
            label: this.$i18n.t('label.region')
          },
          {
            key: 'region.country.name',
            label: this.$i18n.t('label.country')
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
      ...get(store.namespace.cities, ['items', 'loading']),
      ...sync(store.namespace.cities, ['deleted'])
    },
    methods: {
      ...call(store.namespace.cityForm, ['showCityAdd', 'showCityEdit']),
      ...call(store.namespace.cities, ['itemAppend', 'itemsLoad', 'itemsRefresh', 'itemUpdate']),
      addItem () {
        this.editedItem = null;
        this.showCityAdd();
      },
      editItem (item) {
        this.editedItem = item;
        this.showCityEdit(item.id);
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
