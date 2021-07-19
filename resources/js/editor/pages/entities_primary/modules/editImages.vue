<template>
<div id="edit-images" :style="styling">

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

    <div>
        <a
            v-for="(img, i) in images"
            :key="'img' + i + img.src"
            :href="img.href"
            target="_blank"
        >
            <adv-img contain square :src="img.src" :background="img.bg" />
        </a>
    </div>

    <!-- Body
    <v-card-text>

        <adv-img contain :height="grid.dimensions - 80" :src="path" />
        <Imager vertical coin contain
            :item="{ images: images ? images : [] }"
            :index="images ? (index < images.length ? index : 0) : 0"
            :key="refresh + ' ' + index"
        ></Imager>

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
    </v-card-text>-->
</v-card>

</div>
</template>


<script>

export default {
    data () {
        return {
            zoom: 0
        }
    },

    props: {
        item:   { type: Object, default: () => { return { images: [] }} },
        index:  { type: Number, default: 0 },
        refresh:{ type: Number, default: 0 }
    },

    computed: {
        images () {
            const images = this.item.images?.[this.index] ?? {}
            const toReturn = []

            ;['obverse', 'reverse'].forEach((side) => {
                const data = images[side] ?? {}
                toReturn.push({
                    src: data.link ?? null,
                    bg: data.bg_color ?? (data.kind === 'plastercast' ? 'imgbg' : null),
                    href: data.digilib ?? data.link
                })
            })

            console.log(toReturn)

            return toReturn
        },

        styling () {
            return [
                'position: fixed',
                'top: 90px',
                'right: 0',
                'width: 200px'
            ].join(';\n')
        }
    }
}
</script>
