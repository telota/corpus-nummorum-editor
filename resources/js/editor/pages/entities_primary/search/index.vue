<template>
<div>
    <dialog-template
        :dialog="dialog"
        :text="$root.label(entity)"
        :closing="closing"
        :select="select"
        :selected="selected"
        v-on:close="$emit('close')"
    >
        <!-- Toolbar -->
        <div class="header_bg" :class="'component-toolbar' + (dialog ? ' component-toolbar-dialog' : '')">
            <pagination-bar
                :offset="pagination.offset"
                :limit="pagination.limit"
                :count="pagination.count"
                :sortby="pagination.sort_by"
                :sorters="sorters"
                :layout="layout"
                :layouts="layouts"
                :loading="loading"
                :dialog="dialog"
                v-on:reload="setItems()"
                v-on:offset="setOffset"
                v-on:limit="setLimit"
                v-on:sortby="setSortBy"
                v-on:layout="setLayout"
            >
                <template v-slot:right>
                    <template v-if="!dialog">
                        <template v-if="$root.user.level > 17">
                            <v-slide-x-reverse-transition>
                                <item-publisher-group
                                    v-if="publisher"
                                    :entity="entity"
                                    :items="checkedItems"
                                    :checked-state="checkedState"
                                    @checkall="checkAll()"
                                    @refresh="setItems()"
                                />
                            </v-slide-x-reverse-transition>
                            <!-- Publisher Functions
                            <v-slide-x-reverse-transition>
                                <adv-btn
                                    v-if="publisher"
                                    :icon="checkedState ? 'radio_button_unchecked' : 'radio_button_checked'"
                                    :tooltip="(checkedState ? 'Deselect' : 'Select') + ' all ' + labels[entity]"
                                    :disabled="items[0] ? false : true"
                                    color-main="blue_prim"
                                    color-hover="blue_sec"
                                    color-text="white"
                                    @click="checkedState = !checkedState; SetChecked(checkedState);"
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
                            </v-slide-x-reverse-transition> -->

                            <!-- Publisher Toggle -->
                            <adv-btn
                                :icon="publisher ? 'public_off' : 'public'"
                                :tooltip="publisher ? 'Hide Publisher' : 'Show Publisher'"
                                color-hover="header_hover"
                                @click="togglePublisher()"
                            />
                        </template>

                        <!-- Switch Coin/Types -->
                        <a :href="'/editor#/' + (entity === 'coins' ? 'types' : 'coins') + '/' + (publisher ? 'publish' : 'search')">
                            <adv-btn
                                :icon="entity === 'coins' ? 'blur_circular' : 'copyright'"
                                :tooltip="entity === 'coins' ? 'Go to Types' : 'Go to Coins'"
                                color-hover="header_hover"
                            />
                        </a>
                    </template>

                    <div v-else />
                </template>
            </pagination-bar>
        </div>

        <!-- Split Screen -->
        <split-screen-template
            :dialog="dialog"
            :scroll-center-to-top="scrollToTop"
            filter-drawer
            :center-width-min="399"
            :left-width-default="400"
            @search="runQuery()"
            @clear="resetFilters(true)"
            @resize="(emit) => {
                resultsWidth = emit.center
                filterWidth = emit.left
            }"
            @expand="handleDrawerExpansion"
        >
            <template v-slot:left>
                <v-expansion-panels
                    accordion
                    tile
                    flat
                    multiple
                    v-model="activeTab"
                    :dis-style="'z-index:' + (dialog ? 202 : 100)"
                >

                    <!-- Params ------------------------------------------------- ------------------------------------------------- -->
                    <v-expansion-panel  :class="activeTab === 0 ? 'header_bg' : 'transparent'">
                        <!-- Header -->
                        <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                            <div class="d-flex align-center">
                                <v-icon v-text="'list_alt'" />
                                <div v-text="'Current Parameters'" class="ml-4 whitespace-nowrap" />
                            </div>
                        </v-expansion-panel-header>

                        <!-- Content -->
                        <v-expansion-panel-content>
                            <div class="d-flex flex-wrap mt-n2">
                                <template v-for="(value, key, i) in filters">
                                    <template v-if="value !== undefined && value !== null && !(typeof value === 'object' && !value[0])">
                                        <v-chip
                                        :key="key + i"
                                        color="blue_prim"
                                        class="ma-1"
                                        outlined
                                        label
                                        close
                                        @click:close="typeof value === 'array' && value[0] ? filters[key] = [] : filters[key] = null"
                                        >
                                            {{ key }}: {{ value }}
                                        </v-chip>
                                    </template>
                                </template>
                            </div>
                        </v-expansion-panel-content>
                    </v-expansion-panel>

                    <hr class="search-divider" />

                    <!-- Favorites ------------------------------------------------- ------------------------------------------------- -->
                    <v-expansion-panel :class="activeTab === 1 ? 'header_bg' : 'transparent'">
                        <!-- Header -->
                        <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                            <div class="d-flex align-center">
                                <v-icon v-text="'star_border'" />
                                <div v-text="'Stored Queries'" class="ml-4 whitespace-nowrap" />
                            </div>
                        </v-expansion-panel-header>

                        <!-- Content -->
                        <v-expansion-panel-content>
                            <div v-if="dialog">
                                Favorites are not yet available in dialog mode, but routed mode only.
                            </div>

                            <template v-else>

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
                                                :key="'cachedQ' + i"
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
                                        v-text="'+ Add current Query'"
                                        class="caption"
                                        style="cursor: pointer;"
                                        @click="showQueryDialog"
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

                            </template>
                        </v-expansion-panel-content>
                    </v-expansion-panel>

                    <hr class="search-divider" />

                    <!-- String Search ------------------------------------------------- ------------------------------------------------- -->
                    <v-expansion-panel :class="activeTab === 2 ? 'header_bg' : 'transparent'">
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
                                ref="stringSearchInput"
                                v-model="filters.q"
                                :label="$root.label('keywords')"
                                append-icon="keyboard"
                                class="mt-5 mb-n4"
                                v-on:keyup.enter="runQuery()"
                                v-on:click:append="searchStringKeyboardF = !searchStringKeyboardF"
                            />
                            <v-expand-transition>
                                <div v-if="searchStringKeyboardF" class="d-flex justify-center mb-3">
                                    <keyboard
                                        :string="filters.q"
                                        layout="el_uc"
                                        small
                                        hide_options
                                        v-on:input="(emit) => { filters.q = emit }"
                                    />
                                </div>
                            </v-expand-transition>

                            <!-- Options -->
                            <div class="d-flex flex-wrap align-center mt-n5">
                                <div class="d-flex mr-9 align-center">
                                    <v-checkbox
                                        v-model="filters.qre"
                                        label="REGEX"
                                        class="mr-1"
                                    />
                                    <sup class="body-1"><a href="https://en.wikipedia.org/wiki/Regular_expression#Syntax" target="_blank"> *</a></sup>
                                </div>

                                <v-checkbox
                                    v-model="filters.qcs"
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
                                                        v-model="filters.date_start"
                                                        label="Min."
                                                        prepend-icon="last_page"
                                                        v-on:keyup.enter="runQuery()"
                                                    />
                                                </v-col>

                                                <v-col cols="6">
                                                    <v-text-field dense outlined filled clearable
                                                        v-model="filters.date_end"
                                                        label="Max."
                                                        append-outer-icon="first_page"
                                                        v-on:keyup.enter="runQuery()"
                                                    />
                                                </v-col>

                                                <!-- Weight -->
                                                <v-col cols=12 v-text="'Weight'" class="mt-n5 mb-n2 body-1" />
                                                <v-col cols="6" v-if="entity == 'coins'">
                                                    <v-text-field dense outlined filled clearable
                                                        v-model="filters.weight_start"
                                                        label="Min."
                                                        prepend-icon="last_page"
                                                        v-on:keyup.enter="runQuery()"
                                                    />
                                                </v-col>

                                                <v-col cols="6" v-if="entity == 'coins'">
                                                    <v-text-field dense outlined filled clearable
                                                        v-model="filters.weight_end"
                                                        label="Max."
                                                        append-outer-icon="first_page"
                                                        v-on:keyup.enter="runQuery()"
                                                    />
                                                </v-col>

                                                <!-- Diameter -->
                                                <v-col cols=12 v-text="'Diameter'" class="mt-n5 mb-n2 body-1" />
                                                <v-col cols="6" v-if="entity == 'coins'">
                                                    <v-text-field dense outlined filled clearable
                                                        v-model="filters.diameter_start"
                                                        label="Min."
                                                        prepend-icon="last_page"
                                                        v-on:keyup.enter="runQuery()"
                                                    />
                                                </v-col>

                                                <v-col cols="6" v-if="entity == 'coins'">
                                                    <v-text-field dense outlined filled clearable
                                                        v-model="filters.diameter_end"
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
                                                        v-model="filters.state_public"
                                                        :menu-props="{ offsetY: true }"
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

                    <!-- System ------------------------------------------------- ------------------------------------------------- -->
                    <v-expansion-panel :class="activeTab === 3 ? 'header_bg' : 'transparent'">
                        <!-- Header -->
                        <v-expansion-panel-header class="pl-2 font-weight-bold" style="height: 40px">
                            <div class="d-flex align-center">
                                <v-icon v-text="'settings'" />
                                <div v-text="'System'" class="ml-4 whitespace-nowrap" />
                            </div>
                        </v-expansion-panel-header>

                        <!-- Content -->
                        <v-expansion-panel-content>
                            <div :class="fCols.small.flex">
                                <!-- ID -->
                                <div :class="fCols.small.l">
                                    <v-text-field dense outlined filled clearable
                                        v-model="filters.id"
                                        :label="labels[entity.slice(0, -1)] + ' ID'"
                                        prepend-icon="fingerprint"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>

                                <!-- Public -->
                                <div :class="fCols.small.r">
                                    <v-select dense outlined filled
                                        :items="selects.state_public"
                                        v-model="filters.state_public"
                                        prepend-icon="publish"
                                        label="Publication State"
                                        :menu-props="{ offsetY: true }"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <div :class="fCols.small.flex">
                                <!-- Linked to Type/coin -->
                                <div :class="fCols.small.l">
                                    <v-select dense outlined filled
                                        :prepend-icon="entity == 'coins' ? 'blur_circular' : 'copyright'"
                                        :label="'Linked to '+(entity == 'coins' ? 'Type' : 'Coin')+'?'"
                                        v-model="filters.state_linked"
                                        :items="selects.state_yes_no"
                                        :menu-props="{ offsetY: true }"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>

                                <!-- Linked to Type/coin -->
                                <div :class="fCols.small.r">
                                    <v-select dense outlined filled
                                        prepend-icon="sync"
                                        :label="(entity == 'coins' ? 'Is inherited' : 'Inherits to Coins')+'?'"
                                        v-model="filters.state_inherited"
                                        :items="selects.state_yes_no"
                                        :menu-props="{ offsetY: true }"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <div :class="fCols.small.flex">
                                <!-- ID Opposite Entity -->
                                <div :class="fCols.small.l">
                                    <v-text-field dense outlined filled clearable
                                        v-model="filters['id_'+(entity == 'coins' ? 'type' : 'coin')]"
                                        :label="'Linked '+(entity == 'coins' ? 'Type' : 'Coin')+' ID'"
                                        :prepend-icon="entity == 'coins' ? 'blur_circular' : 'copyright'"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                                <div :class="fCols.small.r"/>
                            </div>

                            <!-- Creator / Editor -->
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <SearchForeignKey
                                        entity="users"
                                        label="Creator"
                                        icon="person"
                                        :selected="filters.id_creator"
                                        v-on:select="(emit) => { filters.id_creator = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>

                                <div :class="fCols.small.r">
                                    <SearchForeignKey
                                        entity="users"
                                        label="Editor"
                                        icon="person_outline"
                                        :selected="filters.id_editor"
                                        v-on:select="(emit) => { filters.id_editor = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <!-- Source -->
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <v-select dense outlined filled
                                        v-model="filters.state_source"
                                        :items="selects.state_yes_no"
                                        prepend-icon="arrow_circle_down"
                                        label="Has Source?"
                                        :menu-props="{ offsetY: true }"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>

                                <div :class="fCols.small.r">
                                    <v-text-field dense outlined filled clearable
                                        v-model="filters.source"
                                        label="Source Link"
                                        prepend-icon="arrow_circle_down"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <!-- Import / Group -->
                            <div :class="fCols.large.flex">
                                <div :class="fCols.large.l">

                                    <v-select dense outlined filled
                                        v-model="filters.imported"
                                        :items="$store.state.lists.manual.imports"
                                        prepend-icon="arrow_circle_down"
                                        label="Scripted Coin Import"
                                        :menu-props="{ offsetY: true }"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>

                                <div :class="fCols.large.r">

                                    <SearchForeignKey
                                        entity="objectgroups"
                                        label="Object Group"
                                        icon="control_camera"
                                        :selected="filters.id_group"
                                        v-on:select="(emit) => { filters.id_group = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                            </div>
                        </v-expansion-panel-content>
                    </v-expansion-panel>

                    <hr class="search-divider" />

                    <!-- Comment, References ------------------------------------------------- ------------------------------------------------- -->
                    <v-expansion-panel :class="activeTab === 4 ? 'header_bg' : 'transparent'">
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
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <v-select dense outlined filled
                                        v-model="filters.state_comment_public"
                                        :items="selects.state_yes_no"
                                        prepend-icon="chat_bubble"
                                        label="Has public Comment?"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>

                                <div :class="fCols.small.r">
                                    <v-text-field dense outlined filled clearable
                                        v-model="filters.comment_public"
                                        label="Public Comment"
                                        prepend-icon="chat_bubble"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <!-- Private Comment -->
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <v-select dense outlined filled
                                        v-model="filters.state_comment_private"
                                        :items="selects.state_yes_no"
                                        prepend-icon="chat_bubble_outline"
                                        label="Has private Comment?"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>

                                <div :class="fCols.small.r">
                                    <v-text-field dense outlined filled clearable
                                        v-model="filters.comment_private"
                                        label="Private Comment"
                                        prepend-icon="chat_bubble_outline"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <!-- Private Description -->
                            <div :class="fCols.large.flex">
                                <div :class="fCols.large.l">
                                    <v-text-field dense outlined filled clearable
                                        v-model="filters.description_private"
                                        label="Private Description"
                                        prepend-icon="label_important"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                                <div :class="fCols.large.r">
                                    <v-text-field dense outlined filled clearable
                                        v-if="entity === 'types'"
                                        v-model="filters.name_private"
                                        label="Private Name"
                                        prepend-icon="label"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <!-- References -->
                            <div :class="fCols.large.flex">
	                            <div :class="fCols.large.l">
                                    <SearchForeignKey
                                        entity="references"
                                        label="Bibliography"
                                        icon="menu_book"
                                        :selected="filters.id_reference"
                                        v-on:select="(emit) => { filters.id_reference = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                                <div :class="fCols.large.r">
                                    <!-- Owner -->
                                    <SearchForeignKey
                                        entity="owners"
                                        label="Owner"
                                        icon="account_circle"
                                        :selected="filters.id_owner"
                                        v-on:select="(emit) => { filters.id_owner = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <template v-if="entity === 'coins'">
                                <div :class="fCols.large.flex">
                                    <div :class="fCols.large.l">
                                        <v-text-field dense outlined filled clearable
                                            v-model="filters.provenience"
                                            label="Provenience"
                                            prepend-icon="play_circle_outline"
                                            v-on:keyup.enter="runQuery()"
                                        />
                                    </div>
                                </div>

                                <div :class="fCols.small.flex">
                                    <div :class="fCols.small.l">
                                        <v-text-field dense outlined filled clearable
                                            v-model="filters.collection_nr"
                                            label="Collection Nr."
                                            prepend-icon="bookmarks"
                                            v-on:keyup.enter="runQuery()"
                                        />
                                    </div>
                                    <div :class="fCols.small.r">
                                        <v-text-field dense outlined filled clearable
                                            v-model="filters.plastercast_nr"
                                            label="Plastercast Nr."
                                            prepend-icon="bookmarks"
                                            v-on:keyup.enter="runQuery()"
                                        />
                                    </div>
                                </div>
                            </template>

                            <div :class="fCols.large.flex">
	                            <div :class="fCols.large.l">
                                    <!-- Hoards -->
                                    <SearchForeignKey
                                        entity="hoards"
                                        label="Hoard"
                                        icon="grain"
                                        :selected="filters.id_hoard"
                                        v-on:select="(emit) => { filters.id_hoard = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                                <div :class="fCols.large.r">
                                    <!-- Findspots -->
                                    <SearchForeignKey
                                        entity="findspots"
                                        label="Findspot"
                                        icon="pin_drop"
                                        :selected="filters.id_findspot"
                                        v-on:select="(emit) => { filters.id_findspot = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                            </div>
                        </v-expansion-panel-content>
                    </v-expansion-panel>

                    <hr class="search-divider" />

                    <!-- Production ------------------------------------------------- ------------------------------------------------- -->
                    <v-expansion-panel :class="activeTab === 5 ? 'header_bg' : 'transparent'">
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
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <SearchForeignKey
                                        entity="regions"
                                        label="Region"
                                        icon="terrain"
                                        :selected="filters.id_region"
                                        v-on:select="(emit) => { filters.id_region = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                                <div :class="fCols.small.l">
                                    <SearchForeignKey
                                        entity="mints"
                                        label="Mint"
                                        icon="museum"
                                        :selected="filters.id_mint"
                                        v-on:select="(emit) => { filters.id_mint = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                            </div>
                            <!-- Type of Coinage -->
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <SearchForeignKey
                                        entity="authorities"
                                        label="Type of Coinage"
                                        icon="toll"
                                        :selected="filters.id_authority"
                                        v-on:select="(emit) => { filters.id_authority = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div><div :class="fCols.small.r"/>
                            </div>

                            <!-- Authority -->
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <SearchForeignKey
                                        entity="persons"
                                        :conditions="[{ authority: 1 }]"
                                        label="Ruler"
                                        icon="how_to_reg"
                                        :selected="filters.id_authority_person"
                                        v-on:select="(emit) => { filters.id_authority_person = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                                <div :class="fCols.small.r">
                                    <SearchForeignKey
                                        entity="tribes"
                                        label="Tribe"
                                        icon="sports_kabaddi"
                                        :selected="filters.id_authority_group"
                                        v-on:select="(emit) => { filters.id_authority_group = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <!-- persons -->
                            <SearchForeignKey
                                entity="persons"
                                label="Person"
                                icon="emoji_people"
                                :selected="filters.id_person"
                                v-on:select="(emit) => { filters.id_person = emit }"
                                v-on:keyup_enter="runQuery()"
                            />

                            <!-- Period -->
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <SearchForeignKey
                                        entity="periods"
                                        label="Epoch"
                                        icon="watch_later"
                                        :selected="filters.id_period"
                                        v-on:select="(emit) => { filters.id_period = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                                <div :class="fCols.small.r"></div>
                            </div>

                            <!-- Date -->
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <v-text-field dense outlined filled
                                        v-model="filters.date_start"
                                        label="Date Start"
                                        prepend-icon="first_page"
                                        v-on:keyup.enter="runQuery()"
                                    />
                                </div>
                                <div :class="fCols.small.r">
                                    <v-text-field dense outlined filled
                                        v-model="filters.date_end"
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
                    <v-expansion-panel :class="activeTab === 6 ? 'header_bg' : 'transparent'">
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
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <SearchForeignKey
                                        entity="materials"
                                        label="Material"
                                        icon="palette"
                                        :selected="filters.id_material"
                                        v-on:select="(emit) => { filters.id_material = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div><div :class="fCols.small.l" />
                            </div>

                            <!-- Standard / Denomination -->
                            <div :class="fCols.small.flex">
                                <div :class="fCols.small.l">
                                    <SearchForeignKey
                                        entity="denominations"
                                        label="Denomination"
                                        icon="bubble_chart"
                                        :selected="filters.id_denomination"
                                        v-on:select="(emit) => { filters.id_denomination = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                                <div :class="fCols.small.r">
                                    <SearchForeignKey
                                        entity="standards"
                                        label="Standard"
                                        icon="group_work"
                                        :selected="filters.id_standard"
                                        v-on:select="(emit) => { filters.id_standard = emit }"
                                        v-on:keyup_enter="runQuery()"
                                    />
                                </div>
                            </div>

                            <template v-if="entity === 'coins'">

                                <!-- Weight -->
                                <div :class="fCols.small.flex">
                                    <div :class="fCols.small.l">
                                        <v-text-field dense outlined filled
                                            v-model="filters.weight_start"
                                            label="Weight Min."
                                            prepend-icon="radio_button_unchecked"
                                            v-on:keyup.enter="runQuery()"
                                        />
                                    </div>
                                    <div :class="fCols.small.r">
                                        <v-text-field dense outlined filled
                                            v-model="filters.weight_end"
                                            label="Weight Max."
                                            prepend-icon="lens"
                                            v-on:keyup.enter="runQuery()"
                                        />
                                    </div>
                                </div>

                                <!-- Diameter -->
                                <div :class="fCols.small.flex">
                                    <div :class="fCols.small.l">
                                        <v-text-field dense outlined filled
                                            v-model="filters.diameter_start"
                                            label="Diameter Min."
                                            prepend-icon="hdr_weak"
                                            v-on:keyup.enter="runQuery()"
                                        />
                                    </div>
                                    <div :class="fCols.small.r">
                                        <v-text-field dense outlined filled
                                            v-model="filters.diameter_end"
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
                        <v-expansion-panel :key="'panel' + key.text" :class="activeTab === (7 + i) ? 'header_bg' : 'transparent'">
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
                                    v-model="filters[key.v + '_design']"
                                    :label="key.text + ' Design (Fulltext Search)'"
                                    prepend-icon="notes"
                                    v-on:keyup.enter="runQuery()"
                                />
                                <SearchForeignKey
                                    :entity="'designs'"
                                    :conditions="[{ side: key.v }]"
                                    :label="key.text + ' Design'"
                                    icon="notes"
                                    :selected="filters[key.v + '_id_design']"
                                    v-on:select="(emit) => { filters[key.v + '_id_design'] = emit }"
                                    v-on:keyup_enter="runQuery()"
                                />

                                <!-- Legend -->
                                <SearchForeignKey
                                    :entity="'legends'"
                                    :conditions="[{ side: key.v }]"
                                    :label="key.text + ' Legend'"
                                    icon="translate"
                                    sk="el_uc_adv"
                                    :selected="filters[key.v + '_id_legend']"
                                    v-on:select="(emit) => { filters[key.v + '_id_legend'] = emit }"
                                    v-on:keyup_enter="runQuery()"
                                />

                                <!-- Monograms -->
                                <SearchForeignKey
                                    entity="monograms"
                                    :label="key.text + ' Monogram'"
                                    icon="functions"
                                    :selected="filters[key.v + '_id_monogram']"
                                    v-on:select="(emit) => { filters[key.v + '_id_monogram'] = emit }"
                                    v-on:keyup_enter="runQuery()"
                                />

                                <!-- Symbols -->
                                <SearchForeignKey
                                    entity="symbols"
                                    :label="key.text + ' Symbol'"
                                    icon="flare"
                                    :selected="filters[key.v + '_id_symbol']"
                                    v-on:select="(emit) => { filters[key.v + '_id_symbol'] = emit }"
                                    v-on:keyup_enter="runQuery()"
                                />
                            </v-expansion-panel-content>
                        </v-expansion-panel>

                        <hr v-if="i === 0" :key="'hr' + key.text" class="search-divider" />
                    </template>

                </v-expansion-panels>
            </template>

            <!-- Error -->
            <v-fade-transition>
                <!-- Error -->
                <div
                    v-if="error"
                    class="mt-10 headline d-flex justify-center"
                    v-html="'Sorry, an error occured.<br/>Please reload and retry!'"
                />

                <!-- Results -->
                <div v-else-if="items[0] && ['tiles', 'cards', 'table'].includes(layout)" class="pa-3">
                    <v-row
                        class="ma-0 pa-0"
                        :key="entity + layout + (publisher ? 1 : 0)"
                    >
                        <v-col
                            v-for="(item, i) in items"
                            :key="i"
                            :cols="cols"
                        >
                            <component
                                :is="layouts[layout].component"
                                :key="i + item.id + item.public"
                                :entity="entity"
                                :item="item"
                                :publisher="publisher"
                                :checked="checkedItems[i].state"
                                :select="select"
                                :selected="selected"
                                :width="resultsWidth"
                                @check="checkedItems[i].state = !checkedItems[i].state"
                                @select="$emit('select', item.id)"
                                @refresh="setItems()"
                            />
                        </v-col>
                    </v-row>
                </div>

                <!-- No results -->
                <div
                    v-else-if="loading"
                    class="mt-10 headline d-flex justify-center"
                    v-html="loading ? 'Loading ...' : 'Sorry, no matching Records!'"
                />

                <div v-else style="padding: 20px; padding-left: 40px">
                    <!-- Header -->
                    <v-card
                        tile
                        class="tile_bg d-flex align-center justify-center"
                        style="height: 60px"
                    >
                        <div
                            class="headline font-bold"
                            v-text="queried ? ('Sorry, no matching ' + $root.label(entity)) : ('New ' + $root.label(entity) + ' Search')"
                        />
                    </v-card>

                    <template v-if="!dialog">

                        <div style="height: 20px" />

                        <!-- Cache -->
                        <v-card
                            tile
                            class="tile_bg"
                        >
                            <v-card-title v-text="'Session History'" style="height: 60px" />
                            <v-divider />

                            <v-card-text
                                style="overflow-y: scroll;"
                                :style="'height: calc((100vh - ' + ((dialog ? 170 : 90) + 270) + 'px) / 2)'"
                            >
                                <template v-if="cachedQueries[0]">
                                    <div
                                        v-for="(q, i) in cachedQueries"
                                        :key="'ycachedQ' + i"
                                        class="pt-1 pb-1 pr-5"
                                    >
                                        <div
                                            class="text-truncate" v-text="q.text"
                                            style="cursor: pointer"
                                            @click="restoreCachedQuery(q.value)"
                                        />
                                    </div>
                                </template>
                                <span v-else v-text="'--'" />
                            </v-card-text>
                        </v-card>

                        <div style="height: 20px" />

                        <!-- Favorites -->
                        <v-card
                            tile
                            class="tile_bg"
                        >
                            <v-card-title v-text="'Favorites'" style="height: 60px" />
                            <v-divider />

                            <v-card-text
                                style="overflow-y: scroll;"
                                :style="'height: calc((100vh - ' + ((dialog ? 170 : 90) + 270) + 'px) / 2)'"
                            >
                                <template v-if="Object.keys(storedQueries)[0]">
                                    <div
                                        v-for="(value, name, i) in storedQueries"
                                        :key="'ystoredQ' + i"
                                        class="d-flex justify-space-between pt-1 pb-1"
                                    >
                                        <div
                                            class="text-truncate" v-text="name"
                                            style="cursor: pointer"
                                            @click="restoreCachedQuery(value)"
                                        />
                                    </div>
                                </template>
                                <span v-else v-text="'--'" />
                            </v-card-text>
                        </v-card>
                    </template>
                </div>
            </v-fade-transition>
        </split-screen-template>
    </dialog-template>

    <!-- ------------------------------------------------- ------------------------------------------------- ------------------------------------------------- -->

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
            />

            <v-card-text>

                <div class="text-center pa-4">
                    Please provide a unique name for your Query. Otherwise an older query of the same name will be overwritten.
                </div>

                <v-text-field
                    dense filled outlined clearable
                    v-model="queryDialog.text"
                    ref="storeQueryName"
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

import dialogTemplate from '../../../templates/dialogTemplate.vue'
import h            from './search.js'
import tradingcard  from './../modules/layoutTradingcard.vue'
import indexcard    from './../modules/layoutIndexcard.vue'
import tablerow     from './../modules/layoutTablerow.vue'
import itemPublisherGroup     from './../modules/itemPublisherGroup.vue'

export default {
    components: {
        dialogTemplate,
        tradingcard,
        indexcard,
        tablerow,
        itemPublisherGroup
    },

    data () {
        return {
            queried:            false,
            loading:            false,
            error:              false,
            closing:            0,

            items:              [],

            filters:            h.constructParams(),
            queryCounter:       0,
            scrollToTop:        0,
            resultsWidth:       0,
            filterWidth:        0,
            pagination:         {},

            checkedState:       false,
            checkedItems:       [],

            cachedTab:          [],
            activeTab:          [],

            layout:             this.select ? 'cards' : this.$store.state.searchLayout,
            layouts:            {
                tiles: { component: 'tradingcard',    text: 'Tiles',   icon: 'view_comfy' },
                cards: { component: 'indexcard',      text: 'Cards',   icon: 'view_list' },
                table: { component: 'tablerow',       text: 'Text',    icon: 'view_column' }
            },

            // selectlists
            selects:            {
                state_public:       [
                    { value: 'all', text: 'All' },
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
        publisher:      { type: Boolean, default: false },
        dialog:         { type: Boolean, default: false },
        select:         { type: Boolean, default: false },
        selected:       { type: [Number, String], default: null },
        routedQuery:    { type: Object, default: () => {} }
    },

    computed: {
        router () {
            return !this.dialog
        },

        l () {
            return this.$root.language
        },

        labels () {
            return this.$root.labels
        },

        cols () {
            if (this.layout !== 'tiles' || this.resultsWidth < 400) return 12
            if (this.resultsWidth < 850) return 6
            if (this.resultsWidth < 1100) return 4
            if (this.resultsWidth < 1500) return 3
            if (this.resultsWidth < 2700) return 2
            return 1
        },

        fCols () {
            const small = this.filterWidth > 500 ? true : false
            const large = this.filterWidth > 750 ? true : false

            return {
                small: {
                    flex: small ? 'd-flex' : '',
                    r: 'search-field-50' + (small ? ' side-r' : ''),
                    l: 'search-field-50' + (small ? ' side-l' : '')
                },
                large: {
                    flex: large ? 'd-flex' : '',
                    r: 'search-field-50' + (large ? ' side-r' : ''),
                    l: 'search-field-50' + (large ? ' side-l' : '')
                }
            }
        },

        sorters () {
            const sorters = [
                { value: 'id', text: 'ID' },
                { value: 'date', text: this.labels['date'] }
            ]

            if (this.entity === 'coins') {
                sorters.push(
                    { value: 'weight', text: this.labels['weight'] },
                    { value: 'diameter', text: this.labels['diameter'] }
                )
            }

            sorters.push(
                { value: 'mint', text: this.labels['mint'] },
                { value: 'ruler', text: this.labels['authority_person'] },
                { value: 'created', text: this.labels['created_at'] },
                { value: 'updated', text: this.labels['updated_at'] }
            )

            return sorters
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
                    else if (key !== 'limit') q[key] = (q[key] ? (q[key] + ', ') : '') + val
                })

                let text = Object.keys(q).map((key) => { if (key) return key + ': ' + q[key] }).join('; ') ?? ''
                if (text) text += '; '
                text += 'published: ' + pPublic + '; sort: ' + sortBy

                return {
                    value: query,
                    text
                }
            })
        },

        storedQueries () {
            let queries = this.$root.settings?.queries
            if (!queries) return {}
            queries = JSON.parse(queries)
            return queries?.[this.entity] ?? {}
        },

        query: {
            get: function () {
                const query     = { ...this.filters }
                query.offset    = this.pagination.offset
                query.limit     = this.pagination.limit
                query.sort_by   = this.pagination.sort_by
                query.i         = this.queryCounter

                //console.log('query', query)

                return h.constructRequest(query)
            },

            set: function (query) {
                const params = h.constructParams(query)

                // Filter
                /*if (params.filters.o_design && typeof params.filters.o_design === 'array') params.filters.o_design = params.filters.o_design.join(' ')
                if (params.filters.r_design && typeof params.filters.r_design === 'array') params.filters.o_design = params.filters.o_design.join(' ')*/
                this.filters = params.filters

                // Pagination
                this.pagination.offset = params.pagination.offset ? parseInt(params.pagination.offset) : 0
                this.pagination.limit = params.pagination.limit ? parseInt(params.pagination.limit) : 12

                // Sorting
                if (params.pagination.sort_by) {
                    const split = params.pagination.sort_by.split(/[.\s]+/)
                    this.pagination.sort_by = split[0] + '.' + (split[1]?.toLowerCase() === 'desc' ? 'DESC' : 'ASC')
                }
                else this.pagination.sort_by = this.defaultSortBy
            }
        }
    },

    watch: {
        entity: function () { this.Init() },
        routedQuery: function () { this.handleQuery() }
    },

    created () {
        this.Init()
    },

    mounted () {
        if (!this.dialog) {
            //this.$refs.stringSearchInput.$refs.input.focus()
            this.handleQuery()
        }
        else if (this.select && this.selected) {
            this.filters.id = this.selected
            this.runQuery()
        }
    },

    methods: {
        Init () {
            this.$root.setTitle(this.$root.label(this.entity))
            this.queried = false
            this.resetFilters()
            this.items = []
            this.pagination= {
                sort_by: 'id.DESC',
                count:  0,
                offset: 0,
                limit:  12
            }
        },

        resetFilters (message) {
            const params = h.constructParams()
            this.filters = params.filters
            if (message) this.$store.dispatch('showSnack', { color: 'blue_sec', message: 'Query Parameters set to default!' })
        },

        handleQuery () {
            const queryGiven = Object.keys(this.routedQuery)[0] ? true : false

            if (this.publisher && !queryGiven) this.showPublisher(true)
            else if (queryGiven) {
                if (this.routedQuery) this.query = this.routedQuery
                this.$root.setTitle(this.$root.label(this.entity) + ' (' + this.$handlers.format.query(this.query) + ')')
                this.setItems()
            }
            else this.Init()
        },

        runQuery (offset = 0) {
            let caching = true

            if (offset) {
                caching = false
                if (offset === 'noCaching') offset = 0
            }

            this.pagination.offset = offset
            this.queryIncrement()
            if (caching) this.cacheCurrentQuery()

            if (this.dialog) this.setItems()
            else this.$router.push({
                path: '/' + this.entity + '/' + (this.publisher ? 'publish' : 'search'),
                query: this.query
            })
        },

        queryIncrement () {
            if (this.dialog) ++this.queryCounter
            else {
                const i = parseInt(this.$route.query?.i) ?? 0
                this.queryCounter = this.queryCounter + 1 + (this.queryCounter + 1 === i ? 1 : 0)
            }
        },

        async setItems () {
            this.queried = true
            this.error  = false
            this.loading = this.$root.loading = true
            this.items = []
            //this.scrollToTop++

            const query = h.specialTreatment(this.query)

            const dbi = await this.$root.DBI_SELECT_POST(this.entity, query)

            if (dbi?.contents) {
                this.pagination = {
                    sort_by: dbi.pagination?.sort_by?.replace(' ', '.'),
                    count:  dbi.pagination?.count,
                    offset: dbi.pagination?.offset,
                    limit:  dbi.pagination?.limit
                }
                this.items = dbi.contents

                if (dbi.contents[0]) {
                    this.items = dbi.contents
                    this.checkAll(false)
                }
                else {
                    this.items = []
                    this.$store.dispatch('showSnack', { message: 'No items found!' })
                }
            }
            else {
                console.error('ERROR', dbi)
                this.error = true
            }

            this.loading = this.$root.loading = false
        },

        setOffset (value) {
            this.pagination.offset = value
            this.runQuery(value)
        },

        setLimit (value) {
            this.pagination.limit = value
            this.runQuery('noCaching')
        },

        setSortBy (value) {
            this.pagination.sort_by = value
            this.runQuery()
        },

        setLayout (value) {
            this.$store.commit('set_searchLayout', value)
            this.layout = value
        },

        handleDrawerExpansion (expand) {
            if (expand) {
                if ([null, undefined].includes(this.activeTab)) setTimeout(() => { this.activeTab = this.cachedTab }, 350)
            }
            else {
                this.cachedTab = this.activeTab
                this.activeTab = null
            }
        },

        // Query Cache & Storage ---------------------------------------------------------------------
        getQueryString (page) {
            let query = window.location.href?.split('?') ?? [null]
            query.shift()
            const pagination = '(i' + (page ? '' : '|limit|offset') + ')=[^&]+&?'
            return query.join('?').replaceAll(new RegExp(pagination, 'g'), '').replaceAll('state_public=', 'public=')
        },

        cacheCurrentQuery () {
            if (!this.dialog) {
                const query = this.getQueryString()
                console.log('cache', query, query.length)

                if (query) {
                    const value = this.$store.state.cache[this.entity].slice(0, 49)
                    if (value[0] !== query) {
                        value.unshift(query)
                        this.$store.commit('setCache', { key: this.entity, value })
                    }
                }
            }
        },

        showQueryDialog () {
            if (!this.dialog) {
                this.queryDialog = {
                    show: true,
                    page: false,
                    text: '' + Date.now()
                }
                setTimeout(() => {
                    this.$refs.storeQueryName.$refs.input.focus()
                    this.$refs.storeQueryName.$refs.input.select()
                }, 0)
            }
        },

        saveQuery () {
            //console.log(this.queryDialog)
            const queries = { ...this.storedQueries }
            queries[this.queryDialog.text.trim()] = this.getQueryString(this.queryDialog.page)
            //console.log(queries)
            this.$root.changeSettings('queries', JSON.stringify({ [this.entity]: queries }))
            this.queryDialog.show = false
        },

        deleteStoredQuery (key) {
            const queries = { ...this.storedQueries }
            delete(queries[key])
            this.$root.changeSettings('queries', JSON.stringify({ [this.entity]: queries }))
        },

        restoreCachedQuery (query) {
            this.resetFilters()
            //console.log(query)
            window.location.href = '/editor#/' + this.entity + '/' + (this.publisher ? 'publish' : 'search') + '?' + query
            this.queryIncrement()
        },

        // Publisher ---------------------------------------------------------------------------
        togglePublisher () {
            if (!this.publisher) this.showPublisher()
            else this.$router.push('/' + this.entity + '/search')
        },

        showPublisher (replace) {
            if (Object.keys(this.routedQuery)[0]) {
                let q = window.location.href?.split('?') ?? [null]
                q.shift()
                q = q.join('?')
                this.$router.push('/' + this.entity + '/publish?' + q)
            }
            else {
                const path = {
                    path: '/' + this.entity + '/publish',
                    query: {
                        limit: this.pagination.limit,
                        sort_by: 'updated.DESC',
                        public: 2
                    }
                }

                this.$router[replace ? 'replace' : 'push'](path)
            }
        },

        checkAll (state = !this.checkedState) {
            this.checkedState = state

            this.checkedItems = this.items.map((item) => { return {
                id: item.id,
                public: item.public,
                state: [0, 2].includes(item.public) ? state : false
            }})
        }

        /*async Publish (input, mode) {
            let items = input.filter(item => { return item.state === true })

            if (items[0]) {
                let confirmed = mode === 1 ? true : (confirm(this.labels[this.entity.slice(0, -1)] + ' ' + input[0].id + ' will be ' + (mode === 3 ? 'deleted!' : 'unpublished!') ));

                if (confirmed === true) {
                    const response = await this.$root.DBI_INPUT_POST('publish', 'input', { entity: this.entity, items: items.map((item) => { return item.id }), mode: mode });

                    if (response.success) {
                        this.$store.dispatch('showSnack', { color: 'success', message: response.success })
                        this.setItems()
                    }
                }
            }
            else {
                this.$store.dispatch('showSnack', { color: 'error', message: 'No items selected!' })
                this.setItems()
            }
        }*/
    }
}

</script>

<style scoped>

    .start-screen {
        position: absolute;
        width: 500px;
        left: 50%;
        margin-left: -250px;
        height: 340px;
        top: 50%;
        margin-top: -180px;
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
