<template>
<div class="mb-n1 mr-1" style="padding-top: 1px; padding-bottom: 2px;">
    <v-hover>
        <template v-slot:default="{ hover }" >
            <button
                hover
                :class="('pa-' + padding) + ' ' + (disabled ? color_main : (hover ? color_hover : color_main))"
                @click="$emit('input', sk_value)"
            >
                <v-responsive
                    :aspect-ratio="1" 
                    class="d-flex justify-center align-center"
                    style="border: 1px solid grey"
                    width="24"
                >
                    <div
                        :class="(color_text ? (color_text + '--text ') : '') + (small ? 'caption' : 'body-1')"
                        v-html="sk_text ? sk_text : sk_value"
                    ></div>
                </v-responsive>
            </button>
        </template>
    </v-hover>
</div>
</template>


<script>
export default {
    props: {
        sk_value:       { type: [String, Boolean], required: true },
        sk_text:        { type: String },
        small:          { type: Boolean, default: false },
        disabled:       { type: Boolean, default: false },
        color_main:     { type: String, default: 'transparent' }, 
        color_hover:    { type: String, default: 'sysbar' },
        color_text:     { type: [String, Number] }
    },
    
    computed: {
        padding () {
            return this.small ? 0 : 5
        }
    }
}
</script>