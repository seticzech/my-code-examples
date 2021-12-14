<template>
  <div>
    <div
      class="card mb-3 applicant-card"
      :class="{ disabled: !!applicant.candidate.deletedAt }"
      :id="`applicant_${applicant.id}`"
      :data-applicant-id="applicant.id"
    >

      <div :id="`applicant_header_${applicant.id}`" class="card-header handle padding-rem-050">
        <strong>{{ applicant.candidate.lastName }}, {{ applicant.candidate.firstName }}</strong>
      </div>

      <div class="card-body padding-rem-050">
        <b-row v-for="email in applicant.candidate.emails" :key="email.id" align-h="start" no-gutters class="mb-2">
          <b-col cols="1" class="mr-1">
            <i class="fa fa-envelope"></i>
          </b-col>
          <b-col>
            <b-link v-if="email.isActive && !applicant.candidate.deletedAt" v-bind:href="'mailto:' + email.email">{{ email.email }}</b-link>
            <span v-else>{{ email.email }}</span>
          </b-col>
        </b-row>

        <b-row class="mb-2" align-h="start" no-gutters>
          <b-col cols="1" class="mr-1">
            <i class="fa fa-phone"></i>
          </b-col>
          <b-col>
            <span>{{ applicant.candidate.phone }}</span>
          </b-col>
        </b-row>

        <b-row class="mb-2">
          <b-col cols="8">
            <StarRating v-if="applicant.totalRating !== null" v-bind="starsEnabled" :rating="applicant.totalRating / 10"/>
            <StarRating v-else v-bind="starsDisabled" v-bind:rating="0"/>
          </b-col>
          <b-col class="text-right">
            <b-badge
              class="mt-badge-rating"
              v-if="applicant.totalRating !== null"
              pill
              variant="warning"
            >
              {{ applicant.totalRating }} %
            </b-badge>
          </b-col>
        </b-row>

        <b-row class="mt-4">
          <b-col cols="4">
            <b-button variant="link" class="mr-1 btn-link-red" @click="removeApplicant(applicant)" :disabled="!!applicant.candidate.deletedAt">
              <i class="fa fa-trash"></i>
            </b-button>
          </b-col>

          <b-col class="text-right">
            <b-button variant="link" class="mr-1"  @click="editComments(applicant)" :disabled="!!applicant.candidate.deletedAt">
              <i class="fa fa-file-text-o"></i>
            </b-button>
            <b-button variant="link"  @click="editEvents(applicant)" :disabled="!!applicant.candidate.deletedAt">
              <i class="fa fa-flash"></i>
            </b-button>
          </b-col>
        </b-row>
      </div>

    </div>

    <b-popover
      v-if="applicant.candidate.deletedAt"
      placement="topright"
      :target="`applicant_header_${applicant.id}`"
      triggers="hover"
      :content="$t('_ats.tooltip.candidate_is_deleted')"
    ></b-popover>

  </div>
</template>

<script>
  import StarRating from 'vue-star-rating'

  export default {
    name: "ApplicantCard",
    components: { StarRating },
    props: {
      applicant: {
        type: Object,
        required: true
      }
    },
    data () {
      return {
        starsDisabled: {
          "active-color": "#ddd",
          "border-color": "#ddd",
          "border-width": 2,
          "inactive-color": "#ddd",
          increment: 0.1,
          "max-rating": 10,
          "read-only": true,
          "star-size": 10,
          "show-rating": false
        },
        starsEnabled: {
          "active-color": "#ffa255",
          "border-color": "#ccc",
          "border-width": 2,
          "inactive-color": "#fff",
          increment: 0.1,
          "max-rating": 10,
          "read-only": true,
          "star-size": 10,
          "show-rating": false
        }
      }
    },
    methods: {
      editComments (item) {
        this.$emit('comments', item);
      },
      editEvents (item) {
        this.$emit('events', item);
      },
      removeApplicant (item) {
        this.$emit('remove', item);
      }
    }
  }
</script>

<style scoped>
  .btn-link-red {
    color: #e60808;
  }
  .btn-link-red:hover {
    color: #ac0808;
  }
  .applicant-card {
    /*min-width: 13rem;*/
    flex: 0 0 100%;
  }
  .applicant-card.disabled {
    color: #999;
  }
  .mt-badge-rating {
    margin-top: .4rem!important;
  }
</style>
