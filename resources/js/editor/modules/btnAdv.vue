<template>
<v-tooltip :disabled="disabled || !tooltip" bottom>
    <template v-slot:activator="{ on }">

        <v-hover v-slot="{ hover }">
            <div
                v-on="on"
                class="d-flex justify-center align-center"
                :class="colors[hover && !disabled ? 'hovered' : 'main']"
                :style="style"
                @click="clicked"
            >
                <v-progress-circular
                    v-if="loading"
                    :indeterminate="loading === true"
                    :value="loading === false || loading === true ? 0 : loading"
                    :size="large ? 24 : 18"
                />
                <div
                    v-else-if="text"
                    :class="textClass"
                    v-html="text"
                ></div>
                <v-icon
                    v-else
                    :small="small"
                    :dense="medium"
                    :large="large"
                    :color="colors.text ? colors.text : ''"
                    v-text="icon"
                ></v-icon>
            </div>
        </v-hover>

    </template>
    <span v-html="tooltip"></span>
</v-tooltip>
</template>


<script>

export default {

    data () {
        return { }
    },

    props: {
        disabled:       { type: Boolean, default: false },
        ratio:          { type: [String, Number], default: 1 },
        text:           { type: String, default: null },
        icon:           { type: String, default: 'forward' },
        tooltip:        { type: String, default: null },
        colorMain:      { type: String, default: null },
        colorHover:     { type: String, default: null },
        colorText:      { type: String, default: null },
        large:          { type: Boolean, default: false },
        medium:         { type: Boolean, default: false },
        small:          { type: Boolean, default: false },
        loading:        { type: [Boolean, Number], default: false }
    },

    computed: {
        colors () {
            return {
                main: this.colorMain ?? 'transparent',
                hovered: this.colorHover ?? 'grey_prim',
                text: this.disabled ? 'grey' : (this.colorText ?? '')
            }
        },
        height () {
            if (this.small) return 30
            if (this.medium) return 40
            if (this.large) return 70
            return 50
        },
        width () {
            return Math.round(this.height * parseFloat(this.ratio))
        },
        style () {
            const styles = []

            styles.push('width:' + this.width + 'px')
            styles.push('height:' + this.height + 'px')
            styles.push('cursor:' + (this.disabled ? 'default' : 'pointer'))

            return styles.join(';')
        },
        textClass () {
            const classes = []

            if (this.small) classes.push('caption')
            else if (this.large) classes.push('body-1')
            else classes.push('body-2')

            classes.push('textUppercase font-weight-medium')
            classes.push(this.colors.text + '--text')

            return classes.join(' ')
        }
    },

    methods: {
        clicked (e) {
            if (!this.disabled) this.$emit('click', e)
        },
        classes (hover) {
            const classes = []

            classes.push('')
            classes.push(this.colors[hover ? 'hovered' : 'main'])

            return classes.join(' ')
        }
    }
}

</script>
