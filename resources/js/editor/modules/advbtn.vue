<template>
<div>
    
    <v-tooltip v-if="tooltip && !disabled" bottom>
        <template v-slot:activator="{ on }"> 

            <v-hover>
                <template v-slot:default="{ hover }" >
                    <button 
                        hover v-on="on"
                        :class="('pa-' + pa) + ' ' + (disabled ? color_main : (hover ? color_hover : color_main))"
                        :disabled="disabled" 
                        @click="$emit('click')"
                    >
                        <v-responsive
                            :aspect-ratio="ratio" 
                            class="d-flex justify-center align-center"
                            width="24"
                        >
                            <span
                                v-if="text"
                                :class="(small ? 'caption' : '') + (disabled ? (' grey--text') : '')"  
                                v-html="text"
                            ></span>
                            <v-icon 
                                v-else 
                                :small="small"
                                :class="'mt-n1' + (disabled ? (' grey--text') : '')" 
                                v-text="icon"
                            ></v-icon>
                        </v-responsive>
                    </button>
                </template>
            </v-hover>

        </template>
        <span v-html="tooltip"></span>
    </v-tooltip>

    <v-hover v-else>
        <template v-slot:default="{ hover }" >
            <button
                hover
                :class="('pa-' + pa) + ' ' + (disabled ? color_main : (hover ? color_hover : color_main))"
                :disabled="disabled" 
                @click="$emit('click')"
            >
                <v-responsive
                    :aspect-ratio="ratio" 
                    class="d-flex justify-center align-center"
                    width="24"
                >
                    <span 
                        v-if="text" 
                        :class="(small ? 'caption' : '') + (disabled ? (' grey--text') : '')" 
                        v-html="text"
                    ></span>
                    <v-icon 
                        v-else
                        :small="small"
                        :class="'mt-n1' + (disabled ? (' grey--text') : '')" 
                        v-text="icon"
                    ></v-icon>
                </v-responsive>
            </button>
        </template>
    </v-hover>

</div>
</template>


<script>

export default {

    data () {
        return { }
    },

    props: {
        disabled:       { type: Boolean, default: false },
        ratio:          { type: [String, Number], default: 1 },
        padding:        { type: [String, Number], default: 3 },
        text:           { type: String, default: null },
        icon:           { type: String, default: 'forward' },
        tooltip:        { type: String, default: null }, 
        color_main:     { type: String, default: 'appbar' }, 
        color_hover:    { type: String, default: 'sysbar' }, 
        color_text:     { type: String, default: '' },
        small:          { type: Boolean, default: false }
    },

    computed: {
        pa () {
            if (!this.small) { 
                return this.padding 
            }
            else {
                return 1
            }
        }
    }
}

</script>