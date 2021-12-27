<template>
  <Head title="Admin"/>
  <NavBar/>

  <div v-for="track in nextTracks" :key="track.uri">
    <form @submit.prevent="deleteTrack(track)">
      <h1>{{track.name}}</h1>
      <h2>{{track.artist}}</h2>
      <breeze-input type="hidden" required v-model="track.uri"/>
      <breeze-button v-on:click="submit" type="submit">Delete track</breeze-button>
    </form>

    <form v-if="track.guest_name != ''" @submit.prevent="banUser(track)">
      <h2>{{track.guest_name}}</h2>
      <breeze-input type="hidden" required v-model="track.guest_id"/>
      <breeze-button v-on:click="submit" type="submit">Ban user</breeze-button>
    </form>
  </div>
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
        }
    },

    data() {
        return {
            formDelete: this.$inertia.form({
                uri: ''
            }),

            formBan: this.$inertia.form({
                guest_id: ''
            })
        }
    }
};
</script>