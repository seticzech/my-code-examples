<template>
  <div class="app flex-row align-items-center">
    <div class="container">
      <b-row class="justify-content-center">
        <b-col md="8">
          <b-card-group>
            <b-card no-body class="p-4">
              <b-card-body>
                <b-form>
                  <h1>{{ $t('label.login') }}</h1>
                  <p class="text-muted">{{ $t('message.sign_in_to_your_account' )}}</p>

                  <b-input-group class="mb-3">
                    <b-input-group-prepend><b-input-group-text><i class="icon-user"></i></b-input-group-text></b-input-group-prepend>
                    <b-form-input v-model="username" type="text" class="form-control" :placeholder="$t('label.email')" autocomplete="username email" />
                  </b-input-group>
                  <b-input-group class="mb-3">
                    <b-input-group-prepend><b-input-group-text><i class="icon-lock"></i></b-input-group-text></b-input-group-prepend>
                    <b-form-input v-model="password" type="password" class="form-control" :placeholder="$t('label.password')" autocomplete="current-password" />
                  </b-input-group>

                  <b-input-group class="mb-4" v-if="errorStatus">
                    <span class="help-block"
                          style="color: red"
                    >
                      {{ errorMessage[errorStatus] }}
                    </span>
                  </b-input-group>

                  <b-row>
                    <b-col cols="6">
                      <b-button variant="primary" class="px-4" @click="doLogin">
                        <b-spinner v-if="signingIn" class="mr-2" small></b-spinner>
                        {{ $t('button.login') }}
                      </b-button>
                    </b-col>
                    <!--<b-col cols="6" class="text-right">-->
                      <!--<b-button variant="link" class="px-0">{{ $t('button.forgot_my_password') }}</b-button>-->
                    <!--</b-col>-->
                  </b-row>
                </b-form>
              </b-card-body>
            </b-card>
          </b-card-group>
        </b-col>
      </b-row>
    </div>

    <ModalError/>
  </div>
</template>

<script>
  import { call, get } from 'vuex-pathify'
  import { store } from '@/config'

  import ModalError from '@/components/modals/Error'

  export default {
    name: "Login",
    components: {
      ModalError
    },
    data () {
      return {
        errorMessage: {
          400: this.$t('error.bad_login_credentials'),
          401: this.$t('error.bad_login_credentials')
        },
        username: null,
        password: null,
        errorStatus: null
      }
    },
    computed: {
      ...get(store.namespace.auth, ['signingIn'])
    },
    methods: {
      ...call(store.namespace.app, ['redirect']),
      ...call(store.namespace.auth, ['login']),
      ...call(store.namespace.modalError, ['showModal']),
      doLogin () {
        this.login({
            username: this.username, password: this.password
          })
          .then(response => {
            this.redirect();
          })
          .catch( error => {
            if (error.response) {
              if (error.response.data && error.response.data.error) {
                if (error.response.data.error === 'invalid_client') {
                  this.showModal({
                    message: this.$t('error.invalid_client'),
                    title: this.$t('dialog.title_error')
                  });

                  return;
                }
              }

              switch (error.response.status) {
                case 400:
                case 401:
                  this.errorStatus = error.response.status;
                  break;
              }
            }
          })
      }
    }

  }
</script>
