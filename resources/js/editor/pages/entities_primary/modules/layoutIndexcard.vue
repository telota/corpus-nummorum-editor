<template>
    <v-card
        tile
        :class="select && selected == item.id ? 'tile_selected' : 'tile_bg'"
        :min-height="height"
        style="position: relative"
    >

        <!-- Publish Checker -->
        <v-slide-x-transition>
            <div v-if="publisher" style="position: absolute; width: 40px; height: 100%">
                <v-hover  v-slot="{ hover }">
                    <button
                        :class="'d-flex align-center ' + (checked ? 'blue_prim' : (hover && item.public !== 3 & item.public !== 1 ? 'sysbar' : 'transparent'))"
                        style="height: 100%;"
                        :disabled="item.public === 3 || item.public === 1"
                        @click="$emit('check')"
                    >
                        <v-icon
                            :class="'pa-2' + (item.public === 3 || item.public === 1 ? ' grey--text' : (checked ? ' white--text' : ''))"
                            v-text="checked ? 'radio_button_checked' : 'radio_button_unchecked'"
                        />
                    </button>
                </v-hover>
            </div>
        </v-slide-x-transition>

        <!-- Images -->
        <v-slide-x-transition>
            <div
                v-if="!expand"
                style="position: absolute;"
                class="d-flex align-center fill-height"
                :class="background"
                :style="'left:' + (publisher ? 40 : 0) + 'px; width:' + imgWidth + 'px'"
            >
                <div :style="'width:' + imgWidth + 'px'">
                    <coin-images
                        :images="item.id ? item.images : []"
                        :vertical="Vertical"
                    />
                </div>
            </div>
        </v-slide-x-transition>

        <!-- Content ------------------------------------------------- -->
        <div :style="'min-height:' + height + 'px; margin-left:' + left + 'px; width: calc(100% - ' + left + 'px)'">
            <!-- Header -->
            <div class="pl-3 d-flex align-center" style="height: 40px;">

                <div class="d-flex justify-space-between align-end">
                    <!-- Name -->
                    <div
                        class="d-flex body-1 font-weight-black"
                        :class="item.public === 3 ? ' text-decoration-line-through' : ''"
                    >
                        <div
                            class="text-truncate"
                            v-text="'cn ' + entity.slice(0, -1)"
                        />
                        <div class="d-flex">
                            <div v-html="'&nbsp;' + item.id" />
                            <div v-html="$handlers.format.cn_public_link(item)" />
                        </div>
                    </div>

                    <template v-if="!expand">
                        <!-- Forgery -->
                        <div class="font-weight-bold text-truncate" v-html="$handlers.show_item_data(language, entity, item, 'forgery')" />

                        <!-- Mint -->
                        <div
                            v-if="item.mint.text[language] && !expand"
                            v-text="item.mint.text[language]"
                            class="ml-5"
                        />
                    </template>
                </div>

                <!-- Actions -->
                <div :class="select && selected == item.id ? 'tile_selected' : 'tile_bg'" style="position: absolute; right: 0;">
                    <commandbar
                        :entity="entity"
                        :id="item.id"
                        :status="item.public"
                        :publisher="publisher"
                        :select="select"
                        :selected="selected"
                        :linking="linking"
                        no-details-dialog
                        v-on:publish="$emit('publish')"
                        v-on:unlink="$emit('unlink')"
                        v-on:inherit="$emit('inherit')"
                        v-on:represent="$emit('represent')"
                        v-on:select="$emit('select', item.id)"
                        v-on:details="expand = !expand"
                    />
                </div>
            </div>

            <v-divider />

            <!-- Body not expand ------------------------------------------------- -->
            <div v-if="!expand">
                <!-- not expand -->
                <div :style="'min-height:' + (height - 80) + 'px'">
                    <div class="d-flex mt-2">
                        <!-- Types / Coins -->
                        <linkedInherited
                            :entity="entity"
                            :item="item"
                            class="ml-3 caption"
                        />

                        <!-- Diameter and Weight -->
                        <div
                            v-if="!expand"
                            class="caption ml-5"
                            v-html="$handlers.show_item_data(language, entity, item, 'card_header')"
                        />
                    </div>

                    <div :class="'mt-5 ml-3' + ($vuetify.breakpoint.mdAndUp ? ' d-flex' : '')">
                        <div
                            v-for="(side) in ['obverse', 'reverse']"
                            :key="side"
                            class="pr-5 pb-5 body-2"
                            style="position: relative"
                            :style="$vuetify.breakpoint.mdAndUp ? 'width: 50%;' : ''"
                        >
                            <div
                                class="font-weight-bold text-uppercase"
                                v-text="side.slice(0, 1)"
                                style="position: absolute"
                            />

                            <div class="pl-5">
                                <!-- Legend -->
                                <div class="font-weight-thin pb-1" v-text="printLegend(side)" />

                                <!-- Design -->
                                <div v-text="printDesign(side)" />

                                <!-- Symbols -->
                                <template v-if="item[side].symbols">
                                    <div class="text-uppercase mt-2" v-text="$root.label('symbols')"></div>
                                    <div v-html="item[side].symbols.map((d) => {
                                            return d.text[language] + (d.position ? (' (' + d.position.text[language] + ')') : '')
                                        }).join(';&ensp;')"
                                    ></div>
                                </template>

                                <!-- Monograms -->
                                <template v-if="item[side].monograms">
                                    <div class="text-uppercase mt-2 mb-n1" v-text="$root.label('monograms')"></div>
                                    <v-row>
                                        <v-col
                                            v-for="d in item[side].monograms"
                                            :key="'monograms' + d.id"
                                            cols=12
                                            sm=6
                                            lg=4
                                        >
                                            <div class="d-flex">
                                                <div v-html="$handlers.format.image_tile(d.link, 40)"></div>
                                                <div class="caption pl-3 mt-n1" v-text="d.position ? d.position.text[language] : '--'"></div>
                                            </div>
                                        </v-col>
                                    </v-row>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div style="width: 100%">
                    <v-divider />
                    <div class="d-flex align-center justify-space-between caption pl-3 pr-3" style="height: 40px;">
                        <div v-html="$handlers.show_item_data(language, entity, item, 'card_footer')" />
                        <div v-if="item.name_private" v-text="item.name_private" class="pa-3 ml-5 caption text-truncate" />
                    </div>
                </div>
            </div>

            <!-- Body expand -->
            <div v-else class="sheet_bg pa-5">
                <details-content :entity="entity" :id="item.id" />
            </div>
        </div>

    </v-card>
</template>


<script>

import detailsContent from './../show/content.vue'
import linkedInherited from './../modules/linkedInherited.vue'

export default {
    components: {
        detailsContent,
        linkedInherited
    },

    data () {
        return {
            defaultHeight: 360,
            noWrap: 1200,
            expand: false
        }
    },

    props: {
        entity:         { type: String, required: true },
        item:           { type: Object, default: () => {} },
        publisher:      { type: Boolean, default: false },
        checked:        { type: Boolean, default: false },
        linking:        { type: Boolean, default: false },
        select:         { type: Boolean, default: false },
        selected:       { type: [Number, String], default: null },
        vertical:       { type: Boolean, default: false },
        width:          { type: Number, default: null }
    },

    computed: {
        language () {
            return this.$root.language
        },

        height () {
            if (this.width === null) return this.defaultHeight
            if (this.width > (this.noWrap * 1.5)) return 500
            if (this.width > this.noWrap) return 420
            if (this.width > 600) return this.defaultHeight
            if (this.width > 500) return 180
            return 150
        },

        Vertical () {
            if (this.vertical) return true
            if ((this.width && this.width <= this.noWrap) || this.$vuetify.breakpoint.mdAndDown) return true
            return false
        },

        imgWidth () {
            return this.height / (this.Vertical ? 2 : 1)
        },

        left () {
            return (this.expand ? 0 : this.imgWidth) + (this.publisher ? 40 : 0)
        },

        background () {
            if (!this.item?.images?.[0]?.id) return null
            const img = this.item.images[0]
            return img.bg_color ?? (img.kind === 'plastercast' ? 'imgbg' : 'white')
        }
    },

    methods: {
        printDesign (side) { return this.item?.[side]?.design?.text?.[this.language] ?? '--' },
        printLegend (side) { return this.item?.[side]?.legend?.string ?? '--' }
    }
}

</script>
