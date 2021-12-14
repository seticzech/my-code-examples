<template>
  <div>
    <b-card class="widget text-center">
      <div class="mb-3 mt-3">
        <i class="icon-people font-5xl"></i>
      </div>
      <b-card-title>{{ $t('_ats.widget.candidate_create_title') }}</b-card-title>
      <b-card-sub-title>{{ $t('_ats.widget.candidate_create_subtitle') }}</b-card-sub-title>
      <b-button
        variant="info"
        class="mt-4 btn-lg text-white"
        :disabled="denied('post', 'ats.candidates')"
        @click="addItem"
      >
        {{ $t('_ats.widget.candidate_create_button') }}
      </b-button>
    </b-card>

    <CandidateForm @save="onCandidateFormSave"/>
  </div>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import permissions from '@/mixins/permissions'

  import CandidateForm from '@/components/_ats/forms/CandidateForm'

  export default {
    name: "CandidateCreate",
    mixins: [ permissions ],
    components: {
      CandidateForm
    },
    methods: {
      ...call(store.namespace.ats.candidates, ['itemAppend']),
      ...call(store.namespace.ats.candidateForm, ['showCandidateAdd']),
      addItem () {
        this.showCandidateAdd();
      },
      onCandidateFormSave (data) {
        this.itemAppend(data);
      },
    }
  }
</script>

<style scoped>
  i {
    color: #43b6d7;
  }
</style>
