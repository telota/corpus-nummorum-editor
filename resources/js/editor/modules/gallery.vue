<template>
<div>
    
    <!-- Header -->
    <div class="mb-1 d-flex component-wrap align-center">
        <!-- Label -->
        <div 
            class="mr-4 title text-uppercase" 
            style="white-space: nowrap" 
            v-html="labels['linked_plural'] + ' ' + labels[entity] + '&ensp;(&nbsp;' + counter + '&nbsp;)'"
        ></div>

        <!-- Loading -->
        <div style="width: 100%">
            <v-divider></v-divider>
            <v-progress-linear indeterminate v-if="loading"></v-progress-linear>
        </div>

        <!-- Edit -->
        <div 
            v-if="editor" 
            class="d-flex justify-end" 
            style="white-space: nowrap"
        >
            <v-btn 
                text 
                v-text="'Manage Links'"
                @click="$emit('editor', true)"
            ></v-btn>
        </div>
    </div>    

    <!-- Gallery -->
    <v-expand-transition>
        <div v-if="!items[0]" v-text="'--'"></div>

        <div v-else class="d-flex component-wrap">

            <v-hover>
                <template v-slot:default="{ hover }" >
                    <v-card
                        tile
                        hover
                        class="d-flex align-center mt-3 mb-3 mr-5 pa-1"
                        :class="(disabled.previous ? color_main : (hover ? color_hover : color_main))"
                        :disabled="disabled.previous"
                        @click="navigate('previous');"
                    >
                        <v-icon :class="disabled.previous ? 'grey--text' : ''" v-text="'navigate_before'"></v-icon>
                    </v-card>
                </template>
            </v-hover>

            <v-row v-if="items">
                <v-col 
                    v-for="(item, i) in items" 
                    :key="i"
                    cols="12" 
                    :sm="cols.sm" 
                    :md="cols.md" 
                    :xl="cols.xl" 
                >
                    <v-card 
                        tile
                        :class="color_main"
                    >
                        <v-card 
                            tile
                            flat
                            @click="$emit('select', item.id)"
                        >
                            <Imager
                                coin hide_text contain
                                :item="item.images ? item : {images: []}"
                                :key="item.id"
                                :color_background="item.images ? (item.images[0].obverse.bg_color == 'white' ? 'white' : null) : null"
                                :class="( item.images ? (item.images[0].obverse.bg_color == 'white' ? 'white' : 'imgbg') : 'imgbg')+' pa-1'"                    
                            ></Imager>
                        </v-card>
                        
                        <!-- Name -->
                        <div 
                            :class="'pa-2 d-flex justify-center body-2 font-weight-bold' + (item.public == 3 ? ' text-decoration-line-through' : '')"
                            v-html="'cn ' + entity.slice(0, -1) + ' ' + item.id + (item.public === 1 ? ' &ast;' : '')" 
                        ></div>

                        <!-- Toolbar -->
                        <div v-if="show_toolbar" class="d-flex component-wrap">
                            <advbtn
                                color_main="transparent"
                                icon="edit"
                                :tooltip="'Edit ' + entity.slice(0, -1) + ' in new window.'"
                                v-on:click="$emit('edit', item.id)"
                            ></advbtn>
                            <advbtn
                                color_main="transparent"
                                icon="link_off"
                                :tooltip="'Unlink ' + entity.slice(0, -1)"
                                v-on:click="$emit('delete', item.id)"
                            ></advbtn>
                            <v-spacer></v-spacer>
                            <advbtn v-if="entity == 'coins' && item.images"
                                color_main="transparent"
                                icon="flip_camera_ios"
                                :tooltip="'Choose ' + entity.slice(0, -1) + ' as representing for Type.'"
                                v-on:click="$emit('image', {id: item.id, img: item.images})"
                            ></advbtn>
                        </div>

                    </v-card>
                </v-col>
            </v-row>

            <v-hover>
                <template v-slot:default="{ hover }" >
                    <v-card
                        tile
                        hover
                        class="d-flex align-center mt-3 mb-3 ml-5 pa-1"
                        :class="(disabled.next ? color_main : (hover ? color_hover : color_main))"
                        :disabled="disabled.next"
                        @click="navigate('next')"
                    >
                        <v-icon :class="disabled.next ? 'grey--text' : ''" v-text="'navigate_next'"></v-icon>
                    </v-card>
                </template>
            </v-hover>

        </div>
    </v-expand-transition>

</div>
</template>



<script>

export default { 
    data () {
        return {
            search:         { state_public: [0, 1] },
            items:          [],

            loading:        false,
            no_wrap:        'white-space: nowrap;',

            offset:         0,
            count:          null
        }        
    },

    props: {
        search_key:     { type: String },
        search_val:     { type: [String, Number] },
        entity:         { type: String },
        editor:         { type: Boolean },
        show_toolbar:   { type: Boolean },
        color_main:     { type: String, default: 'appbar' }, 
        color_hover:    { type: String, default: 'sysbar' },
        limit:          { type: [String, Number], default: 24 },
        tiles:          { type: [String, Number], default: 6 }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        cols () { 
            return this.tiles === 4 ? { sm: 6, md: 3, xl: 2 } : { sm: 4, md: 2, xl: 1 } 
        },

        disabled () {
            return {
                previous: this.offset > 0 ? false : true,
                next: this.offset < this.count - this.limit ? false : true
            }
        },

        counter () {
            if (!this.count) { return '-'}
            else {
                if (this.count > this.limit) {
                    return (this.offset + 1) + '&ndash;' + (this.offset + this.items.length) + '&nbsp;/&nbsp;' + this.count
                }
                else {
                    return this.count
                }
            }
        }
    },

    created () {
        if (this.search_key && this.search_val) {
            this.search[this.search_key] = this.search_val
            this.GetItems ();
        }
    },

    methods: {
        async GetItems () {
            this.loading    = true;

            const params = {
                sort_by: 'id.asc',
                offset: this.offset,
                limit:  this.limit
            }
            
            const dbi = await this.$root.DBI_SELECT_POST(this.entity, params, this.search)
            
            if (dbi.contents)
            {
                this.offset = dbi.pagination.offset
                this.count = dbi.pagination.count
                this.items = dbi.contents
            }
            
            this.loading = false;
        },

        async navigate (key) {
            this.offset = key === 'next' ? (this.offset + this.limit) : (this.offset - this.limit)
            this.GetItems ()
        }
    }
}

</script>