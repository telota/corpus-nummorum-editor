<template>
<div>
    <div class="d-flex flex-wrap">
        <keyboardButton v-for="(sk, i) in keys" :key="i + 'k'"
            small
            :sk_value="sk.value"
            :sk_text="sk.text"
            :color_text="sk.color"
            :color_main="color_main"
            :color_hover="color_hover"
            @input="emit"
        />

        <keyboardButton v-for="(sk, i) in deletes" :key="i + 'd'"
            small
            :sk_value="sk.value"
            :sk_text="sk.text"
            :color_text="sk.color"
            :color_main="color_main"
            :color_hover="color_hover"
            @input="emit"
        />
    </div>

</div>
</template>


<script>

import keyboardButton from './keyboardButton.vue'

export default {
    components: {
        keyboardButton
    },

    data () {
        return {
            deletes: [
               { value: false, text: '&#8656;', color: 'error' },
               { value: true, text: '&#10005;', color: 'error' }
            ]
        }
    },

    props: {
        string:         { type: String, default: null },
        layout:         { type: String, default: 'el_uc' },
        //small:          { type: Boolean, default: false },
        color_main:     { type: String, default: 'transparent' },
        color_hover:    { type: String, default: 'sysbar' },
    },

    computed: {
        keys () {

            const keys = this.$store.state.screenkeys[this.$store.state.screenkeys[this.layout] ? this.layout : 'el_uc'].map((i) => {
                return {
                    value: i.value,
                    text: i.value,
                    color: 0
                }
            })

            // Add critical if adv
            if (this.layout.slice(-3) === 'adv') {
                this.$store.state.screenkeys.critical.map((i) => {
                    keys.push({
                        value: i.value,
                        text: i.value,
                        color: 'blue_sec'
                    })
                })
            }

            // Add Blank Space
            keys.push({ value: ' ', text: '&blank;', color: 'blue_prim' })

            return keys
        }
    },

    methods: {
        emit (emit) {
            let output = null
            if (emit === false) output = this.string ? this.string.slice(0, -1) : null
            else                output = this.string ? (this.string + emit) : emit

            this.$emit('input', output)
        }
    }
}

</script>
