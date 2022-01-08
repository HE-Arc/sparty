<template>
<nav class="navbar navbar-expand-sm navbar-dark bg-success fs-4 fw-bolder">
  <div class="container-fluid">
    <a class="navbar-brand w-25" href="/">
    <img class="img-fluid" src="../../../asset/Sparty.svg" alt="sparty">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav ms-auto">
        <Link v-if="username" href="/user" class="nav-link text-white">UserPage</Link>
        <Link v-if="username" href="/logout" class="nav-link text-white">Logout</Link>
        <Link v-else href="/user" class="nav-link text-white">Login</Link>
      </div>
    </div>
  </div>
</nav>
</template>


<script>
import { Head, Link } from "@inertiajs/inertia-vue3"
import { Inertia } from '@inertiajs/inertia'
import { defineComponent } from 'vue'

export default {
  components: {
    Head,
    Link,
  },

  props: {
    username: String
  }
};
</script>
