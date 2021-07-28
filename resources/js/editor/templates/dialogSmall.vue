<template>
<div>

    <v-fade-transition>
        <div v-if="show" class="dialog-overlay" style="z-index: 201">
        </div>
    </v-fade-transition>

    <v-scale-transition origin="center">
        <div v-if="show" class="dialog-container" style="z-index: 202">
            <div :style="styling">
                <v-card tile raised :class="background">

                    <!-- System Header -->
                    <dialogbartop
                        :icon="icon"
                        :text="text"
                        :fullscreen="false"
                        v-on:close="$emit('close')"
                    />

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
        background: { type: String, default: null }
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
                'max-width:' + this.MaxWidth
            ].join(';\n')
        }
    }
}
</script>
