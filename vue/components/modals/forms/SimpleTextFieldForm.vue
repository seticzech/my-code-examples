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

    <b-form-row class="mb-3">
      <b-input v-model="value"></b-input>
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
  export default {
    name: "SimpleTextFieldForm",
    data () {
      return {
        title: '',
        validated: false,
        value: null,
        visible: false
      }
    },
    methods: {
      cancel () {
        this.visible = false;
        this.$emit('cancel');
      },
      isValid () {
        //return !this.$v.$invalid;
        return true;
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
        }
      },
      show (value, title) {
        this.value = value ? value : null;
        if (title) {
          this.title = title;
        }
        this.visible = true;
      },

    }
  }
</script>

<style scoped>

</style>
