<template>
  <b-card no-body>
    <b-card-header>{{ $t('_ats.label.jobs_favorites') }}</b-card-header>
    <b-card-body class="pl-3">

      <div
        class="no-gutters"
        v-if="favorites.length"
        v-for="(item, index) in favorites"
        :key="item.id"
        v-bind:applicants="applicantsLoad(item)"
        v-bind:workflow="workflowLoad(item)"
      >

        <div>
          <span class="h4 mr-3 no-wrap">{{ $dt(item.translations.title) }}</span>
          <br class="d-md-none">
          <span v-if="item.position">{{ $dt(item.position.translations.name) }} - </span>
          <span>{{ item.location.city.name }}, {{ item.location.country.name }}</span>
        </div>

        <b-row v-if="item.workflow.workflowSteps && item.workflow.workflowSteps.length">
          <b-col>
            <div :class="getCalloutClass(index)">
              <small class="text-muted no-wrap">{{ $t('_ats.label.applicants') }}</small>
              <br>
              <strong class="h3">{{ getApplicants(item).length || '-' }}</strong>
            </div>
          </b-col>

          <b-col v-for="workflowStep in item.workflow.workflowSteps" :key="workflowStep.id">
            <div :class="getCalloutClass(index)">
              <small class="text-muted no-wrap">{{ $dt(workflowStep.translations.name) }}</small>
              <br>
              <strong class="h3">{{ getApplicantsInWorkflowStep(item, workflowStep).length || '-' }}</strong>
            </div>
          </b-col>
        </b-row>

        <hr v-if="index < favorites.length - 1" class="border-secondary mt-0">
      </div>

      <div
        class="no-gutters text-center"
        v-if="favorites.length === 0"
      >
        <span class="text-muted">{{ $t('_ats.label.you_have_not_favorites_jobs') }}</span>
      </div>

    </b-card-body>
  </b-card>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import dynamicTranslate from '@/mixins/dynamic-translate'

  export default {
    name: "JobsList",
    mixins: [ dynamicTranslate ],
    computed: {
      ...get(store.namespace.ats.jobs, ['favorites', 'loading'])
    },
    methods: {
      ...call(store.namespace.ats.jobs, ['applicantsLoad', 'itemsLoad', 'workflowLoad']),
      getApplicants (item) {
        if (item.applicants) {
          return item.applicants.filter( i => !i.candidate.deletedAt );
        }

        return [];
      },
      getApplicantsInWorkflowStep (item, workflowStep) {
        return this.getApplicants(item).filter( i => i.workflowStep && i.workflowStep.id === workflowStep.id );
      },
      getCalloutClass (index) {
        let colors = ['warning', 'success', 'primary', 'danger'];
        let i = index % 4;

        return 'callout callout-' + colors[i];
      }
    },
    mounted () {
      this.itemsLoad();
    }
  }
</script>

<style scoped>

</style>
