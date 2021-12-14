<template>
  <div>
    <b-card>
      <b-card no-body>
        <b-tabs pills card vertical>
          <b-tab v-bind:title="$t('label.countries')">
            <b-card-text>
              <Countries/>
            </b-card-text>
          </b-tab>

          <b-tab v-bind:title="$t('label.regions')">
            <b-card-text>
              <Regions/>
            </b-card-text>
          </b-tab>

          <b-tab v-bind:title="$t('label.cities')">
            <b-card-text>
              <Cities/>
            </b-card-text>
          </b-tab>

          <b-tab v-bind:title="$t('label.locations')">
            <b-card-text>
              <Locations/>
            </b-card-text>
          </b-tab>
        </b-tabs>
      </b-card>
    </b-card>
  </div>
</template>

<script>
  import Cities from './Cities'
  import Countries from './Countries'
  import Locations from './Locations'
  import Regions from './Regions'

  export default {
    name: "Places",
    components: {
      Cities,
      Countries,
      Locations,
      Regions
    }
  }
</script>

<style scoped>

</style>
