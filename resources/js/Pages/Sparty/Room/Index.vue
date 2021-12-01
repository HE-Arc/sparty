<template>
  <Head title="Room" />
<div class="card">
    <div class="card-header" id="hide">
        <div>
            <breeze-application-logo width="80" />
        </div>
    </div>
   <div class="container">
        <div class="row">
            <div class="col-md-12"><h1 class="text-center">RoomName</h1></div>
            <div class="col-md-12">
                <div class="col-md-12">
                    <h2 class="text-center">Search bar</h2>
                    <form>
                        <div class="form-group mb-3">
                            <label for="search" class="searchLabel">Search bar : </label>
                            <breeze-input id="search" type="text" v-model="form.search" required autofocus autocomplete="search">Search</breeze-input>
                            <breeze-button type="submit" class="ms-4">
                                Search
                            </breeze-button>
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
