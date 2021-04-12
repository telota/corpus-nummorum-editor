<template>
<div>

    <v-card tile :flat="raised ? false : true" >

        <!-- is Coin (Pair) -->
        <div 
            v-if="coin" 
            :class="background + (vertical ? '' : ' component-wrap d-flex')"
        >
            <div
                v-for="(side) in (!hide_obverse && !hide_reverse ? ['obverse', 'reverse'] : (hide_reverse ? ['obverse'] : ['reverse']))"
                :key="side"
                :style="'width: ' + (vertical ? 100 : (!hide_obverse && !hide_reverse ? 50 : 100)) + '%'"
            >

                <!-- Image -->
                <v-img v-if="img[side].exists"
                    :src="img[side].src"
                    :lazy-src="img[side].lazy"
                    @error="img[side].src=fallback"
                    :contain="contain"
                    aspect-ratio="1"
                > 
                    <span 
                        v-if="!hide_text" 
                        :class="color_text + ' caption pl-1 pb-2'"
                        v-text="labels[side]"
                    ></span> 
                </v-img>

                <!-- Placeholder -->
                <div v-else class="pa-1" >
                    <div 
                        class="sysbar" 
                        style="height:0; width:100%; padding-bottom:100%; border-radius: 50%;"
                    >
                        <div 
                            v-if="!hide_text" 
                            style="padding-top:45%; left: 0; right: 0" 
                            class="text-center"
                            v-text="labels[side]"
                        ></div>                
                    </div>
                </div>
                
            </div>
        </div>

        <!-- is no Coin (single image) ------------------------------------------------------------------------------ -->
        <div v-if="!coin" :class="background">

            <!-- Image -->
            <v-img v-if="img.exists"
                :src="img.src"
                :lazy-src="img.lazy"
                @error="img.src=fallback"
                :contain="contain"
                aspect-ratio="1"
            ></v-img>

            <!-- Placeholder -->
            <div v-else style="width: 100%">
                <div 
                    class="sysbar" 
                    style="height:0; width:100%; padding-bottom:100%;"
                >
                    <div 
                        style="padding-top:45%; left: 0; right: 0" 
                        class="text-center"
                        v-text="'--'"
                    ></div>                
                </div>
            </div>

        </div>

    </v-card>

</div>
</template>


<script>

export default {
    data () {
        return {
            fallback:       this.$handlers.constant.placeholder_fallback,
            background:     this.color_background ? this.color_background : 'imgbg'
        }
    },

    props: {
        // Item
        item:               { type: Object },
        name:               { type: String },     // JK: key of image(s)
        coin:               { type: Boolean },    // JK: if true component expects item[key][index].obverse (or reverse)
        index:              { type: Number, default: 0 },     // JK: relevant for coin - controls the index in item[key]

        // Behaviour
        hide_obverse:       { type: Boolean },
        hide_reverse:       { type: Boolean },
        hide_text:          { type: Boolean },
        vertical:           { type: Boolean },

        // Decoration
        raised:             { type: Boolean },
        square:             { type: Boolean },
        contain:            { type: Boolean },
        size:               { type: Number, default: 500 },
        color_background:   { type: String },
        color_text:         { type: String, default: 'black--text' },
    },
    
    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        formatters () { return this.$handlers.format },

        img () {
            if (this.coin) {
                const self  = this
                const imgkey = self.name ? self.name : 'images'
                const items = {}
                
                if (self.item?.[imgkey]?.[self.index]) {
                    ['obverse', 'reverse'].forEach ((key) => { 
                        items[key] = this.handle_src(self.item?.[imgkey]?.[self.index]?.[key]?.link) 
                    })            
                    return items
                }
                else {
                    return { 
                        obverse: { exists: false }, 
                        reverse: { exists: false }
                    }
                }
            }
            else {
                const imgkey = this.name ? this.name : 'image'
                return this.handle_src(this.item?.[imgkey])
            }
        }
    },

    methods: {
        handle_src (src) {
            if (!src) {
                return { exists: false }
            }
            else {
                return {
                    exists: true,
                    src: this.formatters.image_link(src, this.size),
                    lazy: this.formatters.img_placeholder(src)
                }
            }

        }
    }
}

</script>