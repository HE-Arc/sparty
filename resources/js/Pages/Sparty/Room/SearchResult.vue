<template>
  <Head title="test" />
  <div class="card-body">
    <div class="container">
         <div class="col-md-12"><h1 class="text-center">RoomName</h1>
                <div v-for="track in trackArray" :key="track.name">
                    <form @submit.prevent="submit(track)">
                        <p>{{track.name ?? "marche pas"}}</p>
                        <p>{{track.artist ?? "marche pas"}}</p>
                        <breeze-input-binding type="text" v-model="track.uri"></breeze-input-binding>
                        <breeze-button v-on:click="submit" type="submit">add music to playlist</breeze-button>
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
import BreezeInputBinding from '@/components/sparty/InputBinding.vue'
import { Head, Link } from '@inertiajs/inertia-vue3'
export default {
  components: {
    Head,
    BreezeButton,
    BreezeInput,
    BreezeLabel,
    BreezeValidationErrors,
    Link,
    BreezeInputBinding,
  },
  props: [
      "trackArray"
  ],
   data() {
    return {
      form: this.$inertia.form({
          uri: ''
      })
    }
  },
  methods: {

    submit(track) {
    this.form.uri = track.uri;
    this.form
          .post(this.route('addMusic'))
      }
  }
}
</script>
