<template>
  <Head title="Join room" />
  <div class="card-body">

    <h1 class="text-center">Join a room</h1>
    <breeze-validation-errors class="mb-3" />

    <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div class="col-mb-12">
        <div class="form-group mb-12">
            <breeze-label for="roomname" value="Roomname" />
            <breeze-input id="roomname" type="text" v-model="form.roomname" required autofocus autocomplete="roomname" />
            <breeze-label for="password" value="Password" />
            <breeze-input id="password" type="password" v-model="form.password" required autocomplete="password" />
            <breeze-button type="submit">Join a room</breeze-button>
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
      })
    }
  },
  methods: {
      submit() {
      this.form
          .post(this.route('checkRoom'), {
              onSuccess: () => this.form.reset('roomname'),
          })
      }
  }
}
</script>
