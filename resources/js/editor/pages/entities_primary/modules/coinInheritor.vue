<template>
<div>

    <!-- Button -->
    <v-tooltip bottom>
        <template v-slot:activator="{ on, attrs }">
            <v-btn
                v-bind="attrs"
                v-on="on"
                icon
                :disabled="inherited === null"
                class="ml-1"
                @click="dialog = true"
            >
                <v-icon v-text="on ? ('sync'+(!inherited ? '' : '_disabled')) : ('sync'+(inherited ? '' : '_disabled'))"></v-icon>
            </v-btn>
        </template>
        <span 
            v-text="inherited ? 
            'Click to deactivate coin-type-synchronisation (inheritance) for ' + label + '.' :
            'Click to activate coin-type-synchronisation (inheritance) for ' + label + '.'"
        ></span>
    </v-tooltip>

    <!-- Dialog -->
    <v-dialog v-model="dialog" persistent scrollable max-width="600px">
        <v-card tile>

            <v-system-bar color="sysbar" class="pt-5 pb-4 pl-5">
                <v-icon class="mr-2" v-text="'sync'+(inherited ? '_disabled' : '')"></v-icon>
                <span v-text="(inherited ? 'Dea' : 'A')+'ctivating coin-type-synchronisation'"></span>
                <v-spacer></v-spacer>
                <v-icon @click="dialog = false">close</v-icon>
            </v-system-bar>

            <v-card-text class="mt-5" style="min-height: 200px">
                <div v-if="inherited === 1">
                    Deactivating coin-type-synchronisation (inheritance) will allow you to set an individual value for
                    <b v-text="label"></b>.
                    But any update on the inheriting Type <b v-text="'cn type ' + data.id"></b> will be ignored.                    
                </div>
                <div v-else>
                    Activating coin-type-synchronisation (inheritance) will replace the individual value for
                    <b v-text="label"></b> with the value of Type <b v-text="'cn type ' + data.id"></b>:
                    <div class="pa-4" v-html="showData()"></div>
                    Any update on Type <b v-text="'cn type ' + data.id"></b> will automatically update the coin value. 
                </div>
                <div class="mt-4">
                    Would you like to proceed?
                </div>
            </v-card-text>

            <v-card-actions class="mt-n5">
                <v-spacer></v-spacer>
                <v-btn text class="mr-5" @click="dialog = false">
                    <v-icon>clear</v-icon>
                </v-btn>
                <v-btn text @click="toggle()">
                    <v-icon>done</v-icon>
                </v-btn>
                <v-spacer></v-spacer>
            </v-card-actions>
        </v-card>
    </v-dialog>

</div>
</template>


<script>

export default {

    data () {
        return {
            dialog: false
        }
    },

    props: {
        attribute:  { type: String, required: true },
        status:     { type: Object, default: {} },
        data:       { type: Object, default: {} }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        inherited () {
            if (!data.id) {
                return null
            }
            else {
                return this.status[this.attribute] === 1 ? true : false
            }
        },

        section () {
            if (['o_', 'r_'].includes(this.attribute.slice(0, 2))) { 
                return this.attribute.slice(0, 2) === 'o_' ? 'obverse' : 'reverse'
            }
            else {
                return null
            }
        },

        key () {
            if (['o_', 'r_'].includes(this.attribute.slice(0, 2))) { 
                return this.attribute.slice(2)
            }
            else {
                return this.attribute
            }

        },

        label () {
            return (this.section ? (this.labels[this.section] + ' ') : '') + this.labels[this.key]
        }
    },

    methods: {
        toggle () {
            this.$emit('toggle', (this.inherited ? 0 : 1))
            this.dialog = false
        },

        showData () {
            return this.$handlers.show_item_data(this.l, 'types', this.data, this.key, this.section)
        }
    }
}

</script>