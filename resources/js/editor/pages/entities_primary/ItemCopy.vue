<template>
<div>

<ItemInputTemplate
    :entity="source_entity"
    :id="source_id"
    :item="source_data"
    :images="source_data.images ? source_data.images : []"
    :img_index="img_index"
    :loading="loading"
    v-on:save="save()"
    v-on:index="(emit) => { img_index = emit }"
>
    <template v-slot:content>
        <v-expand-transition>
            <v-card v-if="source_data.id" tile class="grey_trip pa-5">
                <div v-for="(keys, section) in copy_keys" :key="section" class="mb-5">
                    <div class="d-flex align-center">
                        <div class="title text-uppercase" v-text="$root.label(section)"></div>
                        <v-divider class="ml-2"></v-divider>
                    </div>                
                    <v-row class="pl-5">
                        <v-col 
                            cols="12" 
                            sm="6"
                            md="4"
                            lg="3" 
                            v-for="(value, key) in keys" 
                            :key="key"
                        >
                            <div class="d-flex align-start">
                                <v-checkbox
                                    v-model="copy_keys[section][key]"
                                    class="mt-0"
                                    color="blue_prim"
                                ></v-checkbox>
                                <div class="pt-1 ml-1">
                                    <div class="mb-1 font-weight-bold" v-text="$root.label(key)"></div>
                                    <div v-html="showItemData(key, section)"></div>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </div>                
            </v-card>
        </v-expand-transition>
    </template>
</ItemInputTemplate>

</div>
</template>


<script>

export default {
    data () {
        return {            
            source_entity:  this.$route.params.source,
            source_id:      parseInt(this.$route.params.id),
            source_data:    {},

            loading:        false,
            img_index:      0,
            
            copy_keys: {},
            copy_keys_template: {
                production: [
                    { label: 'mint',                cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'issuer',              cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'authority',           cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'authority_person',    cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'authority_group',     cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'date',                cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'period',              cc: 1, ct: 1, tt: 1, tc: 1 }
                ],
                basics: [
                    { label: 'material',            cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'denomination',        cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'standard',            cc: 1, ct: 1, tt: 1, tc: 1 }
                ],
                depiction: [
                    { label: 'design',              cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'legend',              cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'monograms',           cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'symbols',             cc: 1, ct: 1, tt: 1, tc: 1 },
                    { label: 'controlmarks',        cc: 1, ct: 0, tt: 0, tc: 0 }
                ],
                references: [
                    { label: 'citations',           cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'literature',          cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'links',               cc: 1, ct: 0, tt: 1, tc: 0 }
                ],
                individuals: [
                    { label: 'persons',             cc: 1, ct: 1, tt: 1, tc: 1 }
                ],
                miscellaneous: [
                    { label: 'comment_private',     cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'comment_public',      cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'pecularities',        cc: 1, ct: 0, tt: 1, tc: 0 },
                    { label: 'groups',              cc: 1, ct: 0, tt: 1, tc: 0 }
                ]
            }
        }
    },

    props: {
        entity: { type: String }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        mode () { return this.entity.substring(0,1) + this.source_entity.substring(0,1) }
    },

    created () {
        this.$store.commit('setBreadcrumbs', [ // JK: Set Breadcrumbs
            { label: this.entity, to:'' },
            { label: 'copy', to:'' },
            { label: this.source_entity.slice(0, -1) + ' ' + this.source_id, to:'' }
        ]);

        this.getSourceData()
    },
    
    
    methods: {        
        async getSourceData () {

            this.$root.loading = this.loading = true
            const self = this
            const sections = []

            // Create Sections from template
            Object.keys(this.copy_keys_template).forEach((index) => {
                if(index === 'depiction') {
                    sections.push({ index: index, section: 'obverse' })
                    sections.push({ index: index, section: 'reverse' })
                } else {
                    sections.push({ index: index, section: index })
                }
            })

            // Create Copy Keys
            sections.forEach((v) => {
                self.copy_keys[v.section] = {}
                self.copy_keys_template[v.index].forEach((key) => {
                    if(key[self.mode]) { self.copy_keys[v.section][key.label] = true }
                })
            })
            
            const dbi = await this.$root.DBI_SELECT_GET (this.source_entity, this.source_id)

            if (dbi?.contents?.[0]) {
                this.source_data = dbi.contents[0]
            }
            else {
                alert('ERROR: No Date received!')
            }

            this.$root.loading = this.loading = false
        },

        createItem () {
            const self = this
            let item = this.$handlers.create_item(this.entity)
            const source_item = this.$handlers.create_item(this.source_entity, this.source_data)

            // Set inheriting Type 
            if(this.mode === 'ct') { 
                item.inherited.id_type = source_item.id 
            }
            
            // Iterate over Copy Keys
            Object.keys(self.copy_keys).forEach((section) => {
                Object.keys(self.copy_keys[section]).forEach((key) => {
                    if(self.copy_keys[section][key]) {
                        if(['obverse', 'reverse'].includes(section)) { 
                            key = section.slice(0, 1) + '_' + key // add section if obverse/reverse
                        }
                        item = self.$handlers.copy_item_data(item, source_item, key) 
                    }
                })
            })

            return item
        },

        async save () {
            this.$root.loading = this.loading = true
            const item = this.createItem()

            const response = await this.$root.DBI_INPUT_POST (this.entity, 'input', item);

            if (response?.success) {
                this.$root.snackbar(response.success, 'success');
                this.$router.push('/' + this.entity + '/edit/' + response.id)
            }
            else {
                console.log('Data Input Error: response was not ok');
            }
            this.$root.loading = this.loading = false
        },

        showItemData (key, section) {
            return this.$handlers.show_item_data(this.$root.language, this.source_entity, this.source_data, key, section)
        }
    }
}

</script>