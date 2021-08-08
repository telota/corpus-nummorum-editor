<template>
<div :key="entity + id + '_' + refresh">
    <!-- Toolbar -->
    <div class="header_bg" :class="'component-toolbar' + (dialog ? ' component-toolbar-dialog' : '')">
        <div class="d-flex align-center" style="width: 100%; height: 50px; z-index:202">

            <!-- Name -->
            <div
                class="ml-3 headline"
                :class="status === 3 ? ' text-decoration-line-through' : ''"
                v-html="name.replaceAll(' ', '&nbsp;')"
            />
            <a v-if="status === 1" :href="'https://www.corpus-nummorum.eu/' + entity + '/' + id" target="_blank">
                <div class="ml-2 headline" v-text="'*'" />
            </a>

            <v-spacer />

            <!-- History -->
            <adv-btn
                icon="history"
                :dis-tooltip="'Sorted by'"
                color-hover="header_hover"
                :disabled="!cache[0]"
                v-on:click="showHistory = !showHistory"
            />
            <v-expand-transition>
                <v-card
                    v-if="showHistory"
                    tile class="header_bg"
                    style="position: absolute; top: 50px; right: 253px; width: 150px; height: 250px; overflow-y: scroll"
                >
                    <template v-if="cache[0]">
                        <v-hover v-slot="{ hover }" v-for="(c, i) in cache" :key="i">
                            <div
                                v-if="c.value.entity === entity && c.value.id == id"
                                class="pa-3 grey--text"
                                style="cursor: default"
                                v-text="c.text"
                            />
                            <div
                                v-else
                                class="pa-3"
                                :class="hover ? 'header_hover' : ''"
                                style="cursor: pointer;"
                                v-text="c.text"
                                @click="restoreHistory(c.value)"
                            />
                        </v-hover>
                    </template>
                    <div v-else class="grey--text pa-3" v-text="'No history, yet'" />
                </v-card>
            </v-expand-transition>

            <div :class="divider" />

            <!-- Actions -->
            <a :href="'/editor#/' + entity + '/search'">
                <adv-btn
                    icon="search"
                    tooltip="Go to search"
                    color-hover="header_hover"
                />
            </a>
            <a :href="'/editor#/' + entity + '/edit'">
                <adv-btn
                    icon="add"
                    tooltip="Create new Object"
                    color-hover="header_hover"
                />
            </a>

            <div :class="divider" />

            <a :href="'/editor#/types/copy/' + entity + '/' + id">
                <adv-btn
                    icon="add_circle_outline"
                    :tooltip="'Copy ' + name + ' as new ' + $root.label('type')"
                    color-hover="header_hover"
                />
            </a>

            <a :href="'/editor#/coins/copy/' + entity + '/' + id">
                <adv-btn
                    icon="add_circle"
                    :tooltip="'Copy ' + name + ' as new ' + $root.label('coin')"
                    color-hover="header_hover"
                />
            </a>

            <div :class="divider" />

            <div>
                <a v-if="status === 1" :href="'https://www.corpus-nummorum.eu/' + entity + '/' + id" target="_blank">
                    <adv-btn
                        icon="public"
                        :tooltip="'Show ' + name + ' on public website'"
                        color-hover="header_hover"
                    />
                </a>

                <adv-btn
                    v-else
                    icon="publish"
                    color-hover="header_hover"
                    color-text="blue_sec"
                    :tooltip="'publish ' + name"
                    :disabled="$root.user.level < 18 || (status !== 0 || status !== 2)"
                    @click="publish()"
                />
            </div>

            <adv-btn
                icon="delete"
                color-hover="header_hover"
                :tooltip="'delete ' + name"
                :disabled="$root.user.level < 12 || status === 1 || status === 3"
                @click="erase()"
            />

            <a :href="'/editor#/' + entity + '/edit/' + id">
                <adv-btn
                    icon="edit"
                    color-hover="header_hover"
                    :tooltip="'edit ' + name"
                />
            </a>
        </div>
    </div>

    <div :class="'pa-5 sheet_bg component-content' + (dialog ? ' component-content-dialog-small' : '')">
        <details-content
            :key="entity + id"
            :entity="entity"
            :id="id"
            v-on:public="(emit) => { this.status = emit }"
            v-on:cache="(emit) => { this.$store.commit('pushToCache', { key: 'details', value: emit })}"
        />
    </div>

</div>
</template>



<script>
import detailsContent from './content.vue'

export default {
    components: {
        detailsContent
    },

    data () {
        return {
            divider:        'header_hover fill-height width-1px',
            showHistory:    false,
            status:         0,
            refresh:        0
        }
    },

    props: {
        dialog: { type: Boolean, default: false },
        entity: { type: String, required: true },
        id:     { type: [Number, String], required: true }
    },

    computed: {
        name () {
            return ['cn', this.entity.slice(0, -1), this.id].join(' ')
        },

        cache () {
            const cache = [ ...this.$store.state.cache.details ]

            if (!cache[0]) return []
            else {
                if (cache[0].entity === this.entity && cache[0].id == this.id) {
                    if (cache[1]) cache.shift()
                    else return []
                }
                return cache.map((item) => {
                    return {
                        value: item,
                        text: 'cn ' + item.entity.slice(0, -1) + ' ' + item.id
                    }
                })
            }
        }
    },

    watch: {
        name () { this.$root.setTitle(this.name) }
    },

    mounted () {
        this.$root.setTitle(this.name)
    },

    methods: {
        restoreHistory (item) {
            if (this.dialog) this.$store.commit('setDetailsDialog', item)
            else this.$router.push('/' + item.entity + '/show/' + item.id)
        },

        async publish () {
            if (confirm('Publish ' + this.name + '?')) {
                const response = await this.$root.DBI_INPUT_POST('publish', 'input', { entity: this.entity, items: [this.id], mode: 1 });
                if (response.success) this.$store.dispatch('showSnack', { color: 'success', message: response.success[this.$root.language] })
                else console.error(response)
                ++this.refresh
            }
        }
    }
}

</script>
