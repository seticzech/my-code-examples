<template>
  <div class="animated fadeIn">

    <div class="container-fluid bg-white no-padding margin-top">
      <div class="table-responsive">
          <b-table
            class="mb-0"
            :busy="loading"
            :empty-text="emptyText"
            :fields="fields"
            :items="items"
            hover
            show-empty
            striped
            stacked="md"
            v-sortable="sortableOptions"
            primary-key="id"
          >

            <template slot="HEAD_sortable">
              <i class="fa fa-bars"></i>
            </template>

            <template slot="sortable" slot-scope="data">
              <i :class="`sortable-handle flag-icon flag-icon-${data.item.flag} cursor-move`"></i>
            </template>

            <template slot="actions" slot-scope="data">
              <div v-if="data.item.deletedAt" class="no-wrap">
              </div>
              <div v-else class="no-wrap">
                <b-button
                  variant="outline-primary"
                  v-bind:title="$t('button.edit')"
                  @click="editItem(data.item)"
                  :disabled="entityDenied('put_disabled_for_now', data.item, false)"
                >
                  <i class="fa fa-edit"></i>
                </b-button>
              </div>
            </template>

          </b-table>
      </div>
    </div>

    <LanguageForm @save="onLanguageFormSave"/>

  </div>
</template>

<script>
  import { call, get, sync } from 'vuex-pathify'
  import { store } from '@/config'

  import permissions from '@/mixins/permissions'

  import LanguageForm from '@/components/modals/forms/LanguageForm'
  import Sortable from 'sortablejs'

  const createSortable = (el, options, vnode) => {
    return Sortable.create(el, {
      ...options
    });
  };
  const sortable = {
    name: 'sortable',
    bind(el, binding, vnode) {
      const table = el;
      table._sortable = createSortable(table.querySelector("tbody"), binding.value, vnode);
    }
  };

  export default {
    name: "Languages",
    components: { LanguageForm },
    directives: { sortable },
    mixins: [ permissions ],
    data () {
      return {
        editedItem: null,
        emptyText: this.$i18n.t('message.no_items_to_display'),
        fields: [
          {
            key: 'sortable',
            class: 'sortable',
            thStyle: {
              textAlign: 'center',
              width: '1rem'
            }
          },
          {
            key: 'name',
            label: this.$i18n.t('label.name'),
          },
          {
            key: 'adverb',
            label: this.$i18n.t('label.adverb'),
          },
          {
            key: 'code',
            label: this.$i18n.t('label.code'),
          },
          {
            key: 'isoCode2',
            label: this.$i18n.t('label.iso_code_2'),
          },
          {
            key: 'locale',
            label: this.$i18n.t('label.locale'),
          },
          {
            key: 'actions',
            class: 'text-right',
            label: ''
          }
        ],
        sortableOptions: {
          animation: 150,
          handle: '.sortable-handle',
          onEnd: this.onLanguageMove
        }
      }
    },
    computed: {
      ...get(store.namespace.languages, ['items', 'loading'])
    },
    methods: {
      ...call(store.namespace.languageForm, ['showLanguageEdit']),
      ...call(store.namespace.languages, ['itemsReorder']),
      editItem (item) {
        this.editedItem = item;
        this.showLanguageEdit(item.id);
      },
      onLanguageFormSave () {

      },
      onLanguageMove (evt) {
        let oldIndex = evt.oldIndex;
        let newIndex = evt.newIndex;

        if (oldIndex !== newIndex) {
          let idList = this.items.map( i => i.id).move(oldIndex, newIndex);

          this.itemsReorder(idList);
        }
      }
    }
  }
</script>

<style scoped>
  .sortable-handle {
    cursor: move;
    cursor: -webkit-grabbing;
  }
</style>
