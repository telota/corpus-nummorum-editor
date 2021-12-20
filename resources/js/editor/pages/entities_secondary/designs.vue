<template>
    <div>
        <generic-entity-template
            :key="language"
            sync
            :entity="entity"
            :name="$root.label(component)"
            :attributes="attributes"
            :default-sort-by="'name_' + language + '.ASC'"
            small-tiles
            default-layout="tiles"
            gallery="id_design"
            :dialog="dialog"
            :select="select"
            :selected="selected"
            v-on:select="(emit) => { $emit('select', emit) }"
            v-on:close="$emit('close')"
            v-on:setFilter="(emit) => { this.attributes[emit.key].filter = emit.value }"
        >
            <!-- Filter ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:filters>
                <v-text-field dense outlined filled clearable
                    v-model="attributes[key].filter"
                    v-for="(key) in ['id', 'name_de', 'name_en', 'name_bg']"
                    :key="key"
                    :label="attributes[key].text"
                    :prepend-icon="attributes[key].icon"
                />
                <v-select dense outlined filled clearable
                    v-model="attributes[key].filter"
                    v-for="(key) in ['role', 'side', 'border_dots']"
                    :key="key"
                    :items="key === 'side' ? search_sides : (key === 'role' ? search_roles : search_yesNo)"
                    :label="attributes[key].text"
                    :prepend-icon="attributes[key].icon"
                    :menu-props="{ offsetY: true }"
                    style="position: relative; z-index: 200"
                />
            </template>

            <!-- Content Cards ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:search-tile-header="slot">
                {{ 'ID&nbsp;' + slot.item.id }}
            </template>

            <template v-slot:search-tile-body="slot">
                <div
                    class="body-2 mb-3"
                    v-html="attributes.role.content(slot.item) + ', ' + attributes.side.content(slot.item)"
                />
                <!-- Image -->
                <adv-img
                    :key="slot.item.id"
                    :src="slot.item.image"
                    square
                    contain
                    class="mb-3"
                />
                <div
                    v-for="(key) in [(language === 'de' ? 'de' : 'en'), (language === 'de' ? 'en' : 'de'), 'bg']"
                    :key="key"
                    class="body-2 mb-3"
                    v-html="'(' + key.toUpperCase() + ')&ensp;' + attributes['name_' + key].content(slot.item)"
                />
            </template>

            <!-- Editor ---------------------------------------------------------------------------------------------------- -->
            <template v-slot:editor="slot">
                <v-row>
                    <v-col
                        v-for="key in ['name_de', 'name_en', 'name_bg']"
                        :key="key"
                        cols=12
                        md=6
                        xl=4
                    >
                        <v-textarea dense outlined filled clearable
                            no-resize
                            rows=3
                            v-model="slot.item[key]"
                            :label="attributes[key].text"
                            :prepend-icon="attributes[key].icon"
                            class="mb-3"
                            counter=21845
                        />
                    </v-col>
                </v-row>
                <v-row>
                    <v-col
                        v-for="key in ['role', 'side', 'border_dots']"
                        :key="key"
                        cols=12
                        sm=6
                        md=3
                        xl=2
                    >
                        <v-select dense outlined filled
                            v-model="slot.item[key]"
                            :items="key === 'role' ? roles : (key === 'side' ? sides : yesNo)"
                            :label="attributes[key].text"
                            :menu-props="{ offsetY: true }"
                            :prepend-icon="attributes[key].icon"
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
            component:          'design',
            entity:             'designs',
            attributes:         {},
            key_name_class:     'caption font-weight-thin text-uppercase mb-1',
        }
    },

    props: {
        dialog:     { type: Boolean, default: false },
        select:     { type: Boolean, default: false },
        selected:   { type: [Number, String], default: null },
        conditions: { type: Array, default: () => [] }
    },

    computed: {
        language () {
            return this.$root.language === 'de' ? 'de' : 'en'
        },
        filterSide () {
            if (!this.conditions?.[0]) return null

            let found = null
            this.conditions.forEach((el) => {
                if (el.side && (el.side === 'o' || el.side === 'r')) found = el.side
            })
            return found
        },

        // Dropdowns
        yesNo () {
            return this.$store.state.lists.dropdowns.yesNo.map((item) => { return {
                value: item.value,
                text: this.$root.label(item.text)
            }})
        },
        sides () {
            return this.$store.state.lists.dropdowns.sides.map((item) => { return {
                value: item.value,
                text: this.$root.label(item.text)
            }})
        },
        roles () {
            return this.$store.state.lists.dropdowns.typeCoin.map((item) => { return {
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
        search_sides () {
            const list = this.$store.state.lists.dropdowns.sides.map((item) => { return {
                value: this.dialog ? item.value : (item.value + ''),
                text: this.$root.label(item.text)
            }})

            list.push(
                { value: 'o', text: 'Obv. & Obv./Rev.' },
                { value: 'r', text: 'Rev. & Obv./Rev.' }
            )

            return list
        },
        search_roles () {
            return this.$store.state.lists.dropdowns.typeCoin.map((item) => { return {
                value: this.dialog ? item.value : (item.value + ''),
                text: this.$root.label(item.text)
            }})
        }
    },

    watch: {
        language: function () { this.attributes = this.setAttributes() }
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
                name_de: {
                    default: null,
                    text: this.$root.label('design') + ' (DE)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.name_de ?? '--'
                },
                name_en: {
                    default: null,
                    text: this.$root.label('design') + ' (EN)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.name_en ?? '--'
                },
                name_bg: {
                    default: null,
                    text: this.$root.label('design') + ' (BG)',
                    icon: 'label',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => item?.name_bg ?? '--'
                },
                border_dots: {
                    default: 0,
                    text: this.$root.label('border_dots'),
                    icon: 'filter_tilt_shift',
                    filter: null,
                    clone: true,
                    content: (item) => this.yesNo.find((row) => row.value == item.border_dots)?.text ?? '--'
                },
                role: {
                    default: 2,
                    text: this.$root.label('kind'),
                    icon: 'help_outline',
                    header: true,
                    sortable: true,
                    filter: null,
                    clone: true,
                    content: (item) => this.roles.find((row) => row.value == item?.role)?.text ?? '--'
                },
                side: {
                    default: 2,
                    text: this.$root.label('side'),
                    icon: 'tonality',
                    header: true,
                    sortable: true,
                    filter: this.filterSide,
                    clone: true,
                    content: (item) => this.sides.find((row) => row.value == item?.side)?.text ?? '--'
                }
            }
        }
    }
}

</script>
