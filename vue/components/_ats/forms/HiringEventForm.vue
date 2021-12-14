<template>
  <div>

    <b-list-group>
      <b-list-group-item>

        <b-form-group :disabled="!editable" id="fieldset-form">

          <b-form-group
            id="fieldset-type"
            label-cols="4"
            :label="$t('_ats.hiring_event_form.label_event_type')"
            label-for="event-type"
            :invalid-feedback="$t('_ats.validate.select_event_type')"
            :state="getFormState('eventTypeId')"
          >
            <b-form-select id="event-type" :options="eventsTypesList" v-model="eventTypeId"></b-form-select>
          </b-form-group>

          <b-form-group
            id="fieldset-date"
            label-cols="4"
            :label="$t('_ats.hiring_event_form.label_event_date')"
            :invalid-feedback="$t('_ats.validate.enter_event_date')"
            :state="getFormState('occuredAt')"
          >
            <div v-if="editable">
              <date-picker v-model="occuredAt" v-bind="datePickerOptions" :lang="langShort"></date-picker>
            </div>
            <div v-else>
              <b-input :value="occuredAt | moment(dateFormat)"></b-input>
            </div>
          </b-form-group>

          <b-form-group
            id="fieldset-name"
            label-cols="4"
            :label="$t('_ats.hiring_event_form.label_event_name')"
            label-for="event-name"
            :invalid-feedback="$t('_ats.validate.enter_event_name')"
            :state="getFormState('name')"
          >
            <b-form-input id="event-name" v-model="name"></b-form-input>
          </b-form-group>

          <b-form-group
            id="fieldset-comment"
            label-cols="4"
            :label="$t('_ats.hiring_event_form.label_event_comment')"
            label-for="event-comment"
          >
            <b-form-textarea id="event-comment" v-model="comment"></b-form-textarea>
          </b-form-group>

          <div v-if="ratingsTypes && ratingsTypes.length > 0">
            <b-row class="mt-3">
              <b-col>
                <div class="border-bottom border-secondary"></div>
              </b-col>
            </b-row>

            <div v-for="ratingType in ratingsTypes" :key="ratingType.id">
              <b-row class="mt-4" align="start">
                <b-col cols="1">
                  <b-checkbox
                    :id="'rating_type_' + ratingType.id"
                    @change="onRatingTypeStatusChange(ratingType.id, $event)"
                    :checked="ratingEnabled[ratingType.id]"
                    switch
                  ></b-checkbox>
                </b-col>
                <b-col>
                  <label :for="'rating_type_' + ratingType.id" class="font-sm">
                    <strong>{{ $dt(ratingType.translations.name) }}</strong>
                  </label>
                </b-col>
              </b-row>

              <b-row v-if="ratingEnabled[ratingType.id]" class="mt-2">
                <b-col>
                  <div v-if="editable" @mouseenter="onRatingMouseEnter(ratingType.id)" @mouseleave="onRatingMouseLeave(ratingType.id)">
                    <StarRating
                      v-bind="starsEnabled"
                      :rating="getRatingTypeValue(ratingType.id)"
                      @current-rating="onCurrentRating"
                      @rating-selected="onRatingSet(ratingType.id, $event)"
                    />
                  </div>
                  <div v-else>
                    <StarRating
                      v-bind="starsEnabledReadOnly"
                      :rating="getRatingTypeValue(ratingType.id)"
                    />
                  </div>
                </b-col>
                <b-col cols="2" class="text-right">
                  <b-badge
                    v-if="currentRatingId !== ratingType.id"
                    class="mt-badge-rating"
                    pill
                    variant="warning"
                  >
                    {{ getRatingTypeValue(ratingType.id) * 10 }} %
                  </b-badge>
                  <b-badge
                    v-else
                    class="mt-badge-rating"
                    pill
                    variant="primary"
                  >
                    {{ currentRatingValue * 10 }} %
                  </b-badge>
                </b-col>
              </b-row>

              <b-row v-else class="mt-2">
                <b-col>
                  <StarRating v-bind="starsDisabled" :rating="0"/>
                </b-col>
                <b-col cols="4" class="text-right">
                  <b-badge
                    class="mt-badge-rating"
                    pill
                    variant="secondary"
                  >
                    {{ $t('_ats.label.not_rated') }}
                  </b-badge>
                </b-col>
              </b-row>
            </div>
          </div>

        </b-form-group>
      </b-list-group-item>
    </b-list-group>

    <b-row class="mt-3" align-h="end">
      <b-col class="col-auto mr-auto"></b-col>
      <b-col class="col-auto">
        <b-button @click="cancel">
          <i class="fa fa-arrow-circle-left mr-1"></i>
          {{ $t('button.back') }}
        </b-button>
        <b-button v-if="editable" class="ml-3" variant="primary" @click="save">
          <i class="fa fa-save mr-1"></i>
          {{ $t('button.save') }}
        </b-button>
      </b-col>
    </b-row>

  </div>
</template>

<script>
  import { call, commit, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import dynamicTranslate from '@/mixins/dynamic-translate'
  import { minValue, required } from 'vuelidate/lib/validators'

  import DatePicker from 'vue2-datepicker'
  import StarRating from 'vue-star-rating'

  export default {
    name: "HiringEventForm",
    components: {
      DatePicker,
      StarRating
    },
    mixins: [ dynamicTranslate ],
    validations () {
      return {
        eventTypeId: {
          required,
          minValue: 1
        },
        name: {
          required
        },
        occuredAt: {
          required
        }
      }
    },
    data () {
      return {
        currentRatingId: null,
        currentRatingValue: 0,
        datePickerOptions: {
          firstDayOfWeek: 1,
          format: this.$t('dateTime.format.date'),
          inputClass: 'form-control',
          placeholder: '',
          width: '100%'
        },
        dateFormat: this.$t('dateTime.format.date'),
        starsDisabled: {
          "active-color": "#ddd",
          "border-color": "#ddd",
          "border-width": 2,
          "inactive-color": "#ddd",
          increment: 0.1,
          "max-rating": 10,
          "read-only": true,
          "star-size": 15,
          "show-rating": false
        },
        starsEnabled: {
          "active-color": "#ffa255",
          "border-color": "#ccc",
          "border-width": 2,
          "inactive-color": "#fff",
          increment: 0.1,
          "max-rating": 10,
          "read-only": false,
          "star-size": 15,
          "show-rating": false
        },
        starsEnabledReadOnly: {
          "active-color": "#ffa255",
          "border-color": "#ccc",
          "border-width": 2,
          "inactive-color": "#fff",
          increment: 0.1,
          "max-rating": 10,
          "read-only": true,
          "star-size": 15,
          "show-rating": false
        }
      }
    },
    computed: {
      ...get({
        'eventsTypes': store.namespace.ats.hiringEventsTypes + '/items',
        'eventsTypesList': store.namespace.ats.hiringEventsTypes + '/list',
        'ratingsTypes': store.namespace.ats.hiringRatingsTypes + '/items',
      }),
      ...get(store.namespace.ats.hiringEventForm, ['editable', 'ratingEnabled', 'ratingValues']),
      ...get(store.namespace.translations, ['langShort']),
      ...sync(store.namespace.ats.hiringEventForm, ['model', 'visible']),
      ...sync(store.namespace.ats.hiringEventForm + '/model@*')
    },
    methods: {
      ...call({
        'eventsTypesLoad': store.namespace.ats.hiringEventsTypes + '/itemsLoad',
        'ratingsTypesLoad': store.namespace.ats.hiringRatingsTypes + '/itemsLoad',
      }),
      ...call(store.namespace.ats.hiringEventForm, ['itemAppend']),
      cancel () {
        this.visible = false;
        this.$emit('cancel');
      },
      getRatingTypeEnabled (id) {
        return (this.ratingEnabled[id] !== undefined) ? this.ratingEnabled[id] : false;
      },
      getRatingTypeValue (id) {
        return this.ratingValues[id] || 0;
      },
      getFormState (index) {
        if (this.$v[index]) {
          return !this.$v[index].$error;
        }

        return true;
      },
      isValid () {
        this.$v.$touch();

        return !this.$v.$anyError;
      },
      onCurrentRating (value) {
        this.currentRatingValue = value;
      },
      onRatingMouseEnter (id) {
        this.currentRatingId = id;
      },
      onRatingMouseLeave () {
        this.currentRatingId = null;
      },
      onRatingSet (id, value) {
        // we have to change rating value manually
        // because store can't set individual member of array
        commit(store.namespace.ats.hiringEventForm + '/SET_RATING_VALUE', {
          id,
          value
        });
      },
      onRatingTypeStatusChange (id, value) {
        // we have to set ratings enabled and initial values manually
        // because store can't set individual member of array
        commit(store.namespace.ats.hiringEventForm + '/SET_RATING_ENABLED', {
          id,
          value
        });
      },
      save () {
        if (this.isValid()) {
          let data = Object.simpleClone(this.model);

          if (this.model.occuredAt instanceof Date) {
            data.occuredAt = this.model.occuredAt.toISOString().split('.')[0];
          } else if (typeof this.model.occuredAt === 'string') {
            data.occuredAt = this.model.occuredAt.split('.')[0];
          }

          data.ratings = {};
          this.ratingValues.forEach( (item, index) => {
            if (this.ratingEnabled[index] && (index > 0)) {
              data.ratings[index] = item * 10;
            }
          });

          this.$emit('save', data);
        }
      }
    },
    mounted () {
      this.eventsTypesLoad();
      this.ratingsTypesLoad();
    }
  }
</script>

<style scoped>
  .mt-badge-rating {
    margin-top: .4rem!important;
  }
  .list-group-item {
    max-height: calc(100vh - 13rem);
    overflow-y: auto;
  }
</style>
