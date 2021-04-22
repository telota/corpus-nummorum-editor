<template>
<div>

    <v-card tile :loading="loading" style="width: 100%">
        <!-- JK: Appbar / Title -->
        <div class="grey_prim d-flex component-wrap align-center pa-3 pl-4" style="width: 100%">
            <div 
                :class="'headline' + (item_info.public === 3 ? ' text-decoration-line-through' : '')"
                v-html="header"
            ></div>

            <v-spacer></v-spacer>

            <div class="d-flex">
                <v-btn
                    v-if="id && ['types-edit', 'coins-edit'].includes($route.name)"
                    tile 
                    color="blue_sec" 
                    v-text="item_info.public === 2 ? 'Publishing requested' : 'Request Publishing'"
                    :disabled="!id || item_info.public != 0"
                    class="mr-3"
                    @click="$emit('mark', true)"
                ></v-btn>

                <v-btn 
                    v-if="id"
                    tile 
                    color="blue_prim" 
                    v-text="'save'"
                    :disabled="item_info.public === 3"
                    @click="$emit('save', true)"
                ></v-btn>
            </div>
        </div>

        <!-- JK: Toolbar -->
        <div class="d-flex component-wrap align-center appbar">
            <div 
                v-if="id"
                class="caption pl-4"
                v-html="item_info.system ? item_info.system : ''"
            ></div>
            <v-spacer></v-spacer>

            <!-- Actions -->
            <a 
                v-if="!id || ['types-copy', 'coins-copy'].includes($route.name)" 
                :href="'/editor#/' + this.entity + '/search'"
            >
                <v-btn tile depressed>
                    <v-icon v-text="'search'"></v-icon>
                </v-btn>
            </a>
            <template v-else>
                <advbtn
                    icon="delete"
                    tooltip="Request Deletion"
                    :disabled="item_info.public === 3"
                    @click="$emit('erase', true)"
                ></advbtn>
                
                <commandbar
                    :entity="entity"
                    :id="id"
                    :key="id + entity + item_info.public"
                    :public_state="item_info.public"
                    :publisher="false"
                    v-on:details="details_dialog = { entity: entity, id: id, public: item_info.public }"
                ></commandbar>
            </template>
        </div>
    </v-card>

    <v-row class="mt-2">
        <v-col 
            cols="12" 
            sm="12" 
            :md="12 - (id ? zoom : 0)"
        >                
            <slot name="content"></slot>
        </v-col>

        <!-- Image Preview -->
        <v-col
            v-if="id"
            cols="12" 
            sm="12" 
            :md="zoom"
            :class="$vuetify.breakpoint.mdAndUp ? 'pl-0 pl-2' : ''"
        >
            <itemImager
                :images="images"
                :index="img_index"
                :zoom="zoom"
                :refresh="refresh"
                v-on:zoom="(emit) => { zoom = zoom + emit }"
                v-on:index="(emit) => { $emit('index', emit) }"
            ></itemImager>
            
            <v-btn
                tile
                block
                color="blue_prim"
                class="mt-4"
                v-text="'save'"
                :disabled="item_info.public === 3"
                @click="$emit('save', true)"
            ></v-btn>
        </v-col>
    </v-row>

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
import itemImager       from './../pages/entities_primary/modules/itemImager.vue'

export default {
    components: {
        itemImager
    },

    data () {
        return {
            zoom: 2,
            //refresh: 0

            details_dialog: {
                entity: null, 
                id: null,
                public: 0
            },
        }
    },

    props: {
        entity:         { type: String, required: true },
        id:             { type: [String, Number], default: null },
        item:           { type: Object },
        images:         { type: Array },
        img_index:      { type: Number, default: 0 },
        loading:        { type: Boolean, default: false },
        refresh:        { type: Number, default: 0 }
    },

    computed: {
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        item_info () {
            return {
                public: this.item?.dbi?.public ? this.item.dbi.public : 0,
                //inherited: this.item?.dbi?.inherited?.id_type ? ,
                //mint: this.item?.mint?.text?.[this.l],
                system: this.$handlers.show_item_data(this.l, this.entity, this.item, 'system')
            }
        },
        
        header () {
            if (['coins-import', 'types-import'].includes(this.$route.name)) {
                return 'Import external Data as new cn ' + this.entity.slice(0, -1)
            }
            else if (['types-copy', 'coins-copy'].includes(this.$route.name)) {
                return 'Copy ' + this.$handlers.format.cn_entity(this.entity, this.id) + this.$handlers.format.cn_public_link({ 
                    id: this.id, 
                    kind: this.entity.slice(0, 1).toUpperCase() + this.entity.slice(1, -1), 
                    public: this.item_info.public 
                }) + ' as new ' + this.$root.label(this.$route.name.slice(0, 4)).toUpperCase()
            }
            else if (this.id) {
                return this.$handlers.format.cn_entity(this.entity, this.id) + this.$handlers.format.cn_public_link({ 
                    id: this.id, 
                    kind: this.entity.slice(0, 1).toUpperCase() + this.entity.slice(1, -1), 
                    public: this.item_info.public 
                })
            }
            else {
                return this.$root.label('new,' + this.entity.slice(0, -1))
            }
        }
    },

    watch: {
        entity: function () {
            console.log('entity switched')
        },
    },

    created () {
    },
    
    methods: {
        updateEditor (active, input) {
            
        }
    }
}
</script>