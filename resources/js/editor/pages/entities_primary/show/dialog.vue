<template>
<div>
    <dialog-template
        v-if="show"
        dialog
        small
        :text="'cn ' + entity.slice(0, -1) + ' ' + id"
        v-on:close="$store.commit('setDetailsDialog')"
    >
        <div class="app_bg">
        <details-content
            dialog
            :entity="entity"
            :id="id"
        />
        </div>
    </dialog-template>

</div>
</template>


<script>

import detailsContent from './index.vue'

export default {
    components: {
        detailsContent
    },

    computed: {
        language () {
            return this.$root.language
        },

        show () {
            return this.$store.state.detailsDialog.show
        },

        entity () {
            return this.$store.state.detailsDialog.entity
        },

        id () {
            return this.$store.state.detailsDialog.id
        }
    },

    watch: {
        $route (to, from) {
            this.$store.commit('setDetailsDialog')
        }
    },

    methods: {
        close () {
            this.show = false
        }
    }
}
</script>
