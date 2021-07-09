<template>
<div>
    <v-row>
        <v-col
            v-for="(list, name) in items"
            :key="name"
            cols=12
            sm=6
        >
            <v-card :height="$root.position.height - 35">
                <div
                    class="pa-3 d-flex align-center justify-space-between"
                    style="height: 49px;"
                >
                    <div class="title text-uppercase" v-text="name.replaceAll('_', ' ')" />
                    <v-text-field
                        dense clearable
                        v-model="search[name]"
                        prepend-icon="search"
                        class="mt-2"
                        style="width: 200px; max-width: 45%;"
                        append-outer-icon="sync"
                        @click:append-outer="getItems()"
                    />
                </div>
                <v-progress-linear :indeterminate="loading" :height="1" />

                <v-virtual-scroll
                    :items="filterItems(list, search[name])"
                    :item-height="100"
                    style="width: 100%"
                    :height="$root.position.height - 85"
                    :dis-bench="3"
                >
                    <template v-slot:default="{ item }">
                        <div style="height: 100px; overflow: hidden; cursor: pointer" @click="showDialog(item)">
                            <div class="caption pa-3">
                                <div class="font-weight-bold mb-1" v-text="item.slice(1, 20)" />
                                <div v-text="item.slice(22)" />
                            </div>
                        </div>
                    </template>
                </v-virtual-scroll>
            </v-card>
        </v-col>
    </v-row>

    <v-dialog tile v-model="dialog.active" width="50%" scrollable>
        <v-card tile>
            <v-card-text class="pt-4">
                <div class="font-weight-bold mb-1 body-2" v-text="dialog.data.slice(1, 20)" />
                <div v-text="dialog.data.slice(22)" />
            </v-card-text>
        </v-card>
    </v-dialog>

</div>
</template>



<script>

export default {

    data () {
        return {
            loading:    false,
            items:      {
                cn_editor: [],
                cn_website: []
            },
            search:      {
                cn_editor: null,
                cn_website: null
            },
            dialog: {
                active: false,
                data: ''
            }
        }
    },

    computed: {
    },

    created () {
        this.getItems()
    },

    methods:  {
        async getItems () {
            this.loading = true

            await axios.get('/dbi/logs')
                .then((response) => {
                    this.items = response?.data?.contents
                })
                .catch((error) => {
                    console.error(error)
                })

            this.loading = false
        },

        filterItems (list, search) {
            if (!search) return list

            return list.filter((item) => item.includes(search))
        },

        showDialog (data) {
            this.dialog = {
                active: true,
                data
            }
        }
    }
}

</script>
