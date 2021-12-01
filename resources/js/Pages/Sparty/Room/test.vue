<template>
  <Head title="Test" />
<div class="card">
    <div class="card-header" id="hide">
        <div>
            <breeze-application-logo width="80" />
        </div>
    </div>
   <div class="container">

  </div>
</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3";
import BreezeButton from "@/Components/Button.vue";
import BreezeInput from "@/Components/Input.vue";

export default {
  components: {
    Head,
    Link,
    BreezeButton,
    BreezeInput
  },

  props: [
    "users"
  ],

   data() {
    return {
      form: this.$inertia.form({
        search: '',
        remember: false
      })
    }
  }
};
</script>
