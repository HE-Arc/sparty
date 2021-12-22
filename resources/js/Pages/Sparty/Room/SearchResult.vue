<template>
  <Head title="Search" />
  <div class="card-body">
    <div class="container">
         <div class="col-md-12"><h1 class="text-center">{{ roomname }}</h1>
            <div v-for="track in trackArray" :key="track.name">
                <form @submit.prevent="submit(track)">
                    <h2>{{track.name ?? "Unknown"}}</h2>
                    <h2>{{track.artist ?? "Unknown"}}</h2>
                    <breeze-input type="hidden" required v-model="track.uri"/>
                    <breeze-button v-on:click="submit" type="submit"><img :src="track.image" :alt="track.name"/></breeze-button>
                </form>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import BreezeButton from '@/Components/Button.vue'
import BreezeInput from '@/Components/Input.vue'
import { Head, Link } from '@inertiajs/inertia-vue3'
export default {
  components: {
    Head,
    BreezeButton,
    BreezeInput,
    Link,
  },
  props: [
      'trackArray',
      'roomname',
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
