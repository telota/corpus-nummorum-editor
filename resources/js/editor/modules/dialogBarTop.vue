<template>
<div>

    <v-card
        tile
        flat
        class="d-flex align-center"
        :class="color"
    >
        <!-- Left -->
        <v-icon 
            small
            class="ml-3 mr-2"
            v-text="icon"
        ></v-icon> 
        <span 
            class="caption" 
            v-html="text"
        ></span>

        <v-spacer></v-spacer>

        <v-btn
            small
            :tile="$vuetify.breakpoint.mdAndUp"
            :text="$vuetify.breakpoint.smAndDow || !fullscreen"
            depressed
            :disabled="$vuetify.breakpoint.smAndDow || !fullscreen"
            :class="color"
            @click="$root.child_dialog.fullscreen = !$root.child_dialog.fullscreen"
        >
            <v-icon small v-text="$root.child_dialog.fullscreen ? 'fullscreen_exit' : 'fullscreen'"></v-icon>
        </v-btn>
        <v-btn
            small
            tile
            depressed
            class="red darken-4"
            @click="$emit('close')"
        >
            <v-icon small v-text="'close'"></v-icon>
        </v-btn>
        <!-- Right
        <v-icon
            v-if="$vuetify.breakpoint.mdAndUp" 
            class="mr-3"
            v-text="$root.child_dialog.fullscreen ? 'fullscreen_exit' : 'fullscreen'"
            @click="$root.child_dialog.fullscreen = !$root.child_dialog.fullscreen"
        ></v-icon>
        <v-icon
            v-text="'close'"
            @click="$emit('close')"
        ></v-icon> -->
    </v-card>   

</div>
</template>



<script>

export default {    
    data () {
        return { }        
    },
    props: {
        color:  { type: String, default: 'grey_prim' },
        icon:   { type: String, default: 'launch' },
        text:   { type: String, default: 'Dialog' },
        fullscreen: { type: Boolean, default: true }
    },
}

</script>