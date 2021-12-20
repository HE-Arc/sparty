<template>
  <Head title="test" />
  <div class="card-body">
    <div class="container">
         <div class="col-md-12"><h1 class="text-center">RoomName</h1>
            <div v-for="track in trackArray" :key="track.name">
                <p>{{track.name ?? "marche pas"}}</p>
                <p>{{track.artist ?? "marche pas"}}</p>
                <form @submit.prevent="submit">
                    <breeze-input id="uri" type="text" v-model="form.uri">{{ track.uri }}</breeze-input>
                    <breeze-button type="submit">yo</breeze-button>
                    <p>{{track.image ?? "marche pas"}}</p>
                    <p>{{ track.uri }}</p>
                </form>
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
      "trackArray"
  ],
   data() {
    return {
      form: this.$inertia.form({
        uri: '',
      })
    }
  },
  methods: {
      submit() {
      this.form
          .post(this.route('addMusic'), {
              onSuccess: () => this.form.reset('uri'),
          })
      }
  }
}
</script>
