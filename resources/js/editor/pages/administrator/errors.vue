<template>
    <div>
        <v-row>

            <!-- Left Panel: List ----------------------------------------------- --------------------------------------- -->
            <v-col cols="12" sm="12" md="8">
                <!-- JK: Items Template -->
                <ItemsTemplate
                    :prop_entity="entity"
                    :prop_attributes="attributes"
                    :prop_sync="false"
                    :prop_cards="false"
                    :prop_item="{id: item.id ? item.id : -1}"
                    :key="items_refresh"
                    v-on:select="ItemsTemplateReceive"
                ></ItemsTemplate>
            </v-col>
            
            <!-- Right Panel: Input Form --------------------------------------- --------------------------------------- -->
            <v-col cols="12" sm="12" md="4">
                <v-card tile>
                    <v-card-text>
                        <div class="mb-10" style="white-space: pre-line">
                            This is the log of all Axios related "system errors" (no validation issues are tracked).
                            If the "Error Dialog" is triggered, an axios post will be send to dbi/errors/input/insert.
                            That means of cause that this interface must be working. Otherwise Tracking is impossible.
                        </div>
                        <v-simple-table>
                            <tbody>
                            <tr>
                                <td class="font-weight-black" style="vertical-align: top;">
                                    {{ attributes.id.text }}</td>           <td>{{ item.id }}</td>
                            </tr><tr>
                                <td class="font-weight-black" style="vertical-align: top;">
                                    {{ attributes.created_at.text }}</td>   <td>{{ item.created_at}}</td>
                            </tr><tr>
                                <td class="font-weight-black" style="vertical-align: top;">
                                    {{ attributes.user.text }}</td>         <td><span v-if="item.id">( {{ item.id_user }} )&ensp;{{ item.user }}</span></td>
                            </tr><tr>
                                <td class="font-weight-black" style="vertical-align: top;">
                                    {{ attributes.resource.text }}</td>     <td>{{ item.resource }}</td>
                            </tr><tr>
                                <td class="font-weight-black" style="vertical-align: top;">
                                    {{ attributes.exception.text }}</td>    <td>{{ item.exception }}</td>
                            </tr><tr>
                                <td class="font-weight-black" style="vertical-align: top;">
                                    {{ attributes.message.text }}</td>      <td>{{ item.message }}</td>
                            </tr><tr>
                                <td class="font-weight-black" style="vertical-align: top;">
                                    {{ attributes.parameters.text }}</td>   <td style="white-space: pre-line">{{ params }}</td>
                            </tr><tr>
                                <td class="font-weight-black" style="vertical-align: top;">
                                    {{ attributes.archived.text }}</td>   <td><span v-if="item.id">{{ item.archived === 1 ? 'Yes' : 'No' }}&ensp;{{ item.updated_at != item.created_at ? '('+item.updated_at+')' : ''}}</span></td>
                            </tr>
                            </tbody>
                        </v-simple-table>
                    </v-card-text>
                    
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="Delete ()" :disabled="!item.id || true"><v-icon>delete</v-icon></v-btn>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="Archive ()" :disabled="!item.id"><v-icon>{{ item.archive === 1 ? 'archive' : 'unarchive' }}</v-icon></v-btn>
                        <v-spacer></v-spacer>
                    </v-card-actions>
                </v-card>
            
            </v-col>
        </v-row>      
    
    </div>
</template>



<script>

// JK: Set stable variables (outside of vue component scope)
const CompLabelS            = 'Error';
const CompLabelP            = 'Errors';
const CompLabelParent       = 'Administrator';
const CompName              = 'errors';

const entity                = CompName; // JK: change if needed


export default
{    
    components: {},

    data ()  // JK: define VueJS Variables
    {
        return {
            component:          CompLabelS,
            componentp:         CompLabelP,
            entity:             entity,
            item:               {},
            
            // ItemsTemplate
            attributes:     {
                id:         {default: null,     text: 'ID',         sk: null,   header: true,   sortable: true,   filterable: true, },
                resource:   {default: null,     text: 'Resource',   sk: null,   header: true,   sortable: true,   filterable: true, },
                exception:  {default: null,     text: 'Exception',  sk: null,   header: true,   sortable: true,   filterable: true, },
                message:    {default: null,     text: 'Message',    sk: null,   header: false,  sortable: false,  filterable: false, },
                parameters: {default: null,     text: 'Parameters', sk: null,   header: false,  sortable: false,  filterable: false, },
                id_user:    {default: null,     text: 'User ID',    sk: null,   header: true,   sortable: true,   filterable: true, },
                user:       {default: null,     text: 'User',       sk: null,   header: false,  sortable: false,  filterable: true, },
                archived:   {default: null,     text: 'Archived',   sk: null,   header: true,   sortable: true,   filterable: false, },
                created_at: {default: null,     text: 'Date',       sk: null,   header: true,   sortable: true,   filterable: true, },
                updated_at: {default: null,     text: 'Date2',      sk: null,   header: false,  sortable: false,  filterable: false, }
            },
            items_refresh:      0,
        }
    },

    props: 
    {

    },

    computed: 
    {
        params () {

            let params = '';

            if (this.item.parameters && this.item.parameters != 'none given')
            {                
                for (const [key, value] of Object.entries ( JSON.parse (this.item.parameters)  ))
                {
                    params += key+': '+value+"\n";
                }
            }

            return params ? params : 'none given';
        }
    },

    created () 
    {
        this.$store.commit( 'setBreadcrumbs',[ // JK: Set Breadcrumbs
            {label:CompLabelParent,to:''},
            {label:CompLabelP,to:''},
        ]);
    },
    
    methods: 
    {        
        // JK: Template Components
        ItemsTemplateReceive (emit) { // JK: receive emitted Input from child component
                console.log (CompLabelP+': received emit from Items Template (ID: '+emit.id+')')
            this.item = emit;
        },

        async Archive ()
        {
            this.item.archive = this.item.archive === 1 ? 0 : 1;

            console.log (this.item.archive)

            let response = await this.$root.DBI_INPUT_POST ('errors', 'update', this.item);

            if (response.success) 
            {
                this.$root.snackbar (response.success, 'success');
                this.items_refresh++;
            }
            else
            {
                console.log ('Data Input Error: response was not ok');
            }
        },

        async Delete ()
        {
            let response = await this.$root.DBI_INPUT_POST ('errors', 'delete', this.item);

            if (response.success) 
            {
                this.$root.snackbar (response.success, 'success');
                this.items_refresh++;
            }
            else
            {
                console.log ('Data Input Error: response was not ok');
            }

        },
    }
}

</script>