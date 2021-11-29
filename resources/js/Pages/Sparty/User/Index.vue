<template>
  <Head title="User Page" />
  <nav-bar></nav-bar>
  <h1>User</h1>

    <div class="container-md">
        <form>
            <div class="form-group mb-3">
                <label for="username" class="titleLabel">Username : </label>
                <input id="username" type="text">
                <button class="btn btn-dark">Modifiy</button>
            </div>
        </form>
        <form>
            <div class="form-group mb-3">
                <label for="password" class="titleLabel">Password : </label>
                <input name="password">
                <button class="btn btn-dark">Modifiy</button>
            </div>
        </form>
        <form>
            <div class="form-group mb-3">
                <label for="spotify" class="titleLabel">Spotify : </label>
                <button class="btn btn-dark" type="button">CONNECT</button>
            </div>
        </form>
        <breeze-button type="reset" class="ms-4">
            Log in
        </breeze-button>
    </div>

</template>

<script>
import { Head, Link } from "@inertiajs/inertia-vue3";
import BreezeButton from "@/Components/Button.vue";
import NavBar from "@/components/sparty/NavBar.vue";

export default {
  components: {
    Head,
    Link,
    BreezeButton,
    NavBar
  },

  props: [
      "user"
  ],
};
</script>
