<template>
<v-fade-transition>
    <div v-if="show" :style="styling" />
</v-fade-transition>
</template>

<script>
export default {
    computed: {
        show () {
            return this.$root.settings?.night ? true : false
        },
        styling () {
            return [
                'position: fixed',
                'z-index: 500',
                'width: 100vw',
                'height: 100vh',
                'pointer-events: none',
                'background-color: rgb(255,190,10)',
                'opacity: ' + 0.15
            ].join(';\n')
        }
    }
}
</script>
