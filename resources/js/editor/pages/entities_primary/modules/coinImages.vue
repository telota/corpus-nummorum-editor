<template>
<div :class="vertical ? '' : 'd-flex'">
    <div
        v-for="(s) in sides"
        :key="'img_' + (img ? (img.id + '_') : '') + index + '_' + s"
        :style="'width:' + (vertical || sides.length < 2 ? '100%' : '50%')"
    >
        <a
            v-if="link && img.id"
            :href="img[s].link"
            target="_blank"
        >
            <adv-img
                contain
                square
                :src="img.id ? img[s].src : null"
                :background="img.id ? img[s].bg : null"
            />
        </a>

        <adv-img
            v-else
            contain
            square
            :src="img.id ? img[s].src : null"
            :background="img.id ? img[s].bg : null"
        />
    </div>
</div>
</template>

<script>
export default {
    props: {
        images: { type: Array, default: () => [] },
        index:  { type: Number, default: 0 },
        link:   { type: Boolean, default: false },
        side:   { type: [Number, String], default: null },
        vertical: { type: Boolean, default: false }
    },

    computed: {
        sides () {
            if (this.side === 'obverse' || this.side === 'o' || this.side === 0) return ['obverse']
            if (this.side === 'reverse' || this.side === 'r' || this.side === 1) return ['reverse']
            return ['obverse', 'reverse']
        },

        img () {
            const obj = {}
            if (this.images?.[this.index]?.id) {
                const img = this.images[this.index]
                console.log(img)

                obj.id = img.id
                obj.kind = img.kind

                this.sides.forEach((s) => {
                    obj[s] = {
                        src: img[s].link ?? null,
                        link: img[s].digilib ?? img[s].link,
                        bg: img[s].bg_color ?? (img[s].kind === 'plastercast' ? 'imgbg' : 'white')
                    }
                })
            }
            return obj
        }
    }
}
</script>
