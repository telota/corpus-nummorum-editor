<template>
<div><!-- Toolbar -->
    <component-toolbar>
        <div class="title text-uppercase pl-3 pr-3" v-text="'CN Editor Backend Log'" />
        <v-spacer />
        <div class="pr-3" style="width: 400px; max-width: 75%;">
            <v-text-field
                dense clearable
                v-model="search.cn_editor"
                prepend-icon="search"
                class="mt-2"
                append-outer-icon="sync"
                @click:append-outer="getItems()"
            />
        </div>
    </component-toolbar>

    <!-- Content -->
    <component-content style="overflow-y: hidden">
        <div class="sheet_bg">
            <!--<div style="height: 50px;">
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
            </div>-->

            <v-virtual-scroll
                :items="filterItems(items.cn_editor, search.cn_editor)"
                :item-height="75"
                style="overflow-y: scroll; height: calc(100vh - 90px);"
                :dis-bench="3"
            >
                <template v-slot:default="{ item }">
                    <div style="height: 75px; overflow: hidden; cursor: pointer" @click="showDialog(item)">
                        <div class="caption pa-3">
                            <div class="font-weight-bold mb-1" v-text="item.slice(1, 20)" />
                            <div v-text="item.slice(22)" />
                        </div>
                    </div>
                </template>
            </v-virtual-scroll>
        </div>
    </component-content>

    <small-dialog
        :show="dialog.active"
        width="50%"
        icon="note"
        text="Log Record"
        v-on:close="dialog.active = false"
    >
        <div class="font-weight-bold mb-1 body-2" v-text="dialog.data.slice(1, 20)" />
        <div style="height: 50vh; overflow-y: auto;">
            <div style="white-space: pre-line" v-text="printRecord(dialog.data.slice(22))" />
        </div>
    </small-dialog>

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
        this.$root.setTitle('Backend Logs')
        this.getItems()
    },

    methods:  {
        async getItems () {
            this.loading = this.$root.loading = true

            await axios.get('/dbi/logs')
                .then((response) => {
                    console.log(response?.data?.contents)
                    this.items.cn_editor = response?.data?.contents?.cn_editor ?? []
                })
                .catch((error) => {
                    console.error(error)
                })

            this.loading = this.$root.loading = false
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
        },

        printRecord (data) {
            return data.replaceAll('SQLSTATE', '\n\nSQLSTATE').replaceAll(';', ';\n')
        }
    }
}

</script>
