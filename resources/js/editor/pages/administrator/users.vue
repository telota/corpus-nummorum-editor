<template>
<div>
    <!-- Toolbar -->
    <component-toolbar>
        <v-row class="pa-0 ma-0">
            <v-col cols=6 md=3>
                <div class="headline" v-text="'Userverwaltung'"></div>
            </v-col>
            <v-col cols=6 md=3>

            </v-col>
            <v-col cols=12 md=6>
                <div class="d-flex justify-end">
                    <v-btn
                        tile
                        color="blue_prim"
                        v-html="'CN Team'"
                        :disabled="tab === 'team'"
                        @click="tab = 'team'"
                    ></v-btn>
                    <v-btn
                        tile
                        color="blue_prim"
                        v-html="'Community'"
                        :disabled="tab === 'community'"
                        class="ml-4"
                        @click="tab = 'community'"
                    ></v-btn>
                    <v-btn
                        tile
                        :color="items.new.length > 0 ? 'blue_prim' : null"
                        :disabled="tab === 'new' || items.new.length < 1"
                        class="ml-4"
                        v-html="items.new.length < 1 ? 'Keine neuen Registrierungen' : (items.new.length + '&nbsp;neue Registrierung' + (items.new.length > 1 ? 'en' : ''))"
                        @click="tab = 'new'"
                    ></v-btn>
                </div>
            </v-col>
        </v-row>
    </component-toolbar>

    <!-- Content -->
    <component-content>
        <sheet-template>
            <table>
                <tr>
                    <td v-for="(header, h) in headers" :key="'header' + h">
                        <div class="font-weight-bold mb-1" v-text="header.text"></div>
                    </td>
                    <td colspan=3></td>
                </tr>
                <tr v-for="(item, i) in items[tab]" :key="'tr' + i">
                    <td
                        v-for="(header, h) in headers"
                        :key="'tr' + i + 'td' + h"
                        class="pt-1"
                        :class="item.level === 0 ? 'grey--text' : ''"
                    >
                        <div
                            v-if="header.value === 'email'"
                            class="mt-2"
                            :title="item.level === 0 ? 'Email wird gespeichert, um erneute Registrierung mit dieser Adresse zu unterbinden.' : 'Emailadressen sind unique.'"
                            v-html="item[header.value] + '&nbsp;' + $handlers.format.email_link(item[header.value])"
                        ></div>
                        <div
                            v-else
                            class="mt-2"
                            v-text="item[header.value]"
                        ></div>
                    </td>
                    <!-- Rank -->
                    <td class="pt-1" style="width: 1%" align="right">
                        <v-menu offset-y transition="scale-transition">
                            <template v-slot:activator="{ on }">
                                <v-btn
                                    text
                                    depressed
                                    v-on="on"
                                    v-html="showRank(item.level)"
                                    :class="item.level === 0 ? 'red--text' : (item.level > 30 ? 'blue_prim--text' : '')"
                                    :disabled="item.level === 41 || item.level === 0"
                                ></v-btn>
                            </template>
                            <v-list>
                                <template v-for="(rank, r) in ranks.filter(v => v.hide === false)">
                                    <div
                                        v-if="rank.value === null"
                                        :key="'rank' + r"
                                        class="font-weight-bold mt-1"
                                    >
                                        <div class="text-center blue_sec--text" v-text="rank.text"></div>
                                        <v-divider class="mt-1"></v-divider>
                                    </div>
                                    <v-list-item
                                        v-else
                                        :key="'rank' + r"
                                        @click="items[tab][i].level = rank.value"
                                    >
                                        <v-list-item-title :class="rank.value === item.level ? 'font-weight-black' : ''">
                                            <div v-text="rank.text"></div>
                                        </v-list-item-title>
                                    </v-list-item>
                                </template>
                            </v-list>
                        </v-menu>
                    </td>
                    <!-- Options -->
                    <td class="pt-1 pb-1 pl-1" style="width: 1%">
                        <v-btn icon @click="saveItem(item)" :disabled="item.level === 41 || item.level === 0">
                            <v-icon v-text="'done'"></v-icon>
                        </v-btn>
                    </td>
                    <td class="pt-1 pb-1 pl-8" style="width: 1%">
                        <v-btn icon @click="deleteItem(item)" :disabled="item.level === 41 || item.level === 0">
                            <v-icon v-text="'delete'"></v-icon>
                        </v-btn>
                    </td>
                </tr>
            </table>
        </sheet-template>
    </component-content>

</div>
</template>



<script>
import sheetTemplate from '../../templates/sheetTemplate.vue'

export default {
  components: { sheetTemplate },
    data () {
        return {
            entity:     'users',
            search:     '',
            tab:        'team',
            loading:    false,

            items:      {
                team: [],
                community: [],
                new: []
            },
            headers: [
                { value: 'lastname',     text: 'Nachname' },
                { value: 'firstname',    text: 'Vorname' },
                { value: 'email',        text: 'Email' },
                { value: 'name',         text: 'Username' },
                { value: 'created_at',   text: 'Registrierung' },
            ],
            ranks: [
                { value: 0,     text: 'gelöscht',           hide: true },
                { value: null,  text: 'Community',          hide: false },
		        { value: 1,     text: '(1) Registered',     hide: true },
                { value: 2,     text: '(2) Contributor',    hide: false },
                { value: null,  text: 'Team',               hide: false },
                { value: 10,    text: '(10) Reader',        hide: false },
                { value: 11,    text: '(11) Editor (own)',  hide: false },
                { value: 12,    text: '(12) Editor (all)',  hide: false },
                { value: 19,    text: '(19) Publisher',     hide: false },
                { value: 21,    text: '(21) PR Author',     hide: false },
                { value: 22,    text: '(22) PR Supervisor', hide: false },
                { value: 31,    text: '(31) Administrator', hide: false },
                { value: 41,    text: 'Superuser',          hide: true }
            ]
        }
    },

    computed: {},

    created () {
        if (this.$route?.params?.id === 'new') {
            this.tab = 'new'
            this.$router.push('/users')
        }
        this.getItems();
    },

    methods: {
        async getItems () {
            this.loading = true
            let dbi = {}

            dbi = await this.$root.DBI_SELECT_GET(this.entity, null)
            if (dbi?.contents?.[0]?.id) {
                const items_raw = dbi.contents

                this.items = {
                    community: items_raw.filter(item => item.level === 2),
                    team: items_raw.filter(item => item.level > 9),
                    new: items_raw.filter(item => item.level === 1)
                }
            }

            this.loading = false
        },

        showRank (level) {
            const text = this.ranks.find(item => item.value === level)
            return text.text ? text.text : level
        },

        async saveItem (item) {
            this.$root.loading = true

                const response = await this.$root.DBI_INPUT_POST(this.entity, 'input', item);

                if (response.success) {
                    this.$root.snackbar(response.success, 'success');
                    await this.getItems()
                }
                else {
                    console.log('Data Input Error: response was not ok');
                }

            this.$root.loading = false
        },

        async deleteItem (item) {
            if (confirm('Soll der User "' + item.name + '" gelöscht werden?')) {
                this.$root.loading = true

                    const response = await this.$root.DBI_INPUT_POST(this.entity, 'delete', item);

                    if (response.success) {
                        this.$root.snackbar(response.success, 'success');
                        await this.getItems()
                    }
                    else {
                        console.log('Data Input Error: response was not ok');
                    }

                this.$root.loading = false
            }
        }
    }
}

</script>

<style scoped>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    table tr {
        border-bottom: 1pt solid grey;
    }

    table tr:last-child {
        border: 0;
    }
</style>
