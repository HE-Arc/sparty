<template>
  <nav-bar :username="username"></nav-bar>
  <Head title="Register" />

  <div class="card-body">

    <breeze-validation-errors class="mb-3" />

    <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div class="mb-3">
        <breeze-label for="username" value="Username" />
        <breeze-input id="username" type="text" v-model="form.username" required autofocus autocomplete="username" />
      </div>

      <div class="mb-3">
        <breeze-label for="password" value="Password" />
        <breeze-input id="password" type="password" v-model="form.password" required autocomplete="new-password" />
      </div>

      <div class="mb-3">
        <breeze-label for="password_confirmation" value="Confirm Password" />
        <breeze-input id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password" />
      </div>

      <div class="mb-0">
        <div class="d-flex justify-content-end align-items-baseline">
          <Link :href='route("user.index")' class="text-muted me-3 text-decoration-none">
            Already registered?
          </Link>

          <breeze-button class="ms-4" :class="{ 'text-white-50': form.processing }" :disabled="form.processing">
            <div v-show="form.processing" class="spinner-border spinner-border-sm" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>

            Register
          </breeze-button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import BreezeButton from '@/Components/Button.vue'
import BreezeCheckbox from "@/Components/Checkbox.vue";
import BreezeGuestLayout from "@/Layouts/Guest.vue"
import BreezeInput from '@/Components/Input.vue'
import BreezeLabel from '@/Components/Label.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { Head, Link } from '@inertiajs/inertia-vue3'
import NavBar from '@/components/sparty/NavBar.vue'

export default {
  layout: BreezeGuestLayout,

  components: {
    Head,
    BreezeButton,
    BreezeCheckbox,
    BreezeInput,
    BreezeLabel,
    BreezeValidationErrors,
    Link,
    NavBar,
  },

  props: {
    username : String,
    status: String
  },

  data() {
    return {
      form: this.$inertia.form({
        username: '',
        password: '',
        password_confirmation: '',
      })
    }
  },

  methods: {
      submit() {
      this.form
          .post(this.route('user.store'), {
              onSuccess: () => this.form.reset('password'),
          })
      }
  }
}
</script>
