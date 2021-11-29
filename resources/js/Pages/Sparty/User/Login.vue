<template>
  <Head title="Log in" />
  <nav-bar></nav-bar>

  <div class="card-body">

    <breeze-validation-errors class="mb-3" />

    <div v-if="status" class="alert alert-danger mb-3 rounded-0" role="alert">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div class="mb-3">
        <breeze-label for="username" value="Username" />
        <breeze-input id="username" type="text" v-model="form.username" required autofocus />
      </div>

      <div class="mb-3">
        <breeze-label for="password" value="Password" />
        <breeze-input id="password" type="password" v-model="form.password" required autocomplete="current-password" />
      </div>

      <div class="mb-0">
        <div class="d-flex justify-content-end align-items-baseline">

          <breeze-button class="ms-4" :class="{ 'text-white-50': form.processing }" :disabled="form.processing">
            <div v-show="form.processing" class="spinner-border spinner-border-sm" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>

            Log in
          </breeze-button>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import BreezeButton from '@/Components/Button.vue'
import BreezeGuestLayout from "@/Layouts/Guest.vue"
import BreezeInput from '@/Components/Input.vue'
import BreezeCheckbox from '@/Components/Checkbox.vue'
import BreezeLabel from '@/Components/Label.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import NavBar from '@/components/sparty/NavBar.vue'
import { Head, Link } from '@inertiajs/inertia-vue3'

export default {
  layout: BreezeGuestLayout,

  components: {
    Head,
    BreezeButton,
    BreezeInput,
    BreezeCheckbox,
    BreezeLabel,
    BreezeValidationErrors,
    Link,
    NavBar,
  },

  props: {
    status: String
  },

  data() {
    return {
      form: this.$inertia.form({
        username: '',
        password: '',
        remember: false
      })
    }
  },

  methods: {
    submit() {
      this.form
          .post(this.route('checkLogin'), {
            onSuccess: () => this.form.reset('password'),
          })
    }
  }
}
</script>
