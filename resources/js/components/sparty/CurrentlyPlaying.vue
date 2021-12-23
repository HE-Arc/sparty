<template>
    <div class="col-md-8">
        <h2>currently: {{ currentPlaying['name'] }}</h2>
        <h3>{{ currentPlaying['artist'] }}</h3>
        <img :src="currentPlaying['image']" :alt="currentPlaying['name']"/>
    </div>
</template>

<script>
    import { defineComponent } from 'vue'

	export default defineComponent({
		props: [
            'currentPlaying'
        ]
	})
</script>
