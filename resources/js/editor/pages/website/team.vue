<template>
    <div>
        <v-row>
            
            <v-col cols="12" sm="12" md="5">

                <!-- JK: Items Template -->
                <ItemsTemplate
                    :prop_entity="entity"
                    :prop_attributes="attributes"
                    :prop_title="componentp"
                    :prop_sync="false"
                    :prop_cards="false"
                    :prop_img_is_coin="false"
                    :prop_item="{id: item_edited.id ? item_edited.id : -1}"
                    :prop_refresh="items_refresh"
                    v-on:select="ItemsTemplateReceive"
                ></ItemsTemplate>

            </v-col>
            
            <v-col cols="12" sm="12" md="7">
                <v-card tile :loading="loading">

                    <!-- JK: Title -->
                    <v-app-bar color="sysbar">
                        <span class="headline">{{ (item_edited.id ? 'Edit ' : 'New ')+component+(item_edited.id ? ' '+item_edited.id : '') }}</span>
                    </v-app-bar>

                    <InputTemplate
                        :prop_entity="entity"
                        :prop_label="component"
                        :prop_edit_in="null"
                        :prop_clone="false"
                        :prop_item="item_edited"
                        :prop_attributes="attributes"
                        v-on:update="InputTemplateReceive"
                    ></InputTemplate>

                    <!-- JK: Input Form body --> 
                    <v-card-text>
                            <v-row>

                                <!-- JK: ID -->
                                <v-col cols="12" sm="3" md="3">
                                    <v-text-field dense outlined disabled
                                        v-model="item_edited.id"
                                        label="ID"
                                        prepend-icon="fingerprint"
                                    ></v-text-field>
                                </v-col>

                                <!-- JK: Joined Date -->
                                <v-col cols="12" sm="3" md="3">
                                    <v-menu offset-y
                                        :close-on-content-click="false"
                                        :nudge-right="40"
                                        transition="scale-transition"
                                    >
                                        <template v-slot:activator="{ on }">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item_edited.joined_at"
                                                label="Joined at"
                                                prepend-icon="meeting_room"
                                                readonly
                                                v-on="on"
                                            ></v-text-field>
                                        </template>
                                        <v-date-picker v-model="item_edited.joined_at" ></v-date-picker>
                                    </v-menu>
                                </v-col>

                                <!-- JK: Left Date -->
                                <v-col cols="12" sm="3" md="3">
                                    <v-menu offset-y
                                        :close-on-content-click="false"
                                        :nudge-right="40"
                                        transition="scale-transition"
                                    >
                                        <template v-slot:activator="{ on }">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item_edited.left_at"
                                                label="Left at"
                                                prepend-icon="no_meeting_room"
                                                readonly
                                                v-on="on"
                                            ></v-text-field>
                                        </template>
                                        <v-date-picker v-model="item_edited.left_at" ></v-date-picker>
                                    </v-menu>
                                </v-col>

                                <!-- JK: Title-->
                                <v-col cols="12" sm="3" md="3">
                                    <v-text-field dense outlined filled
                                        v-model="item_edited.title"
                                        label="Title (Prof., Dr.)"
                                        prepend-icon="school"
                                        hint="not required"
                                    ></v-text-field>
                                </v-col> 

                                <!-- JK: Name-->  
                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field dense outlined filled
                                        v-model="item_edited.firstname"
                                        label="Firstname"
                                        prepend-icon="person"
                                        hint="required"
                                        counter=255
                                    ></v-text-field>
                                </v-col> 

                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field dense outlined filled
                                        v-model="item_edited.lastname"
                                        label="Lastname"
                                        prepend-icon="person"
                                        hint="required"
                                        counter=255
                                    ></v-text-field>
                                </v-col> 

                                <!-- JK: DE : Text -->
                                <v-col cols="12" sm="6" md="6">
                                    <v-textarea dense outlined filled no-resize height="200"
                                        v-model="item_edited.text_de"
                                        label="Text (DE)"
                                        prepend-icon="subject"
                                        hint="Deutsch (visible on preview: no)"
                                        counter=21845
                                    ></v-textarea>
                                </v-col>
                                <!-- JK: EN : Text -->
                                <v-col cols="12" sm="6" md="6">
                                    <v-textarea dense outlined filled no-resize height="200"
                                        v-model="item_edited.text_en"
                                        label="Text (EN)"
                                        prepend-icon="subject"
                                        hint="English (visible on preview: no)"
                                        counter=21845
                                    ></v-textarea>
                                </v-col>

                                <!-- JK: ID User -->
                                <v-col cols="12" sm="3" md="3">
                                    <v-text-field dense outlined filled
                                        v-model="item_edited.id_user"
                                        label="User ID"
                                        hint="not required"
                                        prepend-icon="fingerprint"
                                    ></v-text-field>
                                </v-col>

                                <!-- JK: Image -->
                                <v-col cols="12" sm="9" md="9">
                                    <div class="component-wrap d-flex">
                                        <v-text-field dense outlined disabled
                                            v-model="item_edited.image"
                                            label="Attached Image"
                                            prepend-icon="image"
                                            hint="Click on folder or cloud icon." persistent-hint
                                            counter=255                                
                                        ></v-text-field>

                                        <v-tooltip bottom v-if="item_edited.image">
                                            <template v-slot:activator="{ on }"> 
                                                <v-btn v-on="on" @click="item_edited.image = null" icon class="ml-1 mr-5">
                                                    <v-icon>clear</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Delete link (file won't be deleted!)</span>
                                        </v-tooltip>
                        
                                        <!-- JK: Files -->
                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on }"> 
                                                <v-btn v-on="on" icon class="ml-1 mr-1" @click="ChildDialog ({component: 'files', key: 'image', id: item_edited.image})">
                                                    <v-icon>folder_open</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Open File Browser.</span>
                                        </v-tooltip> 
                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on }"> 
                                                <v-btn v-on="on" @click="UploadDialog ()" icon>
                                                    <v-icon>cloud_upload</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Direct Upload</span>
                                        </v-tooltip>
                                    </diV>                          
                                </v-col>

                            </v-row>
                    </v-card-text>

                    <!-- JK: Input Template -->
                    <InputTemplate class="mt-3"
                        :prop_entity="entity"
                        :prop_label="component"
                        :prop_edit_in="null"
                        :prop_clone="false"
                        :prop_item="item_edited"
                        :prop_attributes="attributes"
                        v-on:update="InputTemplateReceive"
                    ></InputTemplate>          
                    
                </v-card>
            
            </v-col>
        </v-row>
        
        <!-- Files -->
        <upload
            :prop_active="upload_active"
            :prop_target="upload_target"
            v-on:ChildEmit="UploadReceive"
            v-on:close="upload_active=false"
        ></upload>

        <!-- JK: Child Dialog -->
        <ChildDialog v-if="child_dialog_active"
            :prop_active="child_dialog_active"
            :prop_component="child_dialog_data.component"
            :prop_item="{key: child_dialog_data.key, id: child_dialog_data.id, parent: entity}" 
            v-on:assignment="ChildReceive"
            v-on:close="child_dialog_active=false"
        ></ChildDialog>             
    
    </div>
</template>



<script>

// JK: Set stable variables (outside of vue component scope)
const CompLabelS            = 'Team Member';
const CompLabelP            = 'Team';
const CompLabelParent       = 'Website';
const CompName              = 'team';
const FileBrowserDirectory  = 'storage/website/team';

const entity                = CompName; // JK: change if needed
const directory             = 'storage/'+entity; // JK: upload path


export default
{    
    components: {},

    data ()
    {
        return {
            component:          CompLabelS,
            componentp:         CompLabelP,
            entity:             entity,
            loading:            false,
            item_edited:        {},
            
            attributes:     {
                id:             {default: null,     text: 'ID',         sk: null,   header: true,   sortable: true,   filterable: true,     },
                lastname:       {default: null,     text: 'Lastname',   sk: null,   header: true,   sortable: true,   filterable: true,     clone: false },
                firstname:      {default: null,     text: 'Firstname',  sk: null,   header: true,   sortable: true,   filterable: true,     clone: false },
                title:          {default: null,     text: 'Title',      sk: null,   header: false,  sortable: false,  filterable: false,    clone: false },
                joined_at:      {default: null,     text: 'Joined at',  sk: null,   header: false,  sortable: false,  filterable: false,    clone: false },
                left_at:        {default: null,     text: 'Left at',    sk: null,   header: false,  sortable: false,  filterable: false,    clone: false },
                image:          {default: null,     text: 'Image',      sk: null,   header: false,  sortable: false,  filterable: false,    },
                id_user:        {default: null,     text: 'User ID',    sk: null,   header: false,  sortable: false,  filterable: false,    clone: false },
                text_de:        {default: null,     text: 'Text DE',    sk: null,   header: false,  sortable: false,  filterable: false,    clone: false },
                text_en:        {default: null,     text: 'Text EN',    sk: null,   header: false,  sortable: false,  filterable: false,    clone: false },
            },
            items_refresh:      null,

            // Controls for Child Dialog
            child_dialog_active:    false,
            child_dialog_data:      {component: null, key: null, id: null},

            // Controls for Child Dialog
            upload_active:      false,
            upload_target:      directory,
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
        // JK: Upload Dialog
        UploadDialog () { // JK: Call upload dialog
            this.upload_target = this.directory;
            this.upload_active = true;
        },

        UploadReceive (emit) { // JK: receive emitted Input from upload dialog
            this.item_edited.image = emit.url;
            this.upload_active = false;
        },

        // JK: Parent-Child-Communication
        ChildDialog (input) { // JK: toggle child component
            this.child_dialog_data = {
                component:  !input ? null   :   input.component, 
                key:        !input ? null   :   input.key,
                id:         !input ? null   :   input.id, 
            };
            this.child_dialog_active = true;
                //console.log ('Child Dialog: {component: '+this.child_dialog_data.component+', key: '+this.child_dialog_data.key+', id:'+this.child_dialog_data.id+', active: '+this.child_dialog_active+'}');
        },

        ChildReceive (emit) { // JK: receive emitted Input from child Dialog
                //console.log (Object.entries(emit));
            this.item_edited [emit.key] = emit.id;
            this.$store.commit ('showSnackbar', {message: 'SUCCESS: Assigned ID: '+emit.id+' from '+emit.component.charAt(0).toUpperCase()+ emit.component.slice(1)+' to form. Change not saved, yet', duration: 10000}); // JK: Response to snackbar
            this.ChildDialog (null);
        },
        
        // JK: Template Components
        ItemsTemplateReceive (emit) { // JK: receive emitted Input from child component
                //console.log (CompLabelP+': received emit from Items Template (ID: '+emit.id+')')
            this.item_edited = emit;
            this.$emit('ChildEmit', {component: entity, id: emit.id} );
        },

        InputTemplateReceive (emit) { // JK: receive emitted Input from Input Template
                console.log (CompLabelP+': received emit from Input Template (refresh: '+this.items_refresh+')')

            if (emit.refresh) { // JK: Refresh of items in items template is requested
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
        }      
    }
}

</script>