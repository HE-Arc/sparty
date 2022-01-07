<template>
  <Head title="Room" />
  <NavBar/>

  <div class="card-body">
   <breeze-validation-errors class="mb-3" />

    <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
    </div>
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center text-uppercase">{{ roomname }}</h1>
            </div>
        </div>
        <div class="py-1 text-center container">
            <div class="row py-lg-1">
                <div class="col-md-12">
                    <p class="lead text-muted">
                        Explore and add a music of your choice in the room with the search bar
                    </p>
                    <form @submit.prevent="submit">
                        <div class="form-group">
                            <breeze-label for="search" class="fw-light">Search bar : </breeze-label>
                            <breeze-input id="search" class="form-control form-control-lg" type="text" v-model="form.search" required placeholder="title of music or name of artist..." autofocus autocomplete="search">Search</breeze-input>
                            <CustomButton type="submit" class="btn btn-primary my-2">Search</CustomButton>
                        </div>
                    </form>
                    <div class="col-md-2 btn-group justify-content-between">
                        <Link v-if="isAdmin" href="/admin" as="button" class="btn btn-danger">Admin</Link>
                        <div v-if="currentPlaying != ''">
                            <button v-if="canVote" @click="voteSkip(currentPlaying)" class="btn btn-secondary">Vote skip</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="currentPlaying != ''" class="album py-3">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <h3>Currently:</h3>
                        <MusicComponent :currentPlaying="currentPlaying" ></MusicComponent>
                    </div>
                    <div v-if="nextTrack[1] != ''" class="col-md-4">
                        <h3>Next track 1:</h3>
                        <MusicComponent :currentPlaying="nextTrack[1]" ></MusicComponent>
                    </div>
                    <div v-if="nextTrack[2] != ''" class="col-md-4">
                        <h3>Next track 2:</h3>
                        <MusicComponent :currentPlaying="nextTrack[2]" ></MusicComponent>
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
import BreezeLabel from '@/Components/Label.vue';
import { Inertia } from '@inertiajs/inertia';
import BreezeNavLink from '@/Components/NavLink.vue'
import MusicComponent from '@/components/sparty/MusicComponent.vue'
import NavBar from '@/components/sparty/NavBar.vue'
import CustomButton from '@/components/sparty/CustomButton.vue'

export default {
  components: {
    Head,
    Link,
    BreezeButton,
    BreezeInput,
    BreezeLabel,
    BreezeNavLink,
    MusicComponent,
    NavBar,
    CustomButton
  },
    methods: {
        voteSkip(currentPlaying){
            Inertia.visit(route('vote'), { method: 'post', data: {currentPlaying: currentPlaying, }, });
        },
        submit(){
            this.form
                .get(this.route('search'))
        }
    },
    props : [
        'status',
        'trackname',
        'roomname',
        'currentPlaying',
        'nextTrack',
        'roomid',
        'isAdmin',
        'canVote'
    ],
   data() {
    return {
      form: this.$inertia.form({
        search: '',
      })
    }
  }
};
</script>
