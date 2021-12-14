<template>
  <b-card class="widget text-center">
    <div class="mb-3 mt-3">
      <i class="icon-layers font-5xl"></i>
    </div>
    <b-card-title>{{ $t('_ats.widget.job_detail_title') }}</b-card-title>
    <b-card-sub-title>
      <b-select v-model="jobId" :options="jobsOptions"></b-select>
    </b-card-sub-title>
    <b-button
      variant="success"
      class="mt-4 btn-lg"
      :disabled="denied('post', 'ats.jobs') || (jobId === null)"
      @click="doAction"
    >
      {{ $t('_ats.widget.job_detail_button') }}
    </b-button>
  </b-card>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import permissions from '@/mixins/permissions'

  export default {
    name: "JobDetail",
    mixins: [ permissions ],
    data () {
      return {
        jobId: null
      }
    },
    computed: {
      ...get(store.namespace.ats.jobs, ['jobsOptions']),
    },
    methods: {
      ...call(store.namespace.ats.jobs, ['itemsLoad']),
      doAction () {
        this.$router.push({ 'name': 'Ats_Job_Hiring_Workflow', 'params': { id: this.jobId} });
      }
    },
    mounted () {
      this.itemsLoad();
    }
  }
</script>

<style scoped>
  i {
    color: #3ea662;
  }
</style>
