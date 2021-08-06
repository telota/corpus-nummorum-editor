<template>
    <div>
        <generic-entity-template
            :key="refresh"
            :entity="entity"
            :name="$root.label(component)"
            :headline="headline"
            :attributes="attributes"
            default-sort-by="author.ASC"
            small-tiles
            no-edit
            gallery="id_reference"
            :dialog="dialog"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit) }"
            v-on:close="$emit('close')"
            v-on:setFilter="(emit) => { this.attributes[emit.key].filter = emit.value }"
        >
            <template v-slot:right>
                <div
                    v-if="loading"
                    style="width: 200px; height: 50px; cursor: default"
                    class="d-flex align-center justify-center font-weight-bold text-uppercase grey--text"
                >
                    <div v-text="'Updating ...'" />
                </div>

                <v-hover v-else v-slot="{ hover }">
                    <div
                        class="d-flex align-center justify-center headline font-weight-bold light-blue--text text--darken-2"
                        :class="hover ? 'header_hover' : 'header_bg'"
                        style="width: 200px; height: 50px; cursor: pointer;'"
                        @click="fetchZotero()"
                    >
                        <v-icon v-text="'sync_problem'" class="mr-2" color="light-blue darken-2" />
                        <div v-text="'Zoteroupdate'" />
                    </div>
                </v-hover>
            </template>

            <!-- Filter ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:filters>
                <template v-for="(item, i) in attributes">
                    <div
                        v-if="item.filter !== undefined"
                        :key="'filter' + i"
                    >
                        <v-text-field dense outlined filled clearable
                            v-model="attributes[i].filter"
                            :label="item.text"
                            :prepend-icon="item.icon"
                        />
                    </div>
                </template>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search-tile-header="slot">
                <span v-html="attributes.id.content(slot.item)" />
            </template>

            <template v-slot:search-tile-body="slot">
                <div class="body-2">
                    <div class="font-weight-bold mb-1" v-html="attributes.author.content(slot.item) + ' ' + attributes.year.content(slot.item)" />
                    <div class="mb-1" v-html="attributes.title_short.content(slot.item)" />
                    <div class="mb-2" v-html="attributes.place.content(slot.item)" />
                    <div class="caption" v-html="attributes.status.content(slot.item) + ', updated: ' + attributes.updated_at.content(slot.item)" />
                </div>
            </template>
        </generic-entity-template>
    </div>
</template>


<script>

export default {
    data () {
        return {
            entity:     'bibliography',
            component:  'Reference',
            headline:   'Bibliography',
            loading:    false,
            refresh:    0,
            attributes: {}
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: null }
    },

    computed: {
        language () {
            return this.$root.language === 'de' ? 'de' : 'en'
        },

        log_parsed () {
            if (this.log) {
                const log = this.log.replaceAll('------------------------\n','').replaceAll('\t','&emsp;').trim().split('\n')
                return log.join('<br/>')
            }
            else {
                return '--'
            }
        }
    },

    created () {
        this.attributes = this.setAttributes()
    },

    methods: {
        setAttributes () {
            return {
                id: {
                    default: null,
                    text: 'ID',
                    icon: 'fingerprint',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => {
                        if (!item.link) return item?.id ?? '--'
                        return item.id + this.$handlers.format.resource_link(item.link)
                    }
                },
                author: {
                    default: null,
                    text: this.$root.label('author'),
                    icon: 'person',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => item?.author ?? '--'
                },
                year: {
                    default: null,
                    text: this.$root.label('year'),
                    icon: 'history',
                    sortable: true,
                    filter: null,
                    content: (item) => {
                        if (!item?.year) return '--'
                        if (!item?.year?.includes('-')) return item?.year ?? '--'
                        return this.$handlers.format.date(this.language, item?.year)
                    }
                },
                title_short: {
                    default: null,
                    text: this.$root.label('short_title'),
                    icon: 'short_text',
                    filter: null,
                    content: (item) => item?.title_short ?? '--'
                },
                title: {
                    default: null,
                    text: this.$root.label('title'),
                    icon: 'notes',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => item?.title_short ?? (item?.title ?? '--')
                },
                place: {
                    default: null,
                    text: this.$root.label('place'),
                    icon: 'place',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => item?.place ?? '--'
                },
                status: {
                    default: null,
                    text: this.$root.label('status'),
                    icon: 'public',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => item?.status ?? '--'
                },
                updated_at: {
                    default: null,
                    text: this.$root.label('update'),
                    icon: 'event',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => this.$handlers.format.date(this.language, item?.updated_at)
                },
            }
        },

        async fetchZotero () {
            if (this.$root.user.level > 11) {
                alert('Updating Zotero Titles might take a while. Please contact the IT team if any problems occure.')

                this.$root.loading = this.loading = true
                const self = this

                this.$store.dispatch('showSnack', { message: 'Updating Bibliography ... please wait!' })

                await axios.post('dbi/' + this.entity + '/input', {})
                    .then((response) => {
                        console.log(response?.data)
                        this.$store.dispatch('showSnack', { color: 'success', message: 'Bibliography was updated!' })
                        ++this.refresh
                    })
                    .catch((error) => {
                        self.$root.AXIOS_ERROR_HANDLER(error)
                    })

                this.$root.loading = this.loading =  false
            }
            else alert('You are not permitted to update Zotero References. Please contact an administrator.')
        }
    }
}

</script>
