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
                            <v-col cols="12" sm="6" md="3">
                                <v-text-field dense outlined disabled
                                    v-model="item_edited.id"
                                    label="ID"
                                    prepend-icon="fingerprint"
                                ></v-text-field>
                            </v-col>

                            <!-- JK: Pagename -->
                            <v-col cols="12" sm="12" md="6">
                                <v-text-field dense outlined filled
                                    v-model="item_edited.pagename"
                                    label="Pagename"
                                    prepend-icon="http"
                                ></v-text-field>
                            </v-col>

                            <!-- JK: Language -->
                            <v-col cols="12" sm="6" md="3">
                                <v-select dense outlined filled
                                    v-model="item_edited.language"
                                    label="Language"
                                    prepend-icon="language"
                                    :items="languages"
                                ></v-select>
                            </v-col>

                            <!-- JK: Pagecontent -->
                            <v-col cols="12" sm="12" class="mt-n5">
                                <!--<Editor
                                    v-model="item_edited.pagecontent"
                                    api-key="no-api-key"
                                    :init="{
                                        height:         500,
                                        menubar:        true,
                                        plugins: [
                                            'advlist autolink lists link image charmap print preview anchor',
                                            'searchreplace visualblocks code fullscreen',
                                            'insertdatetime media table paste code help wordcount'
                                            ],
                                        toolbar:
                                            'undo redo | formatselect | bold italic backcolor | \
                                            alignleft aligncenter alignright alignjustify | \
                                            bullist numlist outdent indent | removeformat | help'}"
                                ></Editor>
                                        <!--skin:           ($vuetify.theme.dark ? 'oxide-dark' : ''),
                                        content_css:    ($vuetify.theme.dark ? 'dark' : ''),-->
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

    </div>
</template>



<script>

// JK: Set stable variables (outside of vue component scope)
const CompLabelS            = 'Website Text';
const CompLabelP            = 'Texts';
const CompLabelParent       = 'Website';
const CompName              = 'texts';

const entity                = CompName; // JK: change if needed

// Import Components (don't forget to register in export default -> components)
//import Editor from '@tinymce/tinymce-vue'


export default // JK: Vue Stuff
{
    /*components:
    {
        Editor
    },*/

    data ()
    {
        return {
            component:          CompLabelS,
            componentp:         CompLabelP,
            entity:             entity,
            loading:            false,
            item_edited:        {},

            // Form Drop Down
            languages:          ['de', 'en'],

            attributes:     {
                id:             {default: null,     text: 'ID',             sk: null,   header: true,   sortable: true,   filterable: true,     },
                pagename:       {default: null,     text: 'Page Name',      sk: null,   header: true,   sortable: true,   filterable: true,     clone: false },
                language:       {default: null,     text: 'Language',       sk: null,   header: true,   sortable: true,   filterable: true,     clone: false },
                pagecontent:    {default: '',       text: 'Page Content',   sk: null,   header: false,  sortable: false,  filterable: false,    clone: false },
            },
            items_refresh:      null,

            // Controls for Child Dialog
            child_dialog_active:    false,
            child_dialog_data:      {component: null, key: null, id: null},
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
        /*ChildDialog (input) { // JK: toggle child component
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
        },*/

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
