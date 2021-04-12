<template>
<div class="d-flex component-wrap align-center" style="width: 100%">
    
    <div class="d-flex component-wrap align-center" style="width: 100%">
        <!-- Label -->
        <div
            v-if="label"
            class="title text-uppercase"
            style="white-space: nowrap"
            v-html="header"
        ></div>        
        <inheritButton
            v-if="inheritor.vif"
            :disabled="inheritor.disabled"
            :inherited="inheritor.status"
            no_margin_top
            v-on:click="$emit('inherit')"
        ></inheritButton>
        <v-divider 
            v-if="!loading" 
            class="ml-4"
        ></v-divider>
        <v-progress-linear 
            v-else 
            indeterminate 
            class="ml-4"
        ></v-progress-linear>
    </div>

    <!-- Add Button -->
    <v-btn
        v-if="add !== null"
        fab
        x-small
        depressed
        color="blue_prim"
        class="ml-3"
        :disabled="add === false || inheritor.status === 1"
        @click="$emit('add', true)"
    >
        <v-icon>exposure_plus_1</v-icon>
    </v-btn>

</div>
</template>


<script>

import inheritButton from './../pages/entities_primary/modules/inheritButton.vue'

export default {
    components: {
        inheritButton
    },

    props: {
        label:      { type: String },
        count:      { type: Array },
        loading:    { type: Boolean, default: false },
        add:        { type: Boolean, default: null },
        inherited:  { type: Object }
    },

    computed: {
        header () { 
            return this.label + this.counter() 
        },

        inheritor () {
            if (this.inherited === undefined) {
                return { 
                    vif: false, 
                    disabled: true, 
                    status: 0 
                }
            }
            else {
                return this.inherited
            }
        }
    },
    
    methods: {
        counter () {
            if (this.count === undefined) { 
                return '' 
            }
            else { 
                return this.$handlers.format.counter(this.count) 
            }
        }
    }
}

</script>