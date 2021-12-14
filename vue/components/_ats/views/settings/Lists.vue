<template>
  <div>
    <b-card>
      <div slot="header"><h5 class="no-margin">{{ $t('_ats.place.lists') }}</h5></div>

      <b-card no-body>
        <b-tabs pills card vertical>
          <b-tab v-bind:title="$t('_ats.label.employment_types')">
            <b-card-text>
              <EmploymentTypesList v-bind:items="employmentTypes || []"/>
            </b-card-text>
          </b-tab>
          <b-tab v-bind:title="$t('_ats.label.experiences')">
            <b-card-text>
              <ExperiencesList v-bind:items="experiences || []"/>
            </b-card-text>
          </b-tab>
          <b-tab v-bind:title="$t('_ats.label.positions')">
            <b-card-text>
              <PositionsList v-bind:items="positions || []"/>
            </b-card-text>
          </b-tab>
        </b-tabs>
      </b-card>

    </b-card>
  </div>
</template>

<script>
  import { call, get } from 'vuex-pathify'
  import { store } from '@/config'

  import EmploymentTypesList from '@/components/_ats/views/settings/lists/EmploymentTypesList'
  import ExperiencesList from '@/components/_ats/views/settings/lists/ExperiencesList'
  import PositionsList from '@/components/_ats/views/settings/lists/PositionsList'

  export default {
    name: "AtsSettingsLists",
    components: {
      EmploymentTypesList,
      ExperiencesList,
      PositionsList
    },
    computed: {
      ...get({
        'employmentTypes': store.namespace.ats.employmentTypes + '/items',
        'experiences': store.namespace.ats.experiences + '/items',
        'positions': store.namespace.ats.positions + '/items'
      })
    },
    methods: {
      ...call({
        'loadEmploymentTypes': store.namespace.ats.employmentTypes + '/itemsLoad',
        'loadExperiences': store.namespace.ats.experiences + '/itemsLoad',
        'loadPositions': store.namespace.ats.positions + '/itemsLoad'
      })
    },
    mounted() {
      this.loadEmploymentTypes();
      this.loadExperiences();
      this.loadPositions();
    }
  }
</script>

