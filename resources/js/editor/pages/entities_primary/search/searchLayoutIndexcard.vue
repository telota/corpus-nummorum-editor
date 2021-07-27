<template>
<div>

    <v-card tile class="d-flex component-wrap appbar" width="100%">

        <!-- Publish Checker -->
        <div v-if="publisher">
            <v-hover>
                <template v-slot:default="{ hover }" >
                    <button
                        :class="'d-flex align-center ' + (checked ? 'blue_prim' : (hover ? 'sysbar' : 'appbar'))"
                        style="height: 100%;"
                        :disabled="item.public === 3 || item.public === 1"
                        @click="$emit('checked')"
                    >
                        <v-icon :class="'pa-2' + (item.public === 3 || item.public === 1 ? ' grey--text' : '')" v-text="checked ? 'radio_button_checked' : 'radio_button_unchecked'"></v-icon>
                    </button>
                </template>
            </v-hover>
        </div>

        <!-- Card ------------------------------------------------- -->
        <div style="width: 100%">
            <v-row class="ma-0 pa-0">
                <v-col
                    v-if="!expanded"
                    cols="12"
                    sm="3"
                    md="4"
                    lg="3"
                    class="ma-0 pa-0"
                    :class="(item.images ? (item.images[0].obverse.bg_color === 'white' ? 'white' : 'imgbg') : 'imgbg')"
                >
                    <Imager
                        coin hide_text contain
                        :key="item.id"
                        :item="item.images ? item : {images: []}"
                        :vertical="!$vuetify.breakpoint.mdAndUp || vertical_forced"
                        :color_background="(item.images ? (item.images[0].obverse.bg_color == 'white' ? 'white' : null) : null)"
                    ></Imager>
                </v-col>

                <v-col
                    cols="12"
                    :sm="!expanded ? 9 : 12"
                    :md="!expanded ? 8 : 12"
                    :lg="!expanded ? 9 : 12"
                    class="ma-0 pa-0"
                    style="position: relative;"
                >
                    <!-- Header -->
                    <div class="pl-3 d-flex component-wrap justify-space-between align-center">

                        <div class="d-flex align-end">
                            <!-- Name -->
                            <div class="title" :class="(item.public === 3 ? ' text-decoration-line-through' : '') + (expanded ? ' ml-3' : '')">
                                <span v-html="$handlers.format.cn_entity(entity, item.id)"></span>
                                <span v-html="$handlers.format.cn_public_link(item)"></span>
                            </div>
                            <!-- Types / Coins -->
                            <linkedInherited
                                :entity="entity"
                                :item="item"
                                class="ml-3"
                                v-on:details="(emit) => { details_dialog = { entity: emit.entity, id: emit.id, public: 0 }}"
                            ></linkedInherited>
                            <!-- Mint -->
                            <div
                                v-if="item.mint.text[l] && !expanded"
                                v-text="item.mint.text[l]"
                                class="ml-5"
                            ></div>

                            <!-- Diameter and Weight -->
                            <div
                                v-if="!expanded"
                                class="caption ml-5"
                                v-html="$handlers.show_item_data(l, entity, item, 'card_header')"
                            ></div>
                        </div>

                        <!-- Actions -->
                        <commandbar
                            :entity="entity"
                            :id="item.id"
                            :public_state="item.public"
                            :publisher="publisher"
                            :key="item.id + entity + (publisher ? 1 : 0) + item.public"
                            v-on:details="(emit) => { expanded = !expanded }"
                            v-on:publish="(emit) => { $emit('publish') }"
                            style="z-index: 2"
                        ></commandbar>
                    </div>

                    <v-divider></v-divider>

                    <!-- Body not expanded ------------------------------------------------- -->
                    <div v-if="!expanded" style="width: 100%">
                        <!-- not expanded -->
                        <div style="width: 100%; padding-bottom: 45px">

                            <div :class="'pl-5 pt-5' + ($vuetify.breakpoint.mdAndUp ? ' d-flex component-wrap' : '')">
                                <div v-for="(i) in ['obverse', 'reverse']" :key="i" class="pr-5 pb-5 body-2 d-flex" :style="$vuetify.breakpoint.mdAndUp ? 'width: 50%;' : ''">
                                    <div class="font-weight-bold text-uppercase mr-3">{{ i.substring(0,1) }}</div>
                                    <div>
                                        <!-- Legend -->
                                        <div class="font-weight-thin pb-1" v-text="item[i].legend ? (item[i].legend.string ? item[i].legend.string : '--') : '--'"></div>
                                        <!-- Design -->
                                        <div v-text="item[i].design ? (item[i].design.text[l] ? item[i].design.text[l] : '--') : '--'"></div>
                                        <!-- Symbols -->
                                        <template v-if="item[i].symbols">
                                            <div class="text-uppercase mt-2" v-text="$root.label('symbols')"></div>
                                            <div v-html="item[i].symbols.map((d) => {
                                                    return d.text[l] + (d.position ? (' (' + d.position.text[l] + ')') : '')
                                                }).join(';&ensp;')"
                                            ></div>
                                        </template>
                                        <!-- Monograms -->
                                        <template v-if="item[i].monograms">
                                            <div class="text-uppercase mt-2 mb-n1" v-text="$root.label('monograms')"></div>
                                            <v-row>
                                                <v-col
                                                    v-for="d in item[i].monograms"
                                                    :key="'monograms' + d.id"
                                                    cols=12
                                                    sm=6
                                                    lg=4
                                                >
                                                    <div class="d-flex">
                                                        <div v-html="$handlers.format.image_tile(d.link, 40)"></div>
                                                        <div class="caption pl-3 mt-n1" v-text="d.position ? d.position.text[l] : '--'"></div>
                                                    </div>
                                                </v-col>
                                            </v-row>
                                        </template>
                                        <!-- Symbols / Monograms
                                        <template v-for="section in ['symbols', 'monograms']">
                                            <template v-if="item[i][section]">
                                                <div class="text-uppercase mt-2" :key="'h' + section" v-text="$root.label(section)"></div>
                                                <div :key="section" class="d-flex flex-wrap mt-1">
                                                    <div
                                                        v-for="record in item[i][section]"
                                                        :key="section + i + record.id"
                                                        v-html="$handlers.format.stringify_data.monogram_symbol(section, record, l)"
                                                    ></div>
                                                </div>
                                            </template>
                                        </template> -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div style="position: absolute; bottom: 0px; width: 100%">
                            <v-divider></v-divider>
                            <div class="d-flex justify-space-between">
                                <div class="caption pa-3" v-html="$handlers.show_item_data(l, entity, item, 'card_footer')"></div>
                                <div v-if="item.name_private" v-text="item.name_private" class="pa-3 ml-5 caption text-truncate"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Body expanded -->
                    <div v-else class="sheet_bg pa-5">
                    <details-content
                        :entity="entity"
                        :id="item.id"
                    />
                    </div>

                </v-col>
            </v-row>
        </div>

    </v-card>

</div>
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
            expanded: false
        }
    },

    props: {
        entity:         { type: String },
        item:           { type: Object },
        publisher:      { type: Boolean },
        checked:        { type: Boolean },
        vertical_forced:{ type: Boolean, default: false }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        inheriting_type () {
            if(this.item.types) {
                let found = null
                this.item.types.forEach((v) => {
                    if(v.inherited === 1) { found = v.id }
                })
                return found
            }
            else { return null }
        }
    }
}

</script>
