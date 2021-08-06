<template>
    <div>
        <generic-entity-template
            :entity="entity"
            :name="$root.label(component)"
            :attributes="attributes"
            :default-sort-by="'name.ASC'"
            small-tiles
            gallery="id_owner"
            :dialog="dialog"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit) }"
            v-on:close="$emit('close')"
            v-on:setFilter="(emit) => { this.attributes[emit.key].filter = emit.value }"
        >
            <!-- Filter ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:filters>
                <template v-for="(item, key) in attributes">
                    <div
                        v-if="item.filter !== undefined"
                        :key="key"
                    >
                        <v-select dense outlined filled clearable
                            v-if="['is_name_public', 'type'].includes(key)"
                            v-model="attributes[key].filter"
                            :items="key === 'type' ? owner_types : search_yesNo"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            :menu-props="{ offsetY: true }"
                        />
                        <InputForeignKey
                            v-else-if="key === 'country'"
                            entity="countries"
                            :label="attributes[key].text"
                            :icon="attributes[key].icon"
                            :selected="attributes[key].filter"
                            style="width: 100%"
                            v-on:select="(emit) => { attributes.country.filter = emit }"
                        />
                        <v-text-field dense outlined filled clearable
                            v-else
                            v-model="attributes[key].filter"
                            :label="item.text"
                            :prepend-icon="item.icon"
                        />
                    </div>
                </template>
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search-tile-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:search-tile-body="slot">
                <div class="body-2 mb-3">
                    <div class="font-weight-bold" v-html="attributes.name.content(slot.item)" />
                    <div v-if="slot.item.link" class="caption" v-html="attributes.link.content(slot.item)" />
                    <div v-if="slot.item.nomisma" class="caption" v-html="attributes.nomisma.content(slot.item)" />
                </div>
                <div class="body-2 mb-3">
                    <div v-html="attributes.type.content(slot.item)" />
                    <div v-html="attributes.country.content(slot.item) + ', ' + attributes.city.content(slot.item)" />
                </div>
                <div class="caption">
                    <div v-html="$root.label('public') + ': ' + attributes.is_name_public.content(slot.item)" />
                    <div v-html="$root.label('checked') + ': ' + attributes.is_checked.content(slot.item)" />
                </div>
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <!-- Name DE -->
                    <v-col cols=12>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.name"
                            :label="attributes.name.text"
                            :prepend-icon="attributes.name.icon"
                            hint="required"
                            counter=255
                        />
                    </v-col>

                    <!-- Link -->
                    <v-col cols=12 sm=6>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.link"
                            :label="attributes.link.text"
                            :prepend-icon="attributes.link.icon"
                            :rules="[$handlers.rules.link]"
                            counter=255
                            @click:prepend="$root.openInNewTab(slot.item.link)"
                        />
                    </v-col>

                    <!-- Nomisma -->
                    <v-col cols=12 sm=6>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.nomisma"
                            :label="attributes.nomisma.text"
                            :prepend-icon="attributes.nomisma.icon"
                            counter=255
                            @click:prepend="slot.item.nomisma ? $root.openInNewTab((slot.item.nomisma.slice(0, 4) != 'http' ? $handlers.constant.url.nomisma : '') + slot.item.nomisma) : null"
                        />
                    </v-col>

                     <!-- Type -->
                    <v-col cols=12 sm=6>
                        <v-select dense outlined filled clearable
                            v-model="slot.item.type"
                            :items="owner_types"
                            :label="attributes.type.text"
                            :prepend-icon="attributes.type.icon"
                        />
                    </v-col>

                    <!-- Country -->
                    <v-col cols=12 sm=6>
                        <InputForeignKey
                            entity="countries"
                            :label="attributes.country.text"
                            :icon="attributes.country.icon"
                            :selected="slot.item.country ? slot.item.country.toLowerCase() : null"
                            style="width: 100%"
                            v-on:select="(emit) => { slot.item.country = emit }"
                        />
                    </v-col>

                    <!-- City -->
                    <v-col cols=12 sm=6>
                        <v-text-field dense outlined filled clearable
                            v-model="slot.item.city"
                            :label="attributes.city.text"
                            :prepend-icon="attributes.city.icon"
                            counter=255
                        />
                    </v-col>

                    <!-- Public and Checked -->
                    <v-col
                        cols=6 sm=3
                        v-for="key in ['is_name_public', 'is_checked']"
                        :key="key"
                    >
                        <v-select dense outlined filled clearable
                            v-model="slot.item[key]"
                            :items="yesNo"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            :menu-props="{ offsetY: true }"
                        />
                    </v-col>
                </v-row>
            </template>

        </generic-entity-template>
    </div>
</template>



<script>

export default {
    data () {
        return {
            component:          'owner',
            entity:             'owners',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',
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

        // Dropdowns
        yesNo () {
            return this.$store.state.lists.dropdowns.yesNo.map((item) => { return {
                value: item.value,
                text: this.$root.label(item.text)
            }})
        },
        search_yesNo () {
            return this.$store.state.lists.dropdowns.yesNo.map((item) => { return {
                value: this.dialog ? item.value : (item.value + ''),
                text: this.$root.label(item.text)
            }})
        },
        owner_types (){
            const list = [
                { value: 'Akademie',    text: this.language === 'de' ? 'Akademie' : 'Academy' },
                { value: 'Bibliothek',  text: this.language === 'de' ? 'Bibliothek' : 'Library' },
                { value: 'Gesellschaft',text: this.language === 'de' ? 'Gesellschaft' : 'Society' },
                { value: 'Institution', text: this.language === 'de' ? 'Institution' : 'Institution' },
                { value: 'Museum',      text: this.language === 'de' ? 'Museum' : 'Museum' },
                { value: 'Privat',      text: this.language === 'de' ? 'Privat' : 'Private' },
                { value: 'Unbekannt',   text: this.language === 'de' ? 'Unbekannt' : 'Unknown' },
                { value: 'Universität', text: this.language === 'de' ? 'Universität' : 'University' }
            ]

            return list.sort((a, b) => { return (a, b) => {
                if (a.text < b.text) return -1
                if (a.text > b.text) return 1
                return 0;
            }})
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
                    content: (item) => item?.id ?? '--'
                },
                name: {
                    default: null,
                    text: this.$root.label('name'),
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.name ?? '--'
                },
                type: {
                    default: null,
                    text: this.$root.label('kind'),
                    icon: 'category',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item.type ? (this.owner_types.find((row) => row.value == item.type)?.text ?? '--') : '--'
                },
                country: {
                    default: null,
                    text: this.$root.label('country'),
                    icon: 'public',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.country ? item.country.toUpperCase() : '--'
                },
                city: {
                    default: null,
                    text: this.$root.label('city'),
                    icon: 'place',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: false,
                    content: (item) => item?.city ?? '--'
                },
                link: {
                    default: null,
                    text: 'Website',
                    icon: 'link',
                    header: true,
                    clone: false,
                    content: (item) => item.link ? (item.link.split('//').pop().split('ww.').pop().split('/')[0] + this.$handlers.format.resource_link(item.link)) : '--'
                },
                nomisma: {
                    default: null,
                    text: 'Nomisma ID',
                    icon: 'monetization_on',
                    header: true,
                    filter: null,
                    clone: false,
                    content: (item) => this.$handlers.format.nomisma_link(item.nomisma)
                },
                is_name_public: {
                    default: 0,
                    text: this.$root.label('public'),
                    icon: 'how_to_reg',
                    header: true,
                    sortable: true,
                    filter: null,
                    content: (item) => this.yesNo.find((row) => row.value == item.is_name_public)?.text ?? '--'
                },
                is_checked: {
                    default: 0,
                    text: this.$root.label('checked'),
                    icon: 'how_to_reg',
                    content: (item) => this.yesNo.find((row) => row.value == item.is_checked)?.text ?? '--'
                },
                id_user: {
                    default: null,
                    text: this.$root.label('comment'),
                    icon: 'account_circle',
                    content: (item) => item?.id_user ?? '--'
                }
            }
        }
    }
}

</script>
