<template>
<div>

    <v-fade-transition>
        <div v-if="show" class="dialog-overlay" :style="'z-index:' + zIndex + ' Important!'">
        </div>
    </v-fade-transition>

    <v-scale-transition origin="center">
        <div v-if="show" class="dialog-container" :style="'z-index:' + (zIndex + 1)">
            <div :style="styling" :class="'component-dialog-outline' + ($vuetify.theme.dark ? '-dark' : '')">
                <v-card tile raised :class="background">

                    <!-- System Header -->
                    <div
                        class="d-flex align-center justify-space-between"
                        :class="'component-dialog-sysbar' + ($vuetify.theme.dark ? '-dark' : '')"
                        style="width: 100%; height: 30px;"
                        :style="'z-index:' + (zIndex + 1)"
                    >
                        <div class="d-flex align-center" style="width: 250px;">
                            <v-icon :dark="!$vuetify.theme.dark" :light="$vuetify.theme.dark" small class="ml-2 mr-2" v-text="icon" />
                            <v-card :dark="!$vuetify.theme.dark" :light="$vuetify.theme.dark" flat class="caption transparent text-truncate" v-html="text" />
                        </div>

                        <div style="width: 250px; text-align: end">
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

                    <!-- Content -->
                    <div class="body-2" :class="noPadding ? '' : 'pa-4'">
                        <slot />
                    </div>
                </v-card>
            </div>
        </div>
    </v-scale-transition>

</div>
</template>

<script>
export default {
    props: {
        show:       { type: Boolean, default: false },
        icon:       { type: String, default: 'launch' },
        text:       { type: String, default: 'Dialog' },
        width:      { type: [Number, String], default: '50%' },
        maxWidth:   { type: [Number, String], default: 500 },
        noPadding:  { type: Boolean, default: false },
        background: { type: String, default: null },
        zIndex:     { type: Number, default: 201 }
    },

    computed: {
        MaxWidth () {
            return typeof this.maxWidth === 'number' ? (this.maxWidth + 'px') : this.maxWidth
        },
        Width () {
            return typeof this.width === 'number' ? (this.width + 'px') : this.width
        },

        styling () {
            return [
                'width:' + this.Width,
                'max-width:' + this.MaxWidth,
                'z-index:' + (this.zIndex + 1)
            ].join(';\n')
        }
    },

    methods: {
        close () {
            this.$emit('close', true)
            /*this.show = false
            setTimeout(() => { this.$emit('close', true) }, 300)
            */
        }
    },

    //created () { console.log(this.zIndex) }
}
</script>
