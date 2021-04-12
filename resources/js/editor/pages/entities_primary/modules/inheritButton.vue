<template>
    <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs }">
            <v-hover>
                <template v-slot:default="{ hover }" >
                    <v-icon
                        v-bind="attrs"
                        v-on="on"
                        :color="disabled ? 'grey' : (hover ? 'invert' : 'primary')"
                        :class="margins"
                        v-text="'sync' + (inherited ? '' : '_disabled')"
                        :disabled="disabled"
                        @click="$emit('click', true)"
                    ></v-icon>
                </template>
            </v-hover>
        </template>
        <span v-text="tooltip"></span>
    </v-tooltip>
</template>

<script>
export default {
    props: {
        disabled:   { type: Boolean, default: false },
        inherited:  { type: Number, default: 0 },
        no_margin_top:  { type: Boolean, default: false },
        no_margin_left:  { type: Boolean, default: false }
    },
    computed: {
        tooltip () {
            if (this.disabled) {
                return 'No inheriting Type set.'
            }
            else {
                return (this.inherited === 1 ? 'Deactivate' : 'Activate') + ' Type-Coin-Synchronisation'
            }
        },
        margins () {
            const margins = []
            if (!this.no_margin_top) {
                margins.push('mt-2')
            }
            if (!this.no_margin_left) {
                margins.push('ml-2')
            }
            return margins ? margins.join(' ') : ''
        }
    }
}
</script>