<template>
<div>
    <!-- Toolbar ------------------------------------------------- ------------------------------------------------- ------------------------------------------------- -->
    <component-toolbar>
        <template v-slot:toolbar>
            <pagination-bar
                :offset="pagination.offset"
                :limit="pagination.limit"
                :count="pagination.count"
                :sortby="pagination.sort_by + ' ' + pagination.sort_by_op"
                :sorters="sorters"
                :layout="display"
                :layouts="display_mode"
                v-on:reload="processQuery()"
                v-on:offset="(emit) => { pagination.offset = emit; processQuery() }"
                v-on:limit="(emit) => { pagination.limit = emit; processQuery() }"
                v-on:sortby="OrderBy"
                v-on:layout="setDisplayMode"
            >
                <template v-slot:right>
                    <template v-if="routed && $root.user.level > 17">
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
                            color-hover="header_hover"
                            @click="togglePublisher()"
                        />
                    </template>

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

    <!-- Results Content ------------------------------------------------- ------------------------------------------------- ------------------------------------------------- -->
    <div id="primary-results-container" class="component-content">
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

        <!-- No results -->
        <div
            v-else-if="queried"
            class="mt-10 headline d-flex justify-center"
            v-html="loading ? 'Loading ...' : 'Sorry, no matching Records!'"
        />

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
                        v-on:keyup.enter="runQuery()"
                        style="width: 50%"
                    />

                    <v-text-field clearable dense
                        v-model="search.q"
                        :label="$root.label('keywords')"
                        append-icon="keyboard"
                        class="mb-n2"
                        v-on:keyup.enter="runQuery()"
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
                        @click="runQuery()"
                    />
                </div>
            </v-card>
        </div>
    </div>

    <!-- Search ------------------------------------------------- ------------------------------------------------- ------------------------------------------------- -->
    <drawer
        header
        :collapse="searchCounter"
        v-on:expand="onDrawerExpand"
        v-on:search="runQuery()"
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
                            <v-icon v-text="'star_border'" />
                            <div v-text="'Stored Queries'" class="ml-4 whitespace-nowrap" />
                        </div>
                    </v-expansion-panel-header>

                    <!-- Content -->
                    <v-expansion-panel-content>
                        <!-- Cache -->
                        <div class="d-flex">
                            <v-icon v-text="'history'" />
                            <div class="font-weight-bold ml-2" v-html="'Session&nbsp;History&nbsp;(' + cachedQueries.length + ')' " />
                        </div>
                        <div class="ml-8 mt-2 mb-2">
                            <v-divider />
                            <div style="height: 110px; overflow-y: scroll; overflow-x: hidden">
                                <template v-if="cachedQueries[0]">
                                    <div
                                        v-for="(q, i) in cachedQueries"
                                        :key="'storedQ' + i"
                                        class="pt-1 pb-1 pr-5"
                                    >
                                        <div
                                            class="text-truncate" v-text="q.text"
                                            style="cursor: pointer"
                                            @click="restoreCachedQuery(q.value)"
                                        />
                                    </div>
                                </template>
                                <div v-else class="pt-1 pb-1" v-text="'No cached queries, yet'" />
                            </div>
                            <v-divider />
                        </div>

                        <!-- Favorites -->
                        <div class="d-flex mt-8">
                            <v-icon v-text="'star'" />
                            <div class="font-weight-bold ml-2" v-html="'Favorites&nbsp;(' + Object.keys(storedQueries).length + ')'" />
                            <v-spacer />
                            <div
                                v-text="'+ Add current Query to Favorites'"
                                class="caption"
                                style="cursor: pointer;"
                                @click="storeCurrentQuery"
                            />
                        </div>
                        <div class="ml-8 mt-2 mb-2">
                            <v-divider />
                            <div style="height: 110px; overflow-y: scroll; overflow-x: hidden">
                                <template v-if="Object.keys(storedQueries)[0]">
                                    <div
                                        v-for="(value, name, i) in storedQueries"
                                        :key="'storedQ' + i"
                                        class="d-flex justify-space-between pt-1 pb-1"
                                    >
                                        <div
                                            class="text-truncate" v-text="name"
                                            style="cursor: pointer"
                                            @click="restoreCachedQuery(value)"
                                        />
                                        <v-icon v-text="'delete'" class="ml-5 mr-2" @click="deleteStoredQuery(name)" />
                                    </div>
                                </template>
                                <div v-else class="pt-1 pb-1" v-text="'No favorite queries, yet'" />
                            </div>
                            <v-divider />
                        </div>
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
                            v-on:keyup.enter="runQuery()"
                            v-on:click:append="searchStringKeyboardF = !searchStringKeyboardF"
                        />
                        <v-expand-transition>
                            <div v-if="searchStringKeyboardF" class="d-flex justify-center mb-3">
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
                                                    v-on:keyup.enter="runQuery()"
                                                />
                                            </v-col>

                                            <v-col cols="6">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.date_end"
                                                    label="Max."
                                                    append-outer-icon="first_page"
                                                    v-on:keyup.enter="runQuery()"
                                                />
                                            </v-col>

                                            <!-- Weight -->
                                            <v-col cols=12 v-text="'Weight'" class="mt-n5 mb-n2 body-1" />
                                            <v-col cols="6" v-if="entity == 'coins'">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.weight_start"
                                                    label="Min."
                                                    prepend-icon="last_page"
                                                    v-on:keyup.enter="runQuery()"
                                                />
                                            </v-col>

                                            <v-col cols="6" v-if="entity == 'coins'">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.weight_end"
                                                    label="Max."
                                                    append-outer-icon="first_page"
                                                    v-on:keyup.enter="runQuery()"
                                                />
                                            </v-col>

                                            <!-- Diameter -->
                                            <v-col cols=12 v-text="'Diameter'" class="mt-n5 mb-n2 body-1" />
                                            <v-col cols="6" v-if="entity == 'coins'">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.diameter_start"
                                                    label="Min."
                                                    prepend-icon="last_page"
                                                    v-on:keyup.enter="runQuery()"
                                                />
                                            </v-col>

                                            <v-col cols="6" v-if="entity == 'coins'">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="search.diameter_end"
                                                    label="Max."
                                                    append-outer-icon="first_page"
                                                    v-on:keyup.enter="runQuery()"
                                                />
                                            </v-col>

                                            <!-- Public -->
                                            <v-col cols=12 class="mt-n5">
                                                <div v-text="'Publication State'" class="body-1 mb-3" />
                                                <v-select dense outlined filled
                                                    :items="selects.state_public"
                                                    v-model="search.state_public"
                                                    v-on:keyup.enter="runQuery()"
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
                                    v-on:keyup.enter="runQuery()"
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
                                    v-on:keyup.enter="runQuery()"
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
                                    v-on:keyup.enter="runQuery()"
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
                                    v-on:keyup.enter="runQuery()"
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
                                    v-on:keyup.enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
                                />
                            </div>
                        </div>

                        <v-select dense outlined filled
                            v-model="search.imported"
                            :items="$store.state.lists.manual.imports"
                            prepend-icon="arrow_circle_down"
                            label="Scripted Coin Import"
                            :menu-props="{ offsetY: true }"
                            v-on:keyup.enter="runQuery()"
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
                                    v-on:keyup.enter="runQuery()"
                                />
                            </div>

                            <div class="search-field-50 side-r">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.source"
                                    label="Source Link"
                                    prepend-icon="arrow_circle_down"
                                    v-on:keyup.enter="runQuery()"
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
                            v-on:keyup_enter="runQuery()"
                        />
                    </v-expansion-panel-content>
                </v-expansion-panel>

                <hr class="search-divider" />

                <!-- Comment, References ------------------------------------------------- ------------------------------------------------- -->
                <v-expansion-panel :class="activeTab === 3 ? 'header_bg' : 'transparent'">
                    <!-- Header -->
                    <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                        <div class="d-flex align-center">
                            <v-icon v-text="'import_contacts'" />
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
                                    v-on:keyup.enter="runQuery()"
                                />
                            </div>

                            <div class="search-field-50 side-r">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.comment_public"
                                    label="Public Comment"
                                    prepend-icon="chat_bubble"
                                    v-on:keyup.enter="runQuery()"
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
                                    v-on:keyup.enter="runQuery()"
                                />
                            </div>

                            <div class="search-field-50 side-r">
                                <v-text-field dense outlined filled clearable
                                    v-model="search.comment_private"
                                    label="Private Comment"
                                    prepend-icon="chat_bubble_outline"
                                    v-on:keyup.enter="runQuery()"
                                />
                            </div>
                        </div>

                        <!-- Private Description -->
                        <v-text-field dense outlined filled clearable
                            v-model="search.description_private"
                            label="Private Description"
                            prepend-icon="label_important"
                            v-on:keyup.enter="runQuery()"
                        />
                        <v-text-field dense outlined filled clearable
                            v-if="entity === 'types'"
                            v-model="search.name_private"
                            label="Private Name"
                            prepend-icon="label"
                            v-on:keyup.enter="runQuery()"
                        />

                        <!-- References -->
                        <SearchForeignKey
                            entity="references"
                            label="Bibliography"
                            icon="menu_book"
                            :selected="search"
                            selected_key="id_reference"
                            v-on:select="(emit) => { search.id_reference = emit }"
                            v-on:keyup_enter="runQuery()"
                        />

                        <!-- Owner -->
                        <SearchForeignKey
                            entity="owners"
                            label="Owner"
                            icon="account_circle"
                            :selected="search"
                            selected_key="id_owner"
                            v-on:select="(emit) => { search.id_owner = emit }"
                            v-on:keyup_enter="runQuery()"
                        />
                        <template v-if="entity === 'coins'">
                            <v-text-field dense outlined filled clearable
                                v-if="entity === 'coins'"
                                v-model="search.provenience"
                                label="Provenience"
                                prepend-icon="play_circle_outline"
                                v-on:keyup.enter="runQuery()"
                            />
                            <div class="d-flex">
                                <div class="search-field-50 side-l">
                                    <v-text-field dense outlined filled clearable
                                        v-model="search.collection_nr"
                                        label="Collection Nr."
                                        prepend-icon="bookmarks"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                                <div class="search-field-50 side-r">
                                    <v-text-field dense outlined filled clearable
                                        v-model="search.plastercast_nr"
                                        label="Plastercast Nr."
                                        prepend-icon="bookmarks"
                                        v-on:keyup.enter="runQuery()"
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
                            v-on:keyup_enter="runQuery()"
                        />

                        <!-- Findspots -->
                        <SearchForeignKey
                            entity="findspots"
                            label="Findspot"
                            icon="pin_drop"
                            :selected="search"
                            selected_key="id_findspot"
                            v-on:select="(emit) => { search.id_findspot = emit }"
                            v-on:keyup_enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
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
                            v-on:keyup_enter="runQuery()"
                        />

                        <!-- Period -->
                        <SearchForeignKey
                            entity="periods"
                            label="Epoch"
                            icon="watch_later"
                            :selected="search"
                            selected_key="id_period"
                            v-on:select="(emit) => { search.id_period = emit }"
                            v-on:keyup_enter="runQuery()"
                        />

                        <!-- Date -->
                        <div class="d-flex">
                            <div class="search-field-50 side-l">
                                <v-text-field dense outlined filled
                                    v-model="search.date_start"
                                    label="Date Start"
                                    prepend-icon="first_page"
                                    v-on:keyup.enter="runQuery()"
                                />
                            </div>
                            <div class="search-field-50 side-r">
                                <v-text-field dense outlined filled
                                    v-model="search.date_end"
                                    label="Date End"
                                    prepend-icon="last_page"
                                    v-on:keyup.enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
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
                                    v-on:keyup_enter="runQuery()"
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
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                                <div class="search-field-50 side-r">
                                    <v-text-field dense outlined filled
                                        v-model="search.weight_end"
                                        label="Weight Max."
                                        prepend-icon="lens"
                                        v-on:keyup.enter="runQuery()"
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
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                                <div class="search-field-50 side-r">
                                    <v-text-field dense outlined filled
                                        v-model="search.diameter_end"
                                        label="Diameter Max."
                                        prepend-icon="hdr_strong"
                                        v-on:keyup.enter="runQuery()"
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
                                v-on:keyup.enter="runQuery()"
                            />
                            <SearchForeignKey
                                :entity="'designs'"
                                :conditions="[{ side: key.v }]"
                                :label="key.text + ' Design'"
                                icon="notes"
                                :selected="search"
                                :selected_key="key.v + '_id_design'"
                                v-on:select="(emit) => { search[key.v + '_id_design'] = emit }"
                                v-on:keyup_enter="runQuery()"
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
                                v-on:keyup_enter="runQuery()"
                            />

                            <!-- Monograms -->
                            <SearchForeignKey
                                entity="monograms"
                                :label="key.text + ' Monogram'"
                                icon="functions"
                                :selected="search"
                                :selected_key="key.v + '_id_monogram'"
                                v-on:select="(emit) => { search[key.v + '_id_monogram'] = emit }"
                                v-on:keyup_enter="runQuery()"
                            />

                            <!-- Symbols -->
                            <SearchForeignKey
                                entity="symbols"
                                :label="key.text + ' Symbol'"
                                icon="flare"
                                :selected="search"
                                :selected_key="key.v + '_id_symbol'"
                                v-on:select="(emit) => { search[key.v + '_id_symbol'] = emit }"
                                v-on:keyup_enter="runQuery()"
                            />
                        </v-expansion-panel-content>
                    </v-expansion-panel>

                    <hr v-if="i === 0" :key="'hr' + key.text" class="search-divider" />
                </template>

            </v-expansion-panels>
        </template>
    </drawer>



    <!-- Manage Favorites -->
    <v-dialog
        tile
        persistent
        v-model="queryDialog.show"
        :width="500"
    >
        <v-card tile>
            <dialogbartop
                icon="star"
                text="Query Favorites"
                :fullscreen="null"
                v-on:close="queryDialog.show = false"
            ></dialogbartop>

            <v-card-text>

                <div class="text-center pa-4">
                    Please provide a unique name for your Query. Otherwise an older query of the same name will be overwritten.
                </div>

                <v-text-field
                    dense filled outlined clearable
                    v-model="queryDialog.text"
                />
                <div class="d-flex flex-wrap justify-center mt-n4">
                    <v-checkbox
                        v-model="queryDialog.page"
                        label="Save current Page offset"
                    />
                </div>
            </v-card-text>

            <div class="d-flex justify-center mb-2" >
                <v-btn text v-text="'cancel'" @click="queryDialog.show = false" class="mr-1" />
                <v-btn text v-text="'save'" :disabled="!queryDialog.text" @click="saveQuery()" class="ml-1" />
            </div>
        </v-card>
    </v-dialog>

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
            queried:            false,
            loading:            false,
            error:              false,

            items:              [],

            search:             this.constructParams(),
            searchCounter:      0,
            pagination:         {},

            checked_state:      false,
            checked:            [],

            cachedTab:          1,
            activeTab:          null,

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
            searchStringKeyboardF: false,

            queryDialog: {
                show: false,
                text: null,
                page: false
            }
        }
    },

    props: {
        entity:         { type: String, default: 'coins' },
        publisher:      { type: Boolean, default: 'search' },
        selected:       { type: [Number, String], default: null },
        routedQuery:    { type: Object },
        routed:         { type: Boolean, default: false }
    },

    computed: {
        router () { return this.routed },

        l () { return this.$root.language },
        labels () { return this.$root.labels },

        sorters () {
            const sorters = [
                { value: 'id', text: 'ID' },
                { value: 'date', text: this.labels['date'] }
            ]

            if (this.entity === 'coins') {
                sorters.push({ value: 'weight', text: this.labels['weight'] }),
                sorters.push({ value: 'diameter', text: this.labels['diameter'] })
            }

            sorters.push({ value: 'mint', text: this.labels['mint'] })
            sorters.push({ value: 'ruler', text: this.labels['authority_person'] })
            sorters.push({ value: 'created', text: this.labels['created_at'] })
            sorters.push({ value: 'updated', text: this.labels['updated_at'] })

            return sorters
        },

        query () {
            if (!this.routed) return {}
            else {
                const query = this.routedQuery
                if (query.public !== undefined) {
                    query.state_public = query.public == 'all' ? null : parseInt(query.public)
                    delete(query.public)
                    delete(query.sort_by_op)
                }
                return query
            }
        },

        queryGiven () {
            return Object.keys(this.query)[0] ? true : false
        },

        cachedQueries () {
            return this.$store.state.cache[this.entity].map((query) => {
                let q = {}
                let pPublic = ''
                let sortBy = ''

                query.split('&').forEach((param) => {
                    const split = param.split('=')
                    const key = split.shift()
                    const val = split.join('=')

                    if (key === 'public') pPublic = val.replaceAll('-', ', ')
                    else if (key === 'sort_by') sortBy = val.replace('.', ', ')
                    else q[key] = (q[key] ? (q[key] + ', ') : '') + val
                })

                let text = Object.keys(q).map((key) => { return key + ': ' + q[key] }).join('; ') ?? ''
                if (text) text += '; '
                text += 'sort: ' + sortBy + '; public: ' + pPublic

                return {
                    value: 'limit=' + this.pagination.limit + '&' + query,
                    text
                }
            })
        },

        storedQueries () {
            let queries = this.$root.settings?.queries
            if (!queries) return {}
            queries = JSON.parse(queries)
            return queries?.[this.entity] ?? {}
        }
    },

    watch: {
        entity: function () { this.Init() },
        query: function () { this.handleQuery() }
    },

    created () {
        this.Init()
        if (this.routed) this.handleQuery()
    },

    methods: {
        Init () {
            this.queried = false
            this.SearchDefaults()
            this.items = []
            this.pagination= {
                sort_by: 'id',
                sort_by_op:  'DESC',
                count:  0,
                offset: 0,
                limit:  12
            }
        },

        SearchDefaults (message) {
            /*const search = {}
            Object.keys(this.search).forEach((key) => {
                if (key === 'state_public')                 search[key] = this.publisher ? 2 : null
                else if (Array.isArray(this.search[key]))   search[key] = []
                else                                        search[key] = null
            })*/
            this.search = this.constructParams() //search
            if (message) this.$store.dispatch('showSnack', { color: 'blue_sec', message: 'Query Parameters set to default!' })
        },

        handleQuery () {
            if (this.publisher && !this.queryGiven) this.togglePublisher(true)
            else if (this.queryGiven) {
                this.getItems(this.query)
                this.reconstructSearch()
            }
        },

        runQuery () {
            ++this.searchCounter
            this.cacheCurrentQuery()
            this.processQuery()
        },

        reconstructSearch () {
            this.search = this.constructParams(this.query)
            /*console.log('construct', this.constructParams(this.query))
            for (let [key, value] of Object.entries({ ...this.query })) {
                if (key === 'offset') this.pagination.offset = value ?? 0
                else if (key === 'limit') this.pagination.limit = value ?? 12
                else if (key === 'sort_by') {
                    const split = value?.split('.')
                    this.pagination.sort_by = split?.[0] ?? 'id'
                    this.pagination.sort_by_op = split?.[1] ?? 'DESC'
                }
                else if (key === 'state_public' && (value === [0, 1, 2] || value === 'all'))  this.search.state_public = null
                else if (key !== 'i') {
                    //console.log(key, value)
                    this.search[key] = value
                }
                //console.log('RECO', this.search, this.pagination)
            }*/
                //console.log(this.search)
        },

        processQuery () {
            const query = {}

            // Get Search Parameters
            Object.keys(this.search).forEach((key) => {
                if (key === 'state_public') {
                query[key] = this.search[key] === null ? [0, 1, 2] : this.search[key]
                }
                else if (['string', 'o_design', 'r_design'].includes(key)) {
                    if (this.search[key]) {
                        search[key] = this.search[key].split(' ')
                    }
                }
                else if (![null, []].includes(this.search[key])) {
                    query[key] = this.search[key]
                }
            })

            // Set DBI Parameters
            const params = {}
            Object.keys(this.pagination).forEach((key) => {
                if (key === 'sort_by') {
                    params[key] = this.pagination.sort_by + '.' + this.pagination.sort_by_op
                }
                else if (key != 'sort_by_op') {
                    params[key] = this.pagination[key]
                }
            })

            if (this.router) {
                if (query.state_public) {
                    query.public = typeof query.state_public === 'number' ? query.state_public : 'all'
                    delete(query.state_public)
                }
                this.$router.push({ query: {
                    ...{
                        i: this.searchCounter,
                        offset: params.offset,
                        limit: params.limit,
                        sort_by: params.sort_by,
                        sort_by_op: params.sort_by_op
                    },
                    ...query
                }})
            }
            else this.getItems(params, query)
        },

        async getItems (query) {
            this.queried = true
            this.error  = false
            this.loading = this.$root.loading = true

            const dbi = await this.$root.DBI_SELECT_POST(this.entity, query)

            if (dbi?.contents) {
                const sort_explode = dbi.pagination.sort_by.split(' ')
                dbi.pagination.sort_by = sort_explode[0]
                dbi.pagination.sort_by_op = sort_explode[1]
                this.pagination = dbi.pagination

                this.items = dbi.contents
                if (this.items[0]) this.SetChecked (false)
                else this.$store.dispatch('showSnack', { message: 'No items found!' })
            }
            else {
                console.error('ERROR', dbi)
                this.error = true
            }

            this.loading = this.$root.loading = false
            this.scrollToTop()
        },

        scrollToTop () {
            document.getElementById('primary-results-container')?.scrollTo(0, 0)
        },

        SetChecked (state) {
            const checkers = []
            this.checked = []

            this.items.forEach((item) => {
                checkers.push({ id: item.id, state: [0, 2].includes(item.public) ? state : false })
            })
            this.checked = checkers
        },

        ScrollTop () {
            window.scrollTo({ top: 0, left: 0, behavior: 'smooth' })
        },

        OrderBy (input) {
            if (input === this.pagination.sort_by) {
                this.pagination.sort_by_op = this.pagination.sort_by_op != 'ASC' ? 'ASC' : 'DESC'
            }
            else {
                this.pagination.sort_by = input
                this.pagination.sort_by_op = 'ASC'
            }
            this.pagination.offset = 0
            this.cacheCurrentQuery()
            this.processQuery();
        },

        togglePublisher (replace) {
            if (this.publisher) {

                if (this.queryGiven) {
                    let q = window.location.href?.split('?') ?? [null]
                    q.shift()
                    q = q.join('?')
                    this.$router.push('/editor#/' + this.entity + '/publish?' + q)
                }
                else {
                    const path = {
                        path: '/' + this.entity + '/publish',
                        query: {
                            limit: this.pagination.limit,
                            sort_by: 'updated_at.DESC',
                            public: 2
                        }
                    }

                    this.$router[replace ? 'replace' : 'push'](path)
                }
            }
            else this.$router.push('/editor#/' + this.entity + '/search')
        },

        async Publish (input, mode) {
            let items = input.filter(item => { return item.state === true })

            if (items [0]) {
                let confirmed = mode === 1 ? true : (confirm(this.labels[this.entity.slice(0, -1)] + ' ' + input[0].id + ' will be ' + (mode === 3 ? 'deleted!' : 'unpublished!') ));

                if (confirmed === true) {
                    const response = await this.$root.DBI_INPUT_POST('publish', 'input', { entity: this.entity, items: items.map((item) => { return item.id }), mode: mode });

                    if (response.success) {
                        this.$store.dispatch('showSnack', { color: 'success', message: response.success })
                        this.processQuery()
                    }
                }
            }
            else {
                this.$store.dispatch('showSnack', { color: 'error', message: 'No items selected!' })
                this.processQuery();
            }
        },

        setDisplayMode (value) {
            this.$store.commit('set_display_mode', value)
            this.display = value
        },

        getQueryString (page) {
            let query = window.location.href?.split('?') ?? [null]
            query.shift()
            const pattern = '(i' + (page ? '' : '|limit|offset') + ')=[^&]+&'
            return query.join('?').replaceAll(new RegExp(pattern, 'g'), '')
        },

        cacheCurrentQuery () {
            if (this.routed) {
                const query = this.getQueryString()

                if (query) {
                    const value = this.$store.state.cache[this.entity].slice(0, 49)
                    if (value[0] !== query) {
                        value.unshift(query)
                        this.$store.commit('setCache', { key: this.entity, value })
                    }
                }
            }
        },

        storeCurrentQuery () {
            this.queryDialog = {
                show: true,
                page: false,
                text: '' + Date.now()
            }
        },

        saveQuery () {
            console.log(this.queryDialog)
            const queries = { ...this.storedQueries }
            queries[this.queryDialog.text.trim()] = this.getQueryString(this.queryDialog.page)
            console.log(queries)
            this.$root.changeSettings('queries', JSON.stringify({ [this.entity]: queries }))
            this.queryDialog.show = false
        },

        deleteStoredQuery (key) {
            const queries = { ...this.storedQueries }
            delete(queries[key])
            this.$root.changeSettings('queries', JSON.stringify({ [this.entity]: queries }))
        },

        restoreCachedQuery (query) {
            this.SearchDefaults()
            const regex = new RegExp('limit=[^&]+&')
            if (!regex.test(query)) query = 'limit=' + this.pagination.limit + '&' + query
            //console.log(query)
            window.location.href = '/editor#/' + this.entity + '/' + (this.publisher ? 'publish' : 'search') + '?' + query
            ++this.searchCounter
        },

        onDrawerExpand (expand) {
            if (expand) {
                if ([null, undefined].includes(this.activeTab)) setTimeout(() => { this.activeTab = this.cachedTab }, 350)
            }
            else {
                this.cachedTab = this.activeTab
                this.activeTab = null
            }
        },

        constructParams (input) {
            const d = input === null || input === undefined || typeof input !== 'object' ? {} : { ...input }

            // Public
            let state_public = null
            if (d.state_public !== undefined && d.state_public !== [0, 1, 2]) state_public = parseInt(d.state_public)
            else if (d.public !== undefined && d.public !== 'all') state_public = parseInt(d.state_public)

            return {
                state_public,
                sort_by:            d.sort_by ?? 'id.DESC',
                offset:             d.offset ? parseInt(d.offset) : 0,
                limit:              d.limit ? parseInt(d.limit) : 12,

                id:                 this.parseInt(d.id),
                q:                  d.q ?? null,

                collection_nr:      d.collection_nr ?? null,
                comment_private:    d.comment_private ?? null,
                comment_public:     d.comment_public ?? null,
                date_end:           this.parseYear(d.date_end),
                date_start:         this.parseYear(d.date_start),
                description_private: d.description_private ?? null,
                diameter_end:       this.parseFloat(d.diameter_end),
                diameter_start:     this.parseFloat(d.diameter_start),
                has_images:         this.parseBoolean(d.has_images),
                id_authority:       this.parseArrayInt(d.id_authority),
                id_authority_group: this.parseArrayInt(d.id_authority_group),
                id_authority_person: this.parseArrayInt(d.id_authority_person),
                id_creator:         this.parseArrayInt(d.id_creator),
                id_denomination:    this.parseArrayInt(d.id_denomination),
                id_design:          this.parseArrayInt(d.id_design),
                id_die:             this.parseArrayInt(d.id_die),
                id_editor:          this.parseArrayInt(d.id_editor),
                id_findspot:        this.parseArrayInt(d.id_findspot),
                id_group:           this.parseArrayInt(d.id_group),
                id_hoard:           this.parseArrayInt(d.id_hoard),
                id_legend:          this.parseArrayInt(d.id_legend),
                id_material:        this.parseArrayInt(d.id_material),
                id_mint:            this.parseArrayInt(d.id_mint),
                id_monogram:        this.parseArrayInt(d.id_monogram),
                id_owner:           this.parseArrayInt(d.id_owner),
                id_period:          this.parseArrayInt(d.id_period),
                id_person:          this.parseArrayInt(d.id_person),
                id_reference:       this.parseArrayString(d.id_reference),
                id_region:          this.parseArrayInt(d.id_region),
                id_standard:        this.parseArrayInt(d.id_standard),
                id_symbol:          this.parseArrayInt(d.id_symbol),
                id_type:            this.parseInt(d.id_type),
                imported:           this.parseBoolean(d.imported),

                plastercast_nr:     d.plastercast_nr ?? null,
                provenience:        d.provenience ?? null,

                source:             d.source ?? null,
                state_comment_private: d.state_comment_private ?? null,
                state_comment_public: d.state_comment_public ?? null,
                state_inherited:    this.parseBoolean(d.state_inherited),
                state_linked:       this.parseBoolean(d.state_linked),
                state_source:       this.parseBoolean(d.state_source),
                weight_end:         this.parseFloat(d.weight_end),
                weight_start:       this.parseFloat(d.weight_start),

                o_design:           this.parseArrayInt(d.o_design),
                o_id_design:        this.parseArrayInt(d.o_id_design),
                o_id_legend:        this.parseArrayInt(d.o_id_legend),
                o_id_monogram:      this.parseArrayInt(d.o_id_monogram),
                o_id_symbol:        this.parseArrayInt(d.o_id_symbol),

                r_design:           this.parseArrayInt(d.r_design),
                r_id_design:        this.parseArrayInt(d.r_id_design),
                r_id_legend:        this.parseArrayInt(d.r_id_legend),
                r_id_monogram:      this.parseArrayInt(d.r_id_monogram),
                r_id_symbol:        this.parseArrayInt(d.r_id_symbol),
            }
        },

        parseArrayInt (val) {
            return val ? (typeof val === 'object' ? val : [val]).map((s) => parseInt(s)) : []
        },
        parseArrayString (val) {
            return val ? (typeof val === 'object' ? val : [val]) : []
        },
        parseInt (val) {
            return val ? parseInt(val) : null
        },
        parseFloat (val) {
            return val ? parseFloat(val) : null
        },
        parseBoolean (val) {
            if (val === undefined) return null
            if (val === null) return null
            if (parseInt(val) === 1) return 1
            if (parseInt(val) === 0) return 0
            return null
        },
        parseYear (val) {
            return val ? (parseInt(val) ?? null) : null
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
