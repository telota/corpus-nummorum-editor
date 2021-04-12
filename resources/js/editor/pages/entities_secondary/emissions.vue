<template>
    <div>
        <v-row>
            
            <!-- Left Panel: List ----------------------------------------------- --------------------------------------- -->
            <v-col cols="12" sm="12" md="6">
                <!-- JK: Items Template -->
                <ItemsTemplate
                    :prop_entity="entity"
                    :prop_attributes="attributes"
                    :prop_sync="false"
                    :prop_cards="false"
                    :prop_item="{id: item_edited.id ? item_edited.id : -1}"
                    :prop_refresh="items_refresh"
                    v-on:select="ItemsTemplateReceive"
                ></ItemsTemplate>
            </v-col>

            <!-- Right Panel: Input Form --------------------------------------- --------------------------------------- -->
            <v-col cols="12" sm="12" md="6">
                <v-card tile :loading="loading">

                    <!-- JK: Title -->
                    <v-app-bar color="sysbar">
                        <span class="headline">{{ (item_edited.id ? 'Edit ' : 'New ')+component+(item_edited.id ? ' '+item_edited.id : '') }}</span>
                    </v-app-bar>

                    <!--<InputTemplate
                        :prop_entity="entity"
                        :prop_label="component"
                        :prop_edit_in="null"
                        :prop_clone="true"
                        :prop_item="item_edited"
                        :prop_attributes="attributes"
                        v-on:update="InputTemplateReceive"
                    ></InputTemplate>-->

                    <!-- JK: Input Form body --> 
                    <v-card-text>
                            <v-row>

                                <!-- JK: DEV COMMENT -->
                                <v-col cols="12" sm="12" md="12">
                                    <strong class="red--text">Dev-Kommentar: Anlegen, Bearbeiten und Verknüpfen von Emissionen aktuell nicht verfügbar!</strong>
                                </v-col>

                                <!-- JK: ID -->
                                <v-col cols="12" sm="4" md="4">
                                    <v-text-field dense outlined disabled
                                        v-model="item_edited.id"
                                        label="ID"
                                        prepend-icon="fingerprint"
                                    ></v-text-field>
                                </v-col>

                                <!-- JK: Name EN -->
                                <v-col cols="12" sm="12" md="12">
                                    <v-text-field dense outlined filled
                                        v-model="item_edited.name"
                                        label="Name"
                                        hint="required"
                                        prepend-icon="label"
                                        counter=45
                                    ></v-text-field>
                                </v-col>

                                <!-- JK: Name DE -->
                                <v-col cols="12" sm="12" md="12">
                                    <v-textarea dense outlined filled no-resize height="100"
                                        v-model="item_edited.description"
                                        label="Description"
                                        prepend-icon="short_text"
                                        hint="required"
                                        counter=21845
                                    ></v-textarea>
                                </v-col>

                                <!-- JK: Date start -->
                                <v-col cols="12" sm="12" md="12">
                                    <v-textarea dense outlined filled no-resize height="100"
                                        v-model="item_edited.comment"
                                        label="Comment"
                                        prepend-icon="short_text"
                                        hint="not required"
                                        counter=21845
                                    ></v-textarea>
                                </v-col>

                            </v-row>
                    </v-card-text>

                    <!-- JK: Input Template
                    <InputTemplate class="mt-3"
                        :prop_entity="entity"
                        :prop_label="component"
                        :prop_edit_in="null"
                        :prop_clone="true"
                        :prop_item="item_edited"
                        :prop_attributes="attributes"
                        v-on:update="InputTemplateReceive"
                    ></InputTemplate> -->
                    
                </v-card>
            
            </v-col>
        </v-row>
        
        <!-- JK: Child Dialog -->
        <!--<ChildDialog v-if="child_dialog_active"
            :prop_active="child_dialog_active"
            :prop_component="child_dialog_data.component"
            :prop_item="{key: child_dialog_data.key, id: child_dialog_data.id}" 
            v-on:assignment="ChildReceive"
            v-on:close="child_dialog_active=false"
        ></ChildDialog>-->                 
    
    </div>
</template>



<script>

// JK: Set stable variables (outside of vue component scope)
const CompLabelS            = 'Emission';
const CompLabelP            = 'Emissions';
const CompLabelParent       = 'Helper Tables';
const CompName              = 'emissions';

const entity                = CompName; // JK: change if needed


export default
{    
    components: {},

    data ()  // JK: define VueJS Variables
    {
        return {
            component:          CompLabelS,
            entity:             entity,
            loading:            false,
            item_edited:        {},
            
            // ItemsTemplate
            attributes:     {
                id:             {default: null,     text: 'ID',         sk: null,   header: true,   sortable: true,   filterable: true,     },
                name:           {default: null,     text: 'Name',       sk: null,   header: true,   sortable: true,   filterable: true,     clone: true },
                description:    {default: null,     text: 'Description',sk: null,   header: false,   sortable: false,   filterable: false,     clone: true },
                comment:        {default: null,     text: 'Comment',    sk: null,   header: false,   sortable: false,   filterable: false,     clone: false }
            },
            items_refresh:      null
        }
    },

    props: 
    {
        prop_item: {type: Object}
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
        
        this.ItemDefault (); // JK: Set default item

        if (this.prop_item) { this.item_edited = this.prop_item; }
    },
    
    methods: 
    {
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
        },*/
        
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
        },     
    }
}

</script>