<template>
<div>
    <!-- Toolbar ------------------------------------------------- ------------------------------------------------- ------------------------------------------------- -->
    <component-toolbar>
        <template v-slot:toolbar>
            <pagination-bar
                :offset="dbi_params.offset"
                :limit="dbi_params.limit"
                :count="dbi_params.count"
                :sortby="dbi_params.sort_by + ' ' + dbi_params.sort_by_op"
                :sorters="sorters"
                :layout="display"
                :layouts="display_mode"
                v-on:reload="SetItems()"
                v-on:offset="(emit) => { dbi_params.offset = emit; SetItems() }"
                v-on:limit="(emit) => { dbi_params.limit = emit; SetItems() }"
                v-on:sortby="OrderBy"
                v-on:layout="setDisplayMode"
            >
                <template v-slot:right>
                    <!-- Publisher Functions -->
                    <v-slide-x-reverse-transition>
                        <adv-btn
                            v-if="publisher"
                            :icon="checked_state ? 'radio_button_unchecked' : 'radio_button_checked'"
                            :tooltip="(checked_state ? 'Deselect' : 'Select') + ' all ' + labels[entity]"
                            :disabled="items[0] ? false : true"
                            color-main="blue_prim"
                            color-hover="blue_sec"
                            color-text="white"
                            @click="checked_state = !checked_state; SetChecked(checked_state);"
                        />
                    </v-slide-x-reverse-transition>

                    <v-slide-x-reverse-transition>
                        <adv-btn
                            v-if="publisher"
                            icon="publish"
                            :tooltip="'Publish selected ' + labels[entity]"
                            :disabled="!checked.filter((check) => { return check.state === true })[0]"
                            color-main="blue_prim"
                            color-hover="blue_sec"
                            color-text="white"
                            @click="Publish(checked, 1)"
                        />
                    </v-slide-x-reverse-transition>

                    <!-- Publisher Toggle -->
                    <adv-btn
                        :icon="publisher ? 'public_off' : 'public'"
                        :tooltip="publisher ? 'Hide Publisher' : 'Show Publisher'"
                        :disabled="$root.user.level < 18"
                        color-hover="header_hover"
                        @click="TogglePublisher()"
                    />

                    <!-- Switch Coin/Types -->
                    <a :href="'/editor#/' + (entity === 'coins' ? 'types' : 'coins') + '/search'">
                        <adv-btn
                            :icon="entity === 'coins' ? 'blur_circular' : 'copyright'"
                            :tooltip="entity === 'coins' ? 'Go to Types' : 'Go to Coins'"
                            color-hover="header_hover"
                        />
                    </a>
                </template>
            </pagination-bar>
        </template>
    </component-toolbar>

    <!-- Content ------------------------------------------------- ------------------------------------------------- ------------------------------------------------- -->
    <component-content>
        <template v-slot:content>

            <div id="results-anchor" />
            <!-- Error -->
            <div
                v-if="error"
                class="mt-10 headline d-flex justify-center"
                v-html="'Sorry, an error occured.<br/>Please reload and retry!'"
            />

            <!-- Results -->
            <div v-else-if="items[0]">

                <!-- Trading Cards || Index Cards -->
                <div v-if="[0, 1, 2].includes(display)" style="padding-left: 43px; padding-top: 3px">
                    <v-row class="ma-0 pa-0">
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

            <!-- Start Screen -->
            <div v-else class="start-screen">
                <v-card
                    tile
                    raised
                    class="header_bg"
                    style="height: 100%; position: relative"
                >
                    <div class="pa-3">
                        <div class="text-center title text-uppercase" v-text="'Search ' + entity" />
                        <a :href="'/editor#/' + (entity === 'coins' ? 'types' : 'coins') + '/search'">
                            <div class="text-center caption mb-4" v-text="'Looking for ' + (entity === 'coins' ? 'types' : 'coins') + '?'" />
                        </a>

                        <v-text-field clearable dense
                            v-model="search.id"
                            label="ID"
                            class="mb-3"
                            v-on:keyup.enter="RunSearch()"
                            style="width: 50%"
                        />

                        <v-text-field clearable dense
                            v-model="search.q"
                            :label="$root.label('keywords')"
                            append-icon="keyboard"
                            class="mb-n2"
                            v-on:keyup.enter="RunSearch()"
                            v-on:click:append="searchStringKeyboard = !searchStringKeyboard"
                        />
                        <v-expand-transition>
                            <div v-if="searchStringKeyboard" class="d-flex justify-center">
                                <keyboard
                                    :string="search.q"
                                    layout="el_uc"
                                    small
                                    hide_options
                                    v-on:input="(emit) => { search.q = emit }"
                                ></keyboard>
                            </div>
                        </v-expand-transition>
                    </div>

                    <!-- Search Button -->
                    <div
                        class="text-center mt-1 pa-3"
                        style="position:absolute; bottom: 0; width: 100%"
                    >
                        <div class="text-center body-2 mb-3" v-text="'Please check the left sidebar for advanced filters.'" />
                        <v-btn
                            tile
                            block
                            dark
                            color="blue_prim"
                            class="title"
                            v-text="'search'"
                            :width="350"
                            @click="RunSearch()"
                        />
                    </div>
                </v-card>
            </div>


        </template>
    </component-content>

    <!-- Search ------------------------------------------------- ------------------------------------------------- ------------------------------------------------- -->
    <drawer
        header
        :collapse="searchCounter"
        v-on:expand="onDrawerExpand"
        v-on:search="RunSearch()"
        v-on:clear="SearchDefaults(true)"
    >

        <!-- Filters -->
        <template v-slot:content>
            <v-expansion-panels accordion tile flat v-model="activeTab" style="z-index: 100;">

                <!-- Favorites ------------------------------------------------- ------------------------------------------------- -->
                <v-expansion-panel  :class="activeTab === 0 ? 'header_bg' : 'transparent'">
                    <!-- Header -->
                    <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                        <div class="d-flex align-center">
                            <v-icon v-text="'star'" />
                            <div v-text="'Stored Queries'" class="ml-4 whitespace-nowrap" />
                        </div>
                    </v-expansion-panel-header>

                    <!-- Content -->
                    <v-expansion-panel-content>
                        <v-select dense outlined filled
                            prepend-icon="youtube_searched_for"
                            label="Previous Queries"
                            :items="$store.state.previousSearches[entity].map((ls) => {
                                return { value: ls, text: printPreviousSearch(ls) }
                            })"
                            :menu-props="{ offsetY: true }"
                            @input="restorePreviousSearch"
                        />
                    </v-expansion-panel-content>
                </v-expansion-panel>

                <hr class="search-divider" />

                <!-- String Search ------------------------------------------------- ------------------------------------------------- -->
                <v-expansion-panel :class="activeTab === 1 ? 'header_bg' : 'transparent'">
                    <!-- Header -->
                    <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                        <div class="d-flex align-center">
                            <v-icon v-text="'manage_search'" />
                            <div v-text="'Fulltext Keyword Search'" class="ml-4 whitespace-nowrap" />
                        </div>
                    </v-expansion-panel-header>

                    <!-- Content -->
                    <v-expansion-panel-content>

                        <v-text-field dense outlined filled clearable
                            v-model="search.q"
                            :label="$root.label('keywords')"
                            append-icon="keyboard"
                            class="mt-5 mb-n4"
                            v-on:keyup.enter="RunSearch()"
                            v-on:click:append="searchStringKeyboardf = !searchStringKeyboardf"
                        />
                        <v-expand-transition>
                            <div v-if="searchStringKeyboardf" class="d-flex justify-center mb-3">
                                <keyboard
                                    :string="search.q"
                                    layout="el_uc"
                                    small
                                    hide_options
                                    v-on:input="(emit) => { search.q = emit }"
                                />
                            </div>
                        </v-expand-transition>

                        <!-- Options -->
                        <div class="d-flex flex-wrap align-center mt-n5">
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

                            <!-- Filter -->
                            <v-menu tile offset-y nudge-bottom="5" :close-on-content-click="false">
                                <template v-slot:activator="{ on, attrs }">
                                    <div
                                        class="d-flex align-center body-1 mr-9"
                                        style="cursor: pointer"
                                        v-bind="attrs" v-on="on"
                                    >
                                        <v-btn icon class="mr-1"><v-icon v-text="'filter_alt'" /></v-btn>
                                        <div v-text="'Filters'" />
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
                                                />
                                            </v-col>

                                            <v-col cols="6">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.date_end"
                                                    label="Max."
                                                    append-outer-icon="first_page"
                                                    v-on:keyup.enter="RunSearch()"
                                                />
                                            </v-col>

                                            <!-- Weight -->
                                            <v-col cols=12 v-text="'Weight'" class="mt-n5 mb-n2 body-1" />
                                            <v-col cols="6" v-if="entity == 'coins'">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.weight_start"
                                                    label="Min."
                                                    prepend-icon="last_page"
                                                    v-on:keyup.enter="RunSearch()"
                                                />
                                            </v-col>

                                            <v-col cols="6" v-if="entity == 'coins'">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.weight_end"
                                                    label="Max."
                                                    append-outer-icon="first_page"
                                                    v-on:keyup.enter="RunSearch()"
                                                />
                                            </v-col>

                                            <!-- Diameter -->
                                            <v-col cols=12 v-text="'Diameter'" class="mt-n5 mb-n2 body-1" />
                                            <v-col cols="6" v-if="entity == 'coins'">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.diameter_start"
                                                    label="Min."
                                                    prepend-icon="last_page"
                                                    v-on:keyup.enter="RunSearch()"
                                                />
                                            </v-col>

                                            <v-col cols="6" v-if="entity == 'coins'">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.diameter_end"
                                                    label="Max."
                                                    append-outer-icon="first_page"
                                                    v-on:keyup.enter="RunSearch()"
                                                />
                                            </v-col>

                                            <!-- Public -->
                                            <v-col cols=12 class="mt-n5">
                                                <div v-text="'Publication State'" class="body-1 mb-3" />
                                                <v-select dense outlined filled
                                                    :items="selects.state_public"
                                                    v-model="search.state_public"
                                                    v-on:keyup.enter="RunSearch()"
                                                />
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
                                        <div v-text="'How to'" />
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
                                            Search strings can be restricted to a specific field by adding the key followed by two colons (<b>::</b>), e.g. <i>byzantium AND design::bull</i>.
                                            Please note that adding this parameter might slow down the search engine significantly.
                                        </p>
                                        <!--<p>
                                            The search scope can be narrowed by excluding fields generally (uncheck the keys in the popup menu). Furthermore you can add filters like date, weight etc.
                                        </p>-->
                                        <p>
                                            To add a date, weight or diameter range add an additional filter. You can also restrict the search to (un)published datasets (deleted records are not indexed).
                                            Adding a filter might speed up the search engine significantly.
                                        </p>
                                        <p>
                                            The search is case insensitive by default ("kopf" will match "Kopf"), check "case-sensitive" for strict case matching.
                                        </p>
                                        <p>
                                            Click on the keyboard in the right corner of the search field to expand a Greek keyboard.
                                        </p>
                                        <p>
                                            Check "REGEX" when using <a href="https://en.wikipedia.org/wiki/Regular_expression#Syntax" target="_blank">regular expressions</a>.
                                            Please note that adding this parameter might slow down the search engine significantly.
                                        </p>
                                    </v-card-text>
                                </v-card>
                            </v-menu>

                        </div>
                    </v-expansion-panel-content>
                </v-expansion-panel>

                <hr class="search-divider" />

                <!-- Mixed ------------------------------------------------- ------------------------------------------------- -->
                <v-expansion-panel :class="activeTab === 2 ? 'header_bg' : 'transparent'">
                    <!-- Header -->
                    <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                        <div class="d-flex align-center">
                            <v-icon v-text="'settings'" />
                            <div v-text="'System'" class="ml-4 whitespace-nowrap" />
                        </div>
                    </v-expansion-panel-header>

                    <!-- Content -->
                    <v-expansion-panel-content>
                        <div class="d-flex">
                            <!-- ID -->
                            <div class="search-field-50 side-l">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.id"
                                    :label="labels[entity.slice(0, -1)] + ' ID'"
                                    prepend-icon="fingerprint"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>

                            <!-- Public -->
                            <div class="search-field-50 side-r">
                                <v-select dense outlined filled
                                    :items="selects.state_public"
                                    v-model="search.state_public"
                                    prepend-icon="publish"
                                    label="Publication State"
                                    :menu-props="{ offsetY: true }"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>
                        </div>

                        <div class="d-flex">
                            <!-- Linked to Type/coin -->
                            <div class="search-field-50 side-l">
                                <v-select dense outlined filled
                                    :prepend-icon="entity == 'coins' ? 'blur_circular' : 'copyright'"
                                    :label="'Linked to '+(entity == 'coins' ? 'Type' : 'Coin')+'?'"
                                    v-model="search.state_linked"
                                    :items="selects.state_yes_no"
                                    :menu-props="{ offsetY: true }"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>

                            <!-- Linked to Type/coin -->
                            <div class="search-field-50 side-r">
                                <v-select dense outlined filled
                                    prepend-icon="sync"
                                    :label="(entity == 'coins' ? 'Is inherited' : 'Inherits to Coins')+'?'"
                                    v-model="search.state_inherited"
                                    :items="selects.state_yes_no"
                                    :menu-props="{ offsetY: true }"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>
                        </div>

                        <div class="d-flex">
                            <!-- ID Opposite Entity -->
                            <div class="search-field-50 side-l">
                                <v-text-field dense outlined filled clearable
                                    v-model="search ['id_'+(entity == 'coins' ? 'type' : 'coin')]"
                                    :label="'Linked '+(entity == 'coins' ? 'Type' : 'Coin')+' ID'"
                                    :prepend-icon="entity == 'coins' ? 'blur_circular' : 'copyright'"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>
                            <div class="search-field-50 side-r"/>
                        </div>

                        <!-- Creator / Editor -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <SearchForeignKey
                                    entity="users"
                                    label="Creator"
                                    icon="person"
                                    :selected="search"
                                    selected_key="id_creator"
                                    v-on:select="(emit) => { search.id_creator = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div>

                            <div class="search-field-50 side-r">
                                <SearchForeignKey
                                    entity="users"
                                    label="Editor"
                                    icon="person_outline"
                                    :selected="search"
                                    selected_key="id_editor"
                                    v-on:select="(emit) => { search.id_editor = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div>
                        </div>

                        <v-select dense outlined filled
                            v-model="search.imported"
                            :items="$store.state.lists.manual.imports"
                            prepend-icon="arrow_circle_down"
                            label="Scripted Coin Import"
                            :menu-props="{ offsetY: true }"
                            v-on:keyup.enter="RunSearch()"
                        />

                        <!-- Source -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <v-select dense outlined filled
                                    v-model="search.state_source"
                                    :items="selects.state_yes_no"
                                    prepend-icon="arrow_circle_down"
                                    label="Has Source?"
                                    :menu-props="{ offsetY: true }"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>

                            <div class="search-field-50 side-r">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.source"
                                    label="Source Link"
                                    prepend-icon="arrow_circle_down"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>
                        </div>

                        <SearchForeignKey
                            entity="objectgroups"
                            label="Object Group"
                            icon="control_camera"
                            :selected="search"
                            selected_key="id_group"
                            v-on:select="(emit) => { search.id_group = emit }"
                            v-on:keyup_enter="RunSearch()"
                        />
                    </v-expansion-panel-content>
                </v-expansion-panel>

                <hr class="search-divider" />

                <!-- Comment, References ------------------------------------------------- ------------------------------------------------- -->
                <v-expansion-panel :class="activeTab === 3 ? 'header_bg' : 'transparent'">
                    <!-- Header -->
                    <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                        <div class="d-flex align-center">
                            <v-icon v-text="'local_library'" />
                            <div v-text="'Comment, References, Owner'" class="ml-4 whitespace-nowrap" />
                        </div>
                    </v-expansion-panel-header>

                    <!-- Content -->
                    <v-expansion-panel-content>

                        <!-- Public Comment -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <v-select dense outlined filled
                                    v-model="search.state_comment_public"
                                    :items="selects.state_yes_no"
                                    prepend-icon="chat_bubble"
                                    label="Has public Comment?"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>

                            <div class="search-field-50 side-r">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.comment_public"
                                    label="Public Comment"
                                    prepend-icon="chat_bubble"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>
                        </div>

                        <!-- Private Comment -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <v-select dense outlined filled
                                    v-model="search.state_comment_private"
                                    :items="selects.state_yes_no"
                                    prepend-icon="chat_bubble_outline"
                                    label="Has private Comment?"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>

                            <div class="search-field-50 side-r">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.comment_private"
                                    label="Private Comment"
                                    prepend-icon="chat_bubble_outline"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>
                        </div>

                        <!-- Private Description -->
                        <v-text-field dense outlined filled clearable
                            v-model="search.description_private"
                            label="Private Description"
                            prepend-icon="label_important"
                            v-on:keyup.enter="RunSearch()"
                        />
                        <v-text-field dense outlined filled clearable
                            v-if="entity === 'types'"
                            v-model="search.name_private"
                            label="Private Name"
                            prepend-icon="label"
                            v-on:keyup.enter="RunSearch()"
                        />

                        <!-- References -->
                        <SearchForeignKey
                            entity="references"
                            label="Bibliography"
                            icon="menu_book"
                            :selected="search"
                            selected_key="id_reference"
                            v-on:select="(emit) => { search.id_reference = emit }"
                            v-on:keyup_enter="RunSearch()"
                        />

                        <!-- Owner -->
                        <SearchForeignKey
                            entity="owners"
                            label="Owner"
                            icon="account_circle"
                            :selected="search"
                            selected_key="id_owner"
                            v-on:select="(emit) => { search.id_owner = emit }"
                            v-on:keyup_enter="RunSearch()"
                        />
                        <template v-if="entity === 'coins'">
                            <v-text-field dense outlined filled clearable
                                v-if="entity === 'coins'"
                                v-model="search.provenience"
                                label="Provenience"
                                prepend-icon="play_circle_outline"
                                v-on:keyup.enter="RunSearch()"
                            />
                            <div class="d-flex">
                                <div class="search-field-50 side-l">
                                    <v-text-field dense outlined filled clearable
                                        v-model="search.collection_nr"
                                        label="Collection Nr."
                                        prepend-icon="bookmarks"
                                        v-on:keyup.enter="RunSearch()"
                                    />
                                </div>
                                <div class="search-field-50 side-r">
                                    <v-text-field dense outlined filled clearable
                                        v-model="search.plastercast_nr"
                                        label="Plastercast Nr."
                                        prepend-icon="bookmarks"
                                        v-on:keyup.enter="RunSearch()"
                                    />
                                </div>
                            </div>
                        </template>

                        <!-- Hoards -->
                        <SearchForeignKey
                            entity="hoards"
                            label="Hoard"
                            icon="grain"
                            :selected="search"
                            selected_key="id_hoard"
                            v-on:select="(emit) => { search.id_hoard = emit }"
                            v-on:keyup_enter="RunSearch()"
                        />

                        <!-- Findspots -->
                        <SearchForeignKey
                            entity="findspots"
                            label="Findspot"
                            icon="pin_drop"
                            :selected="search"
                            selected_key="id_findspot"
                            v-on:select="(emit) => { search.id_findspot = emit }"
                            v-on:keyup_enter="RunSearch()"
                        />
                    </v-expansion-panel-content>
                </v-expansion-panel>

                <hr class="search-divider" />

                <!-- Production ------------------------------------------------- ------------------------------------------------- -->
                <v-expansion-panel :class="activeTab === 4 ? 'header_bg' : 'transparent'">
                    <!-- Header -->
                    <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                        <div class="d-flex align-center">
                            <v-icon v-text="'zoom_out_map'" />
                            <div v-text="'Production Conditions'" class="ml-4 whitespace-nowrap" />
                        </div>
                    </v-expansion-panel-header>

                    <!-- Content -->
                    <v-expansion-panel-content>
                        <!-- Region / Mint -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <SearchForeignKey
                                    entity="regions"
                                    label="Region"
                                    icon="terrain"
                                    :selected="search"
                                    selected_key="id_region"
                                    v-on:select="(emit) => { search.id_region = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div>
                            <div class="search-field-50 side-l">
                                <SearchForeignKey
                                    entity="mints"
                                    label="Mint"
                                    icon="museum"
                                    :selected="search"
                                    selected_key="id_mint"
                                    v-on:select="(emit) => { search.id_mint = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div>
                        </div>
                        <!-- Type of Coinage -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <SearchForeignKey
                                    entity="authorities"
                                    label="Type of Coinage"
                                    icon="toll"
                                    :selected="search"
                                    selected_key="id_authority"
                                    v-on:select="(emit) => { search.id_authority = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div><div class="search-field-50 side-r"/>
                        </div>

                        <!-- Authority -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <SearchForeignKey
                                    entity="persons"
                                    :conditions="[{ authority: 1 }]"
                                    label="Ruler"
                                    icon="how_to_reg"
                                    :selected="search"
                                    selected_key="id_authority_person"
                                    v-on:select="(emit) => { search.id_authority_person = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div>
                            <div class="search-field-50 side-r">
                                <SearchForeignKey
                                    entity="tribes"
                                    label="Tribe"
                                    icon="sports_kabaddi"
                                    :selected="search"
                                    selected_key="id_authority_group"
                                    v-on:select="(emit) => { search.id_authority_group = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div>
                        </div>

                        <!-- persons -->
                        <SearchForeignKey
                            entity="persons"
                            label="Person"
                            icon="emoji_people"
                            :selected="search"
                            selected_key="id_person"
                            v-on:select="(emit) => { search.id_person = emit }"
                            v-on:keyup_enter="RunSearch()"
                        />

                        <!-- Period -->
                        <SearchForeignKey
                            entity="periods"
                            label="Epoch"
                            icon="watch_later"
                            :selected="search"
                            selected_key="id_period"
                            v-on:select="(emit) => { search.id_period = emit }"
                            v-on:keyup_enter="RunSearch()"
                        />

                        <!-- Date -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <v-text-field dense outlined filled
                                    v-model="search.date_start"
                                    label="Date Start"
                                    prepend-icon="first_page"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>
                            <div class="search-field-50 side-r">
                                <v-text-field dense outlined filled
                                    v-model="search.date_end"
                                    label="Date End"
                                    prepend-icon="last_page"
                                    v-on:keyup.enter="RunSearch()"
                                />
                            </div>
                        </div>
                    </v-expansion-panel-content>
                </v-expansion-panel>

                <hr class="search-divider" />

                <!-- Technical Parameters ------------------------------------------------- ------------------------------------------------- -->
                <v-expansion-panel :class="activeTab === 5 ? 'header_bg' : 'transparent'">
                    <!-- Header -->
                    <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                        <div class="d-flex align-center">
                            <v-icon v-text="'category'" />
                            <div v-text="'Technical Parameters'" class="ml-4 whitespace-nowrap" />
                        </div>
                    </v-expansion-panel-header>

                    <!-- Content -->
                    <v-expansion-panel-content>

                        <!-- Material -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <SearchForeignKey
                                    entity="materials"
                                    label="Material"
                                    icon="palette"
                                    :selected="search"
                                    selected_key="id_material"
                                    v-on:select="(emit) => { search.id_material = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div><div class="search-field-50 side-l" />
                        </div>

                        <!-- Standard / Denomination -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <SearchForeignKey
                                    entity="denominations"
                                    label="Denomination"
                                    icon="bubble_chart"
                                    :selected="search"
                                    selected_key="id_denomination"
                                    v-on:select="(emit) => { search.id_denomination = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div>
                            <div class="search-field-50 side-r">
                                <SearchForeignKey
                                    entity="standards"
                                    label="Standard"
                                    icon="group_work"
                                    :selected="search"
                                    selected_key="id_standard"
                                    v-on:select="(emit) => { search.id_standard = emit }"
                                    v-on:keyup_enter="RunSearch()"
                                />
                            </div>
                        </div>

                        <template v-if="entity === 'coins'">

                            <!-- Weight -->
                            <div class="d-flex">
                                <div class="search-field-50 side-l">
                                    <v-text-field dense outlined filled
                                        v-model="search.weight_start"
                                        label="Weight Min."
                                        prepend-icon="radio_button_unchecked"
                                        v-on:keyup.enter="RunSearch()"
                                    />
                                </div>
                                <div class="search-field-50 side-r">
                                    <v-text-field dense outlined filled
                                        v-model="search.weight_end"
                                        label="Weight Max."
                                        prepend-icon="lens"
                                        v-on:keyup.enter="RunSearch()"
                                    />
                                </div>
                            </div>

                            <!-- Diameter -->
                            <div class="d-flex">
                                <div class="search-field-50 side-l">
                                    <v-text-field dense outlined filled
                                        v-model="search.diameter_start"
                                        label="Diameter Min."
                                        prepend-icon="hdr_weak"
                                        v-on:keyup.enter="RunSearch()"
                                    />
                                </div>
                                <div class="search-field-50 side-r">
                                    <v-text-field dense outlined filled
                                        v-model="search.diameter_end"
                                        label="Diameter Max."
                                        prepend-icon="hdr_strong"
                                        v-on:keyup.enter="RunSearch()"
                                    />
                                </div>
                            </div>
                        </template>
                    </v-expansion-panel-content>
                </v-expansion-panel>

                <hr class="search-divider" />

                <!-- Obverse / Reverse ------------------------------------------------- ------------------------------------------------- -->
                <template v-for="(key, i) in [
                    { v: 'o', text: 'Obverse' },
                    { v: 'r', text: 'Reverse' }
                ]">
                    <v-expansion-panel :key="'panel' + key.text" :class="activeTab === (6 + i) ? 'header_bg' : 'transparent'">
                        <!-- Header -->
                        <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                            <div class="d-flex align-center">
                                <v-icon v-text="i === 0 ? 'flip_to_front' : 'flip_to_back'" />
                                <div v-text="key.text + ' Depiction'" class="ml-4 whitespace-nowrap" />
                            </div>
                        </v-expansion-panel-header>

                        <!-- Content -->
                        <v-expansion-panel-content>

                            <!-- Design -->
                            <v-text-field filled dense outlined clearable
                                v-model="search[key.v + '_design']"
                                :label="key.text + ' Design (Fulltext Search)'"
                                prepend-icon="notes"
                                v-on:keyup.enter="RunSearch()"
                            />
                            <SearchForeignKey
                                :entity="'designs'"
                                :conditions="[{ side: key.v }]"
                                :label="key.text + ' Design'"
                                icon="notes"
                                :selected="search"
                                :selected_key="key.v + '_id_design'"
                                v-on:select="(emit) => { search[key.v + '_id_design'] = emit }"
                                v-on:keyup_enter="RunSearch()"
                            />

                            <!-- Legend -->
                            <SearchForeignKey
                                :entity="'legends'"
                                :conditions="[{ side: key.v }]"
                                :label="key.text + ' Legend'"
                                icon="translate"
                                sk="el_uc_adv"
                                :selected="search"
                                :selected_key="key.v + '_id_legend'"
                                v-on:select="(emit) => { search[key.v + '_id_legend'] = emit }"
                                v-on:keyup_enter="RunSearch()"
                            />

                            <!-- Monograms -->
                            <SearchForeignKey
                                entity="monograms"
                                :label="key.text + ' Monogram'"
                                icon="functions"
                                :selected="search"
                                :selected_key="key.v + '_id_monogram'"
                                v-on:select="(emit) => { search[key.v + '_id_monogram'] = emit }"
                                v-on:keyup_enter="RunSearch()"
                            />

                            <!-- Symbols -->
                            <SearchForeignKey
                                entity="symbols"
                                :label="key.text + ' Symbol'"
                                icon="flare"
                                :selected="search"
                                :selected_key="key.v + '_id_symbol'"
                                v-on:select="(emit) => { search[key.v + '_id_symbol'] = emit }"
                                v-on:keyup_enter="RunSearch()"
                            />
                        </v-expansion-panel-content>
                    </v-expansion-panel>

                    <hr v-if="i === 0" :key="'hr' + key.text" class="search-divider" />
                </template>

            </v-expansion-panels>
        </template>
    </drawer>

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
            searchCounter:      0,
            search_refresh:     0,
            dbi_params:         {},

            tools:              true,

            publisher:          false,
            checked_state:      false,
            checked:            [],

            /*tab:                'tab-0',
            tabs:               [
                {value: 0,  text: 'String Search'},
                {value: 1,  text: 'Frequently used'},
                {value: 2,  text: 'General'},
                {value: 3,  text: 'Production'},
                {value: 4,  text: 'Depiction'}
            ],*/
            cachedTab: 1,
            activeTab: null,

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

            searchStringKeyboard: false,
            searchStringKeyboardf: false
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
            ++this.searchCounter
            this.cacheCurrentSearch()
            this.SetItems()
        },

        async SetItems () {
            this.loading    = this.$root.loading = true;
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

            this.loading = this.$root.loading = false
            //this.scrollToTop()
        },

        scrollToTop () {
            const el = document.getElementById('results-anchor')
            el?.scrollIntoView({ behavior: "smooth" })
        },

        SetChecked (state) {
            const checkers = []
            this.checked = []

            this.items.forEach((item) => {
                checkers.push({ id: item.id, state: [0, 2].includes(item.public) ? state : false })
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
        },

        onDrawerExpand (expand) {
            if (expand) {
                if ([null, undefined].includes(this.activeTab)) setTimeout(() => { this.activeTab = this.cachedTab }, 350)
            }
            else {
                this.cachedTab = this.activeTab
                this.activeTab = null
            }
        }
    }
}

</script>

<style scoped>

    .start-screen {
        position: fixed;
        width: 500px;
        left: 50%;
        margin-left: -250px;
        height: 300px;
        top: 50%;
        margin-top: -150px;
    }

    .search-divider {
        opacity: 0.15;
        width: 100%;
        height: 0.5px;
    }

    .search-field-50 {
        width: 100%
    }

    .side-l {
        padding-right: 10px;
    }

    .side-r {
        padding-left: 10px;
    }

</style>
