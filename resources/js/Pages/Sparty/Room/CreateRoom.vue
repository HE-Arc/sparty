<template>
  <Head title="Create room" />
  <div class="card-body">

    <breeze-validation-errors class="mb-3" />

    <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
    </div>

    <form @submit.prevent="submit(disabledVote)">
      <div class="col-mb-12">
        <div class="form-group mb-12">
            <h1 class="text-center">Room Name</h1>
            <breeze-label for="roomname" value="Roomname :" />
            <breeze-input id="roomname" type="text" v-model="form.roomname" required autofocus/>
            <breeze-label for="password" value="Password :" />
            <breeze-input id="password" type="password" v-model="form.password" required />
            <breeze-label for="password_confirmation" value="Confirm Password :" />
            <breeze-input id="password_confirmation" type="password" v-model="form.password_confirmation" required />

            <div class="col-mb-12">
                <breeze-label for="enabledVote" value="enabled vote"/>
                <input id="enabledVote" type="radio" v-model="showFirst" value="true" />
                <breeze-label for="enabledVote" value="disabled vote"/>
                <input id="enabledVote" type="radio" v-model="showFirst" value="false" />

                <div v-if="showFirst === 'true'" class="col-mb-6">
                    <breeze-label for="vote_max" value="Vote :" />
                    <breeze-input id="vote_max" type="number" v-model="form.vote" required/>
                </div>
                <div v-else>
                    <breeze-input id="vote_max" type="number" v-model="disabledVote" hidden disabled required/>
                </div>
            </div>
            <breeze-button type="submit">Create room</breeze-button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import BreezeButton from '@/Components/Button.vue'
import BreezeInput from '@/Components/Input.vue'
import BreezeLabel from '@/Components/Label.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import BreezeCheckbox from '@/Components/Checkbox.vue'
import { Head, Link } from '@inertiajs/inertia-vue3'
export default {
  components: {
    Head,
    BreezeButton,
    BreezeInput,
    BreezeLabel,
    BreezeValidationErrors,
    BreezeCheckbox,
    Link,
  },
  props: {
      status: String
  },
  data() {
    return {
        showFirst: false,
        disabledVote: -1,
        form: this.$inertia.form({
            roomname: '',
            password: '',
            password_confirmation: '',
            vote: 1
      })
    }
  },
  methods: {
      submit(disabledVote) {
            if(this.showFirst == false)
            {
                this.form.vote = disabledVote;
            }
            this.form
            .post(this.route('room.store'), {
                onSuccess: () => this.form.reset('roomname'),
        })
      }
  }
}
</script>
