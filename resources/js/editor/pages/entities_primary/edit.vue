<template>
<div>
    <!-- Toolbar -->
    <component-toolbar>
        <template v-slot:toolbar>

        </template>
    </component-toolbar>

    <!-- Content -->
    <div id="edit-form-container" class="component-content" style="padding-left: 43px">
        <div v-text="'TESTETSTET: ' +section" />

        <!-- Images -->
        <edit-images :item="item" :index="img_index" :refresh="img_refresh" />
    </div>

    <!-- Drawer -->
    <edit-tabs :entity="entity" :id="id" :section="section" />


    <!-- Dialog Inheritance ---------------------------------------------------------- -->
    <v-dialog v-model="dialog_inheritance.active" persistent scrollable max-width="600px">
        <v-card tile>

            <!-- System Header -->
            <dialogbartop
                :icon="'sync' + (inherited[dialog_inheritance.key] === 1 ? '_disabled' : '')"
                :text="(inherited[dialog_inheritance.key] === 1 ? 'Dea' : 'A')+'ctivating coin-type-synchronisation'"
                v-on:close="closeDialogInheritance()"
            ></dialogbartop>

            <v-card-text class="mt-5" style="min-height: 200px">
                <div v-if="inherited[dialog_inheritance.key] === 1">
                    Deactivating coin-type-synchronisation (inheritance) will allow you to set an individual value for
                    <b v-text="dialog_inheritance.label"></b>.
                    But any update on <b v-text="'cn type ' + inherited.id_type"></b> will be ignored.
                </div>
                <div v-else>
                    Activating coin-type-synchronisation (inheritance) will replace the individual value for
                    <b v-text="dialog_inheritance.label"></b> with the value of Type <b v-text="'cn type ' + inherited.id_type"></b>:
                    <div class="pa-4" v-html="dialog_inheritance.value"></div>
                    Any update on <b v-text="'cn type ' + inherited.id_type"></b> will automatically update the coin value.
                </div>
                <div class="mt-4">
                    Would you like to proceed?
                </div>
            </v-card-text>

            <v-card-actions class="mt-n5">
                <v-spacer></v-spacer>
                <v-btn text class="mr-5" @click="closeDialogInheritance()">
                    <v-icon>clear</v-icon>
                </v-btn>
                <v-btn text @click="toggleInheritance(dialog_inheritance.key)">
                    <v-icon>done</v-icon>
                </v-btn>
                <v-spacer></v-spacer>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <!-- Dialog Inheritance Manager ---------------------------------------------------------- -->
    <simpleSelectDialog
        :active="inherit_manager.active"
        :text="$root.label('inheritance_management')"
        icon="sync"
        v-on:close="inherit_manager = { active: false, data: {} }"
    >
        <template v-slot:content>
            <div>
                <p>
                    Here you can enable or disable inheritance for the individual values.<br/>
                    For these changes to take effect, please click "save" in this dialog (otherwise they will be discarded).
                </p>
                <p>
                    <span class="font-weight-bold error--text">Important Note:</span>
                    If some values are displayed incorrectly, please close this dialog, save your latest changes, and then reopen the dialog.
                </p>
                <v-btn
                    text
                    block
                    v-text="'Activate Inheritance for all Values'"
                    @click="inheritanceAll()"
                ></v-btn>
            </div>
            <v-row class="mt-n6">
                <v-col
                    v-for="(record, key) in inherit_manager.data"
                    :key="key"
                    cols=12 md=6 xl=4
                >
                    <v-radio-group
                        v-model="inherit_manager.data[key].inherited"
                        mandatory
                    >
                        <v-row>
                            <v-col cols=12>
                                <div class="d-flex align-center">
                                    <div
                                        class="d-flex align-center mr-3"
                                        style="cursor: pointer"
                                        @click="inherit_manager.data[key].inherited = inherit_manager.data[key].inherited === 1 ? 0 : 1"
                                    >
                                        <div class="font-weight-bold mr-2" v-html="record.label"></div>
                                        <v-icon v-text="inherit_manager.data[key].inherited === 1 ? 'sync' : 'sync_disabled'"></v-icon>
                                    </div>
                                    <v-divider></v-divider>
                                </div>
                            </v-col>
                            <v-col
                                cols=6 v-for="e in ['coin', 'type']"
                                :key="e"
                                class="pt-0 pb-0"
                            >
                                <div class="d-flex align-start">
                                    <v-radio
                                        :value="e === 'coin' ? 0 : 1"
                                        color="blue_prim"
                                        class="mr-2"
                                    ></v-radio>
                                    <div>
                                        <div
                                            class="mb-1 font-weight-bold"
                                            :class="inherit_manager.data[key].inherited === 1 && e === 'type' ? 'blue_prim--text' : (inherit_manager.data[key].inherited === 0 && e === 'coin' ? 'blue_prim--text' : '')"
                                            v-html="$root.label(e)"
                                        ></div>
                                        <div class="caption" v-html="record[e + '_value']"></div>
                                    </div>
                                </div>
                            </v-col>
                        </v-row>
                    </v-radio-group>
                </v-col>
            </v-row>
        </template>
        <template v-slot:actions>
            <div class="pb-2 pt-2 d-flex justify-center">
                <div style="width: 50%">
                    <v-btn
                        tile
                        block
                        color="blue_prim"
                        v-text="$root.label('save')"
                        @click="inheritanceUpdate()"
                    ></v-btn>
                </div>
            </div>
        </template>
    </simpleSelectDialog>

</div>
</template>



<script> // ------------------------------------------------------------------------------------------------------------------------------------

import editTabs         from './modules/editTabs.vue'
import editImages       from './modules/editImages.vue'
import ItemLink         from './modules/ItemSingleLink.vue'
import inheritButton    from './modules/inheritButton.vue'
import imported         from './modules/importContent.vue'

export default {
    components: {
        editTabs,
        editImages,
        ItemLink,
        inheritButton,
        imported
    },

    data () {
        return {
            loading:            false,
            refresh:            0,
            gallery_refresh:    0,

            tab:                this.entity === 'types' ? 'coins' : 'images',
            img_index:          0,
            img_refresh:        0,
            show_imported:      false,

            item:               {}, // processed item
            item_data:          {}, // raw DBI data

            new_link:           null,
            wraped:             'd-flex component-wrap align-start',

            // Inheritancestuff:
            inheriting_type:        {}, // processed item
            inheriting_type_data:   {}, // raw DBI data
            inherited:              this.$handlers.format.inherited(),
            new_inheritor:          null,
            inheritance_refresh:    0,
            inherit_manager:        {
                active: false,
                data: {}
            },

            // Relation Keys
            edit_relations: {},
            relations_refresh: 0,

            // Dialogs
            files_dialog: {
                active: false,
                key: null,
                id: null
            },
            upload_dialog: {
                active: false,
                key: null
            },
            link_dialog: {
                active: false,
                index: null,
                side: null,
                input: null
            },
            dialog_entity_link: {
                entity: null,
                id: null
            },
            dialog_inheritance: {
                active: false,
                label: null,
                key: null,
                value: null
            },

            // Dropdowns
            select_img_type: [
                { value: 'original', text: 'Original' },
                { value: 'plastercast', text: 'Plastercast' }
            ],
            select_centerhole: [
                { value: 0, text: 'none' },
                { value: 1, text: 'Obverse' },
                { value: 2, text: 'Reverse' },
                { value: 3, text: 'Both Sides' }
            ]
        }
    },

    props: {
        entity:     { type: String, required: true },
        id:         { type: Number, required: true },
        section:    { type: String, default: null },
        //tab:        { type: String, default: 'images' },
        //img_index:  { type: Number, default: 0 },
        save:       { type: Number, default: 0 },
        mark:       { type: Number, default: 0 },
        erase:      { type: Number, default: 0 }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        rules () { return this.$handlers.rules },
        to_string () { return this.$handlers.format.stringify_data },

        compare_dates () {
            if (this.item?.date_start && this.item?.date_end) {
                return parseInt(this.item.date_end) >= parseInt(this.item.date_start) || 'Start cannot be later than End!'
            }
            else {
                return true
            }

        },

        compare_diameters () {
            if (this.item?.diameter_max && this.item?.diameter_min) {
                return this.item.diameter_max >= this.item.diameter_min || 'Minimum Diameter cannot be bigger than Maximum!'
            }
            else if (this.item?.diameter_min && !this.item?.diameter_max) {
                return false || 'If Minimum is set a Maximum is requried, too!'
            }
            else {
                return true
            }
        }
    },

    watch: {
        id: function () {
            this.$store.commit('setBreadcrumbs', [
                { label: this.entity, to:'' },
                { label: this.id, to:'' }
            ])
            this.SetItem()
        },
        entity: function () {
            this.tab = this.entity === 'types' ? 'coins' : 'images'
            this.img_index = 0
            this.$store.commit('setBreadcrumbs', [
                { label: this.entity, to:'' },
                { label: this.id, to:'' }
            ])
            this.SetItem()
        },
    },

    created () {
        this.$store.commit('setBreadcrumbs', [
            { label: this.entity, to:'' },
            { label: this.id, to:'' }
        ])
        this.SetItem()
    },

    // ----------------------------------------------------------------------------------------------------------------------------------------------------
    methods: {
        Refresh () {
            this.refresh = this.refresh + 1;
        },

        async SetItem () {
            this.loading = this.$root.loading = true

            // Get Entity Data from DBI
            const dbi = await this.$root.DBI_SELECT_GET(this.entity, this.id)
            if(!dbi.contents?.[0]?.id) { alert('ERROR: no data for cn ' + this.entity + ' ' + this.id) }

            this.item_data = dbi.contents[0]
            this.item = this.$handlers.create_item(this.entity, this.item_data)

            if (this.item.inherited?.id_type) {
                this.inherited = this.item_data.dbi.inherited
            }

            // Set Relations for types and coins
            this.SetRelations()

            // Get inheriting Type Date from DBI if entity is coin and inheritance is set
            if (this.entity === 'coins' && this.item.inherited?.id_type) {
                const dbi_inheriting_type = await this.$root.DBI_SELECT_GET('types', this.item.inherited.id_type)
                if(!dbi_inheriting_type.contents?.[0]?.id) { alert('ERROR: no data for inheriting type ' + this.item.inherited.id_type + ' available!') }

                this.inheriting_type_data = dbi_inheriting_type.contents[0]
                this.inheriting_type = this.$handlers.create_item('types', this.inheriting_type_data)
                this.new_inheritor = this.inheriting_type.id ? this.inheriting_type.id : null
            }

            // Refresh
            //this.Refresh ()

            this.loading = this.$root.loading = false;
        },

        async SendItem () {
            this.loading = true

            const response  = await this.$root.DBI_INPUT_POST(this.entity, 'input', this.item)

            if (response.success) {
                this.$root.snackbar (response.success, 'success')
                this.SetItem()
            }
            else {
                console.log ('Data Input Error: response was not ok')
            }

            this.loading = false
        },

        async MarkItemAsReadyToPublish () {
            const confirmed = confirm('Wenn Sie fortfahren, werden Ihre Eingaben gespeichert und das Objekt als "bereit zur Publikation" markiert.' +
            ' Ein verantwortliches Teammitglied wird es anschließend prüfen und über die Freigabe entscheiden.')

            if (confirmed) {
                this.item.public = 2
                this.SendItem()
            }
        },

        async EraseItem () {
            const confirmed = confirm('Are you sure you want to request deletion of ' + this.$handlers.format.cn_entity(this.entity, this.item.id) + '?')

            if (confirmed) {
                this.loading = true

                const response = await this.$root.DBI_INPUT_POST(this.entity, 'delete', this.item)

                if (response.success) {
                    this.$root.snackbar (response.success, 'success')
                    this.SetItem()
                }
                else {
                    console.log ('Data Input Error: response was not ok')
                }

                this.loading = false
            }
        },

        // Handle Relations between primary and secondary Entity
        SetRelations () {
            const self = this
            const d = this.item_data
            const relations = {}

            relations.links         = !d.web_references ? [] : d.web_references.map((row) => { return self.to_string.weblink(row, self.l) })
            relations.persons       = !d.persons ? [] : d.persons.map((row) => { return self.to_string.individual(row, self.l) })
            relations.o_monograms   = !d.obverse?.monograms ? [] : d.obverse.monograms.map((row) => { return self.to_string.monogram_symbol('monograms', row, self.l) })
            relations.o_symbols     = !d.obverse?.symbols ? [] : d.obverse.symbols.map((row) => { return self.to_string.monogram_symbol('symbols', row, self.l) })
            relations.r_monograms   = !d.reverse?.monograms ? [] : d.reverse.monograms.map((row) => { return self.to_string.monogram_symbol('monograms', row, self.l) })
            relations.r_symbols     = !d.reverse?.symbols ? [] : d.reverse.symbols.map((row) => { return self.to_string.monogram_symbol('symbols', row, self.l) })
            relations.citations     = !d.citations ? [] : d.citations.map((row) => { return self.to_string.reference(row, self.l) })
            relations.literature    = !d.literature ? [] : d.literature.map((row) => { return self.to_string.reference(row, self.l) })
            relations.groups        = !d.dbi?.groups ? [] : d.dbi.groups.map((row) => { return self.to_string.objectgroup(row, self.l) })

            if (this.entity === 'coins') {
                relations.images            = !d.images ? [] : d.images.map((row) => { return row })
                relations.o_controlmarks    = !d.obverse?.controlmarks ? [] : d.obverse.controlmarks.map((row) => { return self.to_string.controlmark(row, self.l) })
                relations.r_controlmarks    = !d.reverse?.controlmarks ? [] : d.reverse.controlmarks.map((row) => { return self.to_string.controlmark(row, self.l) })
            }
            else {
                relations.variations    = !d.variations ? [] : d.variations.map((row) => { return 'v' })
                relations.findspots     = !d.dbi?.findspots ? [] : d.dbi.findspots.map((row) => { return self.to_string.hoard_findspot(row, self.l) })
                relations.hoards        = !d.dbi?.hoards ? [] : d.dbi.hoards.map((row) => { return self.to_string.hoard_findspot(row, self.l) })
            }

            this.edit_relations = relations
        },
        AddRelation (entity, fields) {
            if (this.item[entity]) {
                this.item[entity].push(fields);
            } else {
                this.item[entity] = [fields];
            }
            this.edit_relations[entity].push(null)
        },
        DeleteRelation (entity, index) {
            this.edit_relations[entity].splice(index, 1)
            this.item[entity].splice(index, 1)
        },
        EditRelation (key, i) {
            this.edit_relations[key][i] = null
            // Force Update of Display
            this.edit_relations[key].push(null)
            this.edit_relations[key].pop()
        },

        // Image Handler -----------------------------------------------------------------------
        ImgBrowser (emit) {
            const explode = emit.key.split('_');
            this.item.images[explode[0]] [explode [1]].link = emit.id;
            this.files_dialog = { active: false, key: null, id: null };
        },

        // JK: Upload Dialog
        ImgUpload (emit) {
            const explode = emit.key.split('_')
            this.item.images[explode[0]][explode[1]].link = emit.url
            this.upload_dialog = { active: false, key: null }
        },

        // Image Links Methods
        ImgLinkDialog (index, side) {
            let input = this.item.images[index][side].link;

            // Check if set input is a valid link - if not set input to null
            if (input) {
                if (input.substring (0, 4) != 'http') {
                    input = null;
                }
            }

            this.link_dialog = {active: true, index: index, side: side, input: input}
        },

        SetLink () {
            this.item.images[this.link_dialog.index][this.link_dialog.side].link = this.link_dialog.input.trim()
            this.CloseLink ()
        },

        CloseLink () {
            this.link_dialog = {active: false, index: null, side: null, input: null}
        },

        setImgIndex (i) {
            this.img_index = i
        },

        setTypeImage (input) {
            let confirmed = confirm ('Are you sure you want to set this Coin as representing for this Type?');

            if (confirmed === true) {
                //console.log(input.image[0].id)
                this.item.image = input.image[0].id
                this.item.images = input.image
                //++this.img_refresh
            }
        },

        // Handle Inheritance -----------------------------------------------------------------------------------------------------------------------------
        confirmInheritance (key) {
            let section = null
            let sec_key = key

            if (['o_', 'r_'].includes(key.slice(0, 2))) {
                section = key.slice(0, 2) === 'o_' ? 'obverse' : 'reverse'
                sec_key = key.slice(2)
            }

            this.dialog_inheritance = {
                active: true,
                key: key,
                label: ((section ? (this.labels[section] + ' ') : '') + this.labels[sec_key]),
                value: this.$handlers.show_item_data(this.l, 'types', this.inheriting_type_data, sec_key, section)
            }
        },

        toggleInheritance (key) {
            if (this.inherited[key] === 0) {
                let section = null
                let sec_key = key

                // Copy data of raw objects to prevent display errors (rendered monograms, symbols and persons)
                if (['o_', 'r_'].includes(key.slice(0, 2))) {
                    section = key.slice(0, 2) === 'o_' ? 'obverse' : 'reverse'
                    sec_key = key.slice(2)
                    this.item_data[section][sec_key] = this.inheriting_type_data[section][sec_key]
                }
                else if (key === 'persons') {
                    this.item_data.persons = this.inheriting_type_data.persons
                }

                if (key === 'date') {
                    this.item.date_start         =   this.inheriting_type.date_start
                    this.item.date_end           =   this.inheriting_type.date_end
                    this.item.date_text_de       =   this.inheriting_type.date_text_de
                    this.item.date_text_en       =   this.inheriting_type.date_text_en
                }
                else if (key === 'comment_public') {
                    this.item.comment_public_de  =   this.inheriting_type.comment_public_de
                    this.item.comment_public_en  =   this.inheriting_type.comment_public_en
                }
                else {
                    this.item[key] = this.inheriting_type[key]
                }

                //this.item_data[section]
                //this.item[key] = this.inheriting_type[key]
                this.inherited[key] = 1
            }
            else {
                this.inherited[key] = 0
            }
            this.closeDialogInheritance()
        },

        closeDialogInheritance () {
            this.dialog_inheritance = { active: false, key: null, label: null, value: null }
        },

        async inheritanceNew (id) {
            if (id){
                const response = await this.$root.DBI_INPUT_POST('coins', 'connect', {
                    mode:       'link',
                    id_type:    id,
                    id_coin:    this.item.id,
                });
                if (response.success) {
                    this.$root.snackbar(response.success, 'success')
                    await this.SetItem(this.id)
                    ++this.inheritance_refresh
                    this.inheritanceManage()
                }
            }
            else {
                alert('ERROR: No Type ID found!')
            }
        },
        async inheritanceOff () {
            if (this.item?.inherited?.id_type){
                if (confirm('Are you sure you want to unlink inheriting cn type ' + this.item.inherited.id_type + ' from cn coin ' + this.item.id + '? Future updates on the Type will be ignored!')) {
                    const response = await this.$root.DBI_INPUT_POST('coins', 'connect', {
                        mode:       'unlink',
                        id_type:    this.item.inherited.id_type,
                        id_coin:    this.item.id,
                    });
                    if (response.success) {
                        this.$root.snackbar(response.success, 'success')
                        this.inherited = this.$handlers.format.inherited() // force deletion of inheritance
                        await this.SetItem(this.id)
                        ++this.inheritance_refresh
                    }
                }
            }
            else {
                alert('ERROR: No Type ID found!')
            }
        },

        inheritanceManage () {
            const self = this
            const item = {}
            this.$handlers.constant.inheritance_keys.forEach((key) => {
                let section = null
                let sec_key = key
                if (key !== 'id_type') {
                    if (['o_', 'r_'].includes(key.slice(0, 2))) {
                        section = key.slice(0, 2) === 'o_' ? 'obverse' : 'reverse'
                        sec_key = key.slice(2)
                    }
                    item[key] = {
                        inherited: self.inherited[key] === 1 ? 1 : 0,
                        label: self.$root.label((section ? (section + ',') : '') + sec_key),
                        coin_value: self.$handlers.show_item_data(self.l, 'coins', self.item_data, sec_key, section),
                        type_value: self.$handlers.show_item_data(self.l, 'types', self.inheriting_type_data, sec_key, section)
                    }
                }
            })
            //return item
            console.log(item)
            this.inherit_manager = { active: true, data: item }
        },

        async inheritanceUpdate () {
            const self = this
            Object.keys(self.inherit_manager.data).forEach((key) => {
                if (self.inherit_manager.data[key].inherited === 1) {
                    self.inherited[key] = 1
                    if (key === 'date') {
                        self.item.date_start         =   self.inheriting_type.date_start
                        self.item.date_end           =   self.inheriting_type.date_end
                        self.item.date_text_de       =   self.inheriting_type.date_text_de
                        self.item.date_text_en       =   self.inheriting_type.date_text_en
                    }
                    else {
                        self.item[key] = self.inheriting_type[key]
                    }
                }
                else {
                    self.inherited[key] = 0
                }
            })
            this.inherit_manager = { active: false, data: {} }
            await this.SendItem()
        },

        inheritanceAll () {
            const self = this
            Object.keys(self.inherit_manager.data).forEach((key) => { self.inherit_manager.data[key].inherited = 1 })
        }
    }
}

</script>
