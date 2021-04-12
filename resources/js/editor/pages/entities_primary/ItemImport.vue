<template>
<div>

<ItemInputTemplate
    :entity="entity"
    :loading="loading"
>
    <template v-slot:content>

        <!-- JK: Input Mask -->
        <div class="mt-n2">

            <!-- JK: Appbar / Title
            <v-app-bar color="sysbar">
                <div class="d-flex component-wrap align-end" style="width: 100%">
                    <div v-html="header"></div>
                </div>
                <v-spacer></v-spacer>
                <v-btn tile 
                    v-text="'save AND proceed'" 
                    color="blue_prim" 
                    :disabled="!source_data.url"
                    @click="save()"
                ></v-btn>
            </v-app-bar> -->

            <!-- JK: Toolbar
            <v-card tile class="d-flex component-wrap align-center appbar">
                <a 
                    v-if="source_data.url" 
                    :href="source_data.url" 
                    target="_blank"
                    class="font-weight-bold ml-4"
                    v-text="source_data.url"
                ></a>
                <v-spacer></v-spacer>

                <a :href="'/editor#/' + this.entity + '/edit'">
                    <v-btn tile depressed>
                        <v-icon v-text="'add'"></v-icon>
                    </v-btn>
                </a>

                <a :href="'/editor#/' + this.entity + '/search'">
                    <v-btn tile depressed>
                        <v-icon v-text="'search'"></v-icon>
                    </v-btn>
                </a>
            </v-card> -->
        
            <!-- Content -->
            <v-expand-transition>
                <v-row >
                    
                    <!-- Body --------------------------------------------- -------------------------------------------- -->
                    <v-col cols="12" sm="12" md="10">
                        <!-- URL Input -->
                        <v-card tile class="d-flex component-wrap grey_trip pa-5 pb-2 mb-5">
                            <v-text-field dense outlined filled clearable
                                v-model="source_link"
                                label="Source URL"
                                prepend-icon="public"
                                hint="Please provide full link (http:// ...)"
                                :rules="[$handlers.rules.link]"
                            ></v-text-field>
                            <v-btn tile 
                                color="blue_prim"
                                class="ml-5"
                                :disabled="source_link ? (source_link.trim().slice(0, 7) === 'http://' || source_link.trim().slice(0, 8) === 'https://' ? false : true) : true"
                                v-text="'Get Data'"
                                @click="getSourceData()"
                            ></v-btn>
                        </v-card>

                        <!-- Response -->
                        <v-card 
                            tile 
                            v-if="error" 
                            class="grey_trip pa-5"
                            v-html="error"
                        ></v-card>

                        <v-card 
                            tile 
                            v-else-if="source_data.url"
                            class="grey_trip pa-5"
                        >                            
                            <v-row class="pl-">
                                <v-col 
                                    cols="12" 
                                    sm="6"
                                    md="4"
                                    lg="3" 
                                    v-for="(i, key) in selection" 
                                    :key="key"
                                >
                                    <div class="d-flex component-wrap align-start">
                                        <v-checkbox
                                            v-model="selection[key]"
                                            class="mt-0"
                                            color="blue_prim"
                                            :disabled="key === 'source_link'"
                                        ></v-checkbox>
                                        <div class="pt-1 ml-1">
                                            <div class="mb-1 font-weight-bold" v-text="labels[key] ? labels[key] : key"></div><!-- labels[key] -->
                                            <div v-html="source_data.data[key]"></div>
                                            <div v-if="source_data.info[key]" v-text="source_data.info[key]" class="mr-3"></div>
                                        </div>
                                    </div>
                                </v-col>
                            </v-row>                            
                        </v-card>
                    </v-col>                    

                    <!-- Info Tile --------------------------------------------- -------------------------------------------- -->
                    <v-col cols="12" sm="12" md="2">
                        <v-btn tile lg block 
                            v-text="$root.label('save')" 
                            color="blue_prim" 
                            class="mb-5"
                            :disabled="!source_data.url"
                            @click="save()"
                        ></v-btn>

                        <v-card class="grey_trip caption" tile>
                            <v-card-text>
                                <p class="font-weight-bold red--text">Wichtiger Hinweis:</p>
                                <p>
                                    Bitte beachten Sie die korrekte Formatierung des Links, eine vollständige Adresse mit Protokollkürzel (z.B. http://numismatics.org/...).<br />
                                    Vermeiden Sie Leerzeichen, Zeilenumbrüche oder sonstige Steuerzeichen.
                                </p><p>
                                    Um Dubletten zu vermeiden, können bereits importierte Münzen/Typen nicht erneut importiert werden.
                                </p>
                            </v-card-text>
                        </v-card>

                        <v-card class="grey_trip caption mt-5" tile>
                            <v-card-text>
                                <p class="font-weight-bold red--text">Noch ein Wichtiger Hinweis:</p>
                                <p>
                                    Der Importer ist noch nicht zu 100% fertig. 
                                    Es werden noch nicht alle Felder berücksichtigt und die Darstellung in der Übersicht ist unvollständig. Er funktioniert aber.
                                    Aktuell können nur Münzen/Typen von http://numismatics.org ausgelesen werden. 
                                    Weitere Adressen (z.B IKMK) folgen in der Zukunft.
                                </p>
                            </v-card-text>
                        </v-card>
                    </v-col>

                </v-row>
            </v-expand-transition>
        </div>

    </template>
</ItemInputTemplate>

</div>
</template>


<script>

export default {
    data () {
        return {
            source_link:    null,
            source_data:    {},
            error:          null,
            selection:      {},
            loading:        false
        }
    },

    props: {
        entity: { type: String }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        header () {
            return '<span class="title">Import external data as new ' + 
                this.labels[this.entity.slice(0, -1)].toUpperCase()
        }
    },

    created () {
        this.$store.commit( 'setBreadcrumbs',[ // JK: Set Breadcrumbs
            {label:this.entity,to:''},
            {label:'import external data',to:''}
        ]);
    },
    
    
    methods: {        
        async getSourceData () {
            this.$root.loading = this.loading = true
            const self = this
            this.error = null
            this.selection = {}

            this.source_data = await this.fetch()
            
            if (!this.source_data?.url) {
                this.error = '<div class="font-weight-bold red--text">' + this.source_data + '</div>'
            }
            else if (this.source_data?.duplicates?.[0]) {
                this.error = '<div class="font-weight-bold red--text mb-5">Duplicates detected</div>'
                this.source_data.duplicates.forEach((v) => {
                    self.error += '<div class="ml-5 mb-1"><a href="/editor#/' + self.entity + '/edit/' + v + '">cn ' + self.entity.slice(0, -1) + ' ' + v + '<a/></div>'
                })
            }
            else {
                Object.keys(this.source_data.data).forEach((k) => {
                    self.selection[k] = true
                })
            }

            this.$root.loading = this.loading = false
        },

        async fetch () {
            const self = this
            let dbi = [];

            console.log ('AXIOS: Fetching Data from "dbi/import/' + this.entity + '" using POST. Awaiting Server Response ...')

            await axios.post ('dbi/import/' + this.entity, { source: this.source_link })
                .then ((response) => {
                    console.log(response)
                    dbi = response.data
                })
                .catch ((error) => { self.$root.AXIOS_ERROR_HANDLER(error) });

            return dbi;
        },

        async save () {
            this.$root.loading = this.loading = true

            const self = this
            const item = this.$handlers.create_item(this.entity)

            // get selected values
            Object.keys(this.selection).forEach((key) => {
                if (self.selection[key] === true) { item[key] = self.source_data.data[key] }
            })

            let response = await this.$root.DBI_INPUT_POST (this.entity, 'input', item);

            if (response.success) {
                this.$root.snackbar(response.success, 'success');
                this.$router.push('/' + this.entity + '/edit/' + response.id)
            }
            else {
                console.log ('Data Input Error: response was not ok');
            }

            this.$root.loading = this.loading = false
        },

        showItemData (key, section) {
            return this.$handlers.show_item_data(this.$root.language, this.entity, this.source_data, key, section)
        }
    }
}

</script>