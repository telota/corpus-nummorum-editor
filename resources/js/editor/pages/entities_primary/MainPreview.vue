<template>
<div>

    <v-dialog v-model="active" :width="width" :fullscreen="fullscreen || !$vuetify.breakpoint.mdAndUp" persistent scrollable>
        <v-card tile>

            <!-- JK: Sysbar -->
            <v-system-bar color="sysbar" class="pt-5 pb-4 pl-6">
                <v-icon class="mr-2 mb-1">preview</v-icon> <span>Preview</span>
                <v-spacer></v-spacer>
                <v-icon @click="fullscreen = !fullscreen" class="mr-3" v-if="$vuetify.breakpoint.mdAndUp">{{ !fullscreen ? 'fullscreen' : 'fullscreen_exit' }}</v-icon>
                <v-icon @click="$emit('EmitCommand', {command: 'preview', value: false})">close</v-icon>
            </v-system-bar>

            <!-- JK: Title -->
            <v-card-title class="appbar text-center pb-4">
                <v-spacer>
                    Preview {{ item.id ? (label+' '+item.id) : ('New '+label) }}
                </v-spacer>
            </v-card-title>

            <!-- JK: Tables -->
            <v-card-text class="pt-5">
                <div class="d-flex component-wrap justify-center">
                    <div v-for="n in ($vuetify.breakpoint.mdAndUp ? 3 : 2)" :key="n">
                        <table :class="n < ($vuetify.breakpoint.mdAndUp ? 3 : 2) ? 'mr-10' : ''">
                            <tr v-for="(detail, c) in Content (item, ($vuetify.breakpoint.mdAndUp ? 3 : 2), n)" :key="c">
                                <td :class="'pb-1 d-flex align-top font-weight-bold'+(detail.m ? ' pl-'+detail.m : '')" style="white-space: nowrap;">
                                    {{ detail.key !== null ? (detail.text+':') : '&ensp;' }}
                                </td>
                                <td class="pl-4 pb-1">
                                    {{ detail.value !== 'LEAVE__BLANK' ? (detail.value !== null && detail.value !== '' ? detail.value : '--') : '' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </v-card-text>

            <!-- JK: Actions -->
            <v-card-actions class="appbar text-center">
                <v-spacer>
                    <!-- Clear --> 
                    <v-btn @click="$emit ('EmitCommand', {command: 'preview', value: false})" icon class="mr-5"
                    ><v-icon>clear</v-icon></v-btn>

                    <!-- Save --> 
                    <v-btn  @click="$emit ('EmitCommand', {command: 'save', value: false})" icon
                    ><v-icon>done</v-icon></v-btn>

                    <!-- Save and done --> 
                    <v-btn  @click="$emit ('EmitCommand', {command: 'save', value: true})" icon
                    ><v-icon>done_all</v-icon></v-btn>
                </v-spacer>
            </v-card-actions>

        </v-card>
    </v-dialog>

</div>
</template>



<script>

export default
{ 
    data ()  // JK: define VueJS Variables
    {
        return {
            width:          '67%',
            fullscreen:     false,
        }        
    },

    props: 
    {
        attributes:         {type: Object},
        item:               {type: Object},
        label:              {type: String},
        active:             {type: Boolean}
    },
    
    methods: 
    {
        Content (input, cols, col) {
            var self    = this;
            let item    = [];
            
            let c = 0;

            Object.keys (input) .forEach (function (key) 
            {
                const is_string = input[key] != null && (typeof input[key] == 'object' || typeof input[key] == 'array') ? false : true;

                if ( (c > 0 && Number.isInteger(c / 5)) || !is_string ) {item.push ( {key: null, value: 'LEAVE__BLANK', text: null});}
                
                if (is_string) 
                {
                    item.push ( {key: key, value: input[key], text: self.attributes[key] ? self.attributes[key].text : key});
                    c++;
                }
                else 
                {
                    item.push ( {key: key, value: 'LEAVE__BLANK', text: self.attributes[key] ? self.attributes[key].text : key});

                    for (let i = 0; i < input[key].length; i++)
                    {
                        item.push ( {key: key, value: 'LEAVE__BLANK', text: '('+(i+1)+')', m: 3});
                        Object.keys (input[key][i]) .forEach (
                            function (sub_key) 
                            {
                                item.push ( {key: sub_key, value: input[key][i][sub_key], text: self.attributes[key].nested ? self.attributes[key].nested[sub_key].text : sub_key, m: 5} );
                            }
                        );
                    }

                    c=5;
                }                
            });

            const length = item.length;
            const start  = Math.ceil (length / cols * (col -1) );
            const end    = Math.ceil (length / cols * col );
            let item_fin = [];

            for (let i = start; i < end; i++)
            {
                if (item [i].key || (i > start && i < (end-1) )) {item_fin.push (item [i]);}
            }

            return item_fin;
        }
    }
}

</script>