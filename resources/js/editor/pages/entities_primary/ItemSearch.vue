<template>
<div>

    <!-- JK: Tools -->
    <v-card tile class="mb-5" scrollable>

        <!-- Header -->
        <v-btn
            tile
            x-large
            block
            color="sysbar"
            class="d-flex component-wrap justify-space-between"
            :disabled="!items [0]"
            :depressed="tools || !items [0] ? false : true"
            @click="tools = !tools"
        >
            <div
                class="headline d-flex justify-start"
                v-text="'Search ' + labels[entity]"
            ></div>
            <div
                v-if="dbi_params.count"
                class="caption text-center"
                style="position: absolute; width: 100%"
                v-text="$editor_format.number(l, dbi_params.count) + ' ' + labels[dbi_params.count > 1 ? entity : entity.slice(0, -1)] + ' found'"
            ></div>
            <v-avatar
                size="36"
                :class="tools || !items [0] ? '' : 'blue_prim mr-n2'"
            >
                <v-icon v-text="tools || !items [0] ? 'expand_less' : 'search'"></v-icon>
            </v-avatar>
        </v-btn>


        <!-- Search ------------------------------------------------- ------------------------------------------------- --------------------------------------------------->
        <v-expand-transition>
            <v-card-text v-if="tools || !items [0]" class="pa-0">
                <v-card tile class="appbar">
                    <v-tabs v-model="tab" show-arrows class="d-flex justify-center" background-color="appbar">
                        <v-tabs-slider></v-tabs-slider>
                        <v-tab style="white-space: nowrap;"
                            v-for="(item, i) in tabs"
                            :key="i"
                            :href="`#tab-${item.value}`"
                        >
                            {{ item.text }}
                        </v-tab>
                    </v-tabs>
                </v-card>

                <v-tabs-items v-model="tab" class="pa-5 transparent">

                    <!-- String Search --------------------------------------------------------------------------------------------------------------------------------- -->
                    <v-tab-item value="tab-0" transition="expand-transition" reverse-transition="expand-transition">
                        <v-row>
                            <!-- Search Field -->
                            <v-col cols=12>
                                <v-text-field outlined filled clearable dense
                                    v-model="search.q"
                                    :label="$root.label('search_strings')"
                                    prepend-icon="search"
                                    append-icon="keyboard"
                                    class="mt-5 mb-n4"
                                    v-on:keyup.enter="RunSearch()"
                                    v-on:click:append="searchStringKeyboard = !searchStringKeyboard"
                                ></v-text-field>
                                <v-expand-transition>
                                    <div v-if="searchStringKeyboard" class="pl-10 d-flex justify-center">
                                        <keyboard
                                            :string="search.q"
                                            layout="el_uc_adv"
                                            small
                                            hide_options
                                            v-on:input="(emit) => { search.q = emit }"
                                        ></keyboard>
                                    </div>
                                </v-expand-transition>
                            </v-col>

                            <!-- Options -->
                            <v-col cols=12>
                                <div class="d-flex flex-wrap justify-center align-center mt-n6">
                                    <div class="d-flex mr-9 align-center">
                                        <v-checkbox
                                            v-model="search.qre"
                                            label="REGEX"
                                            class="mr-1"
                                        />
                                        <sup class="body-1"><a href="https://en.wikipedia.org/wiki/Regular_expression#Syntax" target="_blank"> *</a></sup>
                                    </div>

                                    <v-checkbox
                                        v-model="search.qcs"
                                        label="case-sensitive"
                                        class="mr-10"
                                    />

                                    <!-- Excludes -->
                                    <v-menu tile offset-y nudge-bottom="5" :close-on-content-click="false">
                                        <template v-slot:activator="{ on, attrs }">
                                            <div
                                                class="d-flex align-center body-1 mr-9"
                                                style="cursor: pointer"
                                                v-bind="attrs" v-on="on"
                                            >
                                                <v-btn icon class="mr-1"><v-icon v-text="'manage_search'" /></v-btn>
                                                <div v-text="'Included Fields'" />
                                            </div>
                                        </template>

                                        <div style="width: 450px" class="grey_sec d-flex flex-wrap pt-7">
                                            <div
                                                v-for="(key, k) in [
                                                    'period', 'region',
                                                    null,
                                                    'coinage', 'mint', 'ruler', 'tribe',
                                                    null,
                                                    'standard', 'denomination', 'material',
                                                    null,
                                                    'design_o_de', 'design_o_en', 'design_r_de', 'design_r_en',
                                                    null,
                                                    'symbol_o_de', 'symbol_o_en', 'symbol_r_de', 'symbol_r_en',
                                                    null,
                                                    'legend_o', 'legend_r',
                                                    null,
                                                    'persons',
                                                    null,
                                                    'literature', 'objectgroups', 'owner',
                                                    null,
                                                    'pecularities_de', 'pecularities_en',
                                                    'comment_de', 'comment_en'
                                                ]"
                                                :key="key + k"
                                                class="pl-5 pr-5"
                                                :style="'width:' + (key ? 50 : 100) + '%;'"
                                            >
                                                <v-checkbox
                                                    v-if="key"
                                                    :input-value="search.qex ? !search.qex.includes(key) : true"
                                                    :label="key"
                                                    class="mt-n3"
                                                    @click="searchStringIncludeFiled(key)"
                                                />
                                                <v-divider v-else class="mb-4 mt-n3" />
                                            </div>
                                        </div>
                                    </v-menu>

                                    <!-- Filter -->
                                    <v-menu tile offset-y nudge-bottom="5" :close-on-content-click="false">
                                        <template v-slot:activator="{ on, attrs }">
                                            <div
                                                class="d-flex align-center body-1 mr-9"
                                                style="cursor: pointer"
                                                v-bind="attrs" v-on="on"
                                            >
                                                <v-btn icon class="mr-1"><v-icon v-text="'filter_alt'" /></v-btn>
                                                <div v-text="'Additional Filters'" />
                                            </div>
                                        </template>

                                        <v-card tile class="grey_sec" width="400">
                                            <v-card-text>
                                                <v-row>
                                                    <!-- Date -->
                                                    <v-col cols=12 v-text="'Date'" class="body-1 mb-n2" />
                                                    <v-col cols="6">
                                                        <v-text-field dense outlined filled clearable
                                                            v-model="search.date_start"
                                                            label="Min."
                                                            prepend-icon="last_page"
                                                            v-on:keyup.enter="RunSearch()"
                                                        ></v-text-field>
                                                    </v-col>

                                                    <v-col cols="6">
                                                        <v-text-field dense outlined filled clearable
                                                            v-model="search.date_end"
                                                            label="Max."
                                                            append-outer-icon="first_page"
                                                            v-on:keyup.enter="RunSearch()"
                                                        ></v-text-field>
                                                    </v-col>

                                                    <!-- Weight -->
                                                    <v-col cols=12 v-text="'Weight'" class="mt-n5 mb-n2 body-1" />
                                                    <v-col cols="6" v-if="entity == 'coins'">
                                                        <v-text-field dense outlined filled clearable
                                                            v-model="search.weight_start"
                                                            label="Min."
                                                            prepend-icon="last_page"
                                                            v-on:keyup.enter="RunSearch()"
                                                        ></v-text-field>
                                                    </v-col>

                                                    <v-col cols="6" v-if="entity == 'coins'">
                                                        <v-text-field dense outlined filled clearable
                                                            v-model="search.weight_end"
                                                            label="Max."
                                                            append-outer-icon="first_page"
                                                            v-on:keyup.enter="RunSearch()"
                                                        ></v-text-field>
                                                    </v-col>

                                                    <!-- Diameter -->
                                                    <v-col cols=12 v-text="'Diameter'" class="mt-n5 mb-n2 body-1" />
                                                    <v-col cols="6" v-if="entity == 'coins'">
                                                        <v-text-field dense outlined filled clearable
                                                            v-model="search.diameter_start"
                                                            label="Min."
                                                            prepend-icon="last_page"
                                                            v-on:keyup.enter="RunSearch()"
                                                        ></v-text-field>
                                                    </v-col>

                                                    <v-col cols="6" v-if="entity == 'coins'">
                                                        <v-text-field dense outlined filled clearable
                                                            v-model="search.diameter_end"
                                                            label="Max."
                                                            append-outer-icon="first_page"
                                                            v-on:keyup.enter="RunSearch()"
                                                        ></v-text-field>
                                                    </v-col>

                                                    <!-- Public -->
                                                    <v-col cols=12 class="mt-n5">
                                                        <div v-text="'Publication State'" class="body-1 mb-3" />
                                                        <v-select dense outlined filled
                                                            :items="selects.state_public"
                                                            v-model="search.state_public"
                                                            v-on:keyup.enter="RunSearch()"
                                                        ></v-select>
                                                    </v-col>
                                                </v-row>
                                            </v-card-text>
                                        </v-card>
                                    </v-menu>

                                    <!-- Instructions -->
                                    <v-menu tile offset-y nudge-bottom="5">
                                        <template v-slot:activator="{ on, attrs }">
                                            <div
                                                class="d-flex align-center body-1"
                                                style="cursor: pointer"
                                                v-bind="attrs" v-on="on"
                                            >
                                                <v-btn icon class="mr-1"><v-icon v-text="'help_outline'" /></v-btn>
                                                <div v-text="'Instructions'" />
                                            </div>
                                        </template>

                                        <v-card tile class="grey_sec" width="500">
                                            <v-card-text>
                                                <p>
                                                    Use <b>AND</b>, <b>OR</b> and (<b>AND</b>/<b>OR</b>) <b>NOT</b> to connect several search strings logically.
                                                    (must be written in upper case and seperated by blanks from search strings), e.g. <i>thrace AND NOT byzantium</i>
                                                </p>
                                                <p>
                                                    Blanks are considered as <b>AND</b> by default. Use double quotation marks (<b>""</b>) to mark two or more strings as connected,
                                                    e.g. <i>apollo AND "nach rechts"</i>
                                                </p>
                                                <p>
                                                    Search strings can be restricted to a specific field by adding the key followed by two colons (<b>::</b>), e.g. <i>byzantium AND design::bull</i>
                                                </p>
                                                <p>
                                                    The search scope can be narrowed by excluding fields generally (uncheck the keys in the popup menu). Furthermore you can add filters like date, weight etc.
                                                </p>
                                                <p>
                                                    The search is case insensitive by default ("kopf" will match "Kopf"), check "case-sensitive" for strict case matching.
                                                </p>
                                                <p>
                                                    Click on the keyboard in the right corner of the search field to expand a Greek keyboard.
                                                </p>
                                                <p>
                                                    Check "REGEX" when using <a href="https://en.wikipedia.org/wiki/Regular_expression#Syntax" target="_blank">regular expressions</a>
                                                </p>
                                            </v-card-text>
                                        </v-card>
                                    </v-menu>

                                </div>
                            </v-col>
                        </v-row>
                    </v-tab-item>

                    <!-- Frequently used --------------------------------------------------------------------------------------------------------------------------------- -->
                    <v-tab-item value="tab-1" transition="expand-transition" reverse-transition="expand-transition">
                        <v-row>

                            <!-- JK: ID -->
                            <v-col cols="12" sm="6">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.id"
                                    :label="labels[entity.slice(0, -1)] + ' ID'"
                                    prepend-icon="fingerprint"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <!-- JK: Mint -->
                            <v-col cols="12" sm="6">
                                <SearchForeignKey
                                    entity="mints"
                                    label="Mint"
                                    icon="museum"
                                    :selected="search"
                                    selected_key="id_mint"
                                    v-on:select="(emit) => { search.id_mint = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- JK: Linked to Type/coin -->
                            <v-col cols="12" sm="2">
                                <v-select dense outlined filled
                                    :prepend-icon="entity == 'coins' ? 'blur_circular' : 'copyright'"
                                    :label="'Linked to '+(entity == 'coins' ? 'Type' : 'Coin')+'?'"
                                    v-model="search.state_linked"
                                    :items="selects.state_yes_no"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-select>
                            </v-col>

                            <!-- JK: ID Opposite Entity -->
                            <v-col cols="12" sm="2">
                                <v-text-field dense outlined filled clearable
                                    v-model="search ['id_'+(entity == 'coins' ? 'type' : 'coin')]"
                                    :label="'Linked '+(entity == 'coins' ? 'Type' : 'Coin')+' ID'"
                                    :prepend-icon="entity == 'coins' ? 'blur_circular' : 'copyright'"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <!-- JK: Linked to Type/coin -->
                            <v-col cols="12" sm="2">
                                <v-select dense outlined filled
                                    prepend-icon="sync"
                                    :label="(entity == 'coins' ? 'Is inherited' : 'Inherits to Coins')+'?'"
                                    v-model="search.state_inherited"
                                    :items="selects.state_yes_no"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-select>
                            </v-col>

                            <!-- JK: Creator / Editor -->
                            <v-col cols="12" sm="2">
                                <SearchForeignKey
                                    entity="users"
                                    label="Creator"
                                    icon="person"
                                    :selected="search"
                                    selected_key="id_creator"
                                    v-on:select="(emit) => { search.id_creator = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>
                            <v-col cols="12" sm="2">
                                <SearchForeignKey
                                    entity="users"
                                    label="Editor"
                                    icon="person_outline"
                                    :selected="search"
                                    selected_key="id_editor"
                                    v-on:select="(emit) => { search.id_editor = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- JK: Public -->
                            <v-col cols="12" sm="2">
                                <v-select dense outlined filled
                                    :items="selects.state_public"
                                    v-model="search.state_public"
                                    prepend-icon="publish"
                                    label="Publication State"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-select>
                            </v-col>

                            <v-col cols="12" sm="2">
                                <v-select dense outlined filled
                                    v-model="search.imported"
                                    :items="$store.state.lists.manual.imports"
                                    prepend-icon="arrow_circle_down"
                                    label="Coins Import"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-select>
                            </v-col>

                            <v-col cols="12" sm="2">
                                <SearchForeignKey
                                    entity="objectgroups"
                                    label="Object Group"
                                    icon="control_camera"
                                    :selected="search"
                                    selected_key="id_group"
                                    v-on:select="(emit) => { search.id_group = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>
                        </v-row>
                    </v-tab-item>


                    <!-- General --------------------------------------------------------------------------------------------------------------------------------- -->
                    <v-tab-item value="tab-2" transition="expand-transition" reverse-transition="expand-transition">
                        <v-row>

                            <!-- JK: Has external Comment -->
                            <v-col cols="12" sm="2">
                                <v-select dense outlined filled
                                    v-model="search.state_comment_public"
                                    :items="selects.state_yes_no"
                                    prepend-icon="chat_bubble"
                                    label="Has public Comment?"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-select>
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.comment_public"
                                    label="Public Comment"
                                    prepend-icon="chat_bubble"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <!-- JK: Has internal Comment -->
                            <v-col cols="12" sm="2">
                                <v-select dense outlined filled
                                    v-model="search.state_comment_private"
                                    :items="selects.state_yes_no"
                                    prepend-icon="chat_bubble_outline"
                                    label="Has private Comment?"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-select>
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.comment_private"
                                    label="Private Comment"
                                    prepend-icon="chat_bubble_outline"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <!-- JK: Has Source -->
                            <v-col cols="12" sm="2">
                                <v-select dense outlined filled
                                    v-model="search.state_source"
                                    :items="selects.state_yes_no"
                                    prepend-icon="arrow_circle_down"
                                    label="Has Source?"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-select>
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.source"
                                    label="Source (Import)"
                                    prepend-icon="arrow_circle_down"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <!-- Private Description -->
                            <v-col cols="12" sm="6">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.description_private"
                                    label="Private Description"
                                    prepend-icon="label_important"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" sm="6">
                                <SearchForeignKey
                                    entity="persons"
                                    label="Person"
                                    icon="emoji_people"
                                    :selected="search"
                                    selected_key="id_person"
                                    v-on:select="(emit) => { search.id_person = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- JK: References -->
                            <v-col cols="12" sm="6">
                                <SearchForeignKey
                                    entity="references"
                                    label="Bibliography"
                                    icon="menu_book"
                                    :selected="search"
                                    selected_key="id_reference"
                                    v-on:select="(emit) => { search.id_reference = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- JK: Owner -->
                            <v-col cols="12" sm="6">
                                <SearchForeignKey
                                    entity="owners"
                                    label="Owner"
                                    icon="account_circle"
                                    :selected="search"
                                    selected_key="id_owner"
                                    v-on:select="(emit) => { search.id_owner = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <v-col cols="12" sm="6">
                                <v-text-field dense outlined filled clearable
                                    v-if="entity === 'coins'"
                                    v-model="search.provenience"
                                    label="Provenience"
                                    prepend-icon="play_circle_outline"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                                <v-text-field dense outlined filled clearable
                                    v-if="entity === 'types'"
                                    v-model="search.name_private"
                                    label="Private Name"
                                    prepend-icon="label"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <!-- Collection -->
                            <v-col cols="12" sm="6" v-if="entity === 'coins'">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.collection_nr"
                                    label="Collection Nr."
                                    prepend-icon="bookmarks"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" v-if="entity === 'coins'">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.plastercast_nr"
                                    label="Plastercast Nr."
                                    prepend-icon="bookmarks"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <!-- JK: Hoards -->
                            <v-col cols="12" sm="6">
                                <SearchForeignKey
                                    entity="hoards"
                                    label="Hoard"
                                    icon="grain"
                                    :selected="search"
                                    selected_key="id_hoard"
                                    v-on:select="(emit) => { search.id_hoard = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- JK: Findspots -->
                            <v-col cols="12" sm="6">
                                <SearchForeignKey
                                    entity="findspots"
                                    label="Findspot"
                                    icon="pin_drop"
                                    :selected="search"
                                    selected_key="id_findspot"
                                    v-on:select="(emit) => { search.id_findspot = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                        </v-row>
                    </v-tab-item>

                    <!-- Production --------------------------------------------------------------------------------------------------------------------------------- -->
                    <v-tab-item value="tab-3" transition="expand-transition" reverse-transition="expand-transition">
                        <v-row>

                            <!-- JK: Mint -->
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="mints"
                                    label="Mint"
                                    icon="museum"
                                    :selected="search"
                                    selected_key="id_mint"
                                    v-on:select="(emit) => { search.id_mint = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- Region -->
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="regions"
                                    label="Region"
                                    icon="terrain"
                                    :selected="search"
                                    selected_key="id_region"
                                    v-on:select="(emit) => { search.id_region = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- Authority -->
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="authorities"
                                    label="Type of Coinage"
                                    icon="toll"
                                    :selected="search"
                                    selected_key="id_authority"
                                    v-on:select="(emit) => { search.id_authority = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- Issuer
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="persons"
                                    label="Issuer"
                                    icon="record_voice_over"
                                    :selected="search"
                                    selected_key="id_issuer"
                                    v-on:select="(emit) => { search.id_issuer = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col> -->

                            <!-- Authority Person -->
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="persons"
                                    :conditions="[{ authority: 1 }]"
                                    label="Authority Person"
                                    icon="how_to_reg"
                                    :selected="search"
                                    selected_key="id_authority_person"
                                    v-on:select="(emit) => { search.id_authority_person = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- Authority Group -->
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="tribes"
                                    label="Authority Group"
                                    icon="sports_kabaddi"
                                    :selected="search"
                                    selected_key="id_authority_group"
                                    v-on:select="(emit) => { search.id_authority_group = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <v-col cols="12" sm="12" class="mt-n5 mb-1">
                                <v-divider></v-divider>
                            </v-col>

                            <!-- Materials -->
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="materials"
                                    label="Material"
                                    icon="palette"
                                    :selected="search"
                                    selected_key="id_material"
                                    v-on:select="(emit) => { search.id_material = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- Denomination -->
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="denominations"
                                    label="Denomination"
                                    icon="bubble_chart"
                                    :selected="search"
                                    selected_key="id_denomination"
                                    v-on:select="(emit) => { search.id_denomination = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- Standard -->
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="standards"
                                    label="Standard"
                                    icon="group_work"
                                    :selected="search"
                                    selected_key="id_standard"
                                    v-on:select="(emit) => { search.id_standard = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <v-col cols="12" sm="2" v-if="entity == 'coins'">
                                <v-text-field dense outlined filled
                                    v-model="search.weight_start"
                                    label="Weight Min."
                                    prepend-icon="radio_button_unchecked"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" sm="2" v-if="entity == 'coins'">
                                <v-text-field dense outlined filled
                                    v-model="search.weight_end"
                                    label="Weight Max."
                                    prepend-icon="lens"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" sm="2" v-if="entity == 'coins'">
                                <v-text-field dense outlined filled
                                    v-model="search.diameter_start"
                                    label="Diameter Min."
                                    prepend-icon="hdr_weak"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" sm="2" v-if="entity == 'coins'">
                                <v-text-field dense outlined filled
                                    v-model="search.diameter_end"
                                    label="Diameter Max."
                                    prepend-icon="hdr_strong"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" sm="12" class="mt-n5 mb-1">
                                <v-divider></v-divider>
                            </v-col>

                            <!-- Epoch -->
                            <v-col cols="12" sm="4">
                                <SearchForeignKey
                                    entity="periods"
                                    label="Epoch"
                                    icon="watch_later"
                                    :selected="search"
                                    selected_key="id_period"
                                    v-on:select="(emit) => { search.id_period = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- Date -->
                            <v-col cols="12" sm="2">
                                <v-text-field dense outlined filled
                                    v-model="search.date_start"
                                    label="Date Start"
                                    prepend-icon="first_page"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" sm="2">
                                <v-text-field dense outlined filled
                                    v-model="search.date_end"
                                    label="Date End"
                                    prepend-icon="last_page"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                        </v-row>
                    </v-tab-item>


                    <!-- Obverse / Reverse --------------------------------------------------------------------------------------------------------------------------------- -->
                    <v-tab-item value="tab-4" transition="expand-transition" reverse-transition="expand-transition">
                        <v-row v-for="key in ['o', 'r']" :key="key">

                            <v-col cols="12" sm="12" class="mt-n5 mb-1" v-if="key == 'r'">
                                <v-divider></v-divider>
                            </v-col>

                            <!-- JK: Designs -->
                            <v-col cols="12" sm="6">
                                <v-text-field filled dense outlined clearable
                                    v-model="search [key+'_design']"
                                    :label="(key == 'o' ? 'Ob' : 'Re')+'verse Design Freetext'"
                                    prepend-icon="notes"
                                    v-on:keyup.enter="RunSearch()"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" sm="6">
                                <SearchForeignKey
                                    :entity="'designs'"
                                    :conditions="[{ side: key }]"
                                    :label="(key === 'o' ? 'Ob' : 'Re') + 'verse Design'"
                                    icon="notes"
                                    :selected="search"
                                    :selected_key="key + '_id_design'"
                                    v-on:select="(emit) => { search[key + '_id_design'] = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- JK: Legends -->
                            <v-col cols="12" sm="6">
                                <SearchForeignKey
                                    :entity="'legends'"
                                    :conditions="[{ side: key }]"
                                    :label="(key === 'o' ? 'Ob' : 'Re') + 'verse Legend'"
                                    icon="translate"
                                    sk="el_uc_adv"
                                    :selected="search"
                                    :selected_key="key + '_id_legend'"
                                    v-on:select="(emit) => { search[key + '_id_legend'] = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- JK: Monograms -->
                            <v-col cols="12" sm="3">
                                <SearchForeignKey
                                    entity="monograms"
                                    :label="(key === 'o' ? 'Ob' : 'Re') + 'verse Monogram'"
                                    icon="functions"
                                    :selected="search"
                                    :selected_key="key + '_id_monogram'"
                                    v-on:select="(emit) => { search[key + '_id_monogram'] = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                            <!-- JK: Symbols -->
                            <v-col cols="12" sm="3">
                                <SearchForeignKey
                                    entity="symbols"
                                    :label="(key === 'o' ? 'Ob' : 'Re') + 'verse Symbol'"
                                    icon="flare"
                                    :selected="search"
                                    :selected_key="key + '_id_symbol'"
                                    v-on:select="(emit) => { search[key + '_id_symbol'] = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                ></SearchForeignKey>
                            </v-col>

                        </v-row>
                    </v-tab-item>
                </v-tabs-items>

                <!-- Search Button ----------------------------------------------------- ----------------------------------------------------- ----------------------------------------------------- -->
                <v-row class="mb-4">
                    <v-col cols="12" sm="2" md="4"></v-col>
                    <v-col cols="12" sm="10" md="4" class="text-center">
                        <!-- Search -->
                        <v-btn
                            large
                            tile
                            block
                            color="blue_prim"
                            class="title"
                            v-text="'search'"
                            @click="RunSearch()"
                        ></v-btn>
                        <!-- Restore Default -->
                        <v-btn
                            text
                            small
                            class="mt-3"
                            v-text="'Reset Search Parameters'"
                            @click="SearchDefaults (true)"
                        ></v-btn>
                    </v-col>
                    <!-- Restore previous Search Parameters -->
                    <v-col cols="12" sm="2" md="4">
                        <v-menu
                            v-if="$store.state.previousSearches[entity][0]"
                            offset-y
                            transition="scale-transition"
                            max-width="300"
                        >
                            <template v-slot:activator="{ on: menu }">
                                <v-tooltip bottom>
                                <template v-slot:activator="{ on: tooltip }">
                                    <v-btn
                                        v-on="{ ...tooltip, ...menu }"
                                        fab
                                        small
                                        color="blue_sec"
                                    >
                                        <v-icon>youtube_searched_for</v-icon>
                                    </v-btn>
                                </template>
                                <span v-text="'Restore previous Search Parameters'"></span>
                                </v-tooltip>
                            </template>
                            <v-card tile>
                                <v-card-subtitle
                                    class="appbar"
                                    v-text="'Previous Searches'"
                                ></v-card-subtitle>
                                <v-list dense class="pa-0 ma-0">
                                    <v-list-item
                                        v-for="(ls, i) in $store.state.previousSearches[entity]"
                                        :key="i"
                                        @click="restorePreviousSearch(ls)"
                                    >
                                        <div
                                            class="caption mb-1"
                                            v-text="printPreviousSearch(ls)"
                                        ></div>
                                    </v-list-item>
                                </v-list>
                            </v-card>
                        </v-menu>
                    </v-col>

                </v-row>

            </v-card-text>
        </v-expand-transition>


        <!-- Control Bar ------------------------------------------------- ------------------------------------------------- ------------------------------------------------- -->
        <v-card tile flat class="sysbar" :loading="loading">
            <v-divider></v-divider>
            <div class="d-flex component-wrap">
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-btn v-on="on" depressed color="sysbar"
                            :tile="$root.user.level > 18"
                            :text="$root.user.level < 18"
                            :disabled="$root.user.level < 18"
                            @click="TogglePublisher()"
                        >
                            <v-icon v-text="publisher ? 'public_off' : 'public'"></v-icon>
                        </v-btn>
                    </template>
                    <span v-text="publisher ? 'Hide Publisher' : 'Show Publisher'"></span>
                </v-tooltip>

                <v-spacer></v-spacer>

                <pagination
                    :offset="dbi_params.offset"
                    :limit="dbi_params.limit"
                    :count="dbi_params.count"
                    v-on:offset="(emit) => { dbi_params.offset = emit; SetItems () }"
                ></pagination>

                <v-spacer></v-spacer>

                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-btn v-on="on"
                            depressed
                            tile
                            color="sysbar"
                            @click="$router.push(entity === 'coins' ? '/types/search' : '/coins/search')"
                        >
                            <v-icon v-text="entity === 'coins' ? 'blur_circular' : 'copyright'"></v-icon>
                        </v-btn>
                    </template>
                    <span v-text="entity === 'coins' ? 'Go to Types' : 'Go to Coins'"></span>
                </v-tooltip>
            </div>
        </v-card>

        <div class="appbar d-flex component-wrap">

            <!-- Publisher -->
            <v-fade-transition>
                <div v-if="publisher" class="d-flex component-wrap">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" color="blue_prim" depressed
                                :tile="items[0] ? true : false"
                                :text="items[0] ? false : true"
                                :disabled="items[0] ? false : true"
                                @click="checked_state = !checked_state; SetChecked (checked_state);"
                            >
                                <v-icon v-text="checked_state ? 'radio_button_unchecked' : 'radio_button_checked'"></v-icon>
                            </v-btn>
                        </template>
                        <span v-text="(checked_state ? 'Deselect' : 'Select') + ' all ' + labels[entity]"></span>
                    </v-tooltip>

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn v-on="on" color="blue_prim" depressed
                                :tile="items[0] ? true : false"
                                :text="!checked.filter((check) => { return check.state === true })[0]"
                                :disabled="!checked.filter((check) => { return check.state === true })[0]"
                                @click="Publish(checked, 1)"
                            >
                                <v-icon>publish</v-icon>
                            </v-btn>
                        </template>
                        <span v-text="'Publish selected ' + labels[entity]"></span>
                    </v-tooltip>
                    <v-divider vertical></v-divider>
                </div>
            </v-fade-transition>

            <!-- JK: Limit -->
            <v-menu offset-y transition="scale-transition">
                <template v-slot:activator="{ on: menu }">
                    <v-tooltip bottom>
                    <template v-slot:activator="{ on: tooltip }">
                        <v-btn v-on="{ ...tooltip, ...menu }" tile depressed>
                            {{ dbi_params.limit }}
                        </v-btn>
                    </template>
                    <span v-text="'Set number of ' + labels[entity] + ' per page'"></span>
                    </v-tooltip>
                </template>
                <v-list>
                    <v-list-item
                        v-for="(ipp, i) in $store.state.ItemsPerPage"
                        :key="i"
                        :class="dbi_params.limit === ipp ? 'font-weight-black' : ''"
                        @click="dbi_params.limit = ipp; SetItems ();"
                    >
                        <v-list-item-title v-text="ipp"></v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>

            <!-- Order by -->
            <v-menu offset-y transition="scale-transition">
                <template v-slot:activator="{ on: menu }">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on: tooltip }">
                            <v-btn v-on="{ ...tooltip, ...menu }" tile depressed>
                                <v-icon>sort_by_alpha</v-icon>
                            </v-btn>
                        </template>
                        <span v-text="'Order ' + labels[entity] + ' by ...'"></span>
                    </v-tooltip>
                </template>
                <v-card tile>
                    <v-list>
                        <v-list-item
                            v-for="(sorter, i) in sorters"
                            :key="i"
                            :class="dbi_params.sort_by === sorter.value ? 'font-weight-black' : ''"
                            @click="OrderBy(sorter.value)"
                        >
                            <v-list-item-icon>
                                <v-icon v-text="dbi_params.sort_by === sorter.value ? (dbi_params.sort_by_op == 'ASC' ? 'keyboard_arrow_down' : 'keyboard_arrow_up') : 'keyboard_arrow_up'"></v-icon>
                            </v-list-item-icon>
                            <v-list-item-title v-text="sorter.text"></v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-menu>

            <!-- Display Mode -->
            <v-menu offset-y transition="scale-transition">
                <template v-slot:activator="{ on: menu }">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on: tooltip }">
                            <v-btn v-on="{ ...tooltip, ...menu }" tile depressed>
                                <v-icon v-text="display_mode[display].icon"></v-icon>
                            </v-btn>
                        </template>
                        <span v-text="'Display ' + labels[entity] + ' as ...'"></span>
                    </v-tooltip>
                </template>
                <v-card tile>
                    <v-list>
                        <v-list-item
                            v-for="(dm, i) in display_mode"
                            :key="i"
                            :class="display === dm.value ? 'font-weight-black' : ''"
                            @click="setDisplayMode(dm.value)"
                        >
                            <v-list-item-icon><v-icon v-text="dm.icon"></v-icon></v-list-item-icon>
                            <v-list-item-title v-text="dm.text"></v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-menu>

            <v-spacer></v-spacer>

            <!-- Refresh -->
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn v-on="on" depressed
                        :tile="items[0] ? true : false"
                        :text="items[0] ? false : true"
                        :disabled="items[0] ? false : true"
                        @click="SetItems ();"
                    >
                        <v-icon>refresh</v-icon>
                    </v-btn>
                </template>
                <span>Refresh current page</span>
            </v-tooltip>

        </div>
    </v-card>

    <!-- Search Results ------------------------------------------------- ------------------------------------------------- ------------------------------------------------- -->

    <!-- Error -->
    <div
        v-if="error"
        class="pt-5 headline d-flex justify-center"
        v-html="'Sorry, an error occured.&ensp;Please reload and retry'"
    ></div>

    <!-- Content -->
    <div v-if="items[0]">

        <!-- Trading Cards || Index Cards -->
        <div v-if="[0, 1, 2].includes(display)">
            <v-row>
                <v-col
                    v-for="(item, i) in items"
                    :key="i + ' ' + display"
                    cols="12"
                    :sm="display === 0 ? 6 : 12"
                    :md="display === 0 ? 3 : 12"
                    :lg="display === 0 ? 2 : 12"
                >
                    <component
                        :is="display_mode[display].component"
                        :key="display + item.id + entity + (publisher ? 1 : 0) + item.public + (checked[i].state ? 1 : 0)"
                        :entity="entity"
                        :item="item"
                        :publisher="publisher"
                        :checked="checked[i].state"
                        v-on:publish="(emit) => { Publish ([{ id: item.id, state: true }], (item.public === 0 ? 1 : 0)) }"
                        v-on:checked="checked[i].state = !checked[i].state"
                    ></component><!-- v-on:inheritor="(emit) => { details_dialog = { entity: 'types', id: emit } }"-->
                </v-col>
            </v-row>
        </div>

    </div>

    <!-- Pagination -->
    <v-card
        v-if="items[0]"
        tile
        :class="'sysbar d-flex component-wrap justify-center'+(display !== 1 ? ' mt-5' : '')"
        sytel="position: relative"
    >
        <pagination
            :offset="dbi_params.offset"
            :limit="dbi_params.limit"
            :count="dbi_params.count"
            v-on:offset="(emit) => {
                dbi_params.offset = emit;
                SetItems();
                ScrollTop();
            }"
        ></pagination>
        <div style="position: absolute; right: 0">
            <!-- Refresh -->
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn
                        v-on="on"
                        depressed
                        tile
                        color="grey_prim"
                        @click="SetItems();"
                    >
                        <v-icon>refresh</v-icon>
                    </v-btn>
                </template>
                <span>Refresh current page</span>
            </v-tooltip>
            <!-- Scroll to top
            <v-tooltip bottom>
                <template v-slot:activator="{ on }">
                    <v-btn v-on="on" depressed
                        tile
                        color="grey_prim"
                        @click="ScrollTop()"
                    >
                        <v-icon>north</v-icon>
                    </v-btn>
                </template>
                <span>Scroll to top</span>
            </v-tooltip> -->
        </div>
    </v-card>

</div>
</template>


<script>

import tradingcard  from './modules/searchLayoutTradingcard.vue'
import indexcard    from './modules/searchLayoutIndexcard.vue'
import tablerow     from './modules/searchLayoutTablerow.vue'

export default {
    components: {
        tradingcard,
        indexcard,
        tablerow
    },

    data () {
        return {
            loading:            false,
            error:              false,

            items:              [],

            search:             {
                id: null,
                state_public:   this.publisher ? 0 : null,
                q: null
            },
            //previous_search:    [],
            search_refresh:     0,
            dbi_params:         {},

            tools:              true,

            publisher:          false,
            checked_state:      false,
            checked:            [],

            tab:                'tab-0',
            tabs:               [
                {value: 0,  text: 'String Search'},
                {value: 1,  text: 'Frequently used'},
                {value: 2,  text: 'General'},
                {value: 3,  text: 'Production'},
                {value: 4,  text: 'Depiction'}
            ],

            display:            this.$store.state.displayMode,
            display_mode:       [
                {value: 0, component: 'tradingcard',    text: 'Tiles',   icon: 'view_comfy'},
                {value: 1, component: 'indexcard',      text: 'Cards',     icon: 'view_list'},
                {value: 2, component: 'tablerow',       text: 'Text',      icon: 'view_column'}
            ],

            // selectlists
            selects:            {
                state_public:       [
                    { value: null, text: 'All' },
                    { value: 0, text: 'Not published' },
                    { value: 2, text: 'Publishing requested' },
                    { value: 1, text: 'Published' },
                    { value: 3, text: 'Deleted' }
                ],
                state_yes_no:       [
                    { value: null, text: 'All' },
                    { value: 0, text: 'No' },
                    { value: 1, text: 'Yes' }
                ]
            },

            searchStringKeyboard: false
        }
    },

    props: {
        entity:   {type: String, default: 'coins'},
        prop_id:  {type: [String, Number], default: null}
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        given_id () {
            return this.$route.params.id === undefined ? this.prop_id : this.$route.params.id
        },

        label () {
            return this.entity == 'coins' ? {s: 'Coin', p: 'Coins'} : {s: 'Type', p: 'Types'};
        },

        sorters () {
            return this.entity === 'coins' ? [
                { value: 'id', text: 'ID' },
                { value: 'date', text: this.labels['date'] },
                { value: 'weight', text: this.labels['weight'] },
                { value: 'diameter', text: this.labels['diameter'] },
                { value: 'mint', text: this.labels['mint'] },
                { value: 'ruler', text: this.labels['authority_person'] },
                { value: 'issuer', text: this.labels['issuer'] },
                { value: 'created', text: this.labels['created_at'] },
                { value: 'updated', text: this.labels['updated_at'] }
            ] : [
                { value: 'id', text: 'ID' },
                { value: 'date', text: this.labels['date'] },
                { value: 'mint', text: this.labels['mint'] },
                { value: 'ruler', text: this.labels['authority_person'] },
                { value: 'issuer', text: this.labels['issuer'] },
                { value: 'created', text: this.labels['created_at'] },
                { value: 'updated', text: this.labels['updated_at'] }
            ]
        }
    },

    watch: {
        entity: function() { this.Init() }
    },

    created () {
        this.Init()
    },

    methods: {
        Init () {
            this.$store.commit('setBreadcrumbs',[
                { label: this.labels[this.entity], to:'' },
                { label: 'Search', to:'' }
            ])
            this.SearchDefaults(false)
            this.publisher = false
            this.items = []
            this.dbi_params= {
                sort_by: 'id',
                sort_by_op:  'DESC',
                count:  0,
                offset: 0,
                limit:  12
            }
        },

        RunSearch () {
            this.cacheCurrentSearch()
            this.SetItems()
        },

        async SetItems () {
            this.loading    = true;
            this.error      = false;
            const self      = this;

            // Get Search Parameters
            const search = {}
            Object.keys(this.search).forEach((key) => {
                if (key === 'state_public') {
                   search[key] = self.search[key] === null ? [0, 1, 2] : self.search[key]
                }
                else if (['string', 'o_design', 'r_design'].includes(key)) {
                    if (self.search[key]) {
                        search[key] = self.search[key].split(' ')
                    }
                }
                else if (![null, []].includes(self.search[key])) {
                    search[key] = self.search[key]
                }
            })

            // Set DBI Parameters
            const params = {}
            Object.keys(self.dbi_params).forEach((key) => {
                if (key === 'sort_by') {
                    params[key] = self.dbi_params.sort_by + '.' + self.dbi_params.sort_by_op
                }
                else if (key != 'sort_by_op') {
                    params[key] = self.dbi_params[key]
                }
            })

            const dbi = await this.$root.DBI_SELECT_POST(this.entity, params, search)

            if (dbi?.contents) {
                const sort_explode = dbi.pagination.sort_by.split(' ')
                dbi.pagination.sort_by = sort_explode[0]
                dbi.pagination.sort_by_op = sort_explode[1]
                this.dbi_params = dbi.pagination

                this.items = Object.values(dbi.contents)
                if (this.items[0]) {
                    this.SetChecked (false)
                }
                else {
                    this.$root.snackbar('No ' + this.labels[this.entity] + ' found')
                }
            }
            else {
                console.log(dbi)
                this.error = true
            }

            this.loading = false
        },

        SetChecked (state) {
            const checkers = []
            this.checked = []

            this.items.forEach((item) => {
                checkers.push({ id: item.id, state: item.public === 0 ? state : false })
            })
            this.checked = checkers
        },

        SearchDefaults (refresh) {
            const self = this
            const search = {}
            Object.keys(this.search).forEach((key) => {
                if (key === 'state_public') {
                   search[key] = this.publisher ? 0 : null
                }
                else if (Array.isArray(self.search[key])) {
                    search[key] = []
                }
                else {
                    search[key] = null
                }
            })
            this.search = search
            /*this.search = { id: null, state_public: (this.publisher ? 0 : null) }
            if (refresh) { ++this.search_refresh }*/
        },

        ScrollTop () {
            window.scrollTo({ top: 0, left: 0, behavior: 'smooth' })
        },

        OrderBy (input) {
            if (input === this.dbi_params.sort_by) {
                this.dbi_params.sort_by_op = this.dbi_params.sort_by_op != 'ASC' ? 'ASC' : 'DESC'
            }
            else {
                this.dbi_params.sort_by = input
                this.dbi_params.sort_by_op = 'ASC'
            }
            this.dbi_params.offset = 0
            this.cacheCurrentSearch()
            this.SetItems();
        },

        TogglePublisher () {
            if (!this.publisher){
                this.publisher = true
                if (this.search.state_public !== 2) {
                    this.search.state_public = this.publisher ? 2 : null
                    this.$root.snackbar('Publishing activated (' + this.labels[this.entity] + ' requested to be published only)')
                    this.dbi_params.offset = 0;
                    this.SetItems();
                }
            }
            else {
                this.publisher = false
                this.search.state_public = null
                this.$root.snackbar('Publishing deactivated (all ' + this.labels[this.entity] + ' shown)')
                this.SetItems()
            }
        },

        async Publish (input, mode) {
            let items = input.filter(item => { return item.state === true })

            if (items [0]) {
                let confirmed = mode === 1 ? true : (confirm(this.labels[this.entity.slice(0, -1)] + ' ' + input[0].id + ' will be ' + (mode === 3 ? 'deleted!' : 'unpublished!') ));

                if (confirmed === true) {
                    const response = await this.$root.DBI_INPUT_POST('publish', 'input', { entity: this.entity, items: items.map((item) => { return item.id }), mode: mode });

                    if (response.success) {
                        this.$root.snackbar(response.success, 'success')
                        this.SetItems()
                    }
                }
            }
            else {
                this.snackbar('No items selected!');
                this.SetItems();
            }
        },

        setDisplayMode (value) {
            this.$store.commit('set_display_mode', value)
            this.display = value
        },

        cacheCurrentSearch () {
            const self = this
            const search = {}
            Object.keys(this.search).forEach((key) => {
                if (![null, []].includes(self.search[key])) {
                    if (Array.isArray(self.search[key])) {
                        const new_array = []
                        self.search[key].forEach((val) => { new_array.push(val)})
                        search[key] = new_array
                    }
                    else {
                        search[key] = self.search[key]
                    }
                }
            })
            if (search) {
                const params = {
                    sort_by: self.dbi_params.sort_by,
                    sort_by_op: self.dbi_params.sort_by_op,
                    count:  0,
                    offset: 0,
                    limit:  self.dbi_params.limit
                }
                const ls_array = []
                ls_array.push({ search: search, params: params })
                this.$store.state.previousSearches[this.entity].forEach((ls) => {
                    ls_array.push(ls)
                })
                this.$store.commit('set_previous_search', { entity: this.entity, data: ls_array })
            }
        },

        printPreviousSearch (ls) {
            let keys = JSON.stringify(ls.search)
            keys = keys.replaceAll(',', ', ').replaceAll('"', '').slice(1,-1).replaceAll(':', ': ')
            return (keys.trim() ? keys : '--') + ', sort by: ' + (ls.params.sort_by + ' ' + ls.params.sort_by_op)
        },

        restorePreviousSearch (ls) {
            this.SearchDefaults()
            // Search
            const search = {}
            Object.keys(ls.search).forEach((key) => {
                search[key] = ls.search[key]
            })
            this.search = search
            // Parameters
            const params = {}
            Object.keys(ls.params).forEach((key) => {
                params[key] = ls.params[key]
            })
            this.dbi_params = params
            this.SetItems()
        },

        searchStringIncludeFiled (key) {
            if (this.search.qex?.[0]) {
                if (this.search.qex?.includes(key)) {
                    if (this.search.qex.length > 1) {
                        const index = this.search.qex.indexOf(key)
                        this.search.qex.splice(index, 1)
                    }
                    else this.search.qex = []
                }
                else this.search.qex.push(key)
            }
            else this.search.qex = [key]
        }
    }
}

</script>
