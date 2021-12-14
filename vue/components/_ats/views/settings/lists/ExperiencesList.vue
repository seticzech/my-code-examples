<template>
  <div>
    <b-row align="end">
      <b-col class="col-auto mr-auto">
        <b-checkbox id="cb-experiences-deleted" v-model="deleted" switch>
          {{ $t('label.show_deleted_items') }}
        </b-checkbox>
      </b-col>
      <b-col class="col-auto">
        <b-button variant="primary" @click="itemsRefresh">
          <i class="fa fa-refresh mr-1"></i>
          {{ $t('button.refresh') }}
        </b-button>
        <b-button class="ml-3" variant="stack-overflow" @click="addItem">
          <i class="fa fa-plus-circle mr-1"></i>
          {{ $t('button.add' )}}
        </b-button>
      </b-col>
    </b-row>

    <b-list-group class="mt-3">
      <b-list-group-item v-if="items.length === 0">
        <span class="mr-2 no-wrap">{{ $t('message.no_items_to_display') }}</span>
      </b-list-group-item>

      <b-list-group-item class="d-flex justify-content-between padding-rem-075 hover" v-for="item in items" :key="item.id">

        <div class="mr-2 no-wrap">
          <span :class="{ deleted: !!item.deletedAt }">
            {{ $dt(item.translations.name) }}
          </span>
        </div>

        <div slot="header">
          <b-button v-if="!item.deletedAt" variant="outline-primary" size="sm" @click="editItem(item)">
            <i class="fa fa-edit"></i>
          </b-button>

          <b-button v-if="item.deletedAt" class="ml-2" variant="outline-success" size="sm" @click="itemRestore(item.id)">
            <i class="fa fa-rotate-left"></i>
          </b-button>
          <b-button v-else class="ml-2" variant="outline-danger" size="sm" @click="itemDelete(item.id)">
            <i class="fa fa-trash"></i>
          </b-button>
        </div>
      </b-list-group-item>
    </b-list-group>

    <SimpleFieldTranslationsForm ref="sftf-experiences" @save="onTranslationsFormSave"/>

  </div>
</template>

<script>
  import { call, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import SimpleFieldTranslationsForm from '@/components/modals/forms/SimpleFieldTranslationsForm'

  import dynamicTranslate from '@/mixins/dynamic-translate'

  export default {
    name: "ExperiencesList",
    components: { SimpleFieldTranslationsForm },
    mixins: [ dynamicTranslate ],
    data () {
      return {
        editedItem: null
      }
    },
    props: {
      items: {
        default: [],
        type: Array
      }
    },
    computed: {
      ...sync(store.namespace.ats.experiences, ['deleted']),
    },
    methods: {
      ...call(store.namespace.ats.experiences, ['itemAppend', 'itemDelete', 'itemsRefresh', 'itemRestore', 'itemUpdate']),
      addItem () {
        this.editedItem = null;

        this.$refs['sftf-experiences'].show(null, this.$i18n.t('_ats.dialog.title_experience_add'));
      },
      editItem (item) {
        this.editedItem = item;

        this.$refs['sftf-experiences'].show(item.translations.name, this.$i18n.t('_ats.dialog.title_experience_edit'));
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
    }
  }
</script>
