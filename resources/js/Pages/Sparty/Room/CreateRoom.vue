<template>
  <Head title="Create room" />
  <div class="card-body">

    <breeze-validation-errors class="mb-3" />

    <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div class="col-mb-12">
        <div class="form-group mb-12">
            <h1 class="text-center">Room Name</h1>
            <breeze-label for="roomname" value="Roomname" />
            <breeze-input id="roomname" type="text" v-model="form.roomname" required autofocus autocomplete="roomname" />
            <breeze-label for="password" value="Password" />
            <breeze-input id="password" type="password" v-model="form.password" required autocomplete="password" />
            <breeze-label for="password_confirmation" value="Confirm Password" />
            <breeze-input id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password" />
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
import { Head, Link } from '@inertiajs/inertia-vue3'
export default {
  components: {
    Head,
    BreezeButton,
    BreezeInput,
    BreezeLabel,
    BreezeValidationErrors,
    Link,
  },
  props: {
      status: String
  },
  data() {
    return {
      form: this.$inertia.form({
        roomname: '',
        password: '',
        password_confirmation: '',
      })
    }
  },
  methods: {
      submit() {
      this.form
          .post(this.route('room.store'), {
              onSuccess: () => this.form.reset('roomname'),
          })
      }
  }
}
</script>
