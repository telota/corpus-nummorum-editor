<template>
<div class="mt-n2 mb-n2">

    <v-card tile flat>
        <div class="appbar d-flex align-start">

            <!-- Publisher -->
            <div class="d-flex align-center">
                <v-btn
                    fab
                    x-small
                    v-if="publisher"
                    :color="checked ? 'blue_prim' : 'sysbar'"
                    class="ma-2"
                    :disabled="item.public === 3 || item.public === 1"
                    @click="$emit('checked')"
                >
                    <v-icon v-text="checked ? 'radio_button_checked' : 'radio_button_unchecked'"></v-icon>
                </v-btn>
            </div>

            <!-- Content (not expanded) -->
            <div v-if="!expanded" style="width: 100%">
                <v-row class="pa-0 ma-0">

                    <v-col cols="12" sm="2">
                        <!-- Name -->
                        <div class="body-1 font-weight-black mb-1" :class="item.public === 3 ? ' text-decoration-line-through' : ''">
                            <span v-html="$handlers.format.cn_entity(entity, item.id)"></span>
                            <span v-html="$handlers.format.cn_public_link(item)"></span>
                        </div>
                        <!-- Types / Coins -->
                        <linkedInherited
                            :entity="entity"
                            :item="item"
                            v-on:details="(emit) => { details_dialog = { entity: emit.entity, id: emit.id, public: 0 }}"
                        ></linkedInherited>
                    </v-col>

                    <!-- Diameter / Weight -->
                    <v-col cols="12" sm="2">
                        <!-- Mint -->
                        <div
                            v-if="item.mint.text[l]"
                            v-text="item.mint.text[l]"
                            class="mb-1"
                        ></div>

                        <!-- Diameter and Weight -->
                        <div v-html="$handlers.show_item_data(l, entity, item, 'card_header')"></div>
                    </v-col>

                    <!-- Depiction -->
                    <v-col v-for="(i) in ['obverse', 'reverse']" :key="i" cols="12" sm="3">
                        <div  class="body-2 d-flex component-wrap">
                            <div class="font-weight-bold text-uppercase mr-3" v-text="i.slice(0, 1)"></div>
                            <div>
                                <div class="font-weight-thin pb-1" v-text="item [i].legend ? (item [i].legend.string ? item [i].legend.string : '--') : '--'"></div>
                                <div v-text="item [i].design ? (item [i].design.text[l] ? item [i].design.text[l] : '--') : '--'"></div>
                            </div>
                        </div>
                    </v-col>

                    <!-- Date etc. -->
                    <v-col cols="12" sm="2">
                        <div v-html="$handlers.show_item_data(l, entity, item, 'card_footer')"></div>
                    </v-col>

                </v-row>
            </div>

            <!-- Name and Status if expanded -->
            <div v-else class="d-flex align-end pa-3">
                <!-- Name -->
                <div class="body-1 font-weight-black" :class="item.public === 3 ? ' text-decoration-line-through' : ''">
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
            </div>
            <v-spacer v-if="expanded"></v-spacer>

            <!-- Actions -->
            <commandbar
                :entity="entity"
                :id="item.id"
                :public_state="item.public"
                :publisher="publisher"
                :key="item.id + entity + (publisher ? 1 : 0) + item.public"
                v-on:details="(emit) => { expanded = !expanded }"
                v-on:publish="(emit) => { $emit('publish') }"
            ></commandbar>
        </div>

        <!-- expanded -->
        <div v-if="expanded" style="width: 100%">
            <v-divider></v-divider>
            <detailscontent
                :entity="entity"
                :id="item.id"
                style="width: 100%"
            ></detailscontent>
        </div>

    </v-card>

    <!-- Details Dialog -->
    <detailsdialog
        v-if="details_dialog.entity"
        :entity="details_dialog.entity"
        :id="details_dialog.id"
        :public_state="details_dialog.public"
        v-on:close="details_dialog = { entity: null, id: null, public: 0 }"
    ></detailsdialog>

</div>
</template>


<script>

import detailscontent from './../modules/detailsContent.vue'
import linkedInherited from './../modules/linkedInherited.vue'

export default {
    components: {
        detailscontent,
        linkedInherited
    },

    data () {
        return {
            expanded: false,
            details_dialog: {
                entity: null,
                id: null
            }
        }
    },

    props: {
        entity:         { type: String },
        item:           { type: Object },
        publisher:      { type: Boolean },
        checked:        { type: Boolean }
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
