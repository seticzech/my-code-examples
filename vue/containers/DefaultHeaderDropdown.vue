<template>
  <AppHeaderDropdown v-if="user" right no-caret>
    <template slot="header">
      <img
        src="img/avatars/user_avatar.png"
        class="img-avatar"
        alt="" />
    </template>
    <template slot="dropdown">
      <div>
        <b-dropdown-header tag="div" class="text-center">
          <strong>{{ user.firstName }} {{ user.lastName }}</strong>
        </b-dropdown-header>
        <b-dropdown-item @click="logout">
          <i class="fa fa-lock" />
          {{ $t('label.logout' )}}
        </b-dropdown-item>
      </div>
    </template>
  </AppHeaderDropdown>
</template>

<script>
  import { call, get } from 'vuex-pathify'
  import { store } from '@/config'

  import { HeaderDropdown as AppHeaderDropdown } from '@coreui/vue'

  export default {
    name: 'DefaultHeaderDropdown',
    components: {
      AppHeaderDropdown
    },
    computed: {
      ...get(store.namespace.auth, ['user'])
    },
    methods: {
      ...call(store.namespace.auth, ['logout'])
    }
  }
</script>

