<template>
<div class="d-flex component-wrap">

    <v-btn :color="color" depressed
        @click="$emit ('offset', 0)" 
        :disabled="offset > 0 ? false : true"
        :text="offset > 0 ? false : true"
        :tile="offset > 0 ? true: false"
    > <v-icon>first_page</v-icon> </v-btn>

    <v-btn :color="color" depressed
        @click="$emit ('offset', offset - limit)" 
        :disabled="offset > 0 ? false : true"
        :text="offset > 0 ? false : true"
        :tile="offset > 0 ? true : false"
    > <v-icon>navigate_before</v-icon> </v-btn>

    <!-- JK: Page Jumper-->
    <v-menu offset-y :close-on-content-click="false" transition="scale-transition">
        <template v-slot:activator="{ on: menu }">
            <v-tooltip bottom>
            <template v-slot:activator="{ on: tooltip }">
                <v-btn v-on="{ ...tooltip, ...menu }" class="caption" :color="color" depressed
                    :disabled="count < limit" 
                    :text="count < limit"
                    :tile="count >= limit"
                >
                    {{ count == 0 ? '0 / 0' : ( (Math.ceil(offset / limit) +1) +' / '+ Math.ceil(count / limit) ) }}
                </v-btn>
            </template>
            <span>Go to specific page</span>
            </v-tooltip>
        </template>
        <v-card class="pb-1 pl-3 pr-3">
            <v-text-field dense clearable autofocus
                v-model="page_jump"
                v-on:input="$emit ('offset', page_jump > 0 ? ((page_jump-1) * limit) : 0)"
                hint="jump to page" persistent-hint
            ></v-text-field>
        </v-card>                       
    </v-menu>

    <v-btn :color="color" depressed
        @click="$emit ('offset', offset + limit)" 
        :disabled="offset < count - limit ? false : true"
        :text="offset < count - limit ? false : true"
        :tile="offset < count - limit ? true : false" 
    > <v-icon>navigate_next</v-icon> </v-btn>

    <v-btn :color="color" depressed
        @click="$emit ('offset', (Math.ceil (count / limit) -1) * limit)" 
        :disabled="offset < count - limit ? false : true"
        :text="offset < count - limit ? false : true"
        :tile="offset < count - limit ? true : false"
    > <v-icon>last_page</v-icon> </v-btn>

</div>
</template>


<script>
export default {
    data () {
        return { 
            page_jump:          null,
        }
    },

    props:
    {
        offset:     {type: [String, Number], default: 0},
        limit:      {type: [String, Number], default: 10},
        count:      {type: [String, Number], default: 0},
        color:      {type: [String], default: 'sysbar'}  
    }
}
</script>