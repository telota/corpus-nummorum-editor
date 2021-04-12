<template>
<div>

<v-card class="appbar" tile>
    <!-- Header -->
    <v-card-title>
        <v-menu offset-y>
            <template v-slot:activator="{ on, attrs }">
                <v-btn
                    icon
                    v-bind="attrs"
                    v-on="on"
                    :disabled="images ? (images.length > 1 ? false : true) : true"
                >
                    <v-icon>camera_alt</v-icon>
                </v-btn>
            </template>

            <v-card tile>
                <v-list>
                    <v-list-item-group>
                        <v-list-item v-for="(iterator, i) in images" :key="i"
                            :class="index === i ? 'font-weight-black' : ''"
                            v-text="'' + (i + 1)"
                            @click="$emit('index', i)"
                        ></v-list-item>
                    </v-list-item-group>
                </v-list>
            </v-card>
        </v-menu>
        <div 
            class="body-1 mb-1"
            :class="images ? (images.length > 1 ? '' : 'grey--text') : 'grey--text'"
            v-html="'&ensp;(&nbsp;' + (images ? (images.length > 1 ? ((index + 1) + '&nbsp;/&nbsp;' + images.length) : 1) : '--') + '&nbsp;)'"
        ></div>

        <v-spacer></v-spacer>
       
        <!-- Zoom in and out -->
        <v-btn icon 
            @click="$emit('zoom', -1)" 
            :disabled="zoom < 3 || !$vuetify.breakpoint.mdAndUp">
            <v-icon>zoom_out</v-icon>
        </v-btn>                                
        <v-btn icon 
            @click="$emit('zoom', 1)" 
            :disabled="zoom > 3 || !$vuetify.breakpoint.mdAndUp">
            <v-icon>zoom_in</v-icon>
        </v-btn>
    </v-card-title>

    <!-- Body -->
    <v-card-text>
        <!-- Imager -->
        <Imager vertical coin contain
            :item="{ images: images ? images : [] }" 
            :index="images ? (index < images.length ? index : 0) : 0" 
            :key="refresh + ' ' + index"
        ></Imager>

        <!-- Open Image as new tab -->
        <div class="pt-2 d-flex justify-space-between" v-if="images[index]">
            <div v-for="(s) in ['obverse', 'reverse']" :key="s">
                <a v-if="images[index][s].link"
                    :href="!images[index][s].link ? '' : (
                        images[index][s].link.substring(0,4) === 'http' ? images[index][s].link : 
                            ( 'https://digilib.bbaw.de/digitallibrary/digilib.html?fn=silo10/thrakien/' + images[index][s].link )
                        )" 
                    target="_blank"
                    v-html="labels[s] +'&ensp;&#10064;'"
                ></a>
                <span v-else class="sysbar--text" v-text="labels[s]"></span>
            </div>
        </div>
    </v-card-text>
</v-card>

</div>
</template>


<script>

export default {

    props: {
        images: { type: Array },
        index:  { type: Number, default: 0 },
        zoom:   { type: Number, default: 2 },
        refresh:{ type: Number, default: 0 }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels }
    }
}
</script>