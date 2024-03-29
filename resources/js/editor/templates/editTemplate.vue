<template>
<div>
    <!-- Toolbar -->
    <div class="header_bg" :class="'component-toolbar' + (dialog ? ' component-toolbar-dialog' : '')">

        <div class="d-flex align-center" style="width: 100%; height: 50px">

            <slot name="editor-toolbar-left" v-bind:item="editorItem">
                <div class="ml-3 headline" v-html="toolbarHeader" />
            </slot>

            <v-spacer />

            <template v-if="select">
                <adv-btn
                    v-if="select"
                    icon="touch_app"
                    color-main="blue_prim"
                    color-hover="blue_sec"
                    color-text="white"
                    :disabled="!editorItem.id || editorItem.id == selected"
                    v-on:click="selectItem()"
                />

                <div :class="divider" />
            </template>

            <adv-btn
                icon="search"
                color-hover="header_hover"
                @click="$emit('search', true)"
            />

            <div :class="divider" />

            <adv-btn
                :icon="editorItem.id ? 'add' : 'clear'"
                color-hover="header_hover"
                :disabled="noEdit"
                @click="newItem()"
            />

            <adv-btn
                icon="library_add"
                color-hover="header_hover"
                :disabled="!editorItem.id || noEdit"
                @click="cloneItem()"
            />

            <div :class="divider" />

            <adv-btn
                icon="delete"
                color-hover="header_hover"
                :disabled="!editorItem.id || noEdit"
                @click="deleteItem()"
            />

            <div :class="divider" />

            <div
                v-if="noEdit"
                style="width: 200px; height: 50px; cursor: default"
                class="d-flex align-center justify-center font-weight-bold text-uppercase grey--text"
            >
                <div v-text="'Not editable'" />
            </div>

            <!-- Save -->
            <div v-else>
                <div v-if="processing" class="d-flex align-center justify-center" style="width: 200px; height: 50px;">
                    <v-progress-circular indeterminate />
                </div>

                <v-hover v-else v-slot="{ hover }">
                    <div
                        class="d-flex align-center justify-center headline font-weight-bold text-uppercase light-blue--text text--darken-2"
                        :class="hover ? 'header_hover' : 'header_bg'"
                        style="width: 200px; height: 50px; cursor: pointer"
                        @click="save()"
                    >
                        <v-icon
                            v-text="mode === 'clone' ? 'play_arrow' : 'save'"
                            class="mr-2 light-blue--text text--darken-2"
                        />
                        <div v-text="mode === 'clone' ? 'proceed' : 'save'" />
                    </div>
                </v-hover>
            </div>
        </div>

    </div>

    <!-- Content -->
    <div :class="'app_bg component-content' + (dialog ? ' component-content-dialog' : '')">
        <sheet-template :dialog="dialog">
            <!-- Editor -->
            <template v-if="mode === 'edit'">
                <div
                    class="mt-n1 d-flex justify-space-between align-center mb-5"
                    style="cursor:pointer"
                    @click="editorExpanded = !editorExpanded"
                >
                    <div class="body-1 font-weight-bold" v-text="showEditor ? 'Edit Data' : 'Details'" />
                    <v-icon class="ml-2 mr-3" v-text="showEditor ? 'expand_less' : 'edit'" />
                    <v-divider />
                </div>

                <v-expand-transition>
                    <div v-if="showEditor">
                        <slot name="editor" v-bind:item="editorItem" />
                    </div>
                </v-expand-transition>

                <v-expand-transition>
                    <div
                        v-if="!showEditor"
                        class="d-flex justify-center mt-n4"
                        v-html="printEditorSumup()"
                    />
                </v-expand-transition>

                <!-- Gallery -->
                <v-expand-transition v-if="gallery" :key="editorItem.id">
                    <ItemGallery
                        v-if="editorItem.id"
                        :entity="entity"
                        :search_key="gallery"
                        :search_val="editorItem.id ? editorItem.id : 0"
                        :linking="linking"
                        dis-card-header
                        class="mt-10"
                    >
                        <template v-slot:link="slot">
                            <div :key="slot.show">
                                <slot name="gallery-link" v-bind:link="slot.link" />
                            </div>
                        </template>
                    </ItemGallery>
                </v-expand-transition>
            </template>

            <!-- Cloning -->
            <template v-else-if="mode === 'clone'">
                <div class="mb-2">
                    <span v-if="language === 'de'">
                        Bitte wählen Sie die Werte aus, die Sie kopieren möchten (Leereinträge können nicht kopiert werden).<br />
                        Anschließend können Sie die Werte weiter bearbeiten.
                    </span>
                    <span v-else>
                        Please select the values you would like to copy (empty values cannot be copied).<br />
                        You can then edit the copied values.
                    </span>
                </div>

                <v-row class="ma-0 pa-0">
                    <template v-for="(key) in Object.keys(cloningItem)">
                        <v-col
                            v-if="cloningItem[key].clone !== undefined"
                            :key="key"
                            :cols="12"
                            :sm="6"
                            :md="4"
                            :lg="3"
                        >
                            <div class="d-flex align-start">
                                <v-checkbox
                                    v-model="cloningItem[key].clone"
                                    :disabled="cloningItem[key].clone === null"
                                    class="mt-0"
                                    color="blue_prim"
                                />
                                <div class="pt-1 ml-1">
                                    <div
                                        class="caption font-weight-thin text-uppercase mb-1"
                                        v-text="cloningItem[key].text"
                                    />
                                    <div v-html="cloningItem[key].content" />
                                </div>
                            </div>
                        </v-col>
                    </template>
                </v-row>
            </template>
        </sheet-template>
    </div>

</div>
</template>


<script>

export default {
    data () {
        return {
            loading:            false,
            processing:         false,
            mode:               null,

            editorItem:         {},
            cloningItem:        {},
            editorItemLabel:    this.itemLabel ?? ('cn ' + (this.entity.slice(-1) === 's' ? this.entity.slice(0, -1) : this.entity)),
            editorExpanded:     this.select ? false : true,

            link: {
                active: false,
                entity: null,
                item: {}
            },

            divider:            'header_hover fill-height width-1px'
        }
    },

    props: {
        dialog:         { type: Boolean, default: false },

        entity:         { type: String, required: true },
        attributes:     { type: Object, required: true },

        id:             { type: [String, Number], default: null },
        gallery:        { type: String, default: null },
        linking:        { type: Boolean, default: false },

        itemLabel:      { type: String, default: null },
        noEdit:         { type: Boolean, default: false },

        select:         { type: Boolean, default: false },
        selected:       { type: [Number, String], default: null }
    },

    computed: {
        language () {
            return this.$root.language
        },

        toolbarHeader () {
            if (this.id.slice(0, 6) === 'clone-' && this.mode === 'clone') return 'Clone ' + this.editorItemLabel + ' ' + this.id.slice(6)
            else if (this.id === 'new' || this.id.slice(0, 6) === 'clone-') return 'Add new Item'
            else return (this.noEdit ? '' : 'Edit ') + this.editorItemLabel + ' ' + this.id
        },

        showEditor () {
            return !this.noEdit && (this.editorExpanded || !this.editorItem.id)
        }
    },

    watch: {
        id () { this.handleId() }
    },

    async created () {
        this.handleId()
    },

    methods: {

        async handleId () {
            if (this.id) {
                this.setEditorItemToDefault()
                this.setCloningItemToDefault()

                if (this.id === 'new') {
                    this.setTitle('new Item')
                    this.mode = 'edit'
                }
                else if (this.id.slice(0, 6) === 'clone-') {
                    await this.setCloningItem(this.id.slice(6))
                    this.setTitle('clone ID ' + this.id.slice(6))
                    this.mode = 'clone'
                }
                else {
                    await this.setEditorItem(this.id)
                    this.setTitle('ID ' + this.id)
                    this.mode = 'edit'
                }
            } else console.error('edit-template: no ID given')
        },

        setTitle (title) {
            if (!this.dialog) this.$root.setTitle(this.$root.label(this.entity) + ' (' + title + ')')
        },

        // Input -----------------------------------------------------------------------------
        newItem () {
            this.$emit('add', true)
        },

        setEditorItemToDefault () {
            const defaultItem = {}

            Object.keys(this.attributes).forEach((key) => {
                if (Array.isArray(this.attributes[key].default) && this.attributes[key].default !== null) {
                    defaultItem[key] = [ ... this.attributes[key].default ]
                }
                else if (typeof this.attributes[key].default === 'object' && this.attributes[key].default !== null) {
                    defaultItem[key] = { ... this.attributes[key].default }
                }
                else defaultItem[key] = this.attributes[key].default
            })

            this.editorItem = defaultItem
        },

        async setEditorItem (id) {
            if (id) this.editorItem = await this.getSingleItem(id)
            else console.error('edit-template: setEditorItem: no id given')
        },

        async setCloningItem (id) {
            if (id) {
                const item = await this.getSingleItem(id)
                Object.keys(this.cloningItem).forEach((key) => {
                    if (key !== 'id' && item[key] !== undefined) {
                        if (item[key] === null) this.cloningItem[key].clone = null
                        this.cloningItem[key].value = item[key]
                        this.cloningItem[key].content = this.attributes[key].content(item)
                    }
                })
                //console.log('clone', this.cloningItem)
            }
            else console.error('edit-template: setCloningItem: no id given')
        },

        setCloningItemToDefault () {
            const defaultItem = {}
            Object.keys(this.attributes).forEach((key) => {
                defaultItem[key] = {
                    clone: this.attributes[key].clone,
                    text: this.attributes[key].text,
                    value: typeof this.attributes[key].default !== 'object' ? { ... this.attributes[key].default } : this.attributes[key].default,
                    content: '--'
                }
            })
            this.cloningItem = defaultItem
        },

        async getSingleItem (id) {
            this.loading = this.$root.loading = true

            let item = {}

            const dbi = await this.$root.DBI_SELECT_GET(this.entity, id)
            if (dbi?.contents?.[0]?.id) item = dbi.contents[0]

            if (!item) {
                alert('Unknown ID ' + id)
                this.$emit('search', true)
            }

            this.loading = this.$root.loading = false
            return item
        },

        async sendItem () {
            this.$root.loading = this.loading = this.processing = true

            const response = await this.$root.DBI_INPUT_POST(this.entity, 'input', this.editorItem);

            if (response.success) {
                this.$store.dispatch('showSnack', { color: 'success', message: response.success[this.language] })
                if (!this.editorItem.id) this.$emit('id', response.id)
            }
            else console.error('edit-template: Update/Creation: server-response was not ok')

            this.$root.loading = this.loading = this.processing = false
        },

        async deleteItem () {
            const confirmed = confirm('Soll Eintrag ID ' + this.editorItem.id + ' wirklich gelöscht werden?')

            if (confirmed) {
                this.$root.loading = this.processing = true

                const response = await this.$root.DBI_INPUT_POST(this.entity, 'delete', this.editorItem)

                if (response.success) {
                    this.$store.dispatch('showSnack', { color: 'success', message: response.success[this.language] })
                    this.setEditorItemToDefault()
                    this.newItem()
                }
                else console.error('edit-template: Deltion: server-response was not ok');

                this.$root.loading = this.processing = false
            }
        },

        printEditorSumup () {
            const print = []

            Object.keys(this.attributes).forEach((key) => {
                if (this.attributes[key].header && this.editorItem[key] !== undefined) print.push(
                    '<td class="pr-5" style="vertical-align: top">' +
                        '<div class="caption font-weight-thin text-uppercase">' + this.attributes[key].text + '</div>' +
                        '<div>' + this.attributes[key].content(this.editorItem) + '</div>' +
                    '</td>'
                )
            })

            return '<table><tr>' + print.join('\n') + '</tr></table>'
        },

        save () {
            if (this.mode === 'clone') {
                this.setEditorItemToDefault()
                Object.keys(this.cloningItem).forEach((key) => {
                    if (this.cloningItem[key].clone) this.editorItem[key] = this.cloningItem[key].value
                })
                this.mode = 'edit'
            }
            else this.sendItem()
        },

        cloneItem () {
            this.$emit('clone', this.editorItem)
        },

        selectItem () {
            this.$emit('select', this.editorItem)
        },
    }
}

</script>
