<template>
<div>
    <v-row>

        <v-col cols="12" sm="5">
            <v-card tile> 

                <v-app-bar color="sysbar">
                    <span class="headline">Data Importer</span>
                </v-app-bar>

                <v-card tile class="appbar component-wrap d-flex">
                    <v-spacer></v-spacer>
                    <v-btn text disabled><v-icon>sync_problem</v-icon></v-btn>
                </v-card>

                <v-card-text>
                    <v-row>
                        <v-col cols="12" sm="4">
                            <v-select dense outlined filled
                                :items="entities"
                                v-model="item_edited.entity"
                                label="Entity"
                            ></v-select>
                        </v-col>

                        <v-col cols="12" sm="8">
                            <v-text-field dense outlined filled clearable
                                v-model="item_edited.src"
                                label="Source URL"
                                hint="Please provide full link (http:// ...)"
                                counter=255
                            ></v-text-field>
                        </v-col>

                        <v-col cols="12" sm="12" class="mt-n3">
                            <v-btn block tile color="light-blue darken-4" 
                                class="white--text"
                                :disabled="item_edited.src ? false : true"
                                @click="GetData ()"
                            > {{ item_edited.src ? 'Get Data' : 'Please provide Source URL' }} </v-btn>
                        </v-col>
                    </v-row>


                    <div class="caption text-justify mt-10">
                        <p>Der "Data Importer" ist ein Tool, um Daten aus externen Quellen abzuholen und in die CN-Datenbank zu importieren. 
                        Die importierten Datensätze folgen einem ähnlichen Schema wie die bisherigen Importe oder von externen Nutzern eingegebene Daten.</p>
                        <p>Bitte wählen Sie die Art des zu importierenden Objekts (Münze oder Typ) und geben die Quell-Adresse ein.
                        Der Data Importer wird die Daten abholen, auswerten und Ihnen das Ergebnis als Checkliste ausgeben (sofern er sie lesen konnte). 
                        Sie können dann ggf. einzelne Einträge abwählen, sollten Sie sie für fehlerhaft halten. Anschließend können Sie den Datensatz in der DB anlegen lassen.
                        Der Data Importer wird Ihnen schließlich einen Link zum alten Admintool erzeugen, damit Sie den Datensatz weiter bearbeiten können.</p>
                        <p>Um Dubletten zu vermeiden, wird der Data Importer das SourceURL-Feld in der DB mit der Importquelle abgleichen. Bei einer Übereinstimmung erfolgt kein Import.
                        Der Data Importer wird Ihnen stattdessen den Link zum bestehenden Datensatz ausgeben.</p>
                        <p><b class="red--text">Wichtiger Hinweis:</b> Bitte beachten Sie die korrekte Formatierung des Links. Der Data Importer erwartet die vollständige Adresse, z.B. http://numismatics.org/sco/id/sc.1.481 oder http://numismatics.org/collection/1967.152.675.
                        Diese bekommen Sie, wenn Sie einfach die vollständige URL aus Ihrem Browser in das Feld kopieren. Aktuell werden nur Datensätze von numusmatics.org unterstützt.</p>
                    </div>
                </v-card-text>

            </v-card>
        </v-col>

        <v-col cols="12" sm="7">
            <v-card tile :loading="loading"> 

                <v-app-bar color="sysbar">
                    <span class="headline">Import Precheck</span>
                </v-app-bar>

                <v-card tile class="appbar component-wrap d-flex">
                    <v-btn text disabled>{{imported [0] == undefined ? 'No Data, yet' : item_edited.src}}</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn depressed class="appbar"
                        @click="imported = {}"
                        :tile="imported [1] != undefined"
                        :text="imported [1] == undefined"
                        :disabled="imported [1] == undefined"                        
                    > <v-icon>clear</v-icon> </v-btn>
                </v-card>

                <v-card-text v-if="imported [1] != undefined">
                    <div class="title pl-4">Success!</div>
                    <v-divider class="ma-4"></v-divider>

                    <v-simple-table dense class="mt-6">
                        <tr v-for="(value, key, i) in imported [1]" :key="i">
                            <td class="font-weight-bold">
                                <div class="d-flex component-wrap pt-5">
                                    <v-checkbox v-model="check [key]" :disabled="disabled[item_edited.entity].includes(key)" class="ma-0 pa-0 mr-6"></v-checkbox>{{ key }}
                                </div>
                            </td>
                            <td>
                                {{ imported[2] ? (imported[2][key] ? (imported[2][key].info+' ('+value+')') : value) : value }}
                            </td>
                        </tr>
                    </v-simple-table>

                    <v-btn block tile color="light-blue darken-4" 
                        class="white--text mt-8"
                        :disabled="item_edited.src ? false : true"
                        @click="Import ()"
                    > {{ 'Import Data' }} </v-btn>
                </v-card-text>

                <v-card-text v-if="imported [0] != undefined && imported [1] == undefined">
                    <div class="title mt-3">Duplicate detected!</div>
                    <div class="mt-1">This resource has already been imported. Please check the following datasets.</div>

                    <v-simple-table dense class="mt-6 mb-5">
                        <tr v-for="(value, key, i) in imported [0].id" :key="i">
                            <td class="d-flex align-start font-weight-bold">{{ key }}</td>
                            <td><a :href="value" target="_blank">{{ value }}</a></td>
                        </tr>
                    </v-simple-table>
                </v-card-text>

                <v-card-text v-if="status.success != undefined">
                    <div class="title mt-3">Server Response</div>
                    <div class="mt-1">{{ status.success }}</div>
                    <div class="mt-5 mb-5 component-wrap d-flex" v-if="status.id != undefined">
                        <div class="mr-10 font-weight-bold">{{ status.id [0] }}</div> <a :href="status.id [1]" target="_blank">{{ status.id [1] }}</a>
                    </div>
                </v-card-text>

                <v-card-text v-if="error.read || error.write">
                    <div class="title">{{ error.read ? 'Parsing Error' : 'Input Error' }}</div>
                    <div class="mt-1">{{ error.read ? error.read : error.write }}</div>
                </v-card-text>

            </v-card>
        </v-col>

    </v-row>
</div>
</template>


<script>


export default
{
    data () 
    {
        return {
            loading:            false,
            entities:           [
                                    {value: 'coins', text: 'Coin'}, 
                                    {value: 'types', text: 'Type'}
                                ],
            item_edited:        {
                                    entity: 'coins',
                                    src: null
            },
            imported:           {},
            check:              {},
            disabled:           {
                                    coins: ['SourceURL','CreateDate','ChangeDate','ReadyToPublish'],
                                    types: ['SourceURL','CreateDate','ChangeDate','ReadyToPublish']
                                },
            status:             {},
            error:              {read: null, write: null},
        }
    },
    
    computed: 
    {
    },

    methods: 
    {
        async GetData () {
            this.loading    = true;
            var self        = this;
            self.check      = {};
            self.imported   = {};
            self.status     = {};
            self.error      = {read: null, write: null};

            let source      = 'dbi/import/'+(self.item_edited.entity == 'coins' ? 'coin' : 'type')+'/'+self.item_edited.src;
            let dbi         = await axios.get (source)

            if (typeof dbi.data == 'string') 
            {
                self.error = {read: dbi.data.substring(7), write: null};
            }
            else
            {
                self.imported = Object.values (dbi.data);

                if (self.imported [1] != undefined) 
                {
                    Object.keys (self.imported [1]) .forEach (function (key) {
                        self.check [key] = true;
                    });
                }
            }

            self.loading = false;
        },


        async Import () {
            this.loading    = true;
            var self        = this;
            let input       = {};

            Object.keys (self.check) .forEach (function (key) 
            {
                if (self.check [key]) {
                    input [key] = self.imported[1][key];
                }
            });

            self.$store.dispatch ('EXE_INPUT_TO_SERVER', 
            {
                path:   'dbi/importer/input/insert', 
                input:  Object.assign ({}, {
                    entity: self.imported[0].entity,
                    input:  input
                })                
            })

            .then(function(response) 
            {
                if (response.success != undefined) 
                {                
                    self.status     = response;
                }
                else
                {
                    self.error = {read: null, write: response.substring(7)};
                }

                self.imported   = {};
                self.check      = {};
                self.loading    = false;
            })

        },
    }
}

</script>