<template>
  <Head title="Admin"/>
  <NavBar/>

  <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{status}}
  </div>

  <div v-for="track in nextTracks" :key="track.uri">
    <form @submit.prevent="deleteTrack(track)">
      <h1>{{track.name}}</h1>
      <h2>{{track.artist}}</h2>
      <breeze-button type="submit">Delete track</breeze-button>
    </form>

    <form v-if="track.guest_name != ''" @submit.prevent="banUser(track)">
      <h2>{{track.guest_name}}</h2>
      <breeze-button type="submit">Ban user</breeze-button>
    </form>
  </div>

  <form @submit.prevent="addAdmin()">
      <label for="username">Username</label>
      <breeze-input type="text" id="username" v-model="formAdmin.username" required autocomplete="username"/>
      <breeze-button type="submit">Add admin</breeze-button>
  </form>
</template>

<script>
import {Inertia} from '@inertiajs/inertia';
import {Head, Link} from "@inertiajs/inertia-vue3";
import BreezeButton from "@/Components/Button.vue";
import BreezeInput from "@/Components/Input.vue";
import BreezeLabel from '@/Components/Label.vue';
import BreezeNavLink from '@/Components/NavLink.vue'
import NavBar from '@/components/sparty/NavBar.vue'

export default {
    components: {
        Head,
        Link,
        BreezeButton,
        BreezeInput,
        BreezeLabel,
        BreezeNavLink,
        NavBar
    },

    props: [
        'status',
        'roomName',
        'nextTracks'
    ],

    methods: {
        deleteTrack(track) {
            this.formDelete.uri = track.uri;
            this.formDelete.post(this.route('deleteTrack'));
        },

        banUser(track) {
            this.formBan.guest_id = track.guest_id;
            this.formBan.post(this.route('banGuest'));
        },

        addAdmin() {
            this.formAdmin.post(this.route('addAdmin'));
        }
    },

    data() {
        return {
            formDelete: this.$inertia.form({
                uri: ''
            }),

            formBan: this.$inertia.form({
                guest_id: ''
            }),

            formAdmin: this.$inertia.form({
                username: ''
            }),
        }
    }
};
</script>