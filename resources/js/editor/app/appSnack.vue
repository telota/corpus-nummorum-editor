<template>
<v-fade-transition>
    <div
        id="app-snack"
        v-if="snack.message"
        class="d-flex justify-center align-center"
    >
        <!--<v-card
            tile
            :class="(snack.color ? snack.color : 'sysbar') + ' mt-1 pa-2 pl-7 pr-7 body-1'"
            style="z-index: 101;"
            @click="$root.snack = { color: null, message: null }"
            v-html="snack.message"
        />-->
    </div>
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
        width: 50%;
        top: 0;
        left: 0;
        right: 0;
        height: 40px;
        z-index: 101;
    }
</style>
