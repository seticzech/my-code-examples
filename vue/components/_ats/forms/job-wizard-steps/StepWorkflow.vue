<template>
  <div>
    <b-row>
      <b-col>
        <label class="h6" for="job-workflow">{{ $t('_ats.label.hiring_workflow') }}</label>
        <b-select id="job-workflow" v-model="workflowId" :options="workflowsList" :disabled="entityDenied('change-workflow', job, true)"></b-select>
      </b-col>
    </b-row>

    <b-row class="mt-3">
      <b-col>
        <h6>{{ $t('_ats.label.hiring_workflow_steps') }}</h6>
        <b-card>
          <b-list-group>
            <b-list-group-item v-for="step in workflowSteps" :key="step.id">
              {{ $dt(step.translations.name) }}
            </b-list-group-item>
          </b-list-group>
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import dynamicTranslate from '@/mixins/dynamic-translate'
  import permissions from '@/mixins/permissions'

  export default {
    name: "StepWorkflow",
    data () {
      return {
        workflowSteps: []
      }
    },
    mixins: [ dynamicTranslate, permissions ],
    computed: {
      ...get(store.namespace.ats.jobWizard, ['job']),
      ...get({
        'workflows': store.namespace.ats.workflows + '/items',
        'workflowsList': store.namespace.ats.workflows + '/list'
      }),
      ...sync(store.namespace.ats.jobWizard + '/model@', {
        workflowId: 'workflowId'
      })
    },
    methods: {
      ...call({
        'loadWorkflows': store.namespace.ats.workflows + '/itemsLoad'
      }),
      onWorkflowIdChange () {
        let workflow = this.workflows.find( i => i.id === parseInt(this.workflowId) );

        this.workflowSteps = workflow ? workflow.workflowSteps : [];
      }
    },
    mounted() {
      this.loadWorkflows();
    },
    watch: {
      workflowId (val) {
        this.onWorkflowIdChange();
      }
    }
  }
</script>

<style scoped>

</style>
