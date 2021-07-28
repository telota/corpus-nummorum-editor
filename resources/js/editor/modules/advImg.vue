<template>
<div :style="'background-color:' + Background">
    <v-img
        :src="img.src"
        :contain="contain"
        :alt="img.name"
        :aspect-ratio="ratio"
        :height="height > 0 ? height : undefined"
        v-on:error="handleError"
    >
        <template v-slot:placeholder>
            <div
                class="d-flex align-center justify-center grey"
                style="height: 100%; width: 100%"
            >
                <v-progress-circular
                    v-if="img.loading"
                    color="white"
                    indeterminate
                />
                <v-icon
                    v-else
                    x-large
                    color="white"
                    v-text="img.icon"
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
            displayable:    ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'jfif', 'pjpeg', 'pjp', 'avif', 'apng'],
        }
    },

    props: {
        src:                { type: [String, Boolean], default: null },
        square:             { type: Boolean, default: false },
        contain:            { type: Boolean, default: false },
        height:             { type: Number, default: 0 },
        background:         { type: String, default: 'grey' },
    },

    computed: {
        baseURL () { return this.$root.baseURL },
        ratio () { return this.square ? 1 : 4/3 },
        Background () {
            if (!this.background) return 'grey'
            if (this.$root.colors[this.background]) return this.$root.colors[this.background]
            return this.background
        }
    },

    watch: {
        src () {
            this.setImg()
        }
    },

    mounted () {
        this.setImg()
    },

    methods: {
        setImg () {
            //if (this.src) {
                const src = this.setImgSrc(this.src)
                this.img = {
                    loading: src ? true : false,
                    src: src ? src : null,
                    icon: this.setIcon(src),
                    name: this.text ?? this.src?.split('/')?.pop()
                }
            /*}
            else Object.keys(this.img).forEach((key) => this.img[key] = null)*/
        },

        handleError (error) {
            this.img.src = null
            this.img.loading = false
        },

        setImgSrc (src) {
            src = src?.trim()

            if (src) {
                if (src.slice(0, 5) === 'blob:' || src.slice(0,4) === 'http') return src
                if (src.toLowerCase().includes('.tif')) return this.$root.digilib(src)
                return this.baseURL + '/' + (src.includes('storage/') ? '' : 'storage/') + src
            }
            else return null
        },

        setIcon (src) {
            if (src) {
                const extension = src.toLowerCase().split('.')[0]
                if (this.displayable.includes(extension)) return 'broken_image'
                return 'help_center'
            }
            else return 'no_photography'
        }
    }
}

</script>
