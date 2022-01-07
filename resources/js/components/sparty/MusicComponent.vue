<template>
    <p class="text-uppercase lead text-muted">
        {{ currentPlaying['name'] }},
        {{ currentPlaying['artist'] }}
    </p>
   <div class="card mb-4 box-shadow">
        <img :style="style" :class="class" :src="currentPlaying['image']" :alt="currentPlaying['name']"/>
    </div>
</template>

<script>
    import { defineComponent } from 'vue'

	export default defineComponent({
		props: {
            currentPlaying : String,
            style: {
                style : String,
                default: 'height: 300px; width: 100%;'
            },
            class: {
                class: String,
                default: 'card-img-top',
            }
        }
	})
</script>
