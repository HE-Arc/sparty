<template>
  <Head title="Room" />
<div>
    <breeze-application-logo width="80" />
    <h1>Sparty</h1>
    <button type="button" class="btn btn-danger">Danger</button>
</div>
  <div>
    <div v-for="user in users" :key="user.id">
        <p>{{user.username}}</p>
    </div>
  </div>
</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3";

export default {
  components: {
    Head,
    Link
  },

  props: [
    "users"
  ]
};
</script>
