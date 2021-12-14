<template>
<div>
    <component-toolbar>
        <div class="title pl-2" v-html="'Direct Publishing'" />
    </component-toolbar>

    <component-content style="overflow-y: hidden">
        <v-row class="pt-0 ma-0" no-gutters justify="center">

            <!-- Persons Index -->
            <v-col cols=6 md=5 lg=4 xl=3>
                <v-card
                    tile
                    class="index-tile sheet_bg pa-3"
                    style="margin: 16px 8px 16px 16px;"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <div>
                        <coins-types-selector
                            entity="types"
                            :selected="id"
                            no-confirm
                            @select="(emit) => { id = emit }"
                            @enter="getItems"
                        />
                        <v-select dense outlined filled
                            v-model="published"
                            :items="values"
                            prepend-icon="help_outline"
                            label="Publication State of related coins"
                            :menu-props="{ offsetY: true }"
                        />

                        <v-btn :disabled="!id" tile block :dark="id ? true : false" class="blue_prim" v-text="'Show related Coins'" @click="getItems" />
                        <v-row class="mt-2">
                            <v-col cols=6>
                                <v-btn :disabled="!Object.keys(selection)[0]" tile block :dark="Object.keys(selection)[0] ? true : false" class="error" v-text="'Unpublish'" @click="publish(0)" />
                            </v-col>
                            <v-col cols=6>
                                <v-btn :disabled="!Object.keys(selection)[0]" tile block :dark="Object.keys(selection)[0] ? true : false" class="blue_sec" v-text="'Publish'" @click="publish(1)" />
                            </v-col>
                        </v-row>
                    </div>
                </v-card>
            </v-col>

            <!-- Names -->
            <v-col cols=6 md=5 lg=4 xl=3>
                <v-card
                    tile
                    class="index-tile sheet_bg"
                    style="margin: 16px 16px 16px 8px;"
                    :style="'outline: 1px solid ' + ($vuetify.theme.dark ? 'black' : 'grey')"
                >
                    <div class="text-center font-bold body-1 pa-3">
                        <span v-if="noQueryYet" v-text="'No Type selected'" />
                        <span v-else-if="loading" v-text="'Please wait ...'" />
                        <span v-else>
                            <span v-text="items.length + ' Coins related to'" />
                            <span
                                class="font-weight-bold"
                                style="cursor: pointer"
                                v-text="'cn type ' + id"
                                @click="$store.commit('setDetailsDialog', { entity: 'types', id })"
                            />
                        </span>
                    </div>

                    <v-progress-linear :indeterminate="loading" height="1" />

                    <v-virtual-scroll
                        :items="items"
                        :item-height="60"
                        style="flex: 1 1 auto;"
                    >
                        <template v-slot:default="{ item, index }">
                            <div class="d-flex pa-1">
                                <v-checkbox
                                    v-model="selection[item.id]"
                                    class="ml-3 mr-3"
                                />
                                <div style="cursor: pointer; white-space: nowrap" @click="$store.commit('setDetailsDialog', { entity: 'coins', id: item.id })">
                                    <div class="font-weight-bold mt-2" v-text="'cn coin ' + item.id + (item.public ? ' *' : '')" />
                                    <div class="caption pt-1">
                                        inherited: <b v-text="item.design ? 'YES' : 'NO'" />,&ensp;
                                        has Design: <b :class="(item.design ? 'green' : 'red') + '--text'" v-text="item.design ? 'YES' : 'NO'" />,&ensp;
                                        Date: <b :class="(item.date ? 'green' : 'red') + '--text'" v-text="item.date ? item.date : 'NONE'" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </v-virtual-scroll>
                </v-card>
            </v-col>
        </v-row>

    </component-content>
</div>
</template>



<script>

export default {
    data () {
        return {
            noQueryYet: true,
            loading: false,
            id: null,
            published: null,
            values: [
                { value: null, text: 'all' },
                { value: 0, text: 'Not published' },
                { value: 2, text: 'Publishing requested' },
                { value: 1, text: 'Published' }
            ],
            items: [],
            selection: {}
        }
    },

    created () {
    },

    methods: {
        async getItems () {
            this.noQueryYet = false
            this.items = []
            this.selection = {}
            this.loading = true

            const dbi = await this.$root.DBI_SELECT_GET('publishadmin', this.id + (this.published === null ? '' : ('-' + this.published)))

            if (dbi?.error) alert(dbi?.error)
            else {
                dbi.forEach((item) => {
                    this.selection[item.id] = true
                    this.items.push(item)
                })
            }

            this.loading = false
        },

        async publish (state) {
            const ids = Object.keys(this.selection).filter((id) => this.selection[id])

            let confirmed = true
            if (state !== 1) confirmed = confirm('Are you sure you want to unpublish cn coin ' + ids.join(', ') + '?')

            if (confirmed) {
                this.loading = true

                const dbi = await this.$root.DBI_INPUT_POST('publishadmin', 'input', { ids, state })

                if (dbi.success) {
                    if (state === 1) {
                        if (ids.length < this.items.length) {
                            console.log('test')
                            this.items = this.items.filter((item) => !ids.includes(item.id + ''))
                            ids.forEach((id) => { delete this.selection[id] })
                        }
                        else {
                            this.items = []
                            this.selection = {}
                        }
                    }
                    else await this.getItems()
                }

                this.loading = false
            }
        }
    }
}

</script>

<style lang="scss" scoped>
    .index-tile {
        height: calc(100vh - 122px);
        display: flex;
        flex-flow: column;
    }
</style>
