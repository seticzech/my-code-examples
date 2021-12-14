<template>
  <div>
    <b-card class="widget text-center">
      <div class="mb-3 mt-3">
        <i class="icon-briefcase font-5xl"></i>
      </div>
      <b-card-title>{{ $t('_ats.widget.job_create_title') }}</b-card-title>
      <b-card-sub-title>{{ $t('_ats.widget.job_create_subtitle') }}</b-card-sub-title>
      <b-button
        variant="primary"
        class="mt-4 btn-lg"
        :disabled="denied('post', 'ats.jobs')"
        @click="addItem"
      >
        {{ $t('_ats.widget.job_create_button') }}
      </b-button>
    </b-card>

    <JobWizard @save="onJobWizardSave"/>
  </div>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import permissions from '@/mixins/permissions'

  import JobWizard from '@/components/_ats/forms/JobWizard'

  export default {
    name: "JobCreate",
    mixins: [ permissions ],
    components: {
      JobWizard
    },
    methods: {
      ...call(store.namespace.ats.jobs, [
        'itemAppend',
        'wizardJobAdd'
      ]),
      addItem () {
        this.wizardJobAdd();
      },
      onJobWizardSave (data) {
        this.itemAppend(data);
      },
    }
  }
</script>

<style scoped>
  i {
    color: #1b8eb7;
  }
</style>
