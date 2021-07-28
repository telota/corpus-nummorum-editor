<template>
<div>
    <!-- Toolbar -->
    <edit-toolbar
        :entity="entity"
        :item="item"
        v-on:save="SendItem()"
        v-on:mark="MarkItemAsReadyToPublish()"
        v-on:erase="EraseItem()"
        v-on:details="(emit) => { this.detailsDialog = emit }"
    />

    <!-- Images -->
    <edit-images :item="item" />

    <!-- Drawer -->
    <edit-tabs
        :entity="entity"
        :id="id"
        :section="section"
        :item="item_data"
        :show-imported="showImported"
        v-on:import="showImported = !showImported"
    />

    <!-- Content -->
    <div id="edit-form-container" class="component-content" style="padding-left: 40px; width: calc(100vw - 200px);">
        <sheet-template>
            <!-- Images --------------------------------------- --------------------------------------------- -->
            <div v-if="entity === 'coins' && (!section || section === 'images')" style="width:100%">
                <!-- Header -->
                <subheader
                    :label="labels.imagesets"
                    add
                    :count="item.images"
                    class="mb-5"
                    v-on:add="AddRelation('images', {
                        id: null,
                        kind: 'plastercast',
                        obverse: {
                            photographer: null,
                            link: null,
                            path: null,
                            public: 1,
                            copyright: 1
                        },
                        reverse: {
                            photographer: null,
                            link: null,
                            path: null,
                            public: 1,
                            copyright: 1
                        }
                    });
                    setImgIndex(item.images ? (item.images.length - 1) : 0)"
                ></subheader>

                <!-- Content -->
                <v-row>
                    <v-col
                        cols=12
                        sm=6
                        v-for="(set, i) in item.images"
                        :key="'set' + i + '-' + set.id"
                    >
                        <v-card tile class="pa-3">
                            <div class="d-flex align-end mb-5">
                                <div class="headline" v-text="set.id ? ('ID ' + set.id) : 'New Set'" style="width: 160px" />

                                <!-- Kind -->
                                <v-hover v-slot="{ hover }">
                                    <div
                                        class="d-flex body-1 font-weight-bold text-uppercase ml-5"
                                        style="cursor:pointer"
                                        :style="'color:' + $root.colors[hover ? 'input_hover' : 'input_main']"
                                        @click="item.images[i].kind = set.kind === 'original' ? 'plastercast' : 'original'"
                                    >
                                        <div v-text="set.kind" />
                                        <v-icon class="ml-2" v-text="'swipe'" />
                                    </div>
                                </v-hover>

                                <v-spacer />

                                <!-- Delete Record -->
                                <v-btn
                                    icon
                                    class="ml-1 mr-n2"
                                    @click="DeleteRelation('images', i)"
                                >
                                    <v-icon v-text="'delete'"></v-icon>
                                </v-btn>
                            </div>

                            <div
                                v-for="(img, side) in { obverse: set.obverse, reverse: set.reverse }"
                                :key="'set' + i + '-' + set.id + side"
                                class="d-flex"
                                :class="side === 'obverse' ? 'mb-5' : ''"
                            >
                                <!-- Img -->
                                <div style="width: 160px" :key="'set' + i + '-' + set.id + side + '_' + img.path">
                                    <adv-img
                                        contain
                                        square
                                        :src="img.link"
                                        :background="img.bg_color ? img.bg_color : (img.kind === 'plastercast' ? 'imgbg' : 'grey')"
                                    />
                                </div>

                                <!-- edit -->
                                <div class="pl-5" style="width: calc(100% - 160px)">
                                    <div class="d-flex justify-space-between">
                                        <div class="body-1 text-uppercase" v-text="$root.label(side)" />
                                        <v-checkbox
                                            class="mt-0 ml-2"
                                            label="public"
                                            v-model="item.images[i][side].public"
                                            disabled
                                            dense
                                        />
                                    </div>

                                    <!-- Path-->
                                    <v-menu offset-y>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-hover v-slot="{ hover }">
                                                <div
                                                    v-bind="attrs"
                                                    v-on="on"
                                                    class="d-flex align-end pt-1 pb-1"
                                                    :style="'border-bottom: 1px solid ' + $root.colors[hover ? 'input_hover' : 'input_main']"
                                                >
                                                    <v-icon class="mr-1" v-text="'insert_drive_file'" />
                                                    <div class="text-truncate" :class="img.path ? '' : 'grey--text'" v-text="img.path ? img.path : 'no file'" />
                                                    <v-spacer />
                                                    <v-icon class="ml-2" v-text="'expand_more'" />
                                                </div>
                                            </v-hover>
                                        </template>

                                        <v-list>
                                            <v-list-item-group>
                                                <v-list-item
                                                    v-text="'Open File Manager'"
                                                    :disabled="img.path ? (img.path.substring(0, 4) === 'http' || !img.path.includes('/') ? true : false) : false"
                                                    @click="files_dialog = { active: true, key: i + '_' + side, id: img.path }"
                                                />
                                                <v-list-item
                                                    v-text="'Upload new image'"
                                                    @click="upload_dialog = { active: true, key: i + '_' + side }"
                                                />
                                                <v-list-item
                                                    v-text="'Link external image'"
                                                    @click="ImgLinkDialog(i, side)"
                                                />
                                                <v-list-item
                                                    v-text="'Delete Relation'"
                                                    :disabled="!img.link"
                                                    @click="item.images[i][side].link = null; item.images[i][side].path = null"
                                                />
                                            </v-list-item-group>
                                        </v-list>

                                    </v-menu>

                                    <div class="mt-5" :class="$vuetify.breakpoint.xlOnly ? 'd-flex' : ''">
                                        <!-- Photographer -->
                                        <v-text-field dense clearable
                                            v-model="item.images[i][side].photographer"
                                            :placeholder="side + ' photographer'"
                                            prepend-inner-icon="person"
                                            :style="$vuetify.breakpoint.xlOnly ? 'width: 50%' : ''"
                                        >
                                            <template v-slot:append>
                                                <v-icon
                                                    v-text="side === 'obverse' ? 'arrow_downward' : 'arrow_upward'"
                                                    @click="item.images[i][side === 'obverse' ? 'reverse' : 'obverse'].photographer = img.photographer"
                                                ></v-icon>
                                            </template>
                                        </v-text-field>

                                        <!-- Copyright -->
                                        <v-select dense
                                            v-model="item.images[i][side].copyright"
                                            :placeholder="side + ' copyright'"
                                            prepend-inner-icon="copyright"
                                            disabled
                                            :class="$vuetify.breakpoint.xlOnly ? 'ml-5' : ''"
                                            :style="$vuetify.breakpoint.xlOnly ? 'width: 50%' : ''"
                                        >
                                            <template v-slot:append>
                                                <v-icon
                                                    v-text="side === 'obverse' ? 'arrow_downward' : 'arrow_upward'"
                                                    @click="item.images[i][side === 'obverse' ? 'reverse' : 'obverse'].copyright = img.copyright"
                                                ></v-icon>
                                            </template>
                                        </v-select>
                                    </div>
                                </div>
                            </div>
                        </v-card>
                    </v-col>
                </v-row>

                <!-- Image File Browser -->
                <ChildDialog v-if="files_dialog.active"
                    :prop_active="files_dialog.active"
                    prop_component="files"
                    :prop_item="{ parent: 'coins', key: files_dialog.key, id: files_dialog.id }"
                    v-on:assignment="ImgBrowser"
                    v-on:close="files_dialog = { active: false, key: null, id: null }"
                />

                <!-- Image Direct Upload -->
                <upload
                    :prop_active="upload_dialog.active"
                    prop_target="storage/coins"
                    :prop_key="upload_dialog.key"
                    v-on:ChildEmit="ImgUpload"
                    v-on:close="upload_dialog = { active: false, key: null }"
                />

                <!-- Image Links -->
                <small-dialog
                    :show="link_dialog.active"
                    icon="link"
                    text="Link external image"
                    v-on:close="CloseLink()"
                >
                    <template v-slot>

                        <p>Please provide a valid external Link for the {{ link_dialog.side }} image.</p>

                        <v-text-field dense outlined filled clearable class="mt-2"
                            v-model="link_dialog.input"
                            label="External link"
                            prepend-icon="link"
                            counter=255
                            :rules="[rules.link]"
                        />

                        <div class="d-flex mt-5">
                            <v-spacer></v-spacer>
                            <v-btn @click="CloseLink()" icon class="mr-3">
                                <v-icon>clear</v-icon>
                            </v-btn>

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <a :href="link_dialog.input" target="_blank" class="mr-3">
                                        <v-btn v-on="on" icon>
                                            <v-icon>link</v-icon>
                                        </v-btn>
                                    </a>
                                </template>
                                <span>Check the link in a new browser tab.</span>
                            </v-tooltip>

                            <v-btn @click="SetLink()" icon>
                                <v-icon>done</v-icon>
                            </v-btn>
                            <v-spacer></v-spacer>
                        </div>

                    </template>
                </small-dialog>
            </div>


            <!-- Linked opposite Entities ----------------------------------------- -------------------------------------------- -->
            <div v-else-if="section === 'coins' || (!section && entity === 'types') || section === 'types'" style="width: 100%">
                <!-- Inheriting Type / Representing Coin -->
                <ItemLink
                    :entity="entity"
                    :item="item"
                    :key="entity + item.id + inheritance_refresh + (entity === 'types' ? item.image : item.id)"
                    class="mb-6"
                    v-on:inheritanceNew="(emit) => { inheritanceNew(emit) }"
                    v-on:inheritanceOff="inheritanceOff()"
                    v-on:inheritanceManage="inheritanceManage()"
                    v-on:deleteImage="item.image = null; item.images = []"
                />
                <!-- Coins / Types Gallery -->
                <ItemGallery
                    :key="item.id + entity + inheritance_refresh"
                    :entity="entity"
                    :search_key="'id_' + entity.slice(0, -1)"
                    :search_val="item.id"
                    :tiles="$vuetify.breakpoint.lgAndDown ? 4 : 6"
                    linking
                    color_main="app_bg"
                    color_hover="grey_prim"
                    v-on:image="(emit) => { setTypeImage(emit) }"
                    v-on:inherit="(emit) => { inheritanceNew(emit.id) }"
                />
            </div>


            <!-- Production ----------------------------------------- -------------------------------------------- -->
            <div v-else-if="section === 'production'" style="width: 100%">

                <!-- Issue -->
                <div class="mb-2">
                    <subheader :label="labels.issue" class="mb-3"></subheader>

                    <v-row>
                        <v-col v-if="entity === 'coins'" cols="12" sm="6" class="font-weight-bold">
                            <div style="width: 0; white-space:nowrap">
                                <v-checkbox
                                    class="mb-2 mt-0"
                                    v-model="item.forgery"
                                >
                                    <template v-slot:label>
                                        <div
                                            class="ml-4"
                                            :class="item.forgery ? 'red--text' : ''"
                                            v-text="'Coin is forgery'"
                                        />
                                    </template>
                                </v-checkbox>
                            </div>
                        </v-col>

                        <v-col cols=12 sm=6 class="text-end font-weight-bold orange--text">
                            <template v-if="item.authority">
                                <div
                                    v-if="item.authority === 1 && !item.authority_person"
                                    v-text="'Type of Coinage is Ruler - please select a Person'"
                                />
                                <div
                                    v-if="item.authority === 2 && !item.authority_group"
                                    v-text="'Type of Coinage is Tribe - please select a Tribe'"
                                />
                                <div
                                    v-if="item.authority === 3 && !item.mint"
                                    v-text="'Type of Coinage is Mint - please select a Mint'"
                                />
                            </template>
                        </v-col>

                        <v-col cols="12" sm="6">
                            <div :class="wraped + ' mt-n5'">
                                <InputForeignKey
                                    entity="authorities"
                                    label="Type of Coinage"
                                    icon="toll"
                                    :selected="item.authority"
                                    :inherited="inherited.authority === 1"
                                    style="width: 100%"
                                    v-on:select="(emit) => { item.authority = emit }"
                                />
                                <inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited.authority"
                                    v-on:click="confirmInheritance('authority')"
                                />
                            </div>
                        </v-col>

                        <v-col cols="12" sm="6">
                            <div :class="wraped + ' mt-n5'">
                                <InputForeignKey
                                    entity="mints"
                                    label="Mint"
                                    icon="museum"
                                    :selected="item.mint"
                                    :key="item.mint + ' ' + inherited.mint"
                                    :inherited="inherited.mint === 1"
                                    style="width: 100%"
                                    v-on:select="(emit) => { item.mint = emit }"
                                />
                                <inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited.mint"
                                    v-on:click="confirmInheritance('mint')"
                                />
                            </div>
                        </v-col>

                        <!--<v-col cols="12" sm="6">
                            <div :class="wraped + ' mt-n5'">
                                <InputForeignKey
                                    entity="persons"
                                    label="Issuer"
                                    icon="record_voice_over"
                                    :selected="item.issuer"
                                    :inherited="inherited.issuer === 1"
                                    style="width: 100%"
                                    v-on:select="(emit) => { item.issuer = emit }"
                                ></InputForeignKey>
                                <inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited.issuer"
                                    v-on:click="confirmInheritance('issuer')"
                                ></inheritButton>
                            </div>
                        </v-col>-->

                        <v-col cols="12" sm="6">
                            <div :class="wraped  + ' mt-n5'">
                                <InputForeignKey
                                    entity="persons"
                                    label="Authority Person"
                                    icon="how_to_reg"
                                    :selected="item.authority_person"
                                    :conditions="[{ authority: 1 }]"
                                    :inherited="inherited.authority_person === 1"
                                    style="width: 100%"
                                    v-on:select="(emit) => { item.authority_person = emit }"
                                />
                                <inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited.authority_person"
                                    v-on:click="confirmInheritance('authority_person')"
                                />
                            </div>
                        </v-col>

                        <v-col cols="12" sm="6">
                            <div :class="wraped + ' mt-n5'">
                                <InputForeignKey
                                    entity="tribes"
                                    label="Authority Group"
                                    icon="sports_kabaddi"
                                    :selected="item.authority_group"
                                    :inherited="inherited.authority_group === 1"
                                    style="width: 100%"
                                    v-on:select="(emit) => { item.authority_group = emit }"
                                />
                                <inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited.authority_group"
                                    v-on:click="confirmInheritance('authority_group')"
                                />
                            </div>
                        </v-col>
                    </v-row>
                </div>


                <!-- Date -->
                <div>
                    <subheader :label="labels.date" class="mb-3"></subheader>

                    <v-row>
                        <v-col cols="12" sm="6">
                            <div :class="wraped">
                                <InputForeignKey
                                    entity="periods"
                                    label="Epoch"
                                    icon="watch_later"
                                    :selected="item.period"
                                    :key="item.period"
                                    :inherited="inherited.period === 1"
                                    style="width: 100%"
                                    v-on:select="(emit) => { item.period = emit }"
                                />
                                <inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited.period"
                                    v-on:click="confirmInheritance('period')"
                                />
                            </div>
                        </v-col>

                        <v-col cols="12" sm="6">
                        </v-col>

                        <v-col cols="12" sm="3" class="mt-n5" v-for="record in ['date_start', 'date_end']" :key="record">
                            <v-text-field dense outlined filled clearable
                                v-model="item [record]"
                                :label="'Date '+(record == 'date_start' ? 'Start' : 'End')"
                                :prepend-icon="record == 'date_start' ? 'first_page' : 'last_page'"
                                :disabled="inherited.date === 1"
                                :hint="inherited.date === 1 ? 'Value inherited from type' : ''"
                                :persistent-hint="inherited.date === 1"
                                :rules="[rules.date, compare_dates]"
                            />
                        </v-col>

                        <v-col cols="12" sm="6" class="mt-n5">
                            <div :class="wraped">
                                <v-text-field dense outlined filled clearable
                                    v-model="item.date_text_de"
                                    label="Date Text (DE)"
                                    prepend-icon="settings_ethernet"
                                    :disabled="inherited.date === 1"
                                    :hint="inherited.date === 1 ? 'Value inherited from type' : ''"
                                    :persistent-hint="inherited.date === 1"
                                />
                                <inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited.authority"
                                    v-on:click="confirmInheritance('date')"
                                />
                            </div>
                        </v-col>
                    </v-row>
                </div>

            </div>


            <!-- Basics ---------------------------------------- -------------------------------------------- -->
            <div v-else-if="section === 'basics'" style="width: 100%">

                <!-- Technical Parameters -->
                <div class="mb-2">
                    <subheader :label="labels.technical_parameters" class="mb-3"></subheader>

                    <v-row>
                        <v-col
                            v-for="(key) in [
                                { k: 'material', i: 'palette' },
                                { k: 'denomination', i: 'bubble_chart' },
                                { k: 'standard', i: 'group_work' }
                            ]"
                            :key="key.k"
                            cols="12"
                            sm="4"
                        >
                            <div :class="wraped">
                                <InputForeignKey
                                    :entity="key.k + 's'"
                                    :label="labels[key.k]"
                                    :icon="key.i"
                                    :selected="item[key.k]"
                                    :inherited="inherited[key.k] === 1"
                                    style="width: 100%"
                                    v-on:select="(emit) => { item[key.k] = emit }"
                                />
                                <inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited[key.k]"
                                    v-on:click="confirmInheritance(key.k)"
                                />
                            </div>
                        </v-col>
                    </v-row>

                    <!-- IF Coins: Weight, Diameter etc. -->
                    <v-row v-if="entity === 'coins'">
                        <v-col cols="12" sm="4" class="mt-n5">
                            <v-text-field dense outlined filled clearable
                                v-model="item.weight"
                                label="Weight in g"
                                prepend-icon="fitness_center"
                                :rules="[rules.numeric_nz]"
                            />
                        </v-col>

                        <v-col cols="12" sm="4" class="mt-n5">
                            <v-text-field dense outlined filled clearable
                                v-model="item.diameter_max"
                                label="Diameter (Max) in mm"
                                prepend-icon="fiber_manual_record"
                                :rules="[rules.numeric_nz, compare_diameters]"
                            />
                        </v-col>

                        <v-col cols="12" sm="4" class="mt-n5">
                            <v-text-field dense outlined filled clearable
                                v-model="item.diameter_min"
                                label="Diameter (Min) in mm"
                                prepend-icon="adjust"
                                :rules="[rules.numeric_nz, compare_diameters]"
                            />
                        </v-col>

                        <v-col cols="12" sm="4" class="mt-n5">
                            <div :class="wraped">
                                <v-icon class="mr-5 mt-1">not_interested</v-icon>
                                <v-checkbox
                                    v-model="item.weight_ignore"
                                    label="Ignore Weight"
                                    class="mt-0"
                                />
                                <v-checkbox
                                    v-model="item.diameter_ignore"
                                    label="Ignore Diameter"
                                    class="ml-4 mt-0"
                                />
                            </div>
                        </v-col>

                        <v-col cols="12" sm="4" class="mt-n5">
                            <v-text-field dense outlined filled clearable
                                v-model="item.axis"
                                label="Axis"
                                prepend-icon="update"
                                :rules="[rules.axis]"
                            />
                        </v-col>

                        <v-col cols="12" sm="4" class="mt-n5">
                            <v-select dense outlined filled
                                v-model="item.centerhole"
                                :items="select_centerhole"
                                label="Centerhole"
                                prepend-icon="album"
                            />
                        </v-col>
                    </v-row>

                    <!-- If Types: weight, diameter etc. -->
                    <v-row v-else class="mt-n2 mb-4">
                        <v-col
                            v-for="(key) in [
                                { k: 'weight', i: 'fitness_center' },
                                { k: 'diameter', i: 'adjust' },
                                { k: 'axis', i: 'update' }
                            ]"
                            :key="key.k"
                            cols="12"
                            sm="4"
                            class="mt-n5"
                        >
                            <div class="d-flex align-center">
                                <v-icon class="mr-5" v-text="key.i"></v-icon>
                                <div>
                                    <div class="caption" v-text="labels[key.k]"></div>
                                    <div class="body-1" v-html="$handlers.show_item_data(l, entity, item_data, key.k)"></div>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </div>

                <!-- Owners -->
                <div v-if="entity === 'coins'" class="mb-2">
                    <subheader :label="labels.owners" class="mb-3"></subheader>

                    <v-row>
                        <v-col cols="12" sm="6">
                            <InputForeignKey
                                entity="owners"
                                label="Owner Original"
                                icon="account_circle"
                                :selected="item.owner_original"
                                v-on:select="(emit) => { item.owner_original = emit }"
                            />
                        </v-col>

                        <v-col cols="12" sm="6">
                            <v-text-field dense outlined filled clearable
                                v-model="item.collection"
                                label="Collection Nr."
                                prepend-icon="label"
                            />
                        </v-col>

                        <v-col cols="12" sm="6" class="mt-n5">
                            <InputForeignKey
                                entity="owners"
                                label="Owner Reproduction/Plastercast"
                                icon="account_circle"
                                :selected="item.owner_reproduction"
                                v-on:select="(emit) => { item.owner_reproduction = emit }"
                            />
                        </v-col>

                        <v-col cols="12" sm="6" class="mt-n5">
                            <v-text-field dense outlined filled clearable
                                v-model="item.plastercast"
                                label="Plastercast Nr."
                                prepend-icon="label"
                            />
                        </v-col>

                        <v-col cols="12" sm="12" class="mt-n5">
                            <div :class="wraped">
                                <v-text-field dense outlined filled clearable
                                    v-model="item.provenience"
                                    label="Provenience"
                                    prepend-icon="zoom_out_map"
                                />
                                <v-checkbox
                                    class="mt-0 mt-1 ml-5"
                                    label="Owner of Original is unsure"
                                    v-model="item.owner_unsure"
                                />
                            </div>
                        </v-col>
                    </v-row>
                </div>

                <!-- IF Type: Type Variations -->
                <div v-if="entity === 'types'">
                    <!-- Header -->
                    <subheader
                        :label="labels.variations"
                        :count="item.variation"
                        add
                        class="mb-5"
                        v-on:add="AddRelation('variations', { de: null, en: null, comment: null })"
                    />

                    <!-- Content -->
                    <v-row v-for="(iterator, i) in item.variations" :key="i" class="mt-n3">

                        <v-col cols="12" sm="12" class="mb-n3 mt-1">
                            <div class="d-flex component-wrap">
                                <span class="body-1" v-text="(i + 1) + '. Variation'"></span>
                                <v-spacer></v-spacer>
                                <v-btn icon
                                    :disabled="!edit_relations.variations[i]"
                                    @click="EditRelation('variations', i)"
                                >
                                    <v-icon>edit</v-icon>
                                </v-btn>
                                <v-btn icon class="ml-3" @click="DeleteRelation ('variations', i)" ><v-icon>delete</v-icon></v-btn>
                            </div>
                        </v-col>

                        <v-col cols="12" sm="6" v-for="record in ['de', 'en']" :key="record">
                            <v-textarea dense outlined filled clearable no-resize
                                v-if="!edit_relations.variations[i]"
                                :rows="2"
                                v-model="item.variations[i][record]"
                                :label="'Description ('+record.toUpperCase()+')'"
                                prepend-icon="notes"
                                counter=21845
                            />

                            <div v-else class="d-flex component-wrap align-start mb-2">
                                <v-icon class="mr-5">notes</v-icon>
                                <div>
                                    <div class="font-weight-thin text-uppercase" v-text="'Description ('+record.toUpperCase()+')'"></div>
                                    {{ item.variations[i][record] ? item.variations[i][record] : '--'}}
                                </div>
                            </div>
                        </v-col>

                        <v-col cols="12" sm="12" class="mt-n5">
                            <v-textarea dense outlined filled clearable no-resize
                                v-if="!edit_relations.variations[i]"
                                :rows="2"
                                v-model="item.variations[i].comment"
                                label="Comment"
                                prepend-icon="drag_handle"
                                counter=21845
                            />

                            <div v-else class="d-flex component-wrap align-start mb-4">
                                <v-icon class="mr-5">drag_handle</v-icon>
                                <div>
                                    <div class="font-weight-thin text-uppercase" v-text="'Comment'"></div>
                                    {{ item.variations[i].comment ? item.variations[i].comment : '--' }}
                                </div>
                            </div>
                        </v-col>

                    </v-row>
                </div>

                <!-- Find Circumstances -->
                <div v-if="entity === 'coins'">
                    <subheader class="mb-3" :label="labels.find_circumstances"></subheader>

                    <!-- IF coins -->
                    <v-row>
                        <v-col v-for="(key) in [{ k: 'findspot', i: 'pin_drop'}, { k: 'hoard', i: 'grain'}]" :key="key.k" cols="12" sm="6">
                            <InputForeignKey
                                :entity="key.k + 's'"
                                :label="labels[key.k]"
                                :icon="key.i"
                                :selected="item[key.k]"
                                v-on:select="(emit) => { item[key.k] = emit }"
                            />
                        </v-col>
                    </v-row>
                </div>

                <!-- IF types -->
                <div v-else>
                    <v-row>
                        <v-col  v-for="(key) in [{ k: 'findspots', i: 'pin_drop'}, { k: 'hoards', i: 'grain'}]" :key="key.k" cols="12" sm="6">
                            <!-- Header -->
                            <subheader
                                :label="labels[key.k]"
                                :count="item[key.k]"
                                add
                                class="mb-5"
                                v-on:add="AddRelation(key.k, { id: null })"
                            />

                            <!-- Content -->
                            <div>
                                <div class="mt-n3" v-for="(iterator, i) in item[key.k]" :key="i">
                                    <!-- Input -->
                                    <div v-if="!edit_relations[key.k][i]" class="d-flex component-wrap">
                                        <InputForeignKey
                                            :entity="key.k"
                                            :label="(i + 1) + '. ' + labels[key.k].slice(0, -1)"
                                            :icon="key.i"
                                            :selected="item[key.k][i].id"
                                            v-on:select="(emit) => { item[key.k][i].id = emit }"
                                        />
                                        <v-spacer />
                                        <v-btn icon class="ml-3" @click="DeleteRelation(key.k, i)" ><v-icon>delete</v-icon></v-btn>
                                    </div>

                                    <!-- String -->
                                    <div v-else class="d-flex component-wrap align-center mb-7 mt-3">
                                        <v-icon class="mr-5">link</v-icon>
                                        <div v-html="edit_relations[key.k][i]"></div>
                                        <v-spacer />
                                        <v-btn icon
                                            :disabled="!edit_relations[key.k][i]"
                                            @click="eEditRelation(key.k, i)"
                                        >
                                            <v-icon>edit</v-icon>
                                        </v-btn>
                                        <v-btn icon class="ml-3" @click="DeleteRelation(key.k, i)" ><v-icon>delete</v-icon></v-btn>
                                    </div>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                </div>
            </div>


            <!-- Obverse / Reverse ----------------------------------------- -------------------------------------------- -->
            <div v-else-if="section === 'obverse' || section === 'reverse'" style="width: 100%" >
                <!-- Legends / Designs -->
                <div :key="section">
                    <subheader :label="labels[section] + ' ' + labels.depiction" class="mb-7" />

                    <v-row>
                        <v-col cols="12" sm="12" v-for="record in ['legend', 'design']" :key="record" class="mt-n5">
                            <div :class="wraped">
                                <InputForeignKey
                                    :entity="record + 's'"
                                    :label="labels[section] + ' ' + labels[record]"
                                    :icon="record === 'legend' ? 'translate' : 'notes'"
                                    :sk="record === 'legend' ? 'el_uc_adv' : null"
                                    :selected="item[section.slice(0, 1) + '_' + record]"
                                    :conditions="[{ side: section.slice(0, 1) }]"
                                    :inherited="inherited[section.slice(0, 1) + '_' + record] === 1"
                                    :disabled="item[section.slice(0, 1) + '_die'] ? (labels[record] + ' set by Die') : false"
                                    style="width: 100%"
                                    v-on:select="(emit) => { item[section.slice(0, 1) + '_' + record] = emit }"
                                />
                                <inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited[section.slice(0, 1) + '_' + record]"
                                    v-on:click="confirmInheritance(section.slice(0, 1) + '_' + record)"
                                />
                            </div>
                        </v-col>

                        <!-- Die -->
                        <v-col v-if="entity === 'coins'" cols="12" sm="6" class="mt-n5">
                            <InputForeignKey
                                :entity="'dies'"
                                :label="(section === 'obverse' ? 'Ob' : 'Re')+'verse Die'"
                                icon="gavel"
                                :selected="item[section.slice(0, 1) + '_die']"
                                :conditions="[{ side: section.slice(0, 1) }]"
                                v-on:select="(emit) => { item[section.slice(0, 1)+'_die'] = emit }"
                            />
                        </v-col>
                    </v-row>
                </div>

                <!-- Symbols / Monograms -->
                <v-row no-gutters>
                    <v-col
                        cols=12
                        xl=6
                        v-for="(record, r) in ['symbols', 'monograms']"
                        :key="record"
                        :class="$vuetify.breakpoint.lgAndDown ? '' : (r === 0 ? 'pr-3' : 'pl-3')"
                    >
                        <!-- Header -->
                        <subheader
                            :label="labels[section] + ' ' + labels[record]"
                            :count="item[section.slice(0, 1) + '_' + record]"
                            add
                            :inherited="{ vif: entity === 'coins', disabled: !inherited.id_type, status: inherited[section.slice(0, 1) + '_' + record] }"
                            class="mb-5"
                            v-on:add="AddRelation((section.slice(0, 1) + '_' + record), {id: null, position: null, side: section.slice(0, 1) === 'o' ? 0 : 1})"
                            v-on:inherit="confirmInheritance(section.slice(0, 1) + '_' + record)"
                        />

                        <!-- Content -->
                        <div v-for="(iterator, i) in item[section.slice(0, 1) + '_' + record]" :key="i" class="mt-n3">
                            <!-- Input -->
                            <v-row v-if="!edit_relations[section.slice(0, 1) + '_' + record][i]">
                                <v-col cols="12" :sm="$vuetify.breakpoint.lgAndDown ? 8 : 6">
                                    <InputForeignKey
                                        :entity="record"
                                        :label="(i + 1)+'. '+(section == 'obverse' ? 'Ob' : 'Re') + 'verse ' + (record === 'monograms' ? 'Monogram' : 'Symbol')"
                                        :icon="record == 'monograms' ? 'functions' : 'flare'"
                                        :selected="item[section.slice(0, 1) + '_' + record][i].id"
                                        :inherited="inherited[section.slice(0, 1) + '_' + record] === 1"
                                        style="width: 100%"
                                        v-on:select="(emit) => { item[section.slice(0, 1) + '_' + record][i].id = emit }"
                                    />
                                </v-col>

                                <v-col cols="12" :sm="$vuetify.breakpoint.lgAndDown ? 4 : 6">
                                    <div class="d-flex component-wrap">
                                        <InputForeignKey
                                            entity="positions"
                                            label="Position"
                                            icon="motion_photos_on"
                                            :selected="item[section.slice(0, 1) + '_' + record][i].position"
                                            :inherited="inherited[section.slice(0,1) + '_' + record] === 1"
                                            style="width: 100%"
                                            v-on:select="(emit) => { item[section.slice(0, 1) + '_' + record][i].position = emit }"
                                        />

                                        <v-btn icon
                                            class="ml-3"
                                            :disabled="inherited[section.slice(0, 1) + '_' + record] === 1"
                                            @click="DeleteRelation((section.slice(0, 1) + '_' + record), i)"
                                        >
                                            <v-icon>delete</v-icon>
                                        </v-btn>
                                    </div>
                                </v-col>
                            </v-row>

                            <!-- String -->
                            <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                                <div class="body-2 mr-5" v-text="(i + 1) + '.'"></div>
                                <div
                                    style="width: 100%"
                                    v-html="edit_relations[section.slice(0, 1) + '_' + record][i]"
                                ></div>
                                <v-btn icon
                                    :disabled="inherited[section.slice(0, 1) + '_' + record] === 1 || !edit_relations[section.slice(0, 1) + '_' + record][i]"
                                    @click="EditRelation(section.slice(0, 1) + '_' + record, i)"
                                >
                                    <v-icon>edit</v-icon>
                                </v-btn>
                                <v-btn icon class="ml-3"
                                    :disabled="inherited[section.slice(0, 1) + '_' + record] === 1"
                                    @click="DeleteRelation((section.slice(0, 1) + '_' + record), i)"
                                >
                                    <v-icon>delete</v-icon>
                                </v-btn>
                            </div>
                        </div>
                    </v-col>
                </v-row>

                <!-- Controlmarks -->
                <div v-if="entity === 'coins'" class="mb-2">
                    <!-- Header -->
                    <subheader
                        :label="labels[section] + ' ' + labels.controlmarks"
                        :count="item[section.slice(0, 1) + '_controlmarks']"
                        add
                        class="mb-5"
                        v-on:add="AddRelation((section.slice(0, 1) + '_controlmarks'), {
                            id: null,
                            count: null,
                            side: section.slice(0, 1) === 'o' ? 0 : 1
                        })"
                    />

                    <!-- Content -->
                    <div v-for="(iterator, i) in item[section.slice(0, 1) + '_controlmarks']" :key="i" class="mt-n3">
                        <v-row v-if="!edit_relations[section.slice(0, 1) + '_controlmarks'][i]">

                            <v-col cols="12" sm="8">
                                <InputForeignKey
                                    entity="controlmarks"
                                    :label="(i + 1) + '. ' + (section === 'obverse' ? 'Ob' : 'Re')+'verse Controlmark'"
                                    icon="control_point"
                                    :selected="item[section.slice(0, 1) + '_controlmarks'][i].id"
                                    v-on:select="(emit) => { item[section.slice(0, 1) + '_controlmarks'][i].id = emit }"
                                />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <div class="d-flex component-wrap">
                                    <v-text-field dense filled outlined clearable
                                        v-model="item[section.slice(0, 1) + '_controlmarks'][i].count"
                                        prepend-icon="filter_1"
                                        label="count"
                                        :rules="[rules.numeric_nz]"
                                    ></v-text-field>

                                    <v-btn icon
                                        class="ml-3"
                                        @click="DeleteRelation((section.slice(0, 1) + '_controlmarks'), i)"
                                    ><v-icon>delete</v-icon></v-btn>
                                </div>
                            </v-col>
                        </v-row>

                        <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                            <div class="body-2 mr-5" v-text="(i + 1) + '.'"></div>
                            <div
                                style="width: 100%"
                                v-html="edit_relations[section.slice(0, 1) + '_controlmarks'][i]"
                            ></div>
                            <v-btn icon
                                :disabled="!edit_relations[section.slice(0, 1) + '_controlmarks'][i]"
                                @click="EditRelation(section.slice(0, 1) + '_controlmarks', i)"
                            >
                                <v-icon>edit</v-icon>
                            </v-btn>
                            <v-btn icon class="ml-3"
                                @click="DeleteRelation((section.slice(0, 1) + '_controlmarks'), i)"
                            >
                                <v-icon>delete</v-icon>
                            </v-btn>
                        </div>
                    </div>
                </div>

                <!-- Countermark / Undertype -->
                <v-row v-if="entity === 'coins'" no-gutters>
                    <v-col
                        cols=12
                        xl=6
                        v-for="(record, r) in ['countermark', 'undertype']"
                        :key="record"
                        :class="$vuetify.breakpoint.lgAndDown ? '' : (r === 0 ? 'pr-3' : 'pl-3')"
                    >
                        <subheader :label="labels[section] + ' ' + labels[record]" class="mb-2"></subheader>

                        <v-row>
                            <v-col cols="12" sm="6" v-for="(lang) in ['de', 'en']" :key="lang">
                                <v-textarea dense outlined filled clearable no-resize
                                    rows="2"
                                    v-model="item[section.slice(0, 1) + '_' + record + '_' + lang]"
                                    :label="labels['description'] + ' (' + lang.toUpperCase() + ')'"
                                    prepend-icon="notes"
                                    counter=21845
                                ></v-textarea>
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
            </div>


            <!-- References ----------------------------------------- -------------------------------------------- -->
            <div v-else-if="section === 'references'" style="width: 100%">
                <!-- Citations and Literature -->
                <div v-for="record in ['citations', 'literature']" :key="record">
                    <!-- Header -->
                    <subheader
                        :label="labels[record]"
                        :count="item[record]"
                        add
                        class="mb-5"
                        v-on:add="AddRelation (record, {
                            id: null,
                            page: null,
                            number: null,
                            plate: null,
                            picture: null,
                            annotation: null,
                            comment_de: null,
                            comment_en: null,
                            this: record == 'citations' ? 1 : 0
                        })"
                    />

                    <!-- Content -->
                    <div class="mt-n3" v-for="(iterator, i) in item[record]" :key="i">
                        <!-- Input -->
                        <v-row v-if="!edit_relations[record][i]">

                            <v-col cols="12" sm="12" class="mb-n3 mt-1">
                                <div class="d-flex component-wrap">
                                    <span class="body-1" v-text="(i+1)+'. Reference'"></span>
                                    <v-spacer></v-spacer>
                                    <v-btn icon class="ml-3" @click="DeleteRelation (record, i)" ><v-icon>delete</v-icon></v-btn>
                                </div>
                            </v-col>

                            <v-col cols="12" sm="12">
                                <InputForeignKey
                                    entity="references"
                                    label="Title"
                                    icon="menu_book"
                                    :selected="item[record][i].id"
                                    v-on:select="(emit) => { item[record][i].id = emit }"
                                />
                            </v-col>

                            <v-col cols="12" sm="2" v-for="q in ['page', 'number', 'plate', 'picture', 'annotation']" :key="q">
                                <v-text-field dense outlined filled clearable class="mt-n5"
                                    v-model="item[record][i][q]"
                                    :label="q.slice(0, 1).toUpperCase() + q.slice(1)"
                                    prepend-icon="south_east"
                                />
                            </v-col>

                            <v-col cols="12" sm="6">
                                <v-textarea dense outlined filled clearable no-resize
                                    height="100"
                                    v-model="item[record][i].comment_de"
                                    label="Comment (DE)"
                                    prepend-icon="notes"
                                    counter=21845
                                />
                            </v-col>

                            <v-col cols="12" sm="6">
                                <v-textarea dense outlined filled clearable no-resize
                                    height="100"
                                    v-model="item[record][i].comment_en"
                                    label="Comment (EN)"
                                    prepend-icon="notes"
                                    counter=21845
                                />
                            </v-col>
                        </v-row>

                        <!-- String -->
                        <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                            <div class="body-2 mr-5" v-text="(i + 1) + '.'"></div>
                            <div
                                class="pr-7"
                                style="width: 100%"
                                v-html="edit_relations[record][i]"
                            />
                            <v-btn icon
                                :disabled="!edit_relations[record][i]"
                                @click="EditRelation(record, i)"
                            >
                                <v-icon>edit</v-icon>
                            </v-btn>
                            <v-btn icon class="ml-3" @click="DeleteRelation(record, i)" ><v-icon>delete</v-icon></v-btn>
                        </div>
                    </div>
                </div>

                <!-- Web Links -->
                <div>
                    <!-- Header -->
                    <subheader
                        :label="labels.links"
                        :count="item.links"
                        add
                        class="mb-5"
                        v-on:add="AddRelation ('links', {
                            id: null,
                            semantics: null,
                            comment_de: null,
                            comment_en: null
                        })"
                    />

                    <!-- Content -->
                    <div>
                        <div class="mt-n3" v-for="(iterator, i) in item.links" :key="i">
                            <!-- Input -->
                            <v-row v-if="!edit_relations.links[i]">

                                <v-col cols="12" sm="12" class="mb-n3 mt-1">
                                    <div class="d-flex component-wrap">
                                        <span class="body-1" v-text="(i+1)+'. Reference'"></span>
                                        <v-spacer></v-spacer>
                                        <v-btn icon class="ml-3" @click="DeleteRelation ('links', i)" ><v-icon>delete</v-icon></v-btn>
                                    </div>
                                </v-col>

                                <v-col cols="12" sm="8">
                                    <v-text-field dense outlined filled clearable
                                        v-model="item.links[i].link"
                                        prepend-icon="link"
                                        label="URL"
                                        :rules="[rules.link]"
                                        @click:prepend="$root.openInNewsection(item.links[i].link)"
                                    />
                                </v-col>

                                <v-col cols="12" sm="4">
                                    <v-select dense outlined filled
                                        v-model="item.links[i].semantics"
                                        label="Semantics"
                                        :items="$store.state.lists.dropdowns.link_semantics"
                                    />
                                </v-col>

                                <v-col cols="12" sm="6">
                                    <v-textarea dense outlined filled clearable no-resize
                                        height="100"
                                        v-model="item.links[i].comment_de"
                                        label="Comment (DE)"
                                        prepend-icon="notes"
                                        counter=21845
                                    />
                                </v-col>

                                <v-col cols="12" sm="6">
                                    <v-textarea dense outlined filled clearable no-resize
                                        height="100"
                                        v-model="item.links[i].comment_en"
                                        label="Comment (EN)"
                                        prepend-icon="notes"
                                        counter=21845
                                    />
                                </v-col>
                            </v-row>

                            <!-- String -->
                            <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                                <div class="body-2 mr-5" v-text="(i + 1) + '.'"></div>
                                <div
                                    class="pr-7"
                                    style="width: 100%"
                                    v-html="edit_relations.links[i]"
                                ></div>
                                <v-btn icon
                                    :disabled="!edit_relations.links[i]"
                                    @click="EditRelation('links', i)"
                                >
                                    <v-icon>edit</v-icon>
                                </v-btn>
                                <v-btn icon class="ml-3" @click="DeleteRelation('links', i)" ><v-icon>delete</v-icon></v-btn>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Literature from Type -->
                <div v-if="entity === 'coins'">
                    <!-- Header -->
                    <subheader
                        :label="labels.literature_type"
                        :count="item.literature_type"
                        class="mb-1"
                    />

                    <div class="d-flex component-wrap align-end mb-6 caption">
                        <div>These titles are linked on the related type(s).</div>
                        <v-spacer></v-spacer>
                        <div>To edit these records check the linked types.</div>
                    </div>

                    <!-- Content -->
                    <div>
                        <div v-for="(iterator, i) in item_data.type_literature" :key="i" class="d-flex component-wrap align-center mb-7 mt-3">
                            <v-icon class="mr-5">menu_book</v-icon>
                            <div
                                class="pr-7"
                                style="width: 100%"
                                v-html="to_string.reference(iterator, l)"
                            />
                        </div>
                    </div>
                </div>

            </div>


            <!-- Individuals ----------------------------------------- -------------------------------------------- -->
            <div v-else-if="section === 'individuals'" style="width: 100%">
                <!-- Header -->
                <subheader
                    :label="labels.persons"
                    :count="item.persons"
                    add
                    :inherited="{ vif: entity === 'coins', disabled: !inherited.id_type, status: inherited.persons }"
                    class="mb-5"
                    v-on:add="AddRelation('persons', { id: null, function: null })"
                    v-on:inherit="confirmInheritance('persons')"
                />

                <!-- Content -->
                <div v-for="(iterator, i) in item.persons" :key="i" class="mt-n3">
                    <!-- Input -->
                    <v-row v-if="!edit_relations.persons[i]">

                        <v-col cols="12" sm="8">
                            <InputForeignKey
                                entity="persons"
                                :label="(i + 1) + '. Person'"
                                icon="emoji_people"
                                sk="el_uc_adv"
                                :selected="item.persons[i].id"
                                :inherited="inherited.persons === 1"
                                style="width: 100%"
                                v-on:select="(emit) => { item.persons[i].id = emit }"
                            />
                        </v-col>

                        <v-col cols="12" sm="4">
                            <div class="d-flex component-wrap">
                                <InputForeignKey
                                    entity="functions"
                                    label="Function"
                                    icon="event_seat"
                                    :selected="item.persons[i].function"
                                    :inherited="inherited.persons === 1"
                                    style="width: 100%"
                                    v-on:select="(emit) => { item.persons[i].function = emit }"
                                />

                                <v-btn icon class="ml-3"
                                    :disabled="inherited.persons === 1"
                                    @click="DeleteRelation('persons', i)"
                                >
                                    <v-icon>delete</v-icon>
                                </v-btn>
                            </div>
                        </v-col>
                    </v-row>

                    <!-- String -->
                    <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                        <div class="body-2 mr-5" v-text="(i + 1) + '.'"></div>
                        <div
                            class="pr-7"
                            style="width: 100%"
                            v-html="edit_relations.persons[i]"
                        ></div>
                        <v-btn icon
                            :disabled="inherited.persons === 1 || !edit_relations.persons[i]"
                            @click="EditRelation('persons', i)"
                        >
                            <v-icon>edit</v-icon>
                        </v-btn>
                        <v-btn icon class="ml-3"
                            :disabled="inherited.persons === 1"
                            @click="DeleteRelation('persons', i)"
                        >
                            <v-icon>delete</v-icon>
                        </v-btn>
                    </div>
                </div>

            </div>


            <!-- Miscellaneous ----------------------------------------- -------------------------------------------- -->
            <div v-else-if="section === 'miscellaneous'" style="width: 100%">
                <!-- Remarks -->
                <div>
                    <!-- Header -->
                    <subheader :label="labels.remarks" class="mb-5"></subheader>

                    <!-- Description and Source Link -->
                    <v-row>
                        <v-col cols="12" sm="12">
                            <v-text-field dense outlined filled clearable
                                v-model="item.source"
                                label="Source Link"
                                prepend-icon="link"
                                hint="The actual source of the imported data. Please provide any other links to the References Tab."
                                :rules="[rules.link]"
                                @click:prepend="() => {
                                    if (item.source) {
                                        const source = item.source.trim()
                                        if (source.slice(0, 7) === 'http://' || source.slice(0, 8) === 'https://') { $root.openInNewTab(source) }
                                    }
                                }"
                            />

                            <v-textarea dense outlined filled clearable no-resize
                                :rows="2"
                                v-model="item.description"
                                label="Private Description"
                                prepend-icon="notes"
                                counter=21845
                                class="mt-2"
                            />

                            <v-text-field
                                v-if="entity === 'types'"
                                dense outlined filled clearable
                                v-model="item.name"
                                label="Private Name"
                                prepend-icon="label"
                                class="mt-2"
                            />
                        </v-col>

                        <!-- Pecularities -->
                        <v-col v-for="record in ['de', 'en']" :key="'p' + record" cols=12 sm=6>
                            <v-textarea dense outlined filled clearable no-resize
                                :rows="2"
                                v-model="item['pecularities_' + record]"
                                :label="$root.label('pecularities') + ' (' + record.toUpperCase() + ')'"
                                prepend-icon="brightness_7"
                                counter=21845
                            />
                        </v-col>

                        <!-- Comments -->
                        <v-col v-for="record in ['de', 'en']" :key="'c' + record" cols=12 sm=6 xl=4>
                            <div :class="wraped">
                                <v-textarea dense outlined filled clearable no-resize
                                    :rows="4"
                                    v-model="item['comment_public_' + record]"
                                    :label="$root.label('comment_public') + ' (' + record.toUpperCase() + ')'"
                                    prepend-icon="chat_bubble_outline"
                                    counter=21845
                                    :DIS-disabled="inherited.comment_public === 1"
                                    :DIS-hint="inherited.comment_public === 1 ? 'Value inherited from type' : ''"
                                    :DIS-persistent-hint="inherited.comment_public === 1"
                                />
                                <!--<inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited.comment_public"
                                    v-on:click="confirmInheritance('comment_public')"
                                ></inheritButton>-->
                            </div>
                            <div v-if="entity === 'coins' && item_data.types" class="ml-9 caption">
                                <div v-if="item_data.type_comment && item_data.type_comment[record]" v-text="'Type Comment: ' + item_data.type_comment[record]"></div>
                                <div v-else v-text="'No comments on linked Types'"></div>
                            </div>
                        </v-col>
                        <v-col cols=12 xl=4>
                            <div :class="wraped">
                                <v-textarea dense outlined filled clearable no-resize
                                    :rows="$vuetify.breakpoint.xl ? 4 : 2"
                                    v-model="item.comment_private"
                                    :label="$root.label('comment_private')"
                                    prepend-icon="chat_bubble"
                                    counter=21845
                                    :DIS-disabled="inherited.comment_private === 1"
                                    :DIS-hint="inherited.comment_private === 1 ? 'Value inherited from type' : ''"
                                    :DIS-persistent-hint="inherited.comment_private === 1"
                                />
                                <!--<inheritButton
                                    v-if="entity === 'coins'"
                                    :disabled="!inherited.id_type"
                                    :inherited="inherited.comment_private"
                                    v-on:click="confirmInheritance('comment_private')"
                                ></inheritButton>-->
                            </div>
                            <div v-if="entity === 'coins' && item_data.types" class="ml-9 caption">
                                <div v-if="item_data.dbi && item_data.dbi.type_comment" v-text="'Type Comment: ' + item_data.dbi.type_comment"></div>
                                <div v-else v-text="'No comments on linked Types'"></div>
                            </div>
                        </v-col>
                    </v-row>
                </div>

                <!-- Groups -->
                <div class="mt-2">
                    <subheader
                        :label="labels.objectgroups"
                        :count="item.groups"
                        add
                        class="mb-5"
                        v-on:add="AddRelation('groups', { id: null, name: null, comment: null })"
                    />

                    <div class="mt-n3" v-for="(iterator, i) in item.groups" :key="i">
                        <div v-if="!edit_relations.groups[i]" class="d-flex component-wrap">
                            <InputForeignKey
                                entity="objectgroups"
                                label="Object Group"
                                icon="control_camera"
                                :selected="item.groups[i].id"
                                v-on:select="(emit) => { item.groups[i].id = emit }"
                            />
                            <v-spacer></v-spacer>
                            <v-btn icon class="ml-3" @click="DeleteRelation('groups', i)" ><v-icon>delete</v-icon></v-btn>
                        </div>

                        <div v-else class="d-flex component-wrap align-center mb-7 mt-3">
                            <v-icon class="mr-5">link</v-icon>
                            <div v-html="edit_relations.groups[i]"></div>
                            <v-spacer></v-spacer>
                            <v-btn icon
                                :disabled="!edit_relations.groups[i]"
                                @click="EditRelation('groups', i)"
                            >
                                <v-icon>edit</v-icon>
                            </v-btn>
                            <v-btn icon class="ml-3" @click="DeleteRelation('groups', i)" ><v-icon>delete</v-icon></v-btn>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Data -->
            <v-expand-transition>
                <div v-if="showImported" class="pa-5" style="width: 100%">
                    <imported :data="item_data.dbi ? item_data.dbi.submitted : {}" />
                </div>
            </v-expand-transition>

        </sheet-template>
    </div>


    <!-- Dialog Inheritance ---------------------------------------------------------- -->
    <v-dialog v-model="dialog_inheritance.active" persistent scrollable max-width="600px">
        <v-card tile>

            <!-- System Header -->
            <dialogbartop
                :icon="'sync' + (inherited[dialog_inheritance.key] === 1 ? '_disabled' : '')"
                :text="(inherited[dialog_inheritance.key] === 1 ? 'Dea' : 'A')+'ctivating coin-type-synchronisation'"
                v-on:close="closeDialogInheritance()"
            />

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
                <v-spacer />
                <v-btn text class="mr-5" @click="closeDialogInheritance()">
                    <v-icon>clear</v-icon>
                </v-btn>
                <v-btn text @click="toggleInheritance(dialog_inheritance.key)">
                    <v-icon>done</v-icon>
                </v-btn>
                <v-spacer />
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
                    />
                </div>
            </div>
        </template>
    </simpleSelectDialog>

</div>
</template>



<script> // ------------------------------------------------------------------------------------------------------------------------------------

import editTabs         from './editTabs.vue'
import editImages       from './editImages.vue'
import editToolbar      from './editToolbar.vue'

import ItemLink         from './../modules/ItemSingleLink.vue'
import inheritButton    from './../modules/inheritButton.vue'
import imported         from './../modules/importContent.vue'

export default {
    components: {
        editTabs,
        editImages,
        editToolbar,

        ItemLink,
        inheritButton,
        imported
    },

    data () {
        return {
            loading:            false,
            refresh:            0,
            gallery_refresh:    0,

            //tab:                null,
            //imgId:              null,
            //img_refresh:        0,
            showImported:      false,

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
        id: function () { this.SetItem() },
        entity: function () { this.SetItem() },
    },

    created () {
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
            const split = emit.key.split('_');
            this.item.images[split[0]][split[1]].path = emit.id;
            this.item.images[split[0]][split[1]].link = emit.id;
            this.files_dialog = { active: false, key: null, id: null };
        },

        // JK: Upload Dialog
        ImgUpload (emit) {
            const split = emit.key.split('_')
            this.item.images[split[0]][split[1]].path = emit.url
            this.item.images[split[0]][split[1]].link = emit.url
            this.upload_dialog = { active: false, key: null }
        },

        // Image Links Methods
        ImgLinkDialog (index, side) {
            let input = this.item.images[index][side].path;

            // Check if set input is a valid link - if not set input to null
            if (input && input.slice(0, 4) !== 'http') input = null

            this.link_dialog = { active: true, index, side, input }
        },

        SetLink () {
            this.item.images[this.link_dialog.index][this.link_dialog.side].link = this.link_dialog.input.trim()
            this.item.images[this.link_dialog.index][this.link_dialog.side].path = this.link_dialog.input.trim()
            this.CloseLink()
        },

        CloseLink () {
            this.link_dialog = { active: false, index: null, side: null, input: null }
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
