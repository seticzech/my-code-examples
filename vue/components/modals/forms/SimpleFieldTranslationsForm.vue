<template>
  <b-modal
    size="md"
    :title="title"
    :visible="visible"
    @change="onChangeVisibility"
    @show="onShow"
    centered
    hide-footer
    no-close-on-backdrop
    no-close-on-esc
  >

    <b-form-row class="mb-3" v-for="language in languages" :key="language.code">
      <b-input-group>
        <b-input-group-text slot="prepend">
          <i :class="'flag-icon flag-icon-' + language.flag"></i>
        </b-input-group-text>
        <b-input
          :placeholder="language.adverb"
          :state="stateTranslation(language.code)"
          :value="translations[language.code]"
          @input="setTranslation(language.code, $event)"
        />
      </b-input-group>
    </b-form-row>

    <b-row class="mt-3" align="end">
      <!--empty column to align buttons right-->
      <b-col class="col-auto mr-auto"></b-col>
      <b-col class="col-auto">
        <b-button @click="cancel">
          <i class="fa fa-close mr-1"></i>
          {{ $t('button.cancel') }}
        </b-button>
        <b-button class="ml-3" variant="primary" @click="save">
          <i class="fa fa-save mr-1"></i>
          {{ $t('button.save') }}
        </b-button>
      </b-col>
    </b-row>

  </b-modal>
</template>

<script>
  import { call, get } from 'vuex-pathify'
  import { store } from '@/config'

  import Vue from 'vue'

  import { required } from 'vuelidate/lib/validators'

  export default {
    name: "SimpleFieldTranslationsForm",
    data () {
      return {
        translations: {},
        title: '',
        validated: false,
        visible: false
      }
    },
    validations () {
      let rules = {
        translations: {}
      };

      this.languages.forEach( l => {
        rules.translations[l.code] = {
          required
        };
      });

      return rules;
    },
    computed: {
      ...get({
        'languages': store.namespace.languages + '/items'
      }),
    },
    methods: {
      ...call({
        'languagesLoad': store.namespace.languages + '/itemsLoad'
      }),
      cancel () {
        this.visible = false;
        this.$emit('cancel');
      },
      clearTranslations () {
        this.translations = {};
        this.languages.forEach( l => {
          Vue.set(this.translations, l.code, '');
        })
      },
      getTranslation (language) {
        return this.translations[language];
      },
      isValid () {
        return !this.$v.$invalid;
      },
      onEsc (event) {
        if (event.keyCode === 27) {
          document.removeEventListener('keyup', this.onEsc);
          this.visible = false;
          this.$emit('cancel');
        }
      },
      onChangeVisibility (isVisible) {
        this.visible = isVisible;
      },
      onShow () {
        document.addEventListener('keyup', this.onEsc);
      },
      save () {
        if (this.isValid()) {
          this.visible = false;
          this.$emit('save', this.translations);
        }
      },
      show (data, title) {
        if (data) {
          this.translations = Object.simpleClone(data);
        } else {
          this.clearTranslations();
        }
        if (title) {
          this.title = title;
        }
        this.visible = true;
      },
      setTranslation (language, value) {
        this.translations[language] = value;
        this.$v.translations[language].$touch();
      },
      stateTranslation (language) {
        return !this.$v.translations[language].$invalid;
      }
    },
    mounted() {
      this.languagesLoad();

    }
  }
</script>
