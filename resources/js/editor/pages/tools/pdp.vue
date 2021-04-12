<template>
    <div>
        <v-row>

            <v-col cols="12" sm="6" md="3">
                <v-card tile>
                    <v-card-title class="sysbar">
                        Persistent Data Provider<v-spacer></v-spacer><a href="http://jkdev:8104/collector" target="_blank"><v-icon>link</v-icon></a>
                    </v-card-title>

                    <v-card tile color="caption appbar pa-2">
                        corpus-nummorum_legends_11720
                    </v-card>
                        

                    <v-card-text>
                        <!-- URI -->
                        <div class="component-wrap d-flex mt-5">
                            <v-text-field dense outlined filled
                                v-model="request.uri"
                                label="URI"
                                hint="Provides data of a given URI" persistent-hint
                            ></v-text-field>
                            <v-btn text @click="run ('uri', true)" class="ml-1">GO</v-btn>
                        </div>

                        <!-- Index -->
                        <div class="component-wrap d-flex mt-5">
                            <v-text-field dense outlined filled
                                v-model="request.index"
                                label="Version Index"
                                hint="Provides all stored versions of a given URI" persistent-hint
                            ></v-text-field>
                            <v-btn text @click="run ('index', true)" class="ml-1">GO</v-btn>
                        </div>

                        <!-- Log -->
                        <div class="component-wrap d-flex mt-5">
                            <v-text-field dense outlined filled
                                v-model="request.log"
                                label="Version Log"
                                hint="Provides all PDP actions related to a given URI" persistent-hint
                            ></v-text-field>
                            <v-btn text @click="run ('log', true)" class="ml-1">GO</v-btn>
                        </div>
                        
                        <!-- Compare -->
                        <div class="component-wrap d-flex mt-5">
                            <v-text-field dense outlined filled
                                v-model="request.compare"
                                label="Version Comparison"
                                hint="Compares all stored versions of a given URI" persistent-hint
                            ></v-text-field>
                            <v-btn text @click="run ('compare', true)" class="ml-1">GO</v-btn>
                        </div>

                        <!-- Collect TEST
                        <div class="component-wrap d-flex mt-5">
                            <v-text-field dense outlined filled
                                v-model="request.collect"
                                label="PDP Collector"
                                hint="Run a PDP collect call on a given entity" persistent-hint
                            ></v-text-field>
                            <v-btn text @click="pdpCollect ()" class="ml-1">GO</v-btn>
                        </div> -->
                    </v-card-text>
                </v-card>
            </v-col>
            
            <!-- JK: Index -->
            <v-col cols="12" sm="6" md="2" v-if="window.index">
                <v-card tile :loading="loading.index">
                    <v-card-title class="sysbar">
                        URI Version Index
                        <v-spacer></v-spacer>
                        <v-btn icon @click="run ('index', false)"><v-icon>clear</v-icon></v-btn>
                    </v-card-title>
                    
                    <v-card tile color="caption appbar pa-2">
                        <a :href="'http://jkdev:8104/uri/'+request.index" target="_blank"><v-icon small class="mr-2">link</v-icon></a> {{ request.index }}
                    </v-card>

                    <v-card-text>
                        <v-data-table hide-default-footer
                            v-if="items.index [0]"
                            @click:row="IndexClick" single-select
                            :headers="headers.index"
                            :items="items.index"
                            :search="search.index"
                            :footer-props="{
                                itemsPerPageOptions: [20, 50],
                            }"
                        ></v-data-table>
                    </v-card-text>
                </v-card>             
            </v-col>
            
            <!-- JK: URI Content -->
            <v-col cols="12" sm="6" md="7" v-if="window.uri">
                <v-card tile :loading="loading.uri">
                    <v-card-title class="sysbar">
                        URI Json Content
                        <v-spacer></v-spacer>
                        <v-btn icon @click="run ('uri', false)"><v-icon>clear</v-icon></v-btn>
                    </v-card-title>
                    
                    <v-card tile color="caption appbar pa-2">
                        <a :href="'http://jkdev:8104/uri/'+request.uri" target="_blank"><v-icon small class="mr-2">link</v-icon></a> {{ request.uri }}
                    </v-card>

                    <v-card-text>
                        <v-simple-table dense>
                            <tr v-for="(value, key, i) in items.uri" :key="i">
                                <td :class="sections.includes (key.toLowerCase()) ? 'pt-5 pb-5 title' : 'pl-10'">{{ sections.includes (key.toLowerCase()) ? key : key.substring (5) }}</td>
                                <td>{{ value }}</td>
                            </tr>
                        </v-simple-table>
                    </v-card-text>
                </v-card>             
            </v-col>
            
            <!-- JK: Log -->
            <v-col cols="12" sm="6" md="7" v-if="window.log">
                <v-card tile :loading="loading.log">
                    <v-card-title class="sysbar">
                        PDP Log
                        <v-spacer></v-spacer>
                        <v-btn icon @click="run ('log', false)"><v-icon>clear</v-icon></v-btn>
                    </v-card-title>
                    
                    <v-card tile color="caption appbar pa-2">
                        <a :href="'http://jkdev:8104/log/'+request.log" target="_blank"><v-icon small class="mr-2">link</v-icon></a> {{ request.log }}
                    </v-card>

                    <v-card-text>
                        <v-data-table hide-default-footer
                            v-if="items.log [0]"
                            @click:row="IndexClick" single-select
                            :headers="headers.log"
                            :items="items.log"
                            :search="search.log"
                            :footer-props="{
                                itemsPerPageOptions: [20, 50],
                            }"
                        ></v-data-table>
                    </v-card-text>
                </v-card>             
            </v-col>

        </v-row>
            
        <!-- JK: Comparison -->
        <v-dialog v-model="window.compare" fullscreen scrollable>
            <v-card tile :loading="loading.compare">
                <v-card-title class="sysbar">
                    URI Version Comparison
                    <v-spacer></v-spacer>
                    <v-btn icon @click="run ('compare', false)"><v-icon>clear</v-icon></v-btn>
                </v-card-title>
                
                <v-card tile color="caption appbar pa-2">
                    <a :href="'http://jkdev:8104/compare/'+request.compare" target="_blank"><v-icon small class="mr-2">link</v-icon></a> {{ request.compare }}
                </v-card>

                <v-card-text>
                    <v-simple-table>
                        <tr v-for="(tr_value, tr_key, tr_i) in items.compare" :key="tr_i">
                            <td class="font-weight-black">{{ tr_key != 'date' ? tr_key : ''}}</td>
                            <td v-for="(td_value, td_key, td_i) in tr_value" :key="td_i" :class="tr_key == 'date' ? 'font-weight-black' : (td_value.changed ? 'red--text' : '')">{{ td_value.data }}</td>
                        </tr>
                    </v-simple-table>
                </v-card-text>
            </v-card>
        </v-dialog>   
    
    </div>
</template>


<script>

// JK: Set stable variables (outside of vue component scope)
const CompLabelS            = 'PDP';
const CompLabelP            = 'PDP';
const CompLabelParent       = 'Tools';
const CompName              = 'pdp';

const entity                = CompName; // JK: change if needed


export default
{    
    components: {},

    data ()  // JK: define VueJS Variables
    {
        return {
            component:          CompLabelS,
            entity:             entity,
            sections:           ['meta', 'data'],
            loading:            { uri: false, index: false, compare: false, log: false, collect: false},
            window:             { uri: false, index: false, compare: false, log: false, collect: false},
            item_edited:        {},
            
            request:            { uri: null, index: null, compare: null, log: null, collect: null},
            search:             { uri: null, index: null, details: null, compare: null},
            items:              { uri: {}, index: [], details: [], compare: [] },
            headers:            {
                                    index: [
                                        {value: 'index',    text: '#'},
                                        {value: 'date',     text: 'Date'},
                                    ],
                                    log: [
                                        {value: 'date',     text: 'Date'},
                                        {value: 'status',   text: ''},
                                        {value: 'message',  text: 'Info'},
                                    ],
                                    compare: [
                                        {value: 'version',   text: 'Version'}
                                    ],
                                },
        }
    },

    props: 
    {
        //prop_item: {type: Object}
    },


    computed: 
    {
        
    },

    created () 
    {
        this.$store.commit( 'setBreadcrumbs',[ // JK: Set Breadcrumbs
            {label:CompLabelParent,to:''},
            {label:CompLabelP,to:''},
        ]);

        this.request = {
            uri:        'corpus-nummorum_legends_11720__2020-06-30_10-00-00', 
            index:      'corpus-nummorum_legends_11720', 
            compare:    'corpus-nummorum_legends_11720', 
            log:        'corpus-nummorum_legends_11720'
        };
    },
    
    methods: 
    {
        IndexClick: function (item, row) { // JK: click on table row to set selected item as item_edited   
            //row.select (true);
            this.request.uri = item.uri;
            this.run ('uri', true);
        },

        run (entity, run) {
            if (run) {
                if      (entity == 'uri')       {this.pdpUri ();        this.window.uri = true;}
                else if (entity == 'index')     {this.pdpIndex ();      this.window.index = true;}
                else if (entity == 'log')       {this.pdpLog ();        this.window.log = true;}
                else if (entity == 'compare')   {this.pdpCompare ();    this.window.compare = true;}
            } else {
               this.window [entity] = false; 
            }
        },

        async pdpUri () {
            this.loading.uri = true;

            var self = this;
            self.items.uri = {};
            let src = this.request.uri.includes ('//') ? this.request.uri : ('http://jkdev:8104/uri/'+this.request.uri);

            let pdp = await axios.get (src);

            this.sections.forEach (function (section) {
                self.items.uri [section.toUpperCase ()] = '';
                Object.keys (pdp.data [section]) .forEach (function (key) {
                    if (pdp.data [section] [key] == null) {
                        self.items.uri [section+'_'+key] = null;
                    } else if (typeof pdp.data [section] [key] != 'object') {
                        self.items.uri [section+'_'+key] = pdp.data [section] [key];
                    } else {
                        Object.keys (pdp.data [section] [key]) .forEach (function (key2) {
                            self.items.uri [section+'_'+key+'_'+key2] = pdp.data [section] [key] [key2];
                        });
                    }
                });            
            });

            this.loading.uri = false;
        },

        async pdpIndex () {
            this.loading.index = true;

            var self = this;
            self.items.index = [];
            let src = this.request.index.includes ('//') ? this.request.index : ('http://jkdev:8104/versions/'+this.request.index);
            let pdp = await axios.get (src);
            this.items.index = pdp.data;

            this.loading.index = false;
        },

        async pdpLog () {
            this.loading.log = true;

            var self = this;
            self.items.log = [];
            let src = this.request.log.includes ('//') ? this.request.log : ('http://jkdev:8104/log/'+this.request.log);
            let pdp = await axios.get (src);
            this.items.log = pdp.data;

            this.loading.log = false;
        },

        async pdpCompare () {
            this.loading.compare = true;

            var self = this;
            self.items.compare = [];
            let src = this.request.compare.includes ('//') ? this.request.compare : ('http://jkdev:8104/compare/'+this.request.compare);
            let pdp = await axios.get (src);
            this.items.compare = pdp.data;

            this.loading.compare = false;
        },

        async pdpCollect () {

        },

        async DataOutput () {
            this.loading = true; // JK: Loading ON

            let source = 'dbi/' +this.entity+ '/all';
            
            let dbi = await axios.get (source);          
            this.items.coins = Object.values (dbi.data [0]);

            this.loading = false; // JK: Loading OFF
        }

        // JK: Parent-Child-Communication 
        /*Receiver () { // JK: Process properties given by parent (in most cases Child Dialog Component)
                console.log ('prop_item {key: '+this.prop_item.key+', id: '+this.prop_item.id+'}');
            let selected = this.items.find ( (item) => item.id === this.prop_item.id );
                console.log ('selected: '+this.prop_item.id);
                console.log ('selected: '+selected);
            this.ItemSelect (selected);
        },

        ChildDialog (input) { // JK: toggle child component
            this.child_dialog_data = {
                component:  !input ? null   :   input.component, 
                key:        !input ? null   :   input.key,
                id:         !input ? null   :   input.id, 
            };
            this.child_dialog_active = true;
                console.log ('Child Dialog: {component: '+this.child_dialog_data.component+', key: '+this.child_dialog_data.key+', id:'+this.child_dialog_data.id+', active: '+this.child_dialog_active+'}');
        },

        ChildReceive (emit) { // JK: receive emitted Input from child Dialog
                //console.log (Object.entries(emit));
            this.item_edited [emit.key] = emit.id;
            this.$store.commit ('showSnackbar', {message: 'SUCCESS: Assigned ID: '+emit.id+' from '+emit.component.charAt(0).toUpperCase()+ emit.component.slice(1)+' to form. Change not saved, yet', duration: 10000}); // JK: Response to snackbar
            this.ChildDialog (null);
        },
        
        // JK: Template Components
        ItemsTemplateReceive (emit) { // JK: receive emitted Input from child component
                console.log (CompLabelP+': received emit from Items Template (ID: '+emit.id+')')
            this.item_edited = emit;
            this.$emit('ChildEmit', {component: entity, id: emit.id} );
        },

        InputTemplateReceive (emit) { // JK: receive emitted Input from Input Template
                console.log (CompLabelP+': received emit from Input Template (refresh: '+this.items_refresh+')')

            if (emit.refresh) {
                this.items_refresh = null; 
                this.items_refresh = emit.item ? (emit.item.id ? emit.item.id : -1) : -1
            };

            if (emit.item) {
                this.item_edited = emit.item;
            } else {
                this.ItemDefault ();
            }
        },
        
        ItemDefault () { // JK: Set edited item to default
            var self = this;
            let item = {};

            Object.keys (self.attributes) .forEach (function (key) { 
                item [key] = self.attributes[key].default
            });

            self.item_edited = item;
        },*/
    }
}

</script>