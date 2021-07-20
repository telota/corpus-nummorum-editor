<template>
<div id="edit-images" class="drawer_bg" :style="styling">

    <div
        v-for="(img, i) in images"
        :key="'img' + img.id"
    >

        <!-- Header -->
        <div
            class="d-flex align-center justify-space-between pa-3"
            :style="'cursor:' + (active === i ? 'default' : 'pointer')"
            @click="selected = i"
        >
            <div class="whitespace-nowrap caption" v-html="img.label" />
            <v-icon v-text="active === i ? 'expand_less' : 'expand_more'" />
        </div>

        <!-- Content -->
        <v-expand-transition>
            <div v-if="active === i">
                <a
                    v-for="(file) in img.files"
                    :key="'img' + img.id + '_' + file.src"
                    :href="file.href"
                    target="_blank"
                >
                    <div style="position: relative;">
                        <adv-img contain square :src="file.src" :background="file.bg" />
                        <div
                            v-text="file.text"
                            class="caption pl-1 black--text"
                            style="position: absolute; bottom: 0; left: 0;"
                        />
                    </div>
                </a>
            </div>
        </v-expand-transition>

        <v-divider />
    </div>

</div>
</template>


<script>

export default {
    data () {
        return {
            zoom: 0,
            selected: 0,
        }
    },

    props: {
        item:       { type: Object, default: () => { return { images: [] }} }
    },

    computed: {
        active () {
            return this.images[this.selected] ? this.selected : 0
        },

        images () {
            const data = this.item.images?.[0] ? this.item.images : []
            const computed = []

            data.forEach((img) => {
                const set = {
                    id: img.id,
                    label: img.kind + '&nbsp;(ID&nbsp;' + img.id + ')',
                    files: []
                }

                ;['obverse', 'reverse'].forEach((side) => {
                    const file = img[side] ?? {}
                    set.files.push({
                        src: file.link ?? null,
                        bg: file.bg_color ?? (file.kind === 'plastercast' ? 'imgbg' : null),
                        href: file.digilib ?? file.link,
                        text: side
                    })
                })

                computed.push(set)
            })

            return computed
        },

        styling () {
            return [
                'position: fixed',
                'top: 90px',
                'right: 0',
                'width: 200px',
                'height: calc(100vh - 90px)',
                'overflow-y: auto'
            ].join(';\n')
        }
    }
}
</script>
