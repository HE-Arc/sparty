<template>
  <Head title="Search" />
  <NavBar :username="username"/>

  <div class="card-body">
    <div class="container">
         <div class="row">
            <div class="col-md-12">
                <h1 class="text-center">{{ roomname }}</h1>
                <div class="row">
                    <div v-for="track in trackArray" :key="track.name" class="col-md-4">
                        <form @submit.prevent="submit(track)">
                            <p class="text-uppercase">
                                {{track.name ?? "Unknown"}},
                                {{track.artist ?? "Unknown"}}
                            </p>
                            <breeze-input type="hidden" required v-model="track.uri"/>
                            <breeze-button v-on:click="submit" type="submit"><img :src="track.image" :alt="track.name"/></breeze-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import BreezeButton from '@/Components/Button.vue'
import BreezeInput from '@/Components/Input.vue'
import { Head, Link } from '@inertiajs/inertia-vue3'
import NavBar from '@/Components/Sparty/NavBar.vue'

export default {
  components: {
    Head,
    BreezeButton,
    BreezeInput,
    Link,
    NavBar
  },
  props: [
      'trackArray',
      'roomname',
      'username'
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
