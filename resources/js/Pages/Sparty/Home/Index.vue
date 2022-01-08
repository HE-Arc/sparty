<template>
  <Head title="Sparty"/>
  <NavBar :username="username"/>

  <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
  </div>

  <div class="px-4 py-5 my-5 text-center">

  <h1 class="display-5 fw-bold">Welcome to Sparty!</h1>

  <div class="col-lg-6 mx-auto">

  <p class="lead mb-4">
    Sparty is a cool way to listen to music with your friend.
    Just connect your Spotify account, create a room and share it!
  </p>

  <Link href="/joinRoom" as="button" class="btn btn-primary btn-lg px-4 gap-3">Join a room</Link>

  </div>
  </div>
</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3"
import BreezeButton from "@/Components/Button.vue"
import BreezeLabel from '@/Components/Label.vue'
import NavBar from "@/components/sparty/NavBar.vue"
import BreezeInput from '@/Components/Input.vue'
import { Inertia } from '@inertiajs/inertia'

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
      status: String
  },
};
</script>
