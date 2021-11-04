<template>
    <small-dialog
        :show="show || error"
        :icon="error ? 'warning' : 'publish'"
        :text="'Publisher' + (error ? ': Warning!' : '')"
        no-padding
        @close="close"
    >
        <!-- Inheriting Error -->
        <template v-if="error">
            <!-- Group Mode -->
            <template v-if="group">
                <div class="pa-4 mb-2 text-center">
                    Coins inheriting from unpublished types cannot be published.<br/>
                    Click on IDs to check record details or arrow for direct publishing.
                </div>

                <v-divider />
                <div
                    class="pa-4"
                    style="height: 50vh; overflow-y: auto;"
                >
                    <v-row :key="refresh">
                        <v-col
                            :cols="6"
                            v-for="(coins, type) in errors"
                            :key="type"
                        >
                            <div class="d-flex align-center mt-n1">
                                <div
                                    class="font-weight-medium"
                                    style="cursor: pointer"
                                    v-html="'cn&nbsp;type&nbsp;' + type"
                                    @click="$store.commit('setDetailsDialog', { entity: 'types', id: type })"
                                />
                                <v-divider class="ml-2 mr-2" />
                                <v-btn
                                    icon
                                    x-small
                                >
                                    <v-icon
                                        x-small
                                        v-text="'publish'"
                                        @click="publishMissingTypeAndCoins(type, coins)"
                                    />
                                </v-btn>
                            </div>

                            <div class="mt-1 caption">
                                <span
                                    v-for="(coin) in coins"
                                    :key="type + '_' + coin"
                                    class="mr-2"
                                    style="cursor: pointer"
                                    v-html="'cn&nbsp;coin&nbsp;' + coin + ' '"
                                    @click="$store.commit('setDetailsDialog', { entity: 'coins', id: coin })"
                                />
                            </div>
                        </v-col>
                    </v-row>
                </div>
                <v-divider />
            </template>

            <!-- Single -->
            <template v-else>
                <div
                    v-for="(coins, type, t) in errors"
                    :key="t"
                    class="pa-4"
                >
                    <div
                        class="mb-2"
                        v-html="'cn&nbsp;coin&nbsp;' + coins[0] + ' cannot be published because the inheriting cn&nbsp;type&nbsp;' + type + ' is not published, yet.'"
                    />
                    <div>
                        Check
                        <b style="cursor: pointer"
                            v-html="'cn&nbsp;type&nbsp;' + type"
                            @click="$store.commit('setDetailsDialog', { entity: 'types', id: type })"
                        />
                        or
                        <b style="cursor: pointer"
                            v-html="'click'"
                            @click="publishMissingType(type)"
                        />
                        to publish the inheriting type without further checks.
                    </div>
                </div>
            </template>

            <!-- Actions -->
            <div class="pa-4 d-flex align-center justify-center">
                <v-btn
                    tile
                    dark
                    class="blue_prim mr-5"
                    v-text="'retry'"
                    @click="publish"
                />

                <v-btn
                    tile
                    v-text="'cancel'"
                    @click="close"
                />
            </div>
        </template>
    </small-dialog>
</template>

<script>
export default {
    data () {
        return {
            show: false,
            errors: {},
            refresh: 0,
            states: [
                'unpublished',
                'published',
                'publishing requested',
                'deleted'
            ]
        }
    },

    props: {
        entity: { type: String, default: null },
        mode:   { type: Number, default: 1 },
        group:  { type: Boolean, default: false },
        run:    { type: Number, default: 0 },
        items:  { type: Array, default: () => [] }
    },

    computed: {
        error () {
            return Object.keys(this.errors)?.[0] ? true : false
        },

        Items () {
            if (this.group) return this.items.filter(item => item.state === true)
            return this.items
        }
    },

    watch: {
        run () { this.publish() }
    },

    methods: {
        async publish () {
            if (this.Items[0]) {
                this.$emit('loading', true)
                const input = {
                    entity: this.entity,
                    items:  this.Items.map((item) => item.id),
                    mode:   this.mode
                }

                const response = await this.$root.DBI_INPUT_POST('publish', 'input', input)

                if (response.success) {
                    if (response.ok?.[0]) this.showSuccess(1, response.ok)

                    if (!response.not_ok) {
                        this.close()
                        this.$emit('refresh', true)
                    }
                    else {
                        const errors = {}
                        response.not_ok?.forEach((e) => {
                            if (!errors[e.type]) errors[e.type] = []
                            errors[e.type].push(e.coin)
                        })
                        this.errors = errors
                    }
                }
                this.$emit('loading', false)
            }
            else {
                this.showError('No items selected!')
                this.close()
            }
        },

        async publishMissingType (type) {
            this.$emit('loading', true)

            const response = await this.$root.DBI_INPUT_POST('publish', 'input', {
                entity: 'types',
                items:  [type],
                mode:   1
            })

            this.$emit('loading', false)

            if (response.ok?.[0] == type) {
                delete this.errors[type]
                ++this.refresh
                this.showSuccess(1, [type], 'types')
                return true
            }
            return false
        },

        async publishMissingTypeAndCoins (type, coins) {
            if (confirm('Publish cn type ' + type + ' and linked coins without further checks?')) {
                const success = await this.publishMissingType(type)

                if (success) {
                    this.$emit('loading', true)

                    await this.$root.DBI_INPUT_POST('publish', 'input', {
                        entity: 'coins',
                        items:  coins,
                        mode:   1
                    })

                    this.$emit('loading', false)

                    this.showSuccess(1, coins, 'coins')

                    if (!Object.keys(this.errors)?.[0]) this.close()
                }
            }
        },

        showError (message) {
            this.$store.dispatch('showSnack', { color: 'error', message })
        },

        showSuccess (mode, ids, entity = this.entity) {
            ids = ids.sort()

            if (ids.length > 2) ids = ids[0] + ' - ' + ids.pop()
            else ids = ids.join(' and ')

            const message = 'cn ' + entity.slice(0, -1) + ' ' + ids + ' ' + this.states[mode]
            this.$store.dispatch('showSnack', { color: 'success', message })
        },

        close () {
            this.errors = {}
            this.show = false
            this.$emit('refresh')
        }
    }
}
</script>
