<template>
<div>
    <!-- Header -->
    <v-card tile class="sysbar mb-2" style="width: 100%;" :loading="loading">
        <div 
            class="pa-3 d-flex justify-center title" 
            v-text="$root.label('welcome' + ($root.user.last_login ? '_back' : '')) + ', ' + $root.user.fullname + '!'"
        ></div>
        <v-row no-gutters>
            <v-col
                v-for="(record, i) in tabs"
                :key="record"
                cols=12 
                md=4
            >
                 <v-hover>
                    <template v-slot:default="{ hover }" >
                        <div                            
                            class="pa-2 text-center text-uppercase font-weight-bold caption"
                            :class="i === tab ? 'blue_prim' : (hover ? 'blue_sec' : 'grey_sec')"
                            v-text="$root.label(record)"
                            :style="i === tab ? '' : 'cursor: pointer'"
                            @click="() => { if (i != tab) { tab = i }}"
                        ></div>
                    </template>
                </v-hover>
            </v-col>
        </v-row>
    </v-card>

    <div>
        <v-row>
            <v-col
                v-for="(item) in coins"
                :key="item.id"
                cols=12
                md=4
                lg=2
            >
                <tradingcard entity="coins" :item="item"></tradingcard>
            </v-col>
        </v-row>
    </div>

</div> 
</template>

<script>

import tradingcard  from './../pages/entities_primary/modules/searchLayoutTradingcard.vue'

export default {
    components: {
        tradingcard
    },

    data () {
        return {
            loading:    false,
            tabs:       ['your_coins', 'new_coin', 'your_account'],
            tab:        0,

            coins:      []
        }
    },

    computed: {
        l () { return this.$root.language },
    },

    created () {
        this.getCoins()
    },

    methods: {
        async getCoins (id) {
            this.loading = true
            const self = this
            let dbi = {}
            
            await axios.get('dbi/coins' + (id ? ('/' + id) : '') + '?id_creator=' + this.$root.user.id)
                .then((response) => { dbi = response.data })
                .catch((error) => { self.AXIOS_ERROR_HANDLER(error) })

            if (dbi.contents?.[0]?.id) {
                this.coins = dbi.contents
            }
            this.loading = false
        },
    }
}
</script>