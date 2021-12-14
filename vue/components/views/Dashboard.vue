<template>
  <div class="animated fadeIn">
    <div id="Dashboard">
      <b-row>
        <b-col cols="12" md="6" xl="4" xxl="3">
          <JobCreate></JobCreate>
        </b-col>
        <b-col cols="12" md="6" xl="4" xxl="3">
          <CandidateCreate></CandidateCreate>
        </b-col>
        <b-col cols="12" md="6" xl="4" xxl="3">
          <JobDetail></JobDetail>
        </b-col>
      </b-row>

      <JobsList v-if="allowed('get', 'ats.jobs')"></JobsList>
    </div>
  </div>
</template>

<script>
  import permissions from '@/mixins/permissions'

  import CandidateCreate from '@/components/_ats/widgets/CandidateCreate'
  import JobCreate from '@/components/_ats/widgets/JobCreate'
  import JobDetail from '@/components/_ats/widgets/JobDetail'
  import JobsList from '@/components/_ats/widgets/JobsList'

  export default {
    name: 'Dashboard',
    components: {
      CandidateCreate,
      JobCreate,
      JobDetail,
      JobsList
    },
    mixins: [ permissions ]
  }
</script>
