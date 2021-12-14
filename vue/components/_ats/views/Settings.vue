<template>
  <div class="animated fadeIn">
    <b-row>
      <b-col>
        <b-card-group deck>

          <b-card class="text-center" v-bind:header="$t('label.lists')" no-body>
            <b-link :to="{ name: 'Ats_Settings_Lists' }">
              <b-card-body>
                <b-card-text>
                  <i class="fa fa-list display-4"></i>
                </b-card-text>
              </b-card-body>
            </b-link>
          </b-card>

          <b-card class="text-center" v-bind:header="$t('label.locations')" no-body>
            <b-link :to="{ name: 'Ats_Settings_Locations' }">
              <b-card-body>
                <b-card-text>
                  <i class="fa fa-map-marker display-4"></i>
                </b-card-text>
              </b-card-body>
            </b-link>
          </b-card>

          <b-card class="text-center" v-bind:header="$t('_ats.label.hiring')" no-body>
            <b-link :to="{ name: 'Ats_Settings_Hiring' }">
              <b-card-body>
                <b-card-text>
                  <i class="fa fa-cubes display-4"></i>
                </b-card-text>
              </b-card-body>
            </b-link>
          </b-card>

        </b-card-group>
      </b-col>
    </b-row>
  </div>
</template>

<script>
  export default {
    name: "AtsSettings"
  }
</script>

<style scoped>
  .card {
    max-width: 10rem;
  }
</style>
