<template>
<div :class="colorBackground">
    <v-img
        :src="img.src"
        :contain="contain"
        :alt="img.name"
        :aspect-ratio="square ? 1 : 4/3"
        :height="height > 0 ? height : undefined"
        v-on:error="handleError"
    >
        <template v-slot:placeholder>
            <div
                class="d-flex align-center justify-center"
                style="height: 100%; width: 100%"
            >
                <v-progress-circular
                    v-if="img.loading"
                    indeterminate
                />
                <v-icon
                    v-else
                    x-large
                    v-text="img.icon ? img.icon : 'insert_drive_file'"
                />
            </div>
        </template>
    </v-img>
</div>
</template>


<script>

export default {
    data () {
        return {
            img: {
                loading: null,
                src: null,
                icon: null,
                name: null
            },
            lazy:           false,

            baseURL:        this.$root.baseURL,
            imgFiles:       ['jpg', 'jpeg', 'png', 'gif', 'svg', 'tif', 'tiff'],
            digilibFiles:   ['tiff', 'tif'],
            digilib:        'https://digilib.bbaw.de/digitallibrary/servlet/Scaler?fn='
        }
    },

    props: {
        src:                { type: [String, Boolean], default: null },
        square:             { type: Boolean, default: false },
        contain:            { type: Boolean, default: false },
        size:               { type: Number, default: 300 },
        height:             { type: Number, default: 0 },
        colorBackground:    { type: String, default: 'grey lighten-2' },
    },

    watch: {
        src () {
            this.setImg()
        },
        selcted () {
            console.log(this.selected)
        }
    },

    mounted () {
        if (this.src) this.setImg()
    },

    methods: {
        setImg () {
            if (this.src) {
                const src = this.setImgSrc(this.src, this.size)
                this.img = {
                    loading: src ? true : false,
                    src: src ? src : null,
                    icon: src === false ? 'no_photography' : 'lock', //'broken_image',
                    name: this.text ?? this.src.split('/').pop()
                }
            }
            else Object.keys(this.img).forEach((key) => this.img[key] = null)
        },

        handleError (error) {
            this.img.src = null
            this.img.loading = false
        },

        setImgSrc (src, size) {
            src = src?.trim()

            if (src) {
                if (src.slice(0, 5) === 'blob:') return src

                const extension = this.extractExtension(src)

                // File is no displayable image
                if (!this.imgFiles.includes(extension)) return false

                // Link is external
                if(src.slice(0,4) === 'http' && src.slice(0, this.baseURL.length) != this.baseURL) return link

                // file is TIFF and shall be given as digilib link
                if (this.digilibFiles.includes(extension) && !raw) return this.digilib + src + '&dw=' + this.size + '&dh=' + this.size

                // file is displayable image
                if (src.slice(0, this.baseURL.length) === this.baseURL) return src
                else return this.baseURL + '/' + (src.includes('storage/') ? '' : 'storage/') + src
            }
            else return null
        },

        setPlaceholder (link) {
            const phdir = this.baseURL + '/placeholder/'

            if (!link) {
                return phdir + 'placeholder_not_found.png'
            }
            else {
                const extension = this.extractExtension(link)
                return phdir + 'placeholder_' + (this.formats.includes(extension) ? extension : 'not_supported') + '.png'
            }
        },

        createDigilibLink (link, scale) {

            if (link) {
                if (link.includes('storage/'))                      { link = link.split('storage/').pop() }
                else if (link.slice(0, this.baseURL.length) === this.baseURL) { link = link.slice(this.baseURL.length) }

                scale = scale ? (Number.isInteger(scale) ? scale : 500) : 500

                return {
                    scaler: digilib.scaler + link.trim() + '&dw=' + scale + '&dh=' + scale,
                    viewer: digilib.viewer + link.trim()
                }
            }
        },

        extractExtension (string, uc) {
            if (string) {
                const split = string.trim().split('.')
                if (!split[1]) return null

                string = split[1]

                if (string) string = uc ? string.toUpperCase() : string.toLowerCase()

                return string
            }
            else return null
        }
    }
}

</script>
