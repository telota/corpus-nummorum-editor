<template>
<div>

    <!-- Full Screen -->
    <template v-if="!dialog">
        <!-- Slot -->
        <div>
            <slot />
        </div>
    </template>

    <!-- Dialog -->
    <template v-else>
        <v-fade-transition>
            <div
                v-if="show"
                class="dialog-overlay"
                :style="'z-index:' + ((zIndex - 1) + (small ? 10 : 0))"
            />
        </v-fade-transition>

        <v-scale-transition origin="center">
            <div
                v-if="show"
                class="component-dialog app_bg"
                :class="'component-dialog-outline' + ($vuetify.theme.dark ? '-dark' : '') + (small ? ' component-dialog-small' : '')"
                :style="'z-index:' + (zIndex + (small ? 10 : 0))"
            >
                <!-- Dialog Sysbar -->
                <div
                    class="d-flex align-center justify-space-between"
                    :class="'component-dialog-sysbar' + ($vuetify.theme.dark ? '-dark' : '')"
                    style="position: absolute; width: 100%; height: 30px;"
                    :style="'z-index:' + ((zIndex + 1) + (small ? 10 : 0))"
                >
                    <div class="d-flex align-center" style="width: 150px;">
                        <v-icon :dark="!$vuetify.theme.dark" :light="$vuetify.theme.dark" small class="ml-2 mr-2" v-text="icon" />
                        <v-card :dark="!$vuetify.theme.dark" :light="$vuetify.theme.dark" flat class="caption transparent text-truncate" v-html="text" />
                    </div>

                    <div
                        v-if="select"
                        class="blue_prim--text font-weight-bold caption text-truncate"
                        v-html="selected ? ('Currently selected ID ' + selected) : 'No Selection, yet'"
                    />

                    <div style="width: 150px; text-align: end">
                        <v-btn
                            small
                            tile
                            dark
                            depressed
                            class="red darken-4"
                            style="height: 29px; margin-bottom: 1px"
                            @click="close()"
                        >
                            <v-icon small v-text="'close'"></v-icon>
                        </v-btn>
                    </div>
                </div>

                <!-- Slot -->
                <div style="">
                    <slot />
                </div>
            </div>
        </v-scale-transition>
    </template>

</div>
</template>

<script>
export default {
    data () {
        return {
            show: false
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        small:      { type: Boolean, default: false },
        icon:       { type: String, default: 'launch' },
        text:       { type: String, default: 'Dialog' },
        select:     { type: Boolean, default: false },
        selected:   { type: [String, Number], default: null },
        closing:    { type: Number, default: 0 },
        zIndex:     { type: Number, default: 202 }
    },

    computed: {
    },

    watch: {
        closing () { this.close() }
    },

    mounted () {
        this.$nextTick(() => { this.show = true })
    },

    methods: {
        close () {
            this.show = false
            setTimeout(() => { this.$emit('close', true) }, 300)
        }
    }
}
</script>
