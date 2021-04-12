<template>
<div>
    
    <v-dialog 
        persistent 
        scrollable 
        :value="active"        
        :fullscreen="$root.child_dialog.fullscreen || !$vuetify.breakpoint.mdAndUp" 
        :max-width="$root.child_dialog.width"
    >
        <v-card tile>

            <!-- System Header -->
            <dialogbartop
                icon="preview"
                :text="$root.label('details') + ':&ensp;' + $handlers.format.cn_entity(entity, id)"
                v-on:close="$emit('close')"
            ></dialogbartop>

            <v-card-text class="pa-0">
                <detailscontent
                    :entity="entity"
                    :id="id"
                    show_header
                    v-on:details="$emit('close')"
                ></detailscontent>
            </v-card-text>

            <!-- Footer -->
            <v-card-actions class="pa-0 ma-0">
                <v-btn 
                    tile 
                    depressed 
                    block 
                    color="blue_prim" 
                    v-text="$root.label('close')" 
                    @click="$emit('close')"
                ></v-btn>
            </v-card-actions>

        </v-card>
    </v-dialog>
</div>
</template>



<script>

import detailscontent from './detailsContent.vue'

export default {

    components: {
        detailscontent
    },

    data () {
        return {}       
    },

    props: {
        entity:         { type: String },
        id:             { type: [Number, String] },
        publisher:      { type: Boolean, default: false }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },
        
        active () { return this.entity ? true : false }
    }
}

</script>