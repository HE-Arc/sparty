<template>
  <button :type="type" :class="class">
    <slot></slot>
  </button>
</template>

<script>
  import { defineComponent } from 'vue'

  export default defineComponent({
    props: {
      type: {
        type: String,
        default: 'submit',
      },
      class: {
          class: String,
          default: 'btn btn-dark text-uppercase',
      }
    }
  })
</script>
