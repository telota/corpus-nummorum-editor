<template>
    <div>

        <!-- JK: Toolbar -->
        <v-card tile class="appbar d-flex component-wrap">

                <!-- Importer -->
                <!--<v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" 
                            @click="gather_dialog = true"
                            tile
                            depressed
                        > <v-icon>arrow_circle_down</v-icon> </v-btn>
                    </template>
                    <span>Create new {{ label }} from existing ressource.</span>
                </v-tooltip>-->

                <!-- Clone -->
                <!--<v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" 
                            @click="$emit('command', {command: 'clone', value: null})"
                            :disabled="item.id ? false : true"
                            :text="item.id ? false : true"
                            :tile="item.id ? true : false"
                            depressed
                        > <v-icon>library_add</v-icon> </v-btn>
                    </template>
                    <span>Clone this {{ label }} (ID: {{ item.id }})</span>
                </v-tooltip>

                <v-divider vertical></v-divider>-->
                
                <!-- Delete -->
                <!--<v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" 
                            @click="$emit('delete', true)"
                            :disabled="item.id ? false : true"
                            :text="item.id ? false : true"
                            :tile="item.id ? true : false"
                            depressed
                        > <v-icon>delete</v-icon> </v-btn>
                    </template>
                    <span>Delete this {{ label }} (ID: {{ item.id }})</span>
                </v-tooltip>-->

                <v-spacer></v-spacer>

                <!-- Clear -->
                <!--<v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" 
                            @click="$emit('command', {command: 'clear', value: null})"                            
                            :disabled="item_empty ? true : false"
                            :text="item_empty ? true : false"
                            :tile="item_empty ? false : true"
                            depressed
                        > <v-icon>clear</v-icon> </v-btn>
                    </template>
                    <span>Clear input fields</span>
                </v-tooltip>

                <v-divider vertical></v-divider>-->

                <!-- Preview -->
                <!--<v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" 
                            @click="$emit('command', {command: 'preview', value: true})"                            
                            :disabled="item_empty ? true : false"
                            :text="item_empty ? true : false"
                            :tile="item_empty ? false : true"
                            depressed
                        > <v-icon>preview</v-icon>
                        </v-btn>
                    </template>
                    <span>Show preview of input</span>
                </v-tooltip>-->

                <!-- Save - keep open -->
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" 
                            @click="$emit('save-and-keep', true)"                            
                            :disabled="item_empty ? true : false"
                            :text="item_empty ? true : false"
                            :tile="item_empty ? false : true"
                            depressed
                        > <v-icon>done</v-icon>
                        </v-btn>
                    </template>
                    <span>Save and keep input form</span>
                </v-tooltip>

                <!-- Save - close -->
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" 
                            @click="$emit('save-and-reset', true)"                            
                            :disabled="item_empty ? true : false"
                            :text="item_empty ? true : false"
                            :tile="item_empty ? false : true"
                            depressed
                        > <v-icon>done_all</v-icon>
                        </v-btn>
                    </template>
                    <span>Save and reset input form</span>
                </v-tooltip>

        </v-card>
        
        <!-- --------------------------------------------------------------------------------------------------------------------------------- -->

        <!-- Dialog -->
        <v-dialog v-model="gather_dialog" persistent scrollable :fullscreen="fullscreen" :max-width="width">
            <v-card tile>

                <!-- JK: Sysbar -->
                <v-system-bar color="sysbar" class="pt-5 pb-4 pl-6">
                    <v-icon class="mr-2 mb-1">arrow_circle_down</v-icon> <span>Import Ressource as {{ label }}</span>
                    <v-spacer></v-spacer>
                    <v-icon @click="fullscreen = !fullscreen" class="mr-3" v-if="$vuetify.breakpoint.mdAndUp">{{ !fullscreen ? 'fullscreen' : 'fullscreen_exit' }}</v-icon>
                    <v-icon @click="Close ()">close</v-icon>
                </v-system-bar>

                <!-- JK: Buttons / Source-Select -->
                <div class="d-flex component-wrap">
                    <div v-for="(item, i) in sources" :key="i" :style="'width:'+(i < sources.length -1 ? Math.floor(100/sources.length) : Math.ceil(100/sources.length))+'%'">
                        <v-btn block tile depressed color="appbar" :disabled="i == gather_index" @click="Gather (i)">
                            {{ item.text }}
                        </v-btn>
                    </div>
                </div>


                <!-- JK: CN Coin or Type selected ---------------------------------------------------------------------------------------------------------- -->
                <v-card-text v-if="gather_index < 2" class="pt-3">
                    <component 
                        :is="sources[gather_index].value" 
                        :read_only="true"
                        v-on:EmitSelect="ReceiveSelect"
                    ></component>
                </v-card-text>

                <v-card-actions v-if="gather_index < 2">
                    <!-- JK: CN Coin or Type selected -->
                    <!--<div v-if="gather_index < 2">
                        <v-btn block tile depressed color="appbar" :disabled="gather_index" @click="Gather (i)">
                            {{ item.text }}
                        </v-btn>
                    </div>-->
                </v-card-actions>


                <!-- JK: External Resource selected ---------------------------------------------------------------------------------------------------------- -->
                <v-card-text v-if="gather_index > 1">
                    <v-row class="mt-3 d-flex justify-center component-wrap">

                        <!-- JK: left Panel -->
                        <v-col cols="12" md="4">
                            <v-card tile max-width="600">
                                <v-card-title class="sysbar">
                                    <v-icon class="mr-2">link</v-icon>External Resource
                                </v-card-title>
                                <v-card tile class="appbar">
                                    <v-btn disabled text></v-btn>
                                </v-card>
                                
                                <v-card-text class="appbar pt-5">
                                    <p class="mb-6">Please provide a full, valid link to a known platform like IKMK or numismatics.org.<br />
                                    Please ensure that the source is indeed a {{ label }}. Non-{{ label }}-Data may create strange results.</p>
                                    <v-text-field dense outlined filled clearable
                                        v-model="import_src"
                                        label="Source URL"
                                        :hint="watch_import_src ? 'The provided link is valid. You may proceed.' : 'The provided link is not valid. Please ensure the link contains https:// oder http://.'"
                                    ></v-text-field>
                                </v-card-text>

                                <v-btn block tile color="light-blue darken-4" 
                                    class="white--text"
                                    :disabled="watch_import_src ? false : true"
                                    @click="GetData ()"
                                > {{ watch_import_src ? 'Get Data' : 'Please provide a valid URL' }} </v-btn>                                
                            </v-card>
                        </v-col>

                        <!-- JK: Right Panel -->
                        <v-col v-if="import_panel" cols="12" md="8">
                            <v-card tile :loading="loading"> 

                                <v-app-bar color="sysbar">
                                    <span class="headline">Import Precheck</span>
                                </v-app-bar>

                                <v-card tile class="appbar component-wrap d-flex">
                                    <v-btn text disabled>{{import_data [0] == undefined ? 'No Data, yet' : import_src}}</v-btn>
                                    <v-spacer></v-spacer>
                                    <v-btn depressed class="appbar"
                                        @click="import_panel = false;"
                                        :tile="import_data [1] != undefined"
                                        :text="import_data [1] == undefined"
                                        :disabled="import_data [1] == undefined"                        
                                    > <v-icon>clear</v-icon> </v-btn>
                                </v-card>

                                <v-card-text v-if="import_data [1] != undefined">
                                    <div class="title">Parsing Success</div>
                                    <div>Please deselect data you don't want to import and proceed.</div>
                                    <v-divider class="mt-3 mb-8"></v-divider>

                                    <div class="d-flex component-wrap">
                                        <div v-for="n in 2" :key="n">
                                            <table :class="n < 2 ? 'mr-10' : ''">
                                                <tr v-for="(item, i) in ImportKeys (n)" :key="i">
                                                    <td class="pb-1 d-flex align-top font-weight-bold" style="white-space: nowrap;">
                                                        <v-checkbox v-model="import_check [item]" :disabled="check_disabled[entity].includes(item)" class="ma-0 pa-0 mr-2"></v-checkbox>{{ item }}
                                                    </td>
                                                    <td class="pb-1 pl-4" style="vertical-align: top">
                                                        {{ import_data[2] ? (import_data[2][item] ? (import_data[2][item].info+' ('+import_data[1][item]+')') : import_data[1][item]) : import_data[1][item] }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </v-card-text>

                                <v-card-text v-if="import_data [0] != undefined && import_data [1] == undefined">
                                    <div class="title mt-3">Duplicate detected!</div>
                                    <div class="mt-1">This resource has already been imported. Please check the following {{ label }}(s).</div>

                                    <v-simple-table dense class="mt-6 mb-5">
                                        <tr v-for="(value, key, i) in import_data [0].id" :key="i">
                                            <td class="d-flex align-start font-weight-bold">{{ key }}</td>
                                            <!--<td><a :href="value" target="_blank">{{ value }}</a></td>-->
                                        </tr>
                                    </v-simple-table>
                                </v-card-text>

                                <v-card-text v-if="import_error">
                                    <div class="title">Parsing Error</div>
                                    <div class="mt-1">{{ import_error }}</div>
                                </v-card-text>                                

                                <v-btn v-if="import_data [1] != undefined" block tile color="light-blue darken-4" 
                                    class="white--text"
                                    :disabled="import_src ? false : true"
                                    @click="EmitData ('import', entity)"
                                > {{ 'Import Data' }} </v-btn>

                            </v-card>
                        </v-col>

                    </v-row>
                </v-card-text>                

            </v-card>
        </v-dialog>



    </div>
</template>



<script>

export default
{ 
    data ()
    {
        return {
            width:              '75%',
            fullscreen:         true,

            // JK: Data Importer
            gather_dialog:      false,
            gather_index:       1,
            sources:            [
                                    {key: 'copy',   value: 'types', text: 'CN Type'},
                                    {key: 'copy',   value: 'coins', text: 'CN Coin'},
                                    {key: 'import', value: this.entity.substring (0,this.entity.length-1),  text: 'External '+this.label},
                                ],

            // JK: Import from external source (index = 2)
            loading:            false,
            import_src:         null,
            import_data:        {},
            import_check:       {},
            check_disabled:     {
                                    coins: ['SourceURL','CreateDate','ChangeDate','ReadyToPublish'],
                                    types: ['SourceURL','CreateDate','ChangeDate','ReadyToPublish']
                                },
            import_panel:       false,
            import_error:       null,
        }
    },

    props: 
    {
        attributes:             {type: Object},
        item:                   {type: Object},
        entity:                 {type: String},
        label:                  {type: String}
    },

    computed: 
    {
        item_empty () { // JK: Checks if there is any input
            let empty = true;

            Object.values (this.item).forEach (function (item) {
                if (item) {empty = false};
            });

            return empty;
        },

        watch_import_src () {
            if (this.import_src) {
                return (this.import_src.substring (0, 8) == 'https://' || this.import_src.substring (0, 7) == 'http://') ? true : false;
            } else {
                return false;
            }
        },
    },

    watch:
    {
    },

    created () 
    {
    },
    
    methods: 
    {
        ReceiveSelect (emit) {
            console.log (emit.id);
            //this.$emit('ChildEmit', {component: this.entity, id: emit.id} );
        },

        Gather (index) {
            this.gather_index = index;
        },

        Close () {
           this.gather_dialog = false ;
        },

        // JK: External Data
        async GetData () {
            this.loading        = true;
            var self            = this;
            self.import_check   = {};
            self.import_data    = {};
            self.import_panel   = true;
            self.import_error   = null;

            let source      = 'dbi/import/'+self.sources[2].value+'/'+self.import_src;
            let dbi         = await axios.get (source)

            if (typeof dbi.data == 'string') 
            {
                self.import_error = dbi.data.substring(7);
            }
            else
            {
                self.import_data = Object.values (dbi.data);

                if (self.import_data [1] != undefined) 
                {
                    Object.keys (self.import_data [1]) .forEach (function (key) {
                        self.import_check [key] = true;
                    });
                }
            }

            self.loading = false;
        },

        ImportKeys (n) {
            var self    = this;
            let array   = [];
            let i       = 0;
            let keys    = Object.keys (self.import_data [1]);
            let divider = Math.floor (keys.length/2);

            keys.forEach (function (key) {
                if (n == 1 && i <= divider) {
                    array.push (key);
                }
                else if (n == 2 && i > divider) {                    
                    array.push (key);
                }
                i = i +1;
            });

            return array;
        },

        EmitData (mode, entity) {
            this.$emit ('data', {mode: mode, entity: entity, data: 'this.import_data [1]'})
            this.gather_dialog  = false;
            this.import_panel   = false;
        },
    }
}

</script>