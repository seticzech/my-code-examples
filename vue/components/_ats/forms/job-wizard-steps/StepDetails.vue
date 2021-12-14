<template>
  <div>
    <b-row class="mt-3 mb-4">
      <b-col>
        <label class="h6" for="recruiters">{{ $t('_ats.label.recruiters') }}</label>
        <b-input-group>
          <multiselect id="recruiters"
                       v-model="recruitersIds"
                       v-bind:placeholder="$t('_ats.placeholder.select_recruiters')"
                       :close-on-select="false"
                       :custom-label="opt => recruitersList.find(v => v.id === opt).name"
                       :multiple="true"
                       :options="recruitersList.map(v => v.id)"
                       :show-labels="false"
          >
          </multiselect>
        </b-input-group>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <b-form-group :state="stateLocation()" :invalid-feedback="$t('validate.select_location')">
          <label class="h6" for="location">{{ $t('label.location') }}</label>
          <b-input-group>
              <multiselect id="location"
                           v-model="locationId"
                           v-bind:placeholder="$t('_ats.placeholder.select_location')"
                           :custom-label="opt => locationsList.find(v => v.id === opt).name"
                           :options="locationsList.map(v => v.id)"
                           :show-labels="false"
              >
              </multiselect>
            <b-input-group-append>
              <b-button variant="light">
                <i class="fa fa-plus"></i>
              </b-button>
            </b-input-group-append>
          </b-input-group>
        </b-form-group>
      </b-col>
    </b-row>

    <b-row class="mt-2">
      <b-col>
        <label class="h6" for="emplType">{{ $t('_ats.label.employment_types') }}</label>
        <b-input-group>
          <multiselect id="emplType"
                       v-model="employmentTypes"
                       v-bind:placeholder="$t('_ats.placeholder.select_employment_types')"
                       :close-on-select="false"
                       :multiple="true"
                       label="name"
                       trackBy="id"
                       :options="employmentTypesList"
                       :show-labels="false"
          >
          </multiselect>
          <b-input-group-append>
            <b-button variant="light">
              <i class="fa fa-plus"></i>
            </b-button>
          </b-input-group-append>
        </b-input-group>
      </b-col>
    </b-row>

    <b-row class="mt-4">
      <b-col>
        <label class="h6" for="job-experience">{{ $t('_ats.label.experience') }}</label>
        <b-input-group>
          <b-select id="job-experience" v-model="experienceId" :options="experiencesList">
          </b-select>
          <b-input-group-append>
            <b-button variant="light">
              <i class="fa fa-plus"></i>
            </b-button>
          </b-input-group-append>
        </b-input-group>
      </b-col>
      <b-col>
        <label class="h6" for="job-position">{{ $t('_ats.label.position') }}</label>
        <b-input-group>
          <b-select id="job-position" v-model="positionId" :options="positionsList"></b-select>
          <b-input-group-append>
            <b-button variant="light">
              <i class="fa fa-plus"></i>
            </b-button>
          </b-input-group-append>
        </b-input-group>
      </b-col>
    </b-row>

    <b-row class="mt-4 mb-5">
      <b-col>
        <label class="h6" >{{ $t('_ats.label.salary') }}</label>
        <div class="mt-5">
          <VueSlider v-model="salary" v-bind="sliderOptions"/>
        </div>
      </b-col>
    </b-row>

  </div>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import { required } from 'vuelidate/lib/validators'

  import Multiselect from 'vue-multiselect'
  import 'vue-multiselect/dist/vue-multiselect.min.css'

  import VueSlider from 'vue-slider-component'
  import 'vue-slider-component/theme/antd.css'

  export default {
    name: "StepDetails",
    components: {
      Multiselect,
      VueSlider
    },
    data () {
      return {
        sliderOptions: {
          max: 150000,
          interval: 1000,
          height: 8,
          marks: [0, 25000, 50000, 75000, 100000, 125000, 150000],
          tooltip: 'always',
          tooltipPlacement: 'top',
        },
        validated: false
      }
    },
    validations () {
      return {
        locationId: {
          required
        }
      }
    },
    computed: {
      ...get({
        'employmentTypesList': store.namespace.ats.jobWizard + '/employmentTypesOptions',
        'experiencesList': store.namespace.ats.jobWizard + '/experiencesOptions',
        'locationsList': store.namespace.locations + '/formattedList',
        'positionsList': store.namespace.ats.jobWizard + '/positionsOptions',
        'recruitersList': store.namespace.users + '/formattedList'
      }),
      ...sync(store.namespace.ats.jobWizard + '/model@', {
        recruitersIds: 'recruitersIds',
        employmentTypes: 'employmentTypes',
        experienceId: 'experienceId',
        locationId: 'locationId',
        positionId: 'positionId',
        salary: 'salary'
      })
    },
    methods: {
      ...call({
        'loadLocations': store.namespace.locations + '/itemsLoad',
        'loadRecruiters': store.namespace.users + '/itemsLoad'
      }),
      isValid () {
        return !this.$v.$invalid;
      },
      reset () {
        Object.assign(this.$data, this.$options.data.call(this));
      },
      stateLocation () {
        return this.validated ? !this.$v.locationId.$invalid : true;
      },
      validate () {
        this.validated = true;

        return this.isValid();
      }
    },
    mounted() {
      this.loadLocations();
      this.loadRecruiters();
    }
  }
</script>
