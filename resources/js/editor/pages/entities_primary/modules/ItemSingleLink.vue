<template>
<div>
    
    <!-- Header -->
    <div class="d-flex align-center mb-4">
        <subheader 
            :label="header"
            :loading="loading"
            style="width: 100%"
        ></subheader>
        <!-- New Inheritance -->
        <v-btn
            fab
            x-small
            depressed
            color="blue_prim"
            class="ml-3"
            @click="add()"
        >
            <v-icon>add</v-icon>
        </v-btn>
        <!-- Manage Inheritance -->
        <v-btn
            v-if="entity === 'coins'"
            fab
            x-small
            depressed
            color="blue_sec"
            class="ml-3"
            :disabled="!inheriting"
            @click="manage()"
        >
            <v-icon>build</v-icon>
        </v-btn>
        <!-- Unset Inheritance -->
        <v-btn
            fab
            x-small
            depressed
            color="red darken-4"
            class="ml-3"
            :disabled="entity === 'types' ? !item.image : !inheriting"
            @click="unlink()"
        >
            <v-icon>link_off</v-icon>
        </v-btn>
    </div>

    <!-- Content -->
    <div v-if="!dbi_item.id" v-text="'--'"></div>

    <indexcard
        v-else
        :key="computed_entity + item.id"
        :entity="computed_entity"
        :item="dbi_item"
    ></indexcard>

    <!-- Link Dialog -->
    <simpleSelectDialog
        :active="dialog.active"
        :width="500"
        :text="'New inheriting Type'"
        icon="sync"
        v-on:close="dialog = { active: false, id: null }"
    >
        <template v-slot:content>
            <div class="mb-8">
                <div class="mb-1" v-text="'Please provide a valid cn type ID.'"></div>
                <div class="mb-5" v-text="'You can also click on the sync icon of a linked type to set this type as inheriting for cn coin ' + item.id + '.'"></div>
                <v-text-field dense outlined filled clearable
                    v-model="dialog.id"
                    :label="'cn type ID'"
                    prepend-icon="blur_circular"
                    :rules="[$handlers.rules.id]"
                    counter=255
                ></v-text-field>
            </div>
            <div class="ml-n6" style="position: absolute; width: 100%; bottom: 0">
                <div class="pa-2 d-flex align-center">
                    <v-spacer></v-spacer>
                    <v-btn 
                        fab 
                        x-small 
                        color="grey_sec" 
                        class="mr-3" 
                        @click="dialog = { active: false, id: null }"
                    >
                        <v-icon v-text="'clear'"></v-icon>
                    </v-btn>
                    <v-btn 
                        fab 
                        small 
                        color="blue_prim" 
                        class="ml-3"
                        :disabled="!dialog.id"
                        @click="$emit('inheritanceNew', dialog.id); dialog = { active: false, id: null }"
                    >
                        <v-icon v-text="'done'"></v-icon>
                    </v-btn>
                    <v-spacer></v-spacer>
                </div>
            </div>
        </template>
    </simpleSelectDialog>

</div>
</template>



<script>
import indexcard    from './searchLayoutIndexcard.vue'

export default {
    components: {
        indexcard
    },

    data () {
        return {
            loading: false,
            dbi_item: {},
            dialog: {
                active: false,
                id: null
            }
        }        
    },

    props: {
        entity:         { type: String, required: true},
        item:           { type: Object }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        computed_entity () {
            return this.entity === 'coins' ? 'types' : 'coins'
        },

        header () {
            return this.$root.label(this.entity === 'coins' ? 'type_inheriting' : 'representing_image')
        },

        inheriting () {
            return this.item?.inherited?.id_type ? true : false
        }
    },

    created () {
        if (this.item?.inherited?.id_type || this.item?.image) {
            this.GetItem()
        }
    },

    methods: {
        async GetItem () {
            this.loading = true;

            const params = {
                sort_by: 'id.ASC',
                offset: 0,
                limit:  1
            }

            const search = this.computed_entity === 'types' ? { id: this.item.inherited.id_type } : { img_id: this.item.image }
            
            const dbi = await this.$root.DBI_SELECT_POST(this.computed_entity, params, search)
            
            if (dbi?.contents?.[0]) {
                this.dbi_item = dbi.contents[0]
            }
            
            this.loading = false;
        },

        add () {
            if (this.entity === 'types') {
                alert('To update the representing image of cn type ' + this.item.id + ', please click on the camera icon of a linked coin!')
            }
            else {
                this.dialog = { active: true, id: null }
            }
        },

        unlink () {
            if (this.entity === 'types') {
                if (confirm('Are you sure you want to unset the representing image of cn type ' + this.item.id + '?')) {
                    this.$emit('deleteImage', true)
                }
            }
            else {
                this.$emit('inheritanceOff')
            }
        },

        manage () {
            this.$emit('inheritanceManage')
        }

        /*linking () {
            if (this.item?.inherited?.id_type) {
                this.$emit('inheritance', 'unlink')
            }
            else {
                alert('Click on a linked type and select it as inheriting.')
            }
        }*/
    }
}

</script>