<template>
  <div class="animated fadeIn">

    <b-row align="end">
      <!--empty column to align buttons right-->
      <b-col class="col-auto mr-auto"></b-col>
      <b-col class="col-auto">
        <b-button variant="primary" @click="itemsRefresh">
          <i class="fa fa-refresh mr-1"></i>
          {{ $t('button.refresh') }}
        </b-button>
        <b-button class="ml-3" variant="stack-overflow" @click="addItem">
          <i class="fa fa-plus-circle mr-1"></i>
          {{ $t('button.add') }}
        </b-button>
      </b-col>
    </b-row>

    <b-list-group class="mt-4">
      <b-list-group-item v-if="items && items.length === 0">
        <span class="mr-2 no-wrap">{{ $t('message.no_items_to_display') }}</span>
      </b-list-group-item>

      <b-list-group-item class="d-flex justify-content-between padding-rem-075 hover" v-for="item in items" :key="item.id">
        <span class="mr-2 no-wrap">{{ $dt(item.translations.name) }}</span>

        <div slot="header">
          <b-badge v-if="item.isSuperAdmin" class="mr-3" variant="secondary">Super admin</b-badge>
          <b-button variant="outline-primary" size="sm" @click="editItem(item)">
            <i class="fa fa-edit"></i>
          </b-button>
          <b-button class="ml-2" variant="outline-primary" size="sm" @click="editPermissions(item)" :disabled="item.isSuperAdmin">
            <i class="icon-lock"></i>
          </b-button>
        </div>

      </b-list-group-item>
    </b-list-group>

    <RolePermissionsForm/>
    <SimpleFieldTranslationsForm ref="sftf-roles" @save="onTranslationsFormSave"/>

  </div>
</template>

<script>
  import { call, get } from 'vuex-pathify'
  import { store } from '@/config'

  import dynamicTranslate from '@/mixins/dynamic-translate'
  import RolePermissionsForm from '@/components/modals/forms/RolePermissionsForm'
  import SimpleFieldTranslationsForm from '@/components/modals/forms/SimpleFieldTranslationsForm'

  export default {
    name: "Roles",
    components: { RolePermissionsForm, SimpleFieldTranslationsForm },
    mixins: [ dynamicTranslate ],
    data () {
      return {
        editedItem: null
      }
    },
    computed: {
      ...get(store.namespace.roles, ['items'])
    },
    methods: {
      ...call(store.namespace.rolePermissionsForm, ['showRolePermissionsEdit']),
      ...call(store.namespace.roles, ['itemAppend', 'itemsLoad', 'itemsRefresh', 'itemUpdate']),
      addItem () {
        this.editedItem = null;
        this.$refs['sftf-roles'].show(null, this.$i18n.t('dialog.title_role_add'));
      },
      editItem (item) {
        this.editedItem = item;
        this.$refs['sftf-roles'].show(item.translations.name, this.$i18n.t('dialog.title_role_edit'));
      },
      editPermissions (item) {
        this.showRolePermissionsEdit(item.id);
      },
      onTranslationsFormSave (translations) {
        if (this.editedItem) {
          this.itemUpdate(
            Object.assign({}, this.editedItem, {
              translations: {
                name: translations
              }
            })
          );

          this.editedItem = null;
        } else {
          this.itemAppend({
            translations: {
              name: translations
            }
          });
        }
      }
    },
    mounted() {
      this.itemsLoad();
    }
  }
</script>

<style scoped>

</style>
