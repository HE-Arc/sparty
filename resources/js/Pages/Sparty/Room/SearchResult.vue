<template>
  <Head title="test" />
  <div class="card-body">

    <breeze-validation-errors class="mb-3" />

    <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
    </div>

    <div class="container">
         <div class="col-md-12"><h1 class="text-center">RoomName</h1>
                    <div v-for="track in trackArray" :key="track.name">
                        <p>{{track.name ?? "marche pas"}}</p>
                        <p>{{track.artist ?? "marche pas"}}</p>
                        <form @submit.prevent="submit">
                        <breeze-input id="uri" type="hidden" v-model="form.uri" value="{{track.uri}}" required/>
                        <breeze-button type="submit">yo</breeze-button>
                        </form>
                        <p>{{track.image ?? "marche pas"}}</p>
                    </div>
            </div>
    </div>
  </div>
</template>

<script>
import BreezeButton from '@/Components/Button.vue'
import BreezeInput from '@/Components/Input.vue'
import BreezeLabel from '@/Components/Label.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { Head, Link } from '@inertiajs/inertia-vue3'
export default {
  components: {
    Head,
    BreezeButton,
    BreezeInput,
    BreezeLabel,
    BreezeValidationErrors,
    Link,
  },
  props: [
      "trackArray",
  ],
  methods: {
      submit() {
      this.form
          .post(this.route('addMusic'))
      }
  },
  data() {
    return {
      form: this.$inertia.form({
        uri: '',
      })
    }
  }
}
</script>
