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
            <form>
                <div class="form-group mb-3">
                    <label for="username" class="titleLabel">Username : </label>
                    <input id="username" type="text" class="mx-3">
                    <breeze-button type="submit">Modifiy</breeze-button>
                </div>
            </form>
            <form @submit.prevent="modifyPassword">

                <div class="mb-3">
                    <breeze-label for="password" value="Password" />
                    <breeze-input id="password" type="password" />
                </div>

                <div class="mb-3">
                    <breeze-label for="password_confirmation" value="Confirm Password" />
                    <breeze-input id="password_confirmation" type="password" />
                </div>
            </form>
            <form @submit.prevent="submit">
                <div class="form-group mb-3">
                    <label for="spotifyUsername" class="titleLabel">Spotify : </label>
                    <label id="spotifyUsername" class="titleLabel mx-3">{{spotifyUsername}}</label>
                    <breeze-button type="submit">Connection</breeze-button>
                </div>
            </form>
        </div>
    </div>

</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3";
import BreezeButton from "@/Components/Button.vue";
import BreezeLabel from '@/Components/Label.vue'
import NavBar from "@/components/sparty/NavBar.vue";
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
      status : String
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
