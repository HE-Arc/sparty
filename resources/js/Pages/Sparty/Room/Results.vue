<template>
  <Head title="Results" />
<h1>Results</h1>
{{trackname}}
</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3";
import BreezeButton from "@/Components/Button.vue";
import BreezeInput from "@/Components/Input.vue";
import BreezeLabel from '@/Components/Label.vue';

export default {
  components: {
    Head,
    Link,
    BreezeButton,
    BreezeInput,
    BreezeLabel
  },
  props: [
      'trackname'
  ]
};
</script>
