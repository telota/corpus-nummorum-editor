<template>
    <div>

        <!-- JK: Toolbar -->
        <div class="component-wrap d-flex">

            <!-- Add new Item from Type -->
            <v-tooltip bottom>
                <template v-slot:activator="{ on }"> 
                    <v-btn v-on="on" tile depressed @click="SetInput ('types')">
                        <v-icon>blur_circular</v-icon>
                    </v-btn>
                </template>
                <span>New {{ prop_label }} from Type</span>
            </v-tooltip>

            <!-- Add new Item from Coin -->
            <v-tooltip bottom>
                <template v-slot:activator="{ on }"> 
                    <v-btn v-on="on" tile depressed @click="SetInput ('coins')">
                        <v-icon>copyright</v-icon>
                    </v-btn>
                </template>
                <span>New {{ prop_label }} from Coin</span>
            </v-tooltip>

             <!-- Add new Item from Die -->
             <v-tooltip bottom>
                <template v-slot:activator="{ on }"> 
                    <v-btn v-on="on" tile depressed @click="SetInput ('dies')">
                        <v-icon>all_out</v-icon>
                    </v-btn>
                </template>
                <span>New {{ prop_label }} from Die</span>
            </v-tooltip>

            <v-divider vertical></v-divider>
        </div>

        <!-- Input for Derive ID -->
        <v-dialog v-model="dialog_input" :width="dialog_width" persistent>
            <v-card tile>
                <v-card-title class="text-center mb-2">
                    <v-spacer>Derivation from {{ source_label }}.</v-spacer>
                </v-card-title>

                <v-card-text>
                    <p>Create a new {{ prop_label }} based on a {{ source_label }}. Enter a {{ source_label }} ID or hit the search.</p>

                    <div class="component-wrap d-flex pt-4">

                        <v-text-field dense outlined filled clearable
                            v-model="derive.id"
                            prepend-icon="fingerprint"
                            :label="source_label+' ID'"
                            autofocus
                        ></v-text-field>

                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn icon v-on="on" class="ml-1 mr-1"
                                    @click="ChildDialog ({component: derive.entity, key: 'derive', id: derive.id})"
                                ><v-icon>launch</v-icon></v-btn>
                            </template>
                            <span>Open {{ derive.entity }} Dialog</span>
                        </v-tooltip>

                    </div>
                </v-card-text>

                <v-card-actions class="text-center">
                    <v-spacer></v-spacer>

                    <v-btn icon @click="SetInput ()" class="mr-2">
                        <v-icon>clear</v-icon>
                    </v-btn>

                    <v-btn icon @click="GetDeriveData ()">
                        <v-icon>done</v-icon>
                    </v-btn>

                    <v-spacer></v-spacer>
                </v-card-actions>

            </v-card>
        </v-dialog>
        
        <!-- Derive Popup Dialog -->
        <v-dialog v-model="dialog_derive" :width="dialog_width" persistent scrollable>
            <v-card tile>
                <v-card-title class="text-center mb-2">
                    <v-spacer>Derivation from {{ source_label }} {{ derive.id }}.</v-spacer>
                </v-card-title>

                <v-card-text>
                    <p>Selected values will be copied. Not selected fields will be ignored.</p>
                    <p>After derivation you can edit the {{ prop_label }} as usual.<br />Please do not forget to save your input!</p>

                    <v-list dense>
                        <v-list-item v-for="(item, i) in derive_item" :key="i">
                            <v-list-item-action>
                                <v-checkbox v-model="item.derive"></v-checkbox>
                            </v-list-item-action>
                            <v-list-item-content>
                                <v-list-item-title>{{ item.text }}</v-list-item-title>
                                <v-list-item-subtitle>{{ item.info != undefined ? ('(ID: '+ item.value+') '+ item.info) : item.value }}</v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>
                </v-card-text>

                <v-card-actions class="text-center">
                    <v-spacer></v-spacer>

                    <!-- Clear --> 
                    <v-btn @click="dialog_derive = false" icon class="mr-5"
                    ><v-icon>clear</v-icon></v-btn>

                    <!-- Save --> 
                    <v-btn  @click="CreateDeriveItem ()" icon
                    ><v-icon>done</v-icon></v-btn>

                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
        
        <!-- JK: Child Dialog -->
        <ChildDialog v-if="child_dialog_derive"
            :prop_active="child_dialog_derive"
            :prop_component="child_dialog_data.component"
            :prop_item="{key: 'derive', id: derive.id}" 
            v-on:assignment="ChildReceive"
            v-on:close="child_dialog_derive=false"
        ></ChildDialog>

    </div>
</template>



<script>

export default
{ 
    data ()
    {
        return {
            dialog_input:       false,
            dialog_derive:      false,
            dialog_width:       500,

            derive:             {entity: null, id: null},
            derive_item:        [],
            derive_item_target: {},

            // Controls for Child Dialog
            child_dialog_derive:    false,
            child_dialog_data:      {component: null, key: null, id: null},
        }
    },

    props: 
    {
        prop_entity:            {type: String},     // JK: relates to dbi/
        prop_label:             {type: String},     // JK: String for title
        prop_presets:           {type: Object},     // JK: information how to handle derivation
        prop_item:              {type: Object},     // JK: item for communication between templates
    },

    computed: 
    {
        source_label () {
            if (this.derive.entity == 'coins') {
                return 'Coin';
            } else if (this.derive.entity == 'dies') {
                return 'Die';
            } else if (this.derive.entity == 'types') {
                return 'Type';
            } else {
                return 'Empty';
            }
        }
    },

    /*created () 
    {

    },*/
    
    methods: 
    {
        // JK: Parent-Child-Communication
        ChildDialog (input) { // JK: toggle child component
            this.child_dialog_data = {
                component:  !input ? null   :   input.component, 
                key:        !input ? null   :   input.key,
                id:         !input ? null   :   input.id, 
            };
            this.child_dialog_derive = true;
                //console.log ('Child Dialog: {component: '+this.child_dialog_data.component+', key: '+this.child_dialog_data.key+', id:'+this.child_dialog_data.id+', active: '+this.child_dialog_derive+'}');
        },

        ChildReceive (emit) { // JK: receive emitted Input from child Dialog
                //console.log (Object.entries(emit));
                
            this.derive.id = emit.id;

            this.$store.commit ('showSnackbar', {message: 'SUCCESS: Assigned ID: '+emit.id+' from '+emit.component.charAt(0).toUpperCase()+ emit.component.slice(1)+' to form. Change not saved, yet', duration: 10000}); // JK: Response to snackbar
            this.ChildDialog (null);
        },

        SetInput (entity) {
            this.derive = {
                entity: this.derive.entity != entity ? entity : null, 
                id: null
            };
            this.dialog_input = entity ? true : false;
        },

        async GetDeriveData () {
            if (this.derive.id) 
            {
                var self            = this;
                let source          = 'dbi/' +self.derive.entity+ '/'+self.derive.id;
                let dbi             = await axios.get (source);          
                let item_raw        = dbi.data [0];

                if (item_raw) 
                {
                    let item_pro        = {}                
                    
                    if (self.derive.entity == 'dies') // JK: deriving entity is 'dies'
                    {
                        Object.keys (self.prop_presets [self.derive.entity]) .forEach (function (key) 
                        {
                            item_pro [key] = {
                                    key:    self.prop_presets [self.derive.entity] [key] .key [item_raw.side], 
                                    value:  item_raw [key],
                                    info:   item_raw [self.prop_presets [self.derive.entity] [key] .info],
                                    text:   self.prop_presets [self.derive.entity] [key] .text [item_raw.side],
                                    derive: self.prop_presets [self.derive.entity] [key] .derive,
                            };
                        });                
                    } 
                    else // JK: deriving entity is 'coins' or 'types
                    {
                        Object.keys (self.prop_presets [self.derive.entity]) .forEach (function (key) 
                        {
                            item_pro [key] = {
                                key:    self.prop_presets [self.derive.entity] [key] .key, 
                                value:  item_raw [key],
                                info:   item_raw [self.prop_presets [self.derive.entity] [key] .info],
                                text:   self.prop_presets [self.derive.entity] [key] .text,
                                derive: self.prop_presets [self.derive.entity] [key] .derive,
                            };
                        });
                    }
                    
                    self.derive_item = item_pro;
                    self.dialog_derive  = true;
                }
                else
                {
                    this.$store.commit ('showSnackbar', {message: 'ERROR: '+this.source_label+' ID '+this.derive.id+' not found. Please try again!', duration: 10000});
                    this.derive.id = null;
                }
            }
        },
        
        CreateDeriveItem () {
            var self = this;

            let item_raw = {};
            let item = {};

            Object.keys (self.derive_item).forEach (function (key) {
                if (self.derive_item [key].derive) {
                    item_raw [self.derive_item [key].key] = self.derive_item [key].value
                }                
            });            

            Object.keys (self.prop_item).forEach (function (key) {

                item [key] = item_raw [key] ? item_raw [key] : null
            });

            self.dialog_derive  = false;
            self.$emit ('derive', item);
            this.$store.commit ('showSnackbar', {message: 'SUCCESS: applyed derived information to form. Change not saved, yet', duration: 10000});
            this.SetInput (null);
        }
        
    }
}

</script>