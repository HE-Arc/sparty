<template>
  <nav-bar></nav-bar>
  <Head title="User Page" />

  <h1 class="text-center">{{username}}</h1>

    <div class="text-center">
        <div class="container-md">
            <form>
                <div class="form-group mb-3">
                    <label for="username" class="titleLabel">Username : </label>
                    <input id="username" type="text" class="mx-3">
                    <button class="btn btn-dark">Modifiy</button>
                </div>
            </form>
            <form>
                <div class="form-group mb-3">
                    <label for="password" class="titleLabel">Password : </label>
                    <input id="password" type="password" class="mx-3">
                    <button class="btn btn-dark">Modifiy</button>
                </div>
            </form>
            <form>
                <div class="form-group mb-3">
                    <label for="spotify" class="titleLabel">Spotify : </label>
                    <input id="spotify" type="text" class="mx-3">
                    <button class="btn btn-dark" type="button">CONNECT</button>
                </div>
            </form>
        </div>
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
      "username"
  ],
};
</script>
