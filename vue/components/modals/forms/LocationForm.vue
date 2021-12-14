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
      <b-select id="countryId" v-model="countryId" :options="countriesOptions" @change="countryChange"></b-select>
    </b-form-group>

    <b-form-group
      id="group-region"
      :invalid-feedback="$t('validate.select_region')"
      label-cols-sm="3"
      label-cols-lg="4"
      :label="$t('label.region')"
      label-for="regionId"
      :state="getState('regionId')"
    >
      <b-select id="regionId" v-model="regionId" :options="regionsOptions" @change="regionChange"></b-select>
    </b-form-group>

    <b-form-group
      id="group-city"
      :invalid-feedback="$t('validate.select_city')"
      label-cols-sm="3"
      label-cols-lg="4"
      :label="$t('label.city')"
      label-for="cityId"
      :state="getState('cityId')"
    >
      <b-select id="cityId" v-model="cityId" :options="citiesOptions"></b-select>
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
    name: "LocationForm",
    data () {
      return {
        validated: false
      }
    },
    validations () {
      return {
        cityId: {
          minValue: minValue(1),
          required
        },
        countryId: {
          minValue: minValue(1),
          required
        },
        regionId: {
          minValue: minValue(1),
          required
        }
      }
    },
    computed: {
      ...get(store.namespace.countries, ['countriesOptions']),
      ...get(store.namespace.locationForm, ['editedItemId', 'regionsOptions', 'citiesOptions']),
      ...sync(store.namespace.locationForm, ['countryId', 'visible']),
      ...sync(store.namespace.locationForm + '/model@*'),
      title () {
        return this.editedItemId
          ? this.$i18n.t('location_form.location_edit_title')
          : this.$i18n.t('location_form.location_add_title');
      }
    },
    methods: {
      ...call({
        loadCountries: store.namespace.countries + '/itemsLoad'
      }),
      ...call(store.namespace.locationForm, ['citiesLoad', 'itemAdd', 'itemUpdate', 'regionsLoad']),
      cancel () {
        this.visible = false;
      },
      countryChange () {
        this.regionId = null;
        this.regionsLoad();
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
      regionChange () {
        this.cityId = null;
        this.citiesLoad();
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
