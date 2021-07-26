<template>
    <search
        entity="types"
        :dialog="dialog"
        :select="select"
        :selected="selected"
        v-on:select="(emit) => { $emit('select', emit) }"
        v-on:close="$emit('close')"
    />
</template>


<script>

import search from './index.vue'

export default {
    components: {
        search,
    },

    props: {
        dialog:     { type: Boolean, default: false },
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: null },
    }
}

</script>
