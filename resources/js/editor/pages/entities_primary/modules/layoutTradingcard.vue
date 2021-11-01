<template>
    <v-card tile :class="select && selected == item.id ? 'tile_selected' : 'tile_bg'">
        <!-- Publisher -->
        <v-btn tile block depressed
            v-if="publisher"
            :color="checked ? 'blue_prim' : 'transparent'"
            :dark="checked"
            :disabled="item.public === 3 || item.public === 1"
            @click="$emit('check')"
        >
            <v-icon v-text="checked ? 'radio_button_checked' : 'radio_button_unchecked'" />
        </v-btn>

        <!-- Image -->
        <coin-images :images="item.id ? item.images : []" />


        <div class="mt-n1 mb-n1" :class="truncate ? 'pa-3' : 'pa-4'">
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
                <div class="caption font-weight-bold text-truncate" v-html="$handlers.show_item_data(language, entity, item, 'forgery')" />
            </div>
        </div>

        <v-divider />

        <!-- Content -->
        <div
            style="overflow-y: auto;"
            :style="'height:' + (truncate ? 235 : 300) + 'px'"
        >
            <v-card-text
                class="caption"
                :class="truncate ? 'pa-3' : 'pa-4 pb-2'"
            >
                <div
                    class="mt-n1 mb-1"
                    :class="truncate ? '' : 'body-2'"
                >
                    <!-- Types / Coins -->
                    <linkedInherited
                        :entity="entity"
                        :item="item"
                        v-on:details="(emit) => { details_dialog = { entity: emit.entity, id: emit.id, public: 0 }}"
                    />

                    <!-- Mint -->
                    <div
                        v-if="item.mint.text[language]"
                        class="font-weight-bold mt-1"
                        v-text="item.mint.text[language]"
                    />
                </div>

                <!-- Diameter and Weight -->
                <div
                    class="caption"
                    v-html="$handlers.show_item_data(language, entity, item, 'card_header')"
                />

                <div
                    class="caption"
                    :class="truncate ? 'text-truncate' : ''"
                    v-html="$handlers.show_item_data(language, entity, item, 'card_footer')"
                />

                <!-- Depiction -->
                <v-divider :class="truncate ? 'mt-2 mb-2' : 'mt-3 mb-3'" />

                <div
                    v-for="(side, s) in ['obverse', 'reverse']"
                    :key="side"
                    :class="(truncate ? 'caption' : 'body-2') + (s === 0 ? ' mb-3' : '')"
                    style="position: relative"
                >
                    <div
                        class="font-weight-bold text-uppercase"
                        v-text="side.slice(0, 1)"
                        style="position: absolute"
                    />
                    <div>
                        <div
                            class="pl-4 font-weight-thin pb-1"
                            :class="truncate ? 'text-truncate' : ''"
                            :title="truncate ? printLegend(side) : null"
                            v-text="printLegend(side)"
                        />
                        <div
                            class="pl-4"
                            :class="truncate ? 'text-truncate' : ''"
                            :title="truncate ? printDesign(side) : null"
                            v-text="printDesign(side)"
                        />
                    </div>
                </div>
            </v-card-text>
        </div>

        <!-- Actions -->
        <v-divider />

        <commandbar
            small
            :entity="entity"
            :id="item.id"
            :status="item.public"
            :publisher="publisher"
            :select="select"
            :selected="selected"
            :linking="linking"
            v-on:publish="$emit('publish')"
            v-on:unlink="$emit('unlink')"
            v-on:inherit="$emit('inherit')"
            v-on:represent="$emit('represent')"
            v-on:select="$emit('select', item.id)"
        />
    </v-card>
</template>


<script>
import linkedInherited from './../modules/linkedInherited.vue'

export default {
    components: {
        linkedInherited
    },

    props: {
        entity:         { type: String, required: true },
        item:           { type: Object, default: () => {} },
        publisher:      { type: Boolean, default: false },
        checked:        { type: Boolean, default: false },
        linking:        { type: Boolean, default: false },
        select:         { type: Boolean, default: false },
        selected:       { type: [Number, String], default: null },
        truncate:       { type: Boolean, default: false }
    },

    computed: {
        language () { return this.$root.language },

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
    },

    methods: {
        printDesign (side) { return this.item?.[side]?.design?.text?.[this.language] ?? '--' },
        printLegend (side) { return this.item?.[side]?.legend?.string ?? '--' }
    }
}

</script>
