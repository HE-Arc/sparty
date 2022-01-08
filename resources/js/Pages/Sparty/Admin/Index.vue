<template>
  <Head title="Admin"/>
  <NavBar :username="username"/>

  <div v-if="status" class="alert alert-warning mb-3 rounded-0" role="alert">
    {{status}}
  </div>

  <div class="container">
  <div class="p-3">
  <div class="row g-5">
  <div class="col-md-6">

  <div class="card">
  <div class="card-header">
    <h2>{{roomName}} administration</h2>
  </div>

  <div class="card-body">

  <div class="row">

  <div class="col-auto">
  <form v-if="canJoin" @submit.prevent="lockRoom(canJoin)" class="mb-3">
    <breeze-button type="submit" class="btn btn-primary">Lock room</breeze-button>
  </form>

  <form v-else @submit.prevent="lockRoom(canJoin)" class="mb-3">
    <breeze-button type="submit">Unlock room</breeze-button>
  </form>
  </div>

  <div class="col-auto">
  <form @submit.prevent="playPlaylist()" class="mb-3">
    <breeze-button type="submit">Play music</breeze-button>
  </form>
  </div>

  <div class="col-auto">
  <form @submit.prevent="deleteRoom()" class="mb-3">
    <breeze-button type="submit">Delete room</breeze-button>
  </form>
  </div>

  <div class="col-auto">
  <Link href="/room" as="button" class="btn btn-dark text-uppercase mb-3">Back to room</Link>
  </div>

  </div>

  <hr>

  <form @submit.prevent="addAdmin()" class="mb-3">
    <label for="username">Username</label>
    <breeze-input type="text" id="username" v-model="formAdmin.username" required autocomplete="username"/>
    <br>
    <breeze-button type="submit">Add admin</breeze-button>
  </form>

  </div>
  </div>
  </div>

  <div class="col-md-6">

  <div v-for="track in nextTracks" :key="track.uri">
    <div class="card mb-3">
    <div class="card-header">
      <h3>{{track.name}}</h3>
      <h4>{{track.artist}}</h4>
    </div>

    <div class="card-body">

    <form @submit.prevent="deleteTrack(track)" class="mb-3">
      <breeze-button type="submit">Delete track</breeze-button>
    </form>

    <form v-if="track.guest_name != ''" @submit.prevent="banUser(track)" class="mb-3">
      <h4>User: {{track.guest_name}}</h4>
      <breeze-button type="submit">Ban user</breeze-button>
    </form>

    </div>
    </div>
    </div>
  </div>

  </div>
  </div>
  </div>
</template>

<script>
import {Inertia} from '@inertiajs/inertia';
import {Head, Link} from "@inertiajs/inertia-vue3"
import BreezeButton from "@/Components/Button.vue"
import BreezeInput from "@/Components/Input.vue"
import BreezeLabel from '@/Components/Label.vue'
import BreezeNavLink from '@/Components/NavLink.vue'
import NavBar from "@/components/sparty/NavBar.vue"

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
        'nextTracks',
        'canJoin',
        'username'
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
        },

        lockRoom(canJoin) {
            this.formLock.lock = canJoin;
            this.formLock.post(this.route('lockRoom'));
        },

        playPlaylist() {
            this.formDelete.post(this.route('playPlaylist'));
        },

        deleteRoom() {
            this.formDelete.post(this.route('deleteRoom'));
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

            formLock: this.$inertia.form({
                lock: false
            })
        }
    }
};
</script>
