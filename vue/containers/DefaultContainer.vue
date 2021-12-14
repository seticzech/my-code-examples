<template>
  <div class="app">
    <AppHeader fixed>
      <SidebarToggler class="d-lg-none" display="md" mobile />
      <b-link class="navbar-brand" to="#">
        <img class="navbar-brand-full" src="img/brand/logo.png" width="131" height="25" alt="Scholasticus">
        <img class="navbar-brand-minimized" src="img/brand/symbol.png" width="25" height="25" alt="Scholasticus">
      </b-link>
      <SidebarToggler class="d-md-down-none" display="lg" />
      <b-navbar-nav class="ml-auto mobile">
        <b-nav-item class="">
          <DefaultHeaderDropdown/>
        </b-nav-item>
      </b-navbar-nav>
      <!--<AsideToggler class="d-none d-lg-block" />-->
    </AppHeader>
    <div class="app-body">
      <AppSidebar fixed>
        <SidebarHeader/>
        <SidebarForm/>
        <SidebarNav></SidebarNav>
        <SidebarFooter/>
        <SidebarMinimizer/>
      </AppSidebar>
      <main class="main">
        <Breadcrumb :list="list"/>
        <div class="container-fluid">
          <router-view></router-view>
        </div>
      </main>
      <!--<AppAside fixed>-->
        <!--&lt;!&ndash;aside&ndash;&gt;-->
        <!--<DefaultAside/>-->
      <!--</AppAside>-->
    </div>
    <TheFooter>
      <!--footer-->
      <div class="ml-auto">
        <a href="https://scholasticus.com" target="_blank">Scholasticus TMS</a>
        <span class="ml-1">&copy; 2018 Scholasticus, s.r.o.</span>
      </div>
      <!--<div class="ml-auto">-->
        <!--<span class="mr-1">Powered by</span>-->
        <!--<a href="https://coreui.io">CoreUI for Vue</a>-->
      <!--</div>-->
    </TheFooter>
    <OverlayLoading :active="loading" full-screen :message="loadingMessage" />
    <ModalError />
  </div>
</template>

<script>
import { call, get } from 'vuex-pathify'
import { store } from '@/config'

import { Header as AppHeader, SidebarToggler, Sidebar as AppSidebar, SidebarFooter, SidebarForm, SidebarHeader, SidebarMinimizer, Aside as AppAside, AsideToggler, Footer as TheFooter } from '@coreui/vue'
import DefaultAside from './DefaultAside'
import DefaultHeaderDropdown from './DefaultHeaderDropdown'

import Breadcrumb from '@/components/breadcrumb/Breadcrumb'
import ModalError from '@/components/modals/Error'
import OverlayLoading from '@/components/overlay/Loading'
import SidebarNav from '@/components/navigation/SidebarNav'


export default {
  name: 'DefaultContainer',
  components: {
    OverlayLoading,
    ModalError,
    AsideToggler,
    AppHeader,
    AppSidebar,
    AppAside,
    TheFooter,
    Breadcrumb,
    DefaultAside,
    DefaultHeaderDropdown,
    SidebarForm,
    SidebarFooter,
    SidebarToggler,
    SidebarHeader,
    SidebarNav,
    SidebarMinimizer
  },
  // data () {
  //   return {
  //     navItems: nav.items
  //   }
  // },
  computed: {
    ...get(store.namespace.app, ['loading', 'loadingMessage']),
    name () {
      return this.$i18n.t('place.' + this.$route.name)
    },
    // navItems () {
    //   return this.$store.get('navigations/items');
    // },
    list () {
      return this.$route.matched.filter((route) => route.name || route.meta.label )
    },
  },
  methods: {
    ...call({
      'translationsReload': `${store.namespace.translations}/reload`,
      'languagesLoad': `${store.namespace.languages}/itemsLoad`
    }),
    ...call(store.namespace.app, ['loadingEnd', 'loadingStart']),
  },
  created () {
    // prepare application, download all necessary data like translations, menus etc.

    this.loadingStart('message.app_preparing');

    this.translationsReload()
      .then(() => {
        this.languagesLoad()
      })
      .finally(() => {
        this.loadingEnd();
      })
  }
}
</script>
