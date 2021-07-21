<template>
<div class="drawer_bg d-flex" :style="styling">
    <div
        v-for="(img) in images"
        :key="'img' + imageSet.id + '_' + img.src"
        style="width: 50%"
        @click="onClick(img)"
    >
        <adv-img contain square :src="img.src" :background="img.bg" />
        <slot />
    </div>

</div>
</template>


<script>

export default {
    data () {
        return {
        }
    },

    props: {
        imageSet: { type: Object, default: () => { return {
            id: null,
            kind: 'original',
            obverse: {},
            reverse: {}
        }} },
        emitOnClick: { type: Boolean, default: false }
    },

    computed: {
        images () {
            const img = this.imageSet

            const set = []

            ;['obverse', 'reverse'].forEach((side) => {
                const file = img[side] ?? {}
                set.push({
                    src: file.link ?? null,
                    bg: file.bg_color ?? (file.kind === 'plastercast' ? 'imgbg' : null),
                    href: file.digilib ?? file.link,
                    text: side
                })
            })

            return set
        },

        styling () {
            return [
                'width: 100%'
            ].join(';\n')
        }
    },

    methods: {
        onClick (file) {
            console.log(file)
        }
    }
}
</script>
