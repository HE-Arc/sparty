<template>
  <Head title="Room" />
  <div class="card-body">
    <breeze-validation-errors class="mb-3" />

    <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
    </div>
        <div class="row">
            <h1 class="text-center">{{ roomname }}</h1>
            <h1>{{ trackname }}</h1>
            <div class="col-md-12"><h1 class="text-center">RoomName</h1>
            </div>
            <div class="col-md-12">
                <div class="col-md-12">
                    <h2 class="text-center">Search bar</h2>
                    <form @submit.prevent="submit">
                        <div class="form-group mb-3">
                            <breeze-label for="search" class="searchLabel">Search bar : </breeze-label>
                            <breeze-input id="search" type="text" v-model="form.search" required autofocus autocomplete="search">Search</breeze-input>
                            <breeze-button type="submit">Search</breeze-button>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-md-8"><h2>currently</h2></div>
                    <div class="col-md-4"><h2>button zone</h2></div>
                </div>
            </div>
        </div>
    </div>
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
    methods: {
      submit() {
      this.form
          .get(this.route('search'))
      }
  },
   props: {
      trackname: String,
      roomname: String,
   },
   data() {
    return {
      form: this.$inertia.form({
        search: '',
      })
    }
  }
};
</script>
