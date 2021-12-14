<template>
  <div class="animated fadeIn">
    <b-row align="end">
      <!--empty column to align buttons right-->
      <b-col class="col-auto mr-auto"></b-col>
      <!--      <b-col class="col-auto mr-auto">-->
      <!--        <b-checkbox id="cb-jobs-deleted" v-model="deleted" switch>-->
      <!--          {{ $t('label.show_deleted_items') }}-->
      <!--        </b-checkbox>-->
      <!--      </b-col>-->
      <b-col class="col-auto">
        <b-button variant="primary" @click="itemsRefresh">
          <i class="fa fa-refresh mr-1"></i>
          {{ $t('button.refresh') }}
        </b-button>
        <b-button class="ml-3" variant="stack-overflow" @click="addItem" :disabled="denied('post', 'location')">
          <i class="fa fa-plus-circle mr-1"></i>
          {{ $t('button.new_location') }}
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

<!--        <template slot="actions" slot-scope="data">-->
<!--          <b-button-->
<!--            disabled-->
<!--            variant="outline-primary"-->
<!--            :title="$t('button.edit')"-->
<!--            @click="editItem(data.item)">-->
<!--            <i class="fa fa-edit"></i>-->
<!--          </b-button>-->
<!--        </template>-->

      </b-table>
    </div>

    <LocationForm @save="onFormSave"></LocationForm>

  </div>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import LocationForm from '@/components/modals/forms/LocationForm'

  import permissions from '@/mixins/permissions'

  export default {
    name: "Locations",
    components: {
      LocationForm
    },
    mixins: [ permissions ],
    data () {
      return {
        editedItem: null,
        emptyText: this.$i18n.t('message.no_items_to_display'),
        fields: [
          {
            key: 'country',
            label: this.$i18n.t('label.country'),
            formatter: value => {
              return value ? value.name : '---';
            }
          },
          {
            key: 'region',
            label: this.$i18n.t('label.region'),
            formatter: value => {
              return value ? value.name : '---';
            }
          },
          {
            key: 'city',
            label: this.$i18n.t('label.city'),
            formatter: value => {
              return value ? value.name : '---';
            }
          },
          // {
          //   key: 'actions',
          //   class: 'text-right',
          //   label: ''
          // }
        ]
      }
    },
    computed: {
      ...get(store.namespace.locations, ['items', 'loading']),
      ...sync(store.namespace.locations, ['deleted'])
    },
    methods: {
      ...call(store.namespace.locationForm, ['showLocationAdd', 'showLocationEdit']),
      ...call(store.namespace.locations, ['itemAppend', 'itemsLoad', 'itemsRefresh', 'itemUpdate']),
      addItem () {
        this.editedItem = null;
        this.showLocationAdd();
      },
      editItem (item) {
        this.editedItem = item;
        this.showLocationEdit(item.id);
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
