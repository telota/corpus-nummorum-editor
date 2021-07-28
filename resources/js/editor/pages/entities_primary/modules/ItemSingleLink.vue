<template>
<div>

    <!-- Header -->
    <div class="d-flex align-center mb-4">
        <subheader
            :label="header"
            :loading="loading"
            style="width: 100%"
        />

        <!-- New Inheritance -->
        <v-btn
            fab
            x-small
            depressed
            dark
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
            :dark="inheriting"
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
            :dark="!(entity === 'types' ? !item.image : !inheriting)"
            :disabled="entity === 'types' ? !item.image : !inheriting"
            @click="unlink()"
        >
            <v-icon>link_off</v-icon>
        </v-btn>
    </div>

    <!-- Content -->
    <div v-if="!Item.id" v-text="'--'" />

    <indexcard
        v-else
        :key="oppositeEntity + item.id"
        :entity="oppositeEntity"
        :item="Item"
    />

    <!-- Link Dialog -->
    <small-dialog
        :show="dialog.active"
        :text="'New inheriting Type'"
        icon="sync"
        v-on:close="dialog = { active: false, id: null }"
    >
        <div class="mb-5">
            <div class="mb-1" v-text="'Please provide a valid cn type ID.'" />
            <div class="mb-5" v-text="'You can also click on the sync icon of a linked type to set this type as inheriting for cn coin ' + item.id + '.'" />
            <v-text-field dense outlined filled clearable
                v-model="dialog.id"
                :label="'cn type ID'"
                prepend-icon="blur_circular"
                :rules="[$handlers.rules.id]"
                counter=255
            />
        </div>

        <div class="pa-2 d-flex align-center justify-center">
            <v-btn
                fab
                x-small
                dark
                class="mr-3 grey darken-2"
                @click="dialog = { active: false, id: null }"
            >
                <v-icon v-text="'clear'" />
            </v-btn>

            <v-btn
                fab
                small
                :dark="dialog.id"
                color="blue_prim"
                class="ml-3"
                :disabled="!dialog.id"
                @click="$emit('inheritanceNew', dialog.id); dialog = { active: false, id: null }"
            >
                <v-icon v-text="'done'" />
            </v-btn>
        </div>
    </small-dialog>

</div>
</template>



<script>
import indexcard    from './../modules/layoutIndexcard.vue'

export default {
    components: {
        indexcard
    },

    data () {
        return {
            loading:    false,
            Item:       {},
            dialog:     {
                active: false,
                id: null
            }
        }
    },

    props: {
        entity: { type: String, required: true},
        item:   { type: Object, default: () => {} }
    },

    computed: {
        oppositeEntity () {
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
        if (this.item?.inherited?.id_type || this.item?.image) this.GetItem()
    },

    methods: {
        async GetItem () {
            this.loading = true;

            const params = {
                sort_by: 'id.ASC',
                offset: 0,
                limit:  1
            }

            const search = this.oppositeEntity === 'types' ? { id: this.item.inherited?.id_type } : { img_id: this.item.image }

            const dbi = await this.$root.DBI_SELECT_POST(this.oppositeEntity, params, search)

            if (dbi?.contents?.[0]?.id) this.Item = dbi.contents[0]
            else this.Item = {}

            this.loading = false;
        },

        add () {
            if (this.entity === 'types') alert('To update the representing image of cn type ' + this.item.id + ', please click on the camera icon of a linked coin!')
            else this.dialog = { active: true, id: null }
        },

        unlink () {
            if (this.entity === 'types') {
                if (confirm('Are you sure you want to unset the representing image of cn type ' + this.item.id + '?')) this.$emit('deleteImage', true)
            }
            else this.$emit('inheritanceOff')
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
