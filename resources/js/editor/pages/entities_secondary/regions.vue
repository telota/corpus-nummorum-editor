<template>
    <div>
        <v-card tile min-height="300">
            <v-card tile class="grey_prim">
                <div v-if="items[0]" class="d-flex justify-content" style="width: 100%">
                    <div v-for="i in items" :key="'r' + i.id" :style="'width: ' + Math.floor(100 / items.length) + '%'">
                        <v-btn 
                            :tile="i.id != item.id"
                            :text="i.id === item.id"
                            block 
                            depressed 
                            :color="i.id === item.id ? 'grey_prim' : 'blue_prim'"
                            :disabled="i.id === item.id"
                            v-text="i['name_' + language]" 
                            @click="item = i" 
                        />
                    </div>
                </div>
                <div class="headline pa-4" style="width: 100%" v-text="item.id ? item['name_' + language] : 'No Item selected'"></div>
            </v-card>
            
            <v-expand-transition>
                <v-card-text >
                    <div v-if="item.id">
                        <v-row>
                            <v-col v-for="(m, i) in item.mints" :key="i" cols=12 md=4>
                                <span v-html="$handlers.format.editor_link('mints', m.id)" />
                                <span v-html="m.name" /> /
                                <span v-html="$handlers.format.nomisma_link(m.nomisma)" />
                            </v-col>
                        </v-row>

                        <ItemGallery
                            :key="'g' + item.id"
                            :entity="entity"
                            search_key="id_region" 
                            :search_val="item.id ? item.id : 0"
                            color_main="grey_trip"
                            color_hover="marked"
                            class="mt-5"
                        />
                    </div>

                    <div v-else>
                        Regions must be created by IT. Please contact the administrators if you would like one to be added.
                    </div>
                </v-card-text>
            </v-expand-transition>
            
        </v-card>       
    
    </div>
</template>


<script>

export default {
    data () {
        return {
            entity:     'regions',
            loading:    false,
            items:      [],
            item:       {},
            search:     '',
            log:        null,

            headers: [
                { 
                    value: 'id',
                    text: 'ID' 
                },
                { 
                    value: 'name_de',
                    text: 'Name (DE)' 
                },
                { 
                    value: 'name_en',
                    text: 'Name (EN)' 
                }
            ]
        }
    },

    props: {
    },

    computed: {
        language () {
            return this.$root.language === 'de' ? 'de' : 'en'
        }       
    },

    created () {
        this.getItems()
    },
    
    methods: {
        selectItem(item, selector) {
            this.log = null
            this.item = item
        },

        async getItems () {
            this.loading = true

            let dbi = {}
            dbi = await this.$root.DBI_SELECT_GET(this.entity, null)
            
            if (dbi?.contents?.[0]?.id) {
                this.items = dbi.contents
            }

            this.loading = false
        }
    }
}

</script>