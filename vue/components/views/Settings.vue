<template>
  <div class="animated fadeIn">
    <b-row>
      <b-col>
        <b-card-group deck>

          <b-card class="text-center" v-bind:header="$t('label.languages')" no-body>
            <b-link :to="{ name: 'Settings_Languages' }">
              <b-card-body>
                <b-card-text>
                  <i class="fa fa-globe display-4"></i>
                </b-card-text>
              </b-card-body>
            </b-link>
          </b-card>

          <b-card class="text-center" v-bind:header="$t('label.places')" no-body>
            <b-link :to="{ name: 'Settings_Places' }">
              <b-card-body>
                <b-card-text>
                  <i class="fa fa-map-marker display-4"></i>
                </b-card-text>
              </b-card-body>
            </b-link>
          </b-card>

          <b-card class="text-center" v-bind:header="$t('label.users')" no-body>
            <b-link :to="{ name: 'Settings_Users' }">
              <b-card-body>
                <b-card-text>
                  <i class="fa fa-users display-4"></i>
                </b-card-text>
              </b-card-body>
            </b-link>
          </b-card>

          <b-card class="text-center" v-bind:header="$t('label.roles')" no-body>
            <b-link :to="{ name: 'Settings_Roles' }">
              <b-card-body>
                <b-card-text>
                  <i class="fa fa-user-circle display-4"></i>
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
    name: "Settings"
  }
</script>

<style scoped>
  .card {
    max-width: 10rem;
  }
</style>
