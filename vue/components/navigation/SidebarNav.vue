<template>
  <nav class="sidebar-nav">
    <VuePerfectScrollbar class="scroll-area" :settings="psSettings" @ps-scroll-y="scrollHandle">
      <ul class="nav">
        <template v-for="(item, index) in items">

          <template v-if="item.title && allowed('access', item.permissionCode)">
            <SidebarNavTitle :key="index" v-bind:name="$t(item.name)" :classes="item.class" :wrapper="item.wrapper"/>
          </template>
          <template v-else-if="item.divider">
            <SidebarNavDivider :key="index" :classes="item.class"/>
          </template>
          <template v-else-if="item.label && allowed('access', item.permissionCode)">
            <SidebarNavLabel :key="index" v-bind:name="$t(item.name)" :url="item.url" :icon="item.icon" :label="item.label" :classes="item.class"/>
          </template>

          <template v-else>

            <template v-if="item.children">
              <!-- First level dropdown -->
              <SidebarNavDropdown v-if="allowed('access', item.permissionCode)" :key="index" v-bind:name="$t(item.name)" :url="item.url" :icon="item.icon">
                <template v-for="(childL1, index1) in item.children">

                  <template v-if="childL1.children">
                    <!-- Second level dropdown -->
                    <SidebarNavDropdown v-if="allowed('access', childL1.permissionCode)" :key="index1" v-bind:name="$t(childL1.name)" :url="childL1.url" :icon="childL1.icon">
                      <li :key="index2" class="nav-item" v-for="(childL2, index2) in childL1.children">

                        <SidebarNavLink v-if="allowed('access', childL2.permissionCode)" v-bind:name="$t(childL2.name)" :url="childL2.url" :icon="childL2.icon" :badge="childL2.badge" :variant="childL2.variant" :attributes="childL2.attributes" />

                      </li>
                    </SidebarNavDropdown>
                  </template>

                  <template v-else>
                    <SidebarNavItem v-if="allowed('access', childL1.permissionCode)" :key="index1" :classes="item.class">
                      <SidebarNavLink v-bind:name="$t(childL1.name)" :url="childL1.url" :icon="childL1.icon" :badge="childL1.badge" :variant="childL1.variant" :attributes="childL1.attributes"/>
                    </SidebarNavItem>
                  </template>

                </template>
              </SidebarNavDropdown>
            </template>

            <template v-else>
              <SidebarNavItem v-if="allowed('access', item.permissionCode)" :key="index" :classes="item.class">
                <SidebarNavLink v-bind:name="$t(item.name)" :url="item.url" :icon="item.icon" :badge="item.badge" :variant="item.variant" :attributes="item.attributes"/>
              </SidebarNavItem>
            </template>

          </template>

        </template>
      </ul>
      <slot></slot>
    </VuePerfectScrollbar>
  </nav>
</template>

<script>
  import { call, get } from 'vuex-pathify'
  import { store } from '@/config'

  import { SidebarNavDivider, SidebarNavDropdown, SidebarNavLink, SidebarNavTitle, SidebarNavItem, SidebarNavLabel } from '@coreui/vue'
  import VuePerfectScrollbar from 'vue-perfect-scrollbar'

  import permissions from '@/mixins/permissions'

  export default {
    name: 'SidebarNav',
    mixins: [ permissions ],
    components: {
      SidebarNavDivider,
      SidebarNavDropdown,
      SidebarNavLink,
      SidebarNavTitle,
      SidebarNavItem,
      SidebarNavLabel,
      VuePerfectScrollbar
    },
    data () {
      return {}
    },
    computed: {
      ...get(store.namespace.navigations, ['items']),
      psSettings: () => {
        // ToDo: find better rtl fix
        return {
          maxScrollbarLength: 200,
          minScrollbarLength: 40,
          suppressScrollX: getComputedStyle(document.querySelector('html')).direction !== 'rtl',
          wheelPropagation: false,
          interceptRailY: styles => ({ ...styles, height: 0 })
        }
      }
    },
    methods: {
      ...call(store.namespace.navigations, ['itemsLoad']),
    },
    mounted () {
      this.itemsLoad();
    }
  }
</script>

<style scoped lang="css">
  .scroll-area {
    position: absolute;
    height: 100%;
    margin: auto;
  }
</style>
