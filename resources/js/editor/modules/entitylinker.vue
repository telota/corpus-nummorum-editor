<template>
<div>

    <v-card tile :loading="loading">

        <div class="pa-3 pl-4 appbar title">
            <span>
                Links on
                {{ label (entity).s }}
                {{ id ? id : ''}}
            </span>
        </div>

        <v-card-text class="mt-n4">

            <div v-for="(target, i) in targets" :key="i">
                <gallery
                    :show_toolbar="show_toolbar"
                    editor
                    :entity="target"
                    :search_key="entities [entity] ['id']"
                    :search_val="id"
                    :key="id+target+refresh"
                    :limit="8"
                    :tiles="4"
                    v-on:editor="editor [target] = !editor [target]"
                ></gallery>

                <div v-if="editor [target]" class="pt-4 mb-n3">
                    <v-row>

                        <v-col cols="12" sm="6">
                            <v-select dense outlined filled
                                v-model="input.id_side" 
                                label="Side"
                                :items="ob_re"
                                prepend-icon="tonality"
                            ></v-select>
                        </v-col>

                        <v-col cols="12" sm="6">
                            <InputForeignKey search_mixed_words
                                entity="positions" 
                                label="Position" 
                                icon="motion_photos_on"                    
                                :selected="input.id_position ? parseInt (input.id_position) : null" 
                                :key="refresh" 
                                v-on:emitid="function (emit) {input.id_position = emit;}"
                            ></InputForeignKey>
                        </v-col>

                        <v-col cols="12" sm="6">
                            <v-text-field dense outlined filled clearable
                                v-model="input.id_target" 
                                :label="label (target).s+' ID'" 
                                :prepend-icon="target == 'coins' ? 'copyright' : 'blur_circular'"
                                :disabled="!id"
                            ></v-text-field>
                        </v-col>
                        
                        <v-col cols="12" sm="6">
                            <v-btn tile color="blue_prim" class="white--text" :disabled="btn_on" @click="link (target, 'link')" v-text="'link'"></v-btn>
                            <v-btn tile color="blue_sec" class="white--text ml-5" :disabled="btn_on" @click="link (target, 'unlink')" v-text="'unlink'"></v-btn>                        
                        </v-col>
                    </v-row>
                </div>
            </div>

        </v-card-text>

    </v-card>

</div>
</template>



<script>

export default
{
    data ()
    {
        return {
            items:          [],

            input:          {id: null, id_side: 0, id_position: null},

            ob_re:          [{value: 0, text: 'Obverse'}, {value: 1, text: 'Reverse'} ],

            loading:        false,
            refresh:        'a',
            input_mode:     'connect',
            editor:         {coins: false, types: false},

            entities:       {
                                monograms: {id: 'id_monogram', input: ['id_side', 'id_position']},
            },
        }        
    },

    props: 
    {
        id:             {type: Number},
        entity:         {type: String,},
        target:         {type: String, default: 'coins'},
        coins_only:     {type: Boolean,},
        types_only:     {type: Boolean,},
        show_toolbar:   {type: Boolean},
    },

    computed:
    {
        targets () {
            if (!this.coins_only && !this.types_only) { return ['coins', 'types']}
            else if (this.types_only) { return ['types']}
            else { return ['types']}
        },

        btn_on () {
            let on = 0;
            var self = this;

            self.entities[self.entity]['input'].forEach ( 
                function (check) 
                {
                    if (self.input [check] === undefined || self.input [check] === null) {on = on +1;}
                }
            );

            if (!self.input ['id_target']) {on = on +1;}

            return on > 0 ? true : false;
        }
    },

    created ()
    {
    },

    methods: 
    {
        label (entity) {
            return {
                s: (entity.charAt(0).toUpperCase() + entity.slice(1)).substring (0, entity.length-1),
                p: (entity.charAt(0).toUpperCase() + entity.slice(1))
            };
        },

        async link (target, operator) {

            var self = this;

            this.input.id = this.id;
            this.input.mode = operator;
            this.input.target = target;

            let response = await this.$root.DBI_INPUT_POST (this.entity, this.input_mode, this.input);

            if (response.success)
            {
                this.$root.snackbar (response.success, 'success');

                self.entities[self.entity]['input'].forEach ( 
                    function (key) 
                    {
                        self.input [key] = null;
                    }
                );

                this.refresh = this.refresh == 'a' ? 'b' : 'a';
            }
            else
            {
                console.log ('Data Input Error: response was not ok');
            }
        },
    }
}

</script>