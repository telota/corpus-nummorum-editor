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
            <div class="whitespace-nowrap caption" v-html="img.kind + '&nbsp;(ID&nbsp;' + img.id + ')'" />
            <v-icon v-text="active === i ? 'expand_less' : 'expand_more'" />
        </div>

        <!-- Content -->
        <v-expand-transition>
            <coin-images
                v-if="active === i"
                :images="images"
                :index="i"
                vertical
                link
            />
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
        entity: { type: String, required: true },
        item:   { type: Object, default: () => {} }
    },

    computed: {
        active () {
            return this.images[this.selected] ? this.selected : 0
        },

        images () {
            if (this.entity === 'types') return this.item?.images?.[0]?.id ? this.item.images : []
            return this.item?.dbi?.images?.[0]?.id ? this.item.dbi.images : []
        },

        styling () {
            return [
                'position: fixed',
                'top: 90px',
                'right: 0',
                'width: 200px',
                'height: calc(100vh - 90px)',
                'overflow-y: auto',
                'z-index: 98',
                'box-shadow: 0px 0px 10px rgba(0,0,0,0.34)'
            ].join(';\n')
        }
    }
}
</script>
