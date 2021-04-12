<template>
    <div>

        <!-- JK: Toolbar -->
        <v-card tile class="appbar mb-2">
            <div class="component-wrap d-flex" v-if="tools_expand">

                <!-- Derive Options -->
                <DeriveTemplate 
                    v-if="prop_derive"                   
                    :prop_entity="prop_entity"
                    :prop_label="prop_label"
                    :prop_presets="derive_presets"
                    :prop_item="item_edited"
                    v-on:derive="DeriveTemplateReceive"
                ></DeriveTemplate>

                <!-- Clone -->
                <v-tooltip bottom v-if="prop_clone">
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" @click="clone_active = true" depressed
                            :disabled="!item_edited.id ? true : false"
                            :text="!item_edited.id ? true : false"
                            :tile="item_edited.id ? true : false"
                        >
                            <v-icon>library_add</v-icon>
                        </v-btn>
                    </template>
                    <span>Clone {{ this.item_label }}</span>
                </v-tooltip>
                
                <!-- Delete -->
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" @click="Delete ()" depressed
                            :disabled="!item_edited.id ? true : false"
                            :text="!item_edited.id ? true : false"
                            :tile="item_edited.id ? true : false"
                        >
                            <v-icon>delete</v-icon>
                        </v-btn>
                    </template>
                    <span>Delete {{ this.item_label }}</span>
                </v-tooltip>
                
                <v-spacer></v-spacer>

                <!-- Clear -->
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" @click="Clear ()" depressed
                            :disabled="item_empty"
                            :text="item_empty"
                            :tile="!item_empty"
                        >
                            <v-icon>clear</v-icon>
                        </v-btn>
                    </template>
                    <span>Clear input fields</span>
                </v-tooltip>

                <v-divider vertical></v-divider>

                <!-- Preview -->
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" @click="preview_active = true" depressed
                            :disabled="item_empty"
                            :text="item_empty"
                            :tile="!item_empty"
                        >
                            <v-icon>preview</v-icon>
                        </v-btn>
                    </template>
                    <span>Show preview of input</span>
                </v-tooltip>

                <!-- Save - keep open -->
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" @click="Save (false)" depressed
                            :disabled="item_empty"
                            :text="item_empty"
                            :tile="!item_empty"
                        >
                            <v-icon>done</v-icon>
                        </v-btn>
                    </template>
                    <span>Save and keep input form</span>
                </v-tooltip>

                <!-- Save - close -->
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }"> 
                        <v-btn v-on="on" @click="Save (true)" depressed
                            :disabled="item_empty"
                            :text="item_empty"
                            :tile="!item_empty"
                        >
                            <v-icon>done_all</v-icon>
                        </v-btn>
                    </template>
                    <span>Save and reset input form</span>
                </v-tooltip>

            </div>
        </v-card>


        <!-- Clone Dialog -->
        <v-dialog v-model="clone_active" :width="clone_width" scrollable>
            <v-card tile>

                <v-card-title>
                    <v-spacer>
                        New {{ prop_label }} based on ID {{ this.prop_item.id }}
                    </v-spacer>
                </v-card-title>

                <v-card-text>
                    <p>Selected values will be copied. Not selected fields will be ignored.</p>
                    <p>After cloning you can edit the item as usual.<br />Please do not forget to save your input!</p>

                    <v-list dense>
                        <v-list-item v-for="(item, key, i) in clone_props" :key="i">
                            <v-list-item-action>
                                <v-checkbox v-model="item.clone"></v-checkbox>
                            </v-list-item-action>
                            <v-list-item-content>
                                <v-list-item-title>{{ item.text }}</v-list-item-title>
                                <v-list-item-subtitle>{{ item_edited [key] }}</v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>
                </v-card-text>

                <v-card-actions class="text-center">
                    <v-spacer>
                        <!-- Clear --> 
                        <v-btn @click="clone_active = false" icon class="mr-5"
                        ><v-icon>clear</v-icon></v-btn>

                        <!-- Save --> 
                        <v-btn  @click="Clone ()" icon
                        ><v-icon>done</v-icon></v-btn>
                    </v-spacer>
                </v-card-actions>

            </v-card>
        </v-dialog>


        <!-- Preview Dialog -->
        <v-dialog v-model="preview_active" :width="preview_width" scrollable>
            <v-card tile>

                <v-card-title class="text-center">
                    <v-spacer>
                        {{ prop_label }}-Preview
                    </v-spacer>
                </v-card-title>

                <v-card-text>
                    <v-simple-table dense>
                        <template v-slot:default>
                            <tbody>
                                <tr v-for="(value, key, i) in item_edited" :key="i">
                                    <td><b>{{ prop_attributes [key].text }}</b></td>
                                    <td>{{ value }}</td>
                                </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-card-text>

                <v-card-actions class="text-center">
                    <v-spacer>
                        <!-- Clear --> 
                        <v-btn @click="preview_active = false" icon class="mr-5"
                        ><v-icon>clear</v-icon></v-btn>

                        <!-- Save --> 
                        <v-btn  @click="Save (false)" icon
                        ><v-icon>done</v-icon></v-btn>

                        <!-- Save and done --> 
                        <v-btn  @click="Save (true)" icon
                        ><v-icon>done_all</v-icon></v-btn>
                    </v-spacer>
                </v-card-actions>

            </v-card>
        </v-dialog>


        <!-- Input Check
        <InputCheck></InputCheck> -->

    </div>
</template>



<script>

export default
{ 
    data ()  // JK: define VueJS Variables
    {
        return {
            tools_expand:       true,

            item_edited:        this.prop_item,
            input_mode:         'none',
            input_reset:        false,

            preview_active:     false,
            preview_width:      600,

            clone_active:       false,
            clone_width:        500,
            clone_props:        {},

            derive_presets:     this.prop_derive ? this.prop_derive : {}
        }
    },

    props: 
    {
        prop_entity:            {type: String},     // JK: relates to dbi/
        prop_label:             {type: String},     // JK: String for title
        prop_clone:             {type: Boolean},    // JK: If "true" provide clone option (controlled by attributes)
        prop_item:              {type: Object},     // JK: Selected Item
        prop_attributes:        {type: Object},
        prop_derive:            {type: Object}
    },

    computed: 
    {
        item_label () {
            return (this.prop_label ? this.prop_label : 'Item')+(this.prop_item.id ? (' '+this.prop_item.id) : '')
        },

        item_empty () { // JK: Checks if there is any input
            let empty = true;

            Object.values (this.item_edited).forEach (function (item) {
                if (item) {empty = false};
            });

            return empty;
        }
    },

    watch:
    {
        prop_item: function () { // JK: Sometimes, there is an prop_item update issue - this watch ensures that a change of state is noted
                //console.log ('Input Template: new prop_item received (ID: '+this.prop_item.id+')');
            this.item_edited = this.prop_item;
        }
    },

    created () 
    {
        if (this.prop_clone) {this.CloneProps ();}
    },
    
    methods: 
    {
        DeriveTemplateReceive (emit) { // JK: receive emitted Input from Derive Template
            this.$emit ('update', {refresh: false, item: emit});
        },

        CloneProps () {
            var self = this;
            let items = {};

            Object.keys (self.prop_attributes) .forEach (function (key) {
                if (self.prop_attributes[key].clone != undefined ) { //JK: clone prop is undefined (usually this is the case for ID)
                    items [key] = {text: self.prop_attributes[key].text, clone: self.prop_attributes[key].clone};
                }
            });

            self.clone_props = items;
        },

        Clone () {
            var self = this;
            let item = {};

            Object.keys (self.item_edited) .forEach (function (key) {
                if (!self.clone_props [key]) { //JK: clone prop is not set (usually this is the case for ID)
                    item [key] = self.prop_attributes[key].default;
                } 
                else if (!self.clone_props [key].clone) { // JK: clone prop is false (not seleceted - either by default or by user)
                    item [key] = self.prop_attributes[key].default;
                } 
                else { // JK: clone prop is true get value from item_edited
                    item [key] = self.item_edited [key];
                }
            });
            
            self.$emit ('update', {refresh: false, item: item}); // JK: Update item in parent
            self.clone_active = false;

            self.CloneProps (); // reset clone checkboxes to default
        },

        Clear () {
            var self = this;
            let item = {};

            Object.keys (self.item_edited).forEach (function (key) {
                item [key] = self.prop_attributes[key] ? self.prop_attributes[key].default : null;
            });

            self.$emit ('update', {refresh: false, item: item});
        },

        // JK: Input Actions to dbi/
        Save (reload) {
            this.input_mode     = 'input';
            this.input_reset    = reload;
            this.preview_active = false;

            this.DataInput ();                    
        }, 

        Delete () {
                console.log ('Deletion: waiting for confirmation by user ...')
            let self = this;
            let confirmed = confirm ('Are you sure you want to delete '+self.item_label+'?'); 

            if (confirmed === true) 
            {
                console.log ('Deletion: accepted.');

                self.input_mode = 'delete';
                self.DataInput ()
            } 
            else 
            {
                console.log ('Deletion: declined.');
            }                
        },

        async DataInput () {

            if (this.prop_entity) 
            {
                let response = await this.$root.DBI_INPUT_POST (this.prop_entity, this.input_mode, this.item_edited);

                if (response.success) 
                {
                    this.$root.snackbar (response.success, 'success');

                    if (!this.item_edited.id) {this.item_edited.id = response.id;}

                    this.$emit ('update', { refresh: true, item: (this.input_mode != 'delete' ? this.item_edited : {}) });

                    if (this.input_reset) { this.Clear (); }

                    this.input_reset = false;
                }
                else
                {
                    console.log ('Data Input Error: response was not ok');
                }
            }
            else 
            {
                console.log ('Data Input Error: prop_entity is missing');
            }          
        },
        
    }
}

</script>
