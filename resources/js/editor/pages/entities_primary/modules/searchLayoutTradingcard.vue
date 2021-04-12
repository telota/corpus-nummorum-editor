<template>
<div>

    <v-card tile class="appbar">
        <!-- Publisher -->
        <v-btn tile block depressed 
            v-if="publisher" 
            :color="checked ? 'blue_prim' : 'sysbar'" 
            :disabled="item.public === 3 || item.public === 1"
            @click="$emit('checked')"
        >
            <v-icon>{{ checked ? 'radio_button_checked' : 'radio_button_unchecked'}}</v-icon>
        </v-btn>

        <!-- Image -->
        <Imager
            coin hide_text contain
            :key="item.id"
            :item="item.images ? item : {images: []}"
            :color_background="(item.images ? (item.images[0].obverse.bg_color == 'white' ? 'white' : null) : null)"
            :class="(item.images ? (item.images[0].obverse.bg_color == 'white' ? 'white' : 'imgbg') : 'imgbg')+' pa-1'"
        ></Imager>

        <!-- Content -->
        <v-card-text class="pt-4 caption">
            <div class="mt-n1 mb-1 body-2">
                <!-- Name -->
                <div class="body-1 font-weight-black" :class="item.public === 3 ? ' text-decoration-line-through' : ''">
                    <span v-html="$handlers.format.cn_entity(entity, item.id)"></span>
                    <span v-html="$handlers.format.cn_public_link(item)"></span>
                </div>
                <!-- Types / Coins -->
                <linkedInherited
                    :entity="entity"
                    :item="item"
                    v-on:details="(emit) => { details_dialog = { entity: emit.entity, id: emit.id, public: 0 }}"
                ></linkedInherited>
                <!-- Mint -->
                <div 
                    v-if="item.mint.text[l]"
                    v-text="item.mint.text[l]"
                ></div>
            </div>
            <!-- Diameter and Weight -->
            <div 
                class="caption" 
                v-html="$handlers.show_item_data(l, entity, item, 'card_header')"
            ></div>

            <!-- Depiction -->
            <v-divider class="mb-3 mt-3"></v-divider>
            <div v-for="(i) in ['obverse', 'reverse']" :key="i" class="body-2 d-flex component-wrap mb-3">
                <div class="font-weight-bold text-uppercase mr-3" v-text="i.slice(0, 1)"></div> 
                <div>
                    <div 
                        class="font-weight-thin pb-1" 
                        v-text="item [i].legend ? (item [i].legend.string ? item [i].legend.string : '--') : '--'"
                    ></div>
                    <div v-text="item [i].design ? (item [i].design.text[l] ? item [i].design.text[l] : '--') : '--'"></div>
                </div>
            </div>

            <!-- Footer -->
            <v-divider></v-divider>
            <div class="caption mt-3" v-html="$handlers.show_item_data(l, entity, item, 'card_footer')"></div>
        </v-card-text>

        <!-- Actions -->
        <v-divider></v-divider>
        <commandbar
            small
            :entity="entity"
            :id="item.id"
            :public_state="item.public"
            :publisher="publisher"
            :key="item.id + entity + (publisher ? 1 : 0) + item.public"
            v-on:details="details_dialog = { entity: entity, id: item.id, public: item.public }"
            v-on:publish="(emit) => { $emit('publish') }"
        ></commandbar>                    
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
import linkedInherited from './linkedInherited.vue'

export default {
    components: {
        linkedInherited
    },

    data () {
        return {
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