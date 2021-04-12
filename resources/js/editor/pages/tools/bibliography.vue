<template>
    <div>
        <v-row>            
            <v-col cols=12 md=7 lg=8 xl=9>
                <v-card tile>
                    <v-app-bar color="sysbar">
                        <div class="headline">Zotero Library</div>
                        <v-spacer></v-spacer>
                        <v-text-field
                            dense outlined filled clearable
                            v-model="search"
                            prepend-icon="search"
                            label="Search"
                            single-line
                            hide-details
                            class="ml-5 mr-5"
                        ></v-text-field>
                        <v-btn tile color="blue_prim" v-text="'update'" :disabled="$root.user.level < 12" @click="fetchZotero()"></v-btn>
                    </v-app-bar>

                    <v-data-table
                        :items="items"
                        :headers="headers"
                        :loading="loading"
                        :search="search"
                        :items-per-page="20"
                        :footer-props="{
                            'items-per-page-options': [20, 50, 100]
                        }"
                        @click:row="selectItem"
                    ></v-data-table>
                </v-card>
            </v-col>

            <v-col cols=12 md=5 lg=4 xl=3>
                <v-card tile min-height="300">

                    <v-app-bar color="sysbar">
                        <div class="headline" style="width: 100%">
                            <span 
                                v-if="log"
                                v-text="'Update Log'"
                            ></span> 
                            <a 
                                v-else-if="item.id"
                                :href="item.link" 
                                target="_blank"
                            >
                                <div class="invert--text d-flex">
                                    <div v-text="item.id"></div>
                                    <v-spacer></v-spacer>
                                    <v-icon v-text="'link'"></v-icon>
                                </div>
                            </a>
                            <span 
                                v-else 
                                v-text="'No Item selected'"
                            ></span>
                        </div>
                    </v-app-bar>
                    
                    <v-expand-transition>
                        <v-card-text >
                            <div v-if="log">
                                <div v-html="log_parsed"></div>
                            </div> 
                                                   
                            <table v-else-if="item.id" style="border-collapse: collapse; border-spacing: 0;">
                                <tr v-for="(val, key) in item" :key="key">
                                    <td
                                        class="pa-1 d-flex align-start font-weight-thin text-uppercase pr-5"
                                        style="white-space: nowrap"
                                        v-text="key.replaceAll('_', ' ')"></td>                                
                                    <td class="pa-1">
                                        <a 
                                            v-if="key === 'link' && val"
                                            :href="val"
                                            target="_blank"
                                            v-text="val.split('//').pop()"
                                        ></a>
                                        <span v-else v-text="val ? val : '--'"></span>
                                    </td>
                                </tr>
                            </table>

                            <div v-else>
                                Zotero Library is updated every night automatically.<br/><br/>
                                However, fully authorised users are permitted to trigger a manual update.
                            </div>
                        </v-card-text>
                    </v-expand-transition>
                    
                </v-card>
            
            </v-col>
        </v-row>        
    
    </div>
</template>


<script>

export default {
    data () {
        return {
            entity:     'bibliography',
            loading:    false,
            items:      [],
            item:       {},
            search:     '',
            log:        null,

            headers: [
                { 
                    value: 'id',
                    text: 'ZoteroID' 
                },
                { 
                    value: 'author',
                    text: 'Author' 
                },
                { 
                    value: 'year',
                    text: 'Year' 
                },
                { 
                    value: 'title_short',
                    text: 'Title'
                },
                {
                    value: 'created_at',
                    text: 'Added',
                    filterable: false
                },
                { 
                    value: 'updated_at',
                    text: 'Modified',
                    filterable: false
                },
                {
                    value: 'fetched_at',
                    text: 'Checked',
                    filterable: false 
                },
                { 
                    value: 'status',
                    text: 'Status',
                    sortable: false
                }
            ]
        }
    },

    props: {
    },

    computed: {
        language () {
            return this.$root.language === 'de' ? 'de' : 'en'
        },

        log_parsed () {
            if (this.log) {
                const log = this.log.replaceAll('------------------------\n','').replaceAll('\t','&emsp;').trim().split('\n')
                return log.join('<br/>')
            }
            else {
                return '--'
            }
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
        },

        async fetchZotero () {
            if (this.$root.user.level > 11) {
                alert('Updating Zotero Titles might take a while. Please contact the IT team if any problems occure.')
                this.$root.loading = true
                const self = this

                await axios.post('dbi/' + this.entity + '/input', {})
                    .then((response) => {
                        console.log(response?.data)
                        self.log = response?.data ? response.data : null
                        self.$root.snackbar({
                            en: 'Zotero Titles were updated',
                            de: 'Zotero-Titel aktualisiert'
                        }, 'success');
                        self.getItems()
                    })
                    .catch((error) => {
                        self.$root.AXIOS_ERROR_HANDLER(error)
                    })

                this.$root.loading = false
            }
            else {
                alert('You are not permitted to update Zotero References. Please contact an administrator.')
            }
        }
    }
}

</script>