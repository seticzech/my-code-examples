<template>
  <div id="tab-description">
    <b-row>
      <b-col>
        <b-tabs>
          <b-tab v-for="language in languages" :key="language.id">
            <template slot="title" class="bg-danger">
              <i :class="'flag-icon flag-icon-' + language.flag"></i>
              <!--<span v-if="!stateTitle(language.code)" class="ml-2">-->
                <!--<i class="fa fa-warning" style="color: #8b0000;"></i>-->
              <!--</span>-->
            </template>

            <label class="h6" v-bind:for="'title-' + language.code">{{ $t('_ats.label.job_title') }}</label>
            <b-form-group :state="stateTitle(language.code)" :invalid-feedback="$t('_ats.validate.enter_job_title_at_least_in_one_language')">
              <b-form-input class="field-input full-width" type="text"
                            :id="'title-' + language.code"
                            :data-language="language.code"
                            :value="translatedTitle(language.code)"
                            @input="setJobTitle(language.code, $event)"/>
            </b-form-group>

            <label class="h6 margin-top" v-bind:for="'description-' + language.code">{{ $t('_ats.label.job_description') }}</label>
            <quill-editor style="position:relative;"
                          :id="'description-' + language.code"
                          :content="translatedDescription(language.code)"
                          :options="quillOptions"
                          @change="setJobDescription(language.code, $event)"/>

          </b-tab>
        </b-tabs>
      </b-col>
    </b-row>

  </div>
</template>

<script>
  import { commit, get } from 'vuex-pathify'
  import { store } from '@/config'

  import { quillEditor } from 'vue-quill-editor'
  import 'quill/dist/quill.core.css'
  import 'quill/dist/quill.snow.css'
  import 'quill/dist/quill.bubble.css'

  export default {
    name: "StepDescription",
    components: {
      quillEditor
    },
    data () {
      return {
        quillOptions: {
          placeholder: this.$i18n.t('message.enter_description')
        },
        validated: false
      }
    },
    validations () {
      let i = null;
      let language = null;
      let rules = {
        title: {}
      };

      for (i in this.languages) {
        language = this.languages[i];

        rules.title[language.code] = {
          requiredUnless: this.validateTitles
        };
      }

      return rules;
    },
    computed: {
      languages: get(store.namespace.languages + '/items'),
      currentLanguage: get(store.namespace.translations + '/current'),
      ...get(store.namespace.ats.jobWizard, ['translatedDescription', 'translatedTitle', 'title'])
    },
    methods: {
      isCurrentLanguage (language) {
        return language.code === this.currentLanguage;
      },
      isValid () {
        return !this.$v.$invalid;
      },
      reset () {
        Object.assign(this.$data, this.$options.data.call(this));
      },
      setJobDescription (language, e) {
        commit(store.namespace.ats.jobWizard + '/SET_TRANSLATED_DESCRIPTION', {
          language,
          value: e.html
        });
      },
      setJobTitle (language, value) {
        commit(store.namespace.ats.jobWizard + '/SET_TRANSLATED_TITLE', {
          language,
          value
        });

        this.$v.title[language].$touch();
      },
      stateTitle (language) {
        return this.validated ? !this.$v.title[language].$invalid : true;
      },
      validate () {
        this.validated = true;

        return this.isValid();
      },
      validateTitles (value, vm) {
        // at least one title is not empty
        for (let lang of this.languages) {
          if (this.translatedTitle(lang.code).length > 0) {
            return true;
          }
        }

        return false;
      }
    }
  }
</script>

