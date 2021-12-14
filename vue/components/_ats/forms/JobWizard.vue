<template>
  <b-modal class="scrollable-modal" :visible="visible" :title="title" size="lg" @hide="closeWizard" hide-footer no-close-on-backdrop>
    <form-wizard color="#20a8d8"
                 ref="wizard"
                 shape="square"
                 step-size="xs"
                 :backButtonText="backButtonText"
                 :nextButtonText="nextButtonText"
                 :finishButtonText="finishButtonText"
                 :start-index="0"
                 @on-complete="onComplete">

      <tab-content class="mb-3"
                   v-for="tab in tabs"
                   v-if="!tab.hide"
                   :key="tab.ref"
                   :title="tab.title"
                   :icon="tab.icon"
                   :before-change="() => beforeStepChange(tab.ref)"
      >

        <component :is="tab.component" v-bind:ref="tab.ref"></component>

      </tab-content>

    </form-wizard>
  </b-modal>
</template>

<script>
  import { call, get } from 'vuex-pathify'
  import { store } from '@/config'

  import { FormWizard, TabContent } from 'vue-form-wizard'
  import 'vue-form-wizard/dist/vue-form-wizard.min.css'

  import manageRefs from '@/mixins/manage-refs'

  import StepDescription from './job-wizard-steps/StepDescription'
  import StepDetails from './job-wizard-steps/StepDetails'
  import StepWorkflow from './job-wizard-steps/StepWorkflow'

  export default {
    name: "JobWizard",
    components: {
      FormWizard,
      TabContent,
      StepDescription,
      StepDetails
    },
    mixins: [ manageRefs ],
    data () {
      return {
        backButtonText: this.$i18n.t('button.back'),
        nextButtonText: this.$i18n.t('button.next'),
        finishButtonText: this.$i18n.t('button.save'),
        tabs: [
          {
            component: StepDescription,
            icon: 'icon-info',
            ref: 'tab-description',
            title: this.$i18n.t('_ats.job_wizard.step_job_description'),
          },
          {
            component: StepDetails,
            icon: 'icon-puzzle',
            ref: 'tab-details',
            title: this.$i18n.t('_ats.job_wizard.step_job_details'),
          },
          {
            component: StepWorkflow,
            icon: 'fa fa-bullseye',
            ref: 'tab-workflow',
            title: this.$i18n.t('_ats.job_wizard.step_job_hiring_workflow'),
          }
        ]
      }
    },
    computed: {
      ...get(store.namespace.ats.jobWizard, ['editedItemId', 'model', 'visible']),
      title () {
        return this.editedItemId
          ? this.$i18n.t('_ats.job_wizard.title_job_edit')
          : this.$i18n.t('_ats.job_wizard.title_job_add')
      }
    },
    methods: {
      ...call(store.namespace.ats.jobWizard, ['itemAdd', 'setVisible', 'itemUpdate']),
      beforeStepChange (ref) {
        ref = this.getRef(ref);

        if (typeof ref.validate === 'function') {
          return ref.validate();
        }

        return true;
      },
      closeWizard () {
        this.setVisible(false);

        // reset whole wizard
        this.$refs.wizard.reset();

        Object.keys(this.$refs).forEach( (key) => {
          let ref = this.getRef(key);

          if (typeof ref.reset === 'function') {
            ref.reset()
          }
        });
      },
      onComplete () {
        if (this.editedItemId) {
          this.itemUpdate()
            .then( response => {
              if (response.data) {
                this.closeWizard();
                this.$emit('save', response.data);
              }
            })
        } else {
          this.itemAdd(this.model)
            .then( response => {
              if (response.data) {
                this.closeWizard();
                this.$emit('save', response.data);
              }
            })
        }
      }
    }
  }
</script>
