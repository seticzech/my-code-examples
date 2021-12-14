<template>
  <b-modal
    size="md"
    class="mt-5"
    :visible="visible"
    :title="title"
    @hide="onHide"
    @shown="onShown"
    hide-footer
    no-close-on-backdrop
  >

    <b-form-group
      id="group-country"
      :invalid-feedback="$t('validate.select_country')"
      label-cols-sm="3"
      label-cols-lg="4"
      :label="$t('label.country')"
      label-for="countryId"
      :state="getState('countryId')"
    >
      <b-select id="countryId" v-model="countryId" :options="countriesOptions"></b-select>
    </b-form-group>

    <b-form-group
      id="group-name"
      :invalid-feedback="$t('validate.enter_name')"
      label-cols-sm="3"
      label-cols-lg="4"
      :label="$t('label.name')"
      label-for="name"
      :state="getState('name')"
    >
      <b-form-input v-model="name" id="name" type="text" ref="focused"></b-form-input>
    </b-form-group>

    <b-row class="mt-3" align="end">
      <!--empty column to align buttons right-->
      <b-col class="col-auto mr-auto"></b-col>
      <b-col class="col-auto">
        <b-button @click="cancel">
          <i class="fa fa-close mr-1"></i>
          {{ $t('button.cancel') }}
        </b-button>
        <b-button class="ml-3" variant="primary" @click="save">
          <i class="fa fa-save mr-1"></i>
          {{ $t('button.save') }}
        </b-button>
      </b-col>
    </b-row>

  </b-modal>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import { minValue, required } from 'vuelidate/lib/validators'

  export default {
    name: "RegionForm",
    data () {
      return {
        validated: false
      }
    },
    validations () {
      return {
        countryId: {
          minValue: minValue(1),
          required
        },
        name: {
          required
        }
      }
    },
    computed: {
      ...get(store.namespace.countries, ['countriesOptions']),
      ...get(store.namespace.regionForm, ['editedItemId']),
      ...sync(store.namespace.regionForm, ['visible']),
      ...sync(store.namespace.regionForm + '/model@*'),
      title () {
        return this.editedItemId
          ? this.$i18n.t('region_form.region_edit_title')
          : this.$i18n.t('region_form.region_add_title');
      }
    },
    methods: {
      ...call({
        loadCountries: store.namespace.countries + '/itemsLoad'
      }),
      ...call(store.namespace.regionForm, ['itemAdd', 'itemUpdate']),
      cancel () {
        this.visible = false;
      },
      getState (field) {
        return this.validated
          ? this.$v[field] && !this.$v[field].$invalid
          : true;
      },
      onShown () {
        this.validated = false;
      },
      onHide () {
        this.visible = false;
      },
      isValid () {
        this.validated = true;
        this.$v.$touch();

        return !this.$v.$invalid;
      },
      save () {
        if (this.isValid()) {
          if (this.editedItemId) {
            this.itemUpdate()
              .then(response => {
                if (response.data) {
                  this.visible = false;
                  this.$emit('save', response.data);
                }
              });
          } else {
            this.itemAdd()
              .then(response => {
                if (response.data) {
                  this.visible = false;
                  this.$emit('save', response.data);
                }
              });
          }
        }
      },
    },
    mounted () {
      this.loadCountries();
    }
  }
</script>

<style scoped>

</style>
