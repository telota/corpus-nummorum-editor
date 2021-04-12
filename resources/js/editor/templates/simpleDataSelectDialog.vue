<template>
    <v-dialog 
        :value="active" 
        persistent
        scrollable
        :fullscreen="!width && ($root.child_dialog.fullscreen || !$vuetify.breakpoint.mdAndUp)" 
        :max-width="width ? width : $root.child_dialog.width"
    >
        <v-card tile class="app_bg">

            <!-- System Header -->
            <dialogbartop
                :icon="icon"
                :text="text"
                :fullscreen="!width"
                v-on:close="$emit('close')"
            ></dialogbartop>

            <!-- Content -->
            <v-card-text class="mt-5">
                <slot name="content"></slot>
            </v-card-text>

            <!-- Actions -->
            <v-card-actions>
                <div style="width: 100%">
                    <slot name="actions"></slot>
                </div>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    data () {
        return {

        }
    },

    props: {
        active:     { type: Boolean, default: false },
        icon:       { type: String, default: 'launch' },
        text:       { type: String, default: 'Dialog' },
        width:      { type: Number, default: 0 }
    },
    
}
</script>