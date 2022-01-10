<template>
  <nav-bar :username="username"></nav-bar>
  <Head title="User Page" />

  <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
  </div>

  <h1 class="text-center">{{username}}</h1>

    <div class="text-center">

        <breeze-validation-errors class="mb-3"/>

        <div class="container-md">
            <form @submit.prevent="submit">
                <div class="form-group mb-3">
                    <label for="spotifyUsername" class="titleLabel">Spotify : </label>
                    <label v-if="spotifyUsername" id="spotifyUsername" class="titleLabel mx-3">{{spotifyUsername}}</label>
                    <label v-else id="spotifyUsername" class="titleLabel mx-3">Not connected yet!</label>
                    <breeze-button v-if="spotifyUsername == null" type="submit">Connection</breeze-button>
                </div>
            </form>

            <Link v-if="spotifyUsername && !hasRoom" href="/createRoom" as="button" class="btn btn-primary btn-lg px-4 gap-3 mx-3">Create a room</Link>
            <Link v-else-if="hasRoom" href="/toMyRoom" as="button" class="btn btn-primary btn-lg px-4 gap-3 mx-3">Join my room</Link>            
        </div>
    </div>

</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3";
import BreezeButton from "@/Components/Button.vue";
import BreezeLabel from '@/Components/Label.vue'
import NavBar from "@/Components/Sparty/NavBar.vue";
import BreezeInput from '@/Components/Input.vue'
import { Inertia } from '@inertiajs/inertia';

export default {
  components: {
    Head,
    Link,
    BreezeButton,
    NavBar,
    BreezeLabel,
    BreezeInput,
  },

  props: {
      username : String,
      spotifyUsername : String,
      status : String,
      hasRoom : Boolean
  },

  methods: {
      submit() {
          Inertia.get('/connection', this.form)
      },
      modifyPassword() {
          Inertia.post('/modifyPassword', this.form)
      }
  }
};
</script>
