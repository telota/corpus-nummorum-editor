<template>
<v-fade-transition>
    <div
        id="app-snack"
        v-if="snack.message"
        class="d-flex justify-center align-center"
    />
</v-fade-transition>
</template>


<script>

export default {
    computed: {
        snack () { return this.$root.snack }
    }
}

</script>

<style scoped>
    #app-snack {
        position: fixed;
        pointer-events: none;
        cursor: default;
        width: 50%;
        top: 0;
        left: 0;
        right: 0;
        height: 40px;
        z-index: 101;
    }
</style>
