<template>
  <Head title="Welcome" />

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
