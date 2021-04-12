<template>
<div>

    <!-- JK: Input Mask -->
    <v-card tile class="mt-2" :loading="loading">

        <!-- JK: Appbar / Title -->
        <v-app-bar color="sysbar">
            <div class="d-flex component-wrap align-end" style="width: 100%">
                <div :class="'headline'+(item.public == 3 ? ' text-decoration-line-through' : '')" style="white-space: nowrap;">
                    {{ !GivenID ? ('Create new '+component) : (component+'&ensp;'+GivenID+( item.public == 1 ? '&ensp;*' : '' )) }}
                </div>            
                <div class="caption ml-5">                                                
                    created: {{ item.created_at+' ('+(item.creator ? item.creator : '--') }}) 
                    &ensp;|&ensp; 
                    updated: {{ item.updated_at+' ('+(item.editor ? item.editor : '--') }})
                    &ensp;|&ensp;
                    <span v-if="item.public == 1" v-text="'published'" class="green--text"></span>
                    <span v-else-if="item.public == 3" v-text="'deleted'" class="red--text"></span>
                    <span v-else v-text="'not published'"></span>
                </div>

                <v-spacer></v-spacer>

                <v-btn v-if="!parent_id" tile color="blue_prim" class="white--text" @click="$router.push ('/types/search'+(item.id ? ('/'+item.id) : ''))">
                    List all {{ componentp }}
                </v-btn>
                <v-btn v-else tile color="blue_prim" class="white--text" @click="$emit ('close')">
                    Back to Search Results
                </v-btn>
            </div>
        </v-app-bar>

        <!-- JK: Toolbar -->
        <toolbar 
            :attributes="attributes" 
            :item="item"
            :entity="entity"
            :label="component" 
            :key="refresh"
            v-on:command="ToolbarReceive"
            v-on:data="DataReceive"
            v-on:save-and-keep="SendItem (false, false)"
            v-on:save-and-reset="SendItem (true, false)"
            v-on:delete="SendItem (true, true)"
        ></toolbar>

        <!-- JK: Input Form body ------------------------------------------- -------------------------------------------- -------------------------------------------- --> 
        <v-card-text>
            <v-row class="ma-0 pa-0">

                <!-- Tabs Area -->
                <v-col cols="12" sm="12" :md="12 - img_card.zoom" class="ma-0 pa-0">
                    <div :class="$vuetify.breakpoint.mdAndUp ? 'd-flex component-wrap' : ''" style="width: 100%">

                        <!-- Tab Menu -->
                        <div :class="$vuetify.breakpoint.mdAndUp ? 'mr-5' : 'mb-5'">
                            <v-card tile class="sysbar">
                                <v-list class="transparent" :disabled="!GivenID">
                                    <v-list-item-group class="mt-n2 mb-n2">
                                        <v-list-item
                                            v-for="(text, key) in tabs" :key="key"
                                            :disabled="tab == key" 
                                            class="pr-9"
                                            v-text="text"
                                            style="white-space: nowrap;"
                                            @click="tab = key"
                                        ></v-list-item>
                                    </v-list-item-group>
                                </v-list>
                                <v-divider></v-divider>
                                <v-list class="transparent" :disabled="!GivenID">
                                    <v-list-item-group class="mt-n2 mb-n2">
                                        <v-list-item 
                                            class="pr-9"
                                            style="white-space: nowrap;"
                                            @click="import_active = !import_active"
                                        >
                                            External Data <v-icon class="ml-1" v-text="import_active ? 'expand_less' : 'expand_more'"></v-icon>
                                        </v-list-item>
                                    </v-list-item-group>
                                </v-list>
                            </v-card>
                        </div>

                        <!-- -------------------------------------------------------------------------------------------- -->

                        <div style="width: 100%">
                            <v-card tile class="appbar pt-5 pl-5 pr-7" min-height="500px">

                                <!-- New Coin ----------------------------------------- -------------------------------------------- -->
                                <div v-if="!GivenID" style="width: 100%; height: 400px; padding-left: 15%; padding-right: 15%" class="d-flex align-center justify-center">
                                    <div style="width: 100%;">
                                        <v-btn tile block color="blue_prim" class="mb-5" v-text="'New empty Coin'" @click="SendItem (false, false)"></v-btn>
                                        <v-btn tile block color="blue_prim" class="mb-5" v-text="'New Coin from CN Type'" disabled></v-btn>
                                        <v-btn tile block color="blue_prim" class="mb-5" v-text="'New Coin from CN Coin'" disabled></v-btn>
                                        <v-btn tile block color="blue_sec" v-text="'New Coin from Import'" disabled></v-btn>
                                    </div>
                                </div>

                                <!-- Images --------------------------------------- --------------------------------------------- -->                                
                                <div v-else-if="tab == 'images'" class="pb-5" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'IMAGES'"></div>

                                    <v-row>
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-6">
                                                <div class="title">Imagesets &ensp;( {{ item.images ? item.images.length : '--' }} )</div>
                                                <v-spacer></v-spacer>

                                                <v-btn text @click="AddRelation ('images', {
                                                    id: null,
                                                    obverse: {
                                                        kind: 'plastercast',
                                                        photographer: null,
                                                        link: null,
                                                        public: 1,
                                                        copyright: 1
                                                    },
                                                    reverse: {
                                                        kind: 'plastercast',
                                                        photographer: null,
                                                        link: null,
                                                        public: 1,
                                                        copyright: 1
                                                    }
                                                })">
                                                    <v-icon>add</v-icon>&ensp;Add
                                                </v-btn>
                                            </div>

                                            <v-row v-for="(iterator, i) in item.images" :key="i" class="mt-n3">

                                                <v-col cols="12" sm="12" class="mb-5 mt-1">
                                                    <div :class="wraped">
                                                        <v-card tile flat 
                                                            class="transparent body-1"
                                                            :disabled="img_index === i"
                                                            @click="img_index = i"
                                                        >
                                                            {{ i+1 }}. Imageset <v-icon small v-if="img_index === i" class="ml-1">content_copy</v-icon>
                                                        </v-card>
                                                        <v-spacer></v-spacer>
                                                        <v-btn icon class="ml-3" @click="DeleteRelation ('images', i)" ><v-icon>delete</v-icon></v-btn>
                                                    </div>
                                                </v-col>

                                                <v-col cols="12" sm="12" v-for="(s) in ['obverse', 'reverse']" :key="s" class="mt-n10">
                                                    <v-row>
                                                        <!-- Link -->
                                                        <v-col cols="12" sm="6">
                                                            <div :class="wraped">
                                                                <v-menu offset-y>
                                                                    <template v-slot:activator="{ on, attrs }">                                                                        
                                                                        <v-hover
                                                                            v-slot="{ hover }"
                                                                        >
                                                                            <v-card flat
                                                                                v-bind="attrs"
                                                                                v-on="on"
                                                                                :class="'mr-1 pa-2 caption pb-1 text-truncate ' + ( hover ? 'sysbar' : 'transparent' )"                                                                                                                                                    
                                                                            >
                                                                                <v-icon>camera_alt</v-icon>&emsp;
                                                                                <span v-text="item.images[i][s].link ? item.images[i][s].link : 'no file set, yet'"></span>
                                                                            </v-card>
                                                                        </v-hover>
                                                                    </template>
                                                                    <v-card tile>
                                                                        <v-list>
                                                                            <v-list-item-group>
                                                                                <v-list-item
                                                                                    v-text="'Switch Image Preview'"
                                                                                    :disabled="img_index == i"
                                                                                    @click="img_index = i"
                                                                                ></v-list-item>
                                                                                <v-list-item
                                                                                    v-text="'Open Server Directory'"
                                                                                    :disabled="item.images[i][s].link ? (item.images[i][s].link.substring(0, 4) === 'http' || !item.images[i][s].link.includes('/') ? true : false) : false"
                                                                                    @click="files_dialog = { active: true, key: (i+'_'+s), id: item.images[i][s].link }"
                                                                                ></v-list-item>
                                                                                <v-list-item
                                                                                    v-text="'Upload new image'"
                                                                                    @click="upload_dialog = { active: true, key: (i+'_'+s) }"
                                                                                ></v-list-item>
                                                                                <v-list-item
                                                                                    v-text="'Link external image'"
                                                                                    @click="ImgLinkDialog(i, s)"
                                                                                ></v-list-item>
                                                                                <v-list-item
                                                                                    v-text="'Delete Image Link'"
                                                                                    :disabled="item.images[i][s].link === null"
                                                                                    @click="item.images[i][s].link = null"
                                                                                ></v-list-item>
                                                                            </v-list-item-group>
                                                                        </v-list>
                                                                    </v-card>
                                                                </v-menu>
                                                                <v-spacer></v-spacer>
                                                                <v-btn text
                                                                    v-text="item.images[i][s].kind"
                                                                    @click="item.images[i][s].kind = item.images[i][s].kind === 'original' ? 'plastercast' : 'original'"
                                                                ></v-btn>
                                                            </div>
                                                        </v-col>
                                                        <!-- Photographer -->
                                                        <v-col cols="12" sm="6">
                                                            <div :class="wraped">
                                                                <v-text-field dense filled outlined clearable
                                                                    v-model="item.images[i][s].photographer"
                                                                    :label="(s === 'obverse' ? 'Ob' : 'Re') + 'verse Photographer'"
                                                                    :hint="s === 'obverse' ? 'Click on arrow to copy obv. to rev. photographer' : ''"
                                                                >
                                                                    <template v-slot:append v-if="s === 'obverse'">
                                                                        <v-icon
                                                                            v-text="'call_received'"
                                                                            @click="item.images[i].reverse.photographer = item.images[i].obverse.photographer"
                                                                        ></v-icon>
                                                                    </template>
                                                                </v-text-field>
                                                                <v-checkbox
                                                                    class="mt-0 mt-1 ml-5"
                                                                    label="public"
                                                                    v-model="item.images[i][s].public"
                                                                    disabled
                                                                ></v-checkbox>
                                                            </div>
                                                        </v-col>
                                                    </v-row>
                                                </v-col>

                                            </v-row>

                                        </v-col>

                                    </v-row>

                                    <!-- Image File Browser -->
                                    <ChildDialog v-if="files_dialog.active"
                                        :prop_active="files_dialog.active"
                                        prop_component="files"
                                        :prop_item="{ parent: 'coins', key: files_dialog.key, id: files_dialog.id }" 
                                        v-on:assignment="(emit) => { ImgBrowser(emit) }"
                                        v-on:close="files_dialog = { active: false, key: null, id: null }"
                                    ></ChildDialog>
                                        
                                    <!-- Image Direct Upload -->
                                    <upload
                                        :prop_active="upload_dialog.active"
                                        prop_target="storage/coins"
                                        :prop_key="upload_dialog.key"
                                        v-on:ChildEmit="(emit) => { ImgUpload(emit) }"
                                        v-on:close="upload_dialog = { active: false, key: null }"
                                    ></upload>

                                    <!-- Image Links -->
                                    <v-dialog v-model="link_dialog.active" persistent max-width="600px">
                                        <v-card tile>

                                            <v-system-bar color="sysbar" class="pt-5 pb-4 pl-6">
                                                <v-icon class="mr-2 mb-1">link</v-icon> <span>External image link</span>
                                                <v-spacer></v-spacer>
                                                <v-icon @click="CloseLink ()">close</v-icon>
                                            </v-system-bar>

                                            <v-card-text class="mt-5">
                                                <p>Please provide a valid external Link for the {{ link_dialog.side }} image.</p>

                                                <v-text-field dense outlined filled clearable class="mt-2"
                                                    v-model="link_dialog.input"
                                                    label="External link"
                                                    prepend-icon="link"
                                                    counter=255
                                                    :rules="[rules.link]"
                                                ></v-text-field>
                                            </v-card-text>

                                            <v-card-actions class="mt-n5">
                                                <v-spacer></v-spacer>
                                                <v-btn @click="CloseLink ()" icon class="mr-3">
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

                                                <v-btn @click="SetLink ()" icon>
                                                    <v-icon>done</v-icon>
                                                </v-btn>
                                                <v-spacer></v-spacer>
                                            </v-card-actions>

                                        </v-card>
                                    </v-dialog>
                                </div>

                                <!-- Types ----------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab == 'types'" class="pb-5" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'TYPE COIN RELATIONS'"></div>
                                    
                                    <v-row class="mb-5">
                                        <!-- Inheriting Type -->
                                        <v-col cols="12" sm="6">
                                            <v-divider></v-divider>
                                            <div class="title pt-2 pb-1" v-text="'Link inheriting Type'"></div>
                                            <div class="caption mb-5" v-text="'Only values of this Type can be applied to the Coin.'"></div>

                                            <div v-if="new_inheritor === this.inheriting_type.id" :class="wraped + ' align-center mt-2'">
                                                <div class="font-weight-bold pr-5" v-text="InheritingTypesName"></div>
                                                <v-btn icon @click="new_inheritor = null">
                                                    <v-icon v-text="'edit'"></v-icon>
                                                </v-btn>
                                                <v-btn icon class="ml-1" @click="manage_inheritance('unlink', inheriting_type.id)">
                                                    <v-icon v-text="'delete'"></v-icon>
                                                </v-btn>
                                            </div>
                                            <div v-else :class="wraped">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="new_inheritor"
                                                    label="Type ID"
                                                    hint="Enter a valid Type ID."
                                                    :rules="[value => { const pattern = /^(null|[1-9][0-9]*)$/; return pattern.test(value) || 'Must be numeric.' }]"
                                                    style="max-width: 50%"
                                                ></v-text-field>
                                                <v-btn icon class="ml-1" @click="manage_inheritance('link', new_inheritor)">
                                                    <v-icon v-text="'link'"></v-icon>
                                                </v-btn>
                                                <v-btn icon class="ml-1" @click="manage_inheritance('unlink', inheriting_type.id)">
                                                    <v-icon v-text="'link_off'"></v-icon>
                                                </v-btn>
                                            </div>
                                        </v-col>

                                        <!-- Linked Types -->
                                        <v-col cols="12" sm="6">
                                            <v-divider></v-divider>
                                            <div class="title pt-2 pb-1" v-text="'Link additional non-inheriting Types'"></div>
                                            <div class="caption mb-5" v-html="'Values of this Types <i>cannot</i> be applied to the Coin.'"></div>
                                            
                                            <div :class="wraped">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="new_link"
                                                    label="Type ID"
                                                    hint="Enter a valid Type ID."
                                                    :rules="[value => { const pattern = /^(null|[1-9][0-9]*)$/; return pattern.test(value) || 'Must be numeric.' }]"
                                                    style="max-width: 50%"
                                                ></v-text-field>
                                                <v-btn icon class="ml-1" @click="manage_link('link', new_link)">
                                                    <v-icon v-text="'link'"></v-icon>
                                                </v-btn>
                                            </div>
                                        </v-col>
                                    </v-row>

                                    <gallery 
                                        entity="types"
                                        types_only
                                        show_toolbar
                                        search_key="id_coin" 
                                        :search_val="item.id" 
                                        :key="item.id+' '+gallery_refresh"
                                        v-on:select="function (emit) {}"                                        
                                        v-on:delete="emit => {manage_link ('unlink', emit)}"
                                    ></gallery>
                                </div><!-- v-on:edit="emit => "-->



                                <!-- Production ----------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab == 'production'" class="pb-5" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'PRODUCTION'"></div>                                    

                                    <!-- Issue -->
                                    <v-row>
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="title pt-2" v-text="'Issue'"></div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <div :class="wraped">
                                                <InputForeignKey 
                                                    entity="mints" 
                                                    label="Mint" 
                                                    icon="museum"
                                                    :selected="parseInt (item.mint)" 
                                                    :key="item.mint + ' ' +item.inherited.mint"
                                                    v-on:emitid="(emit) => { item.mint = emit }"
                                                    :inherited="item.inherited.mint === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    keyname="mint"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.mint"
                                                    :inheritedvalue="!inheriting_type.mint ? null : (inheriting_type.mint.id ? ('( ' + inheriting_type.mint.id + ' )&emsp;' + inheriting_type.mint.text.de ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'mint') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6">
                                            <v-checkbox
                                                class="mt-0 mt-1"
                                                label="Coin is Forgery"
                                                v-model="item.forgery"
                                            ></v-checkbox>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <div :class="wraped + ' mt-n5'">
                                                <InputForeignKey
                                                    entity="authorities" 
                                                    label="Type of Coinage" 
                                                    icon="toll"
                                                    :selected="parseInt (item.authority)" 
                                                    :key="item.authority"
                                                    v-on:emitid="(emit) => { item.authority = emit }"
                                                    :inherited="item.inherited.authority === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    keyname="type of coinage"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.authority"
                                                    :inheritedvalue="!inheriting_type.authority ? null : (inheriting_type.authority.kind.id ? ('( ' + inheriting_type.authority.kind.id + ' )&emsp;' + inheriting_type.authority.kind.text.de ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'authority') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <div :class="wraped + ' mt-n5'">
                                                <InputForeignKey
                                                    entity="persons" 
                                                    label="Issuer" 
                                                    icon="record_voice_over" 
                                                    sk="el_uc" 
                                                    sync 
                                                    :selected="parseInt (item.issuer)" 
                                                    :key="item.issuer"
                                                    v-on:emitid="(emit) => { item.issuer = emit }"
                                                    :inherited="item.inherited.issuer === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    keyname="issuer"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.issuer"
                                                    :inheritedvalue="!inheriting_type.issuer ? null : (inheriting_type.issuer.id ? ('( ' + inheriting_type.issuer.id + ' )&emsp;' + inheriting_type.issuer.name ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'issuer') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <div :class="wraped  + ' mt-n5'">
                                                <InputForeignKey
                                                    entity="persons" 
                                                    label="Authority Person" 
                                                    icon="how_to_reg" 
                                                    sk="el_uc" 
                                                    sync 
                                                    :selected="parseInt (item.authority_person)" 
                                                    :key="item.authority_person"
                                                    v-on:emitid="(emit) => { item.authority_person = emit }"
                                                    :inherited="item.inherited.authority_person === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    keyname="authority person"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.authority_person"
                                                    :inheritedvalue="!inheriting_type.authority ? null : (inheriting_type.authority.person ? ('( ' + inheriting_type.authority.person.id + ' )&emsp;' + inheriting_type.authority.person.name ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'authority_person') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <div :class="wraped + ' mt-n5'">
                                                <InputForeignKey
                                                    entity="tribes" 
                                                    label="Authority Group" 
                                                    icon="sports_kabaddi"
                                                    :selected="parseInt (item.authority_group)" 
                                                    :key="item.authority_group"
                                                    v-on:emitid="(emit) => { item.authority_group = emit }"
                                                    :inherited="item.inherited.authority_group === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    keyname="authority group"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.authority_group"
                                                    :inheritedvalue="!inheriting_type.authority ? null : (inheriting_type.authority.group ? ('( ' + inheriting_type.authority.group.id + ' )&emsp;' + inheriting_type.authority.group.name ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'authority_group') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>
                                    </v-row>
                                    
                                    
                                    <!-- Date -->
                                    <v-row>
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="title pt-2" v-text="'Date'"></div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <div :class="wraped">
                                                <InputForeignKey 
                                                    entity="epochs" 
                                                    label="Epoch" 
                                                    icon="watch_later"
                                                    :selected="item.period ? parseInt (item.period) : null" 
                                                    :key="item.period"
                                                    v-on:emitid="(emit) => { item.period = emit }"
                                                    :inherited="item.inherited.period === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    keyname="epoch"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.period"
                                                    :inheritedvalue="!inheriting_type.date ? null : (inheriting_type.date.period.id ? ('( ' + inheriting_type.date.period.id + ' )&emsp;' + inheriting_type.date.period.text.de ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'period') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                        </v-col>

                                        <v-col cols="12" sm="3" class="mt-n5" v-for="record in ['date_start', 'date_end']" :key="record">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item [record]"
                                                :label="'Date '+(record == 'date_start' ? 'Start' : 'End')"
                                                :prepend-icon="record == 'date_start' ? 'first_page' : 'last_page'"
                                                :disabled="item.inherited.date === 1"
                                                :hint="item.inherited.date === 1 ? 'Value inherited from type' : ''"
                                                :persistent-hint="item.inherited.date === 1"
                                                :rules="[rules.date]"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col cols="12" sm="6" class="mt-n5">
                                            <div :class="wraped">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="item.date_text_de"
                                                    label="Date Text (DE)"
                                                    prepend-icon="settings_ethernet"
                                                    :disabled="item.inherited.date === 1"
                                                    :hint="item.inherited.date === 1 ? 'Value inherited from type' : ''"
                                                    :persistent-hint="item.inherited.date === 1"
                                                ></v-text-field>
                                                <inheritor
                                                    keyname="date"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.date"
                                                    :inheritedvalue="!inheriting_type.date ? null : (
                                                        !inheriting_type.dbi.date_start && 
                                                        !inheriting_type.dbi.date_end && 
                                                        !inheriting_type.date.text.de ? null : (
                                                            (inheriting_type.dbi.date_start ? inheriting_type.dbi.date_start : '--') + ' - ' + 
                                                            (inheriting_type.dbi.date_end ? inheriting_type.dbi.date_end : '--') + '&emsp;|&emsp;' + 
                                                            (inheriting_type.date.text.de ? inheriting_type.date.text.de : '--')
                                                        ))"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'date') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>

                                    </v-row>
                                </div>



                                <!-- Basics ---------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab == 'basics'" class="pb-5" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'BASICS'"></div>

                                    <v-row>

                                        <!-- Technical Parameters -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="title pt-2" v-text="'Technical Parameters'"></div>
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <div :class="wraped">
                                                <InputForeignKey
                                                    entity="materials" 
                                                    label="Material" 
                                                    icon="palette"
                                                    :selected="parseInt (item.material)" 
                                                    :key="item.material"
                                                    v-on:emitid="(emit) => { item.material = emit }"
                                                    :inherited="item.inherited.material === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    keyname="material"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.material"
                                                    :inheritedvalue="!inheriting_type.material ? null : (inheriting_type.material.id ? ('( ' + inheriting_type.material.id + ' )&emsp;' + inheriting_type.material.text.de ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'material') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <div :class="wraped">
                                                <InputForeignKey
                                                    entity="denominations" 
                                                    label="Denomination" 
                                                    icon="bubble_chart"
                                                    :selected="parseInt (item.denomination)" 
                                                    :key="item.denomination"
                                                    v-on:emitid="(emit) => { item.denomination = emit }"
                                                    :inherited="item.inherited.denomination === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    keyname="denomination"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.denomination"
                                                    :inheritedvalue="!inheriting_type.denomination ? null : (inheriting_type.denomination.id ? ('( ' + inheriting_type.denomination.id + ' )&emsp;' + inheriting_type.denomination.text.de ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'denomination') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <div :class="wraped">
                                                <InputForeignKey
                                                    entity="standards" 
                                                    label="Standard" 
                                                    icon="group_work"
                                                    :selected="parseInt (item.standard)" 
                                                    :key="item.standard"
                                                    v-on:emitid="function (emit) { item.standard = emit }"
                                                    :inherited="item.inherited.standard === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    keyname="standard"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.standard"
                                                    :inheritedvalue="!inheriting_type.standard ? null : (inheriting_type.standard.id ? ('( ' + inheriting_type.standard.id + ' )&emsp;' + inheriting_type.standard.text.de ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'standard') }"
                                                ></inheritor>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item.weight"
                                                label="Weight in g"
                                                prepend-icon="fitness_center"
                                                :rules="[rules.numeric_nz]"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item.diameter_max"
                                                label="Diameter (Max) in mm"
                                                prepend-icon="fiber_manual_record"
                                                :rules="[rules.numeric_nz]"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col cols="12" sm="4">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item.diameter_min"
                                                label="Diameter (Min) in mm"
                                                prepend-icon="adjust"
                                                :rules="[rules.numeric_nz]"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col cols="12" sm="4" class="mt-n5">
                                            <div :class="wraped">
                                                <v-icon class="mr-5 mt-1">not_interested</v-icon>
                                                <v-checkbox
                                                    v-model="item.weight_ignore"
                                                    label="Ignore Weight"
                                                    class="mt-0"
                                                ></v-checkbox>
                                                <v-checkbox
                                                    v-model="item.diameter_ignore"
                                                    label="Ignore Diameter"
                                                    class="ml-4 mt-0"
                                                ></v-checkbox>                                            
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="4" class="mt-n5">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item.axis"
                                                label="Axis"
                                                prepend-icon="update"
                                                :rules="[rules.axis]"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col cols="12" sm="4" class="mt-n5">
                                            <v-select dense outlined filled
                                                v-model="item.centerhole"
                                                :items="select_centerhole"
                                                label="Centerhole"
                                                prepend-icon="album"
                                            ></v-select>
                                        </v-col>
                                        
                                        <!-- Owner -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-1">
                                                <div class="title">Owners</div>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey 
                                                entity="owners" 
                                                label="Owner Original" 
                                                icon="account_circle" 
                                                sync 
                                                :selected="item.owner_original" 
                                                :key="item.owner_original"
                                                v-on:emitid="(emit) => { item.owner_original = emit; }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item.collection"
                                                label="Collection Nr." 
                                                prepend-icon="label"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col cols="12" sm="6" class="mt-n5">
                                            <InputForeignKey 
                                                entity="owners" 
                                                label="Owner Reproduction/Plastercast" 
                                                icon="account_circle" 
                                                sync 
                                                :selected="item.owner_reproduction" 
                                                :key="item.owner_reproduction"
                                                v-on:emitid="(emit) => { item.owner_reproduction = emit; }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <v-col cols="12" sm="6" class="mt-n5">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item.plastercast"
                                                label="Plastercast Nr." 
                                                prepend-icon="label"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col cols="12" sm="12">
                                            <div :class="wraped">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="item.provenience"
                                                    label="Provenience" 
                                                    prepend-icon="zoom_out_map"
                                                ></v-text-field>
                                                <v-checkbox
                                                    class="mt-0 mt-1 ml-5"
                                                    label="Owner of Original is unsure"
                                                    v-model="item.owner_unsure"
                                                ></v-checkbox>
                                            </div>
                                        </v-col>

                                        <!-- Find circumstances -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-1">
                                                <div class="title">Find circumstances</div>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey 
                                                entity="findspots" 
                                                label="Findspot" 
                                                icon="pin_drop" 
                                                sync 
                                                :selected="item.findspot" 
                                                :key="item.findspot"
                                                v-on:emitid="(emit) => { item.findspot = emit; }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey 
                                                entity="hoards" 
                                                label="Hoard" 
                                                icon="grain" 
                                                sync 
                                                :selected="item.hoard" 
                                                :key="item.hoard"
                                                v-on:emitid="(emit) => { item.hoard = emit; }"
                                            ></InputForeignKey>
                                        </v-col>

                                    </v-row>
                                </div>


                                <!-- Obverse / Reverse ----------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab === 'obverse' || tab === 'reverse'" class="ml-3" style="width:100%" >
                                    <div class="headline text-center mt-n1" v-text="(tab == 'obverse' ? 'OB' : 'RE')+'VERSE'"></div>

                                    <v-row>
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div :class="wraped + ' align-end'">
                                                <div class="title pt-2" v-text="'Description'"></div>
                                                <v-spacer></v-spacer>
                                            </div>
                                        </v-col>

                                        <!-- Legends / Designs -->
                                        <v-col cols="12" sm="12" v-for="record in ['legend', 'design']" :key="record">
                                            <div :class="wraped">
                                                <InputForeignKey 
                                                    :entity="record + 's_' + tab" 
                                                    :label="(tab == 'obverse' ? 'Ob' : 'Re')+'verse '+(record == 'legend' ? 'Legend' : 'Design')" 
                                                    :icon="record == 'legend' ? 'translate' : 'notes'" 
                                                    :sk="record == 'legend' ? 'el_uc_adv' : null" 
                                                    sync 
                                                    :selected="parseInt(item[tab.slice(0, 1) + '_' + record])" 
                                                    :key="item[tab.slice(0, 1) + '_' + record]"
                                                    v-on:emitid="(emit) => { item[tab.slice(0, 1) + '_' + record] = emit }"
                                                    :inherited="item.inherited[tab.slice(0, 1) + '_' + record] === 1"
                                                    style="width: 100%"
                                                ></InputForeignKey>
                                                <inheritor
                                                    :keyname="tab + ' ' + record"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited[tab.slice(0, 1) + '_' + record]"
                                                    :inheritedvalue="!inheriting_type[tab] ? null : (inheriting_type[tab][record].id ? ('( ' + inheriting_type[tab][record].id + ' )&emsp;' + (
                                                        record === 'design' ? inheriting_type[tab][record].text.de : inheriting_type[tab][record].string
                                                    ) ) : null)"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, (tab.slice(0, 1) + '_' + record)) }"
                                                ></inheritor>
                                            </div>
                                        </v-col>

                                        <!-- Die -->
                                        <v-col cols="12" sm="6">
                                            <InputForeignKey 
                                                :entity="'dies_' + tab" 
                                                :label="(tab == 'obverse' ? 'Ob' : 'Re')+'verse Die'" 
                                                icon="gavel"
                                                sync 
                                                :selected="parseInt (item[tab.substring(0,1)+'_die'])" 
                                                :key="item[tab.substring(0,1)+'_die']"
                                                v-on:emitid="(emit) => { item[tab.substring(0,1)+'_die'] = emit }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <!-- Symbols / Monograms -->
                                        <v-col cols="12" sm="12" v-for="record in ['symbols', 'monograms']" :key="record">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-6">
                                                <div class="title">{{ record === 'monograms' ? 'Monograms' : 'Symbols' }} &ensp;( {{ item[tab.slice(0, 1) + '_' + record] ? item[tab.slice(0, 1) + '_' + record].length : '--' }} )</div>
                                                <inheritor
                                                    class="ml-1 mb-n1"
                                                    :keyname="tab + ' ' + record"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited[tab.slice(0, 1) + '_' + record]"
                                                    :inheritedvalue="!inheriting_type[tab] ? null : (!inheriting_type[tab][record] ? null : printTypeData(record, inheriting_type[tab][record]) )"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, (tab.slice(0, 1) + '_' + record)) }"
                                                ></inheritor>
                                                <div v-if="item.inherited [tab.slice(0, 1) + '_' + record] === 1" class="caption ml-5" v-text="'Values inherited from Type'"></div>
                                                <div v-else class="caption ml-5" v-text="'At least the ' + ( record == 'monograms' ? 'Monogram' : 'Symbol' ) + ' is required - otherwise input will be ignored.'"></div>
                                                <v-spacer></v-spacer>

                                                <v-btn text
                                                    :disabled="item.inherited [tab.slice(0, 1) + '_' + record] === 1"
                                                    @click="AddRelation((tab.slice(0, 1) + '_' + record), {id: null, position: null, side: tab.substring(0,1) == 'o' ? 0 : 1})"
                                                >
                                                    <v-icon>add</v-icon>&ensp;Add
                                                </v-btn>
                                            </div>

                                            <div v-for="(iterator, i) in item[tab.substring(0,1)+'_'+record]" :key="i" class="mt-n3">
                                                <v-row v-if="edit_relations[tab.substring(0,1)+'_'+record].includes(i)">
                                                    
                                                    <v-col cols="12" sm="8">
                                                        <InputForeignKey
                                                            :entity="record" 
                                                            :label="(i+1)+'. '+(tab == 'obverse' ? 'Ob' : 'Re')+'verse '+(record == 'monograms' ? 'Monogram' : 'Symbol')" 
                                                            :icon="record == 'monograms' ? 'functions' : 'flare'" 
                                                            :sk="record == 'monograms' ? 'el_uc' : null" 
                                                            sync
                                                            :key="item[tab.substring(0,1)+'_'+record][i].id"
                                                            :selected="parseInt (item[tab.substring(0,1)+'_'+record][i].id)"
                                                            :search_mixed_chars="record == 'monograms' ? true : false"
                                                            v-on:emitid="(emit) => { item[tab.substring(0,1)+'_'+record][i].id = emit }"
                                                            :inherited="item.inherited [tab.substring(0,1) + '_' + record] === 1"
                                                            style="width: 100%"
                                                        ></InputForeignKey>
                                                    </v-col>

                                                    <v-col cols="12" sm="4">
                                                        <div class="d-flex component-wrap">
                                                            <InputForeignKey
                                                                search_mixed_words
                                                                entity="positions" 
                                                                label="Position" 
                                                                icon="motion_photos_on"                    
                                                                :selected="parseInt (item[tab.substring(0,1)+'_'+record][i].position)" 
                                                                :key="item[tab.substring(0,1)+'_'+record][i].position" 
                                                                v-on:emitid="(emit) => { item[tab.substring(0,1)+'_'+record][i].position = emit }"
                                                                :inherited="item.inherited [tab.substring(0,1) + '_' + record] === 1"
                                                                style="width: 100%"
                                                            ></InputForeignKey>

                                                            <v-btn icon 
                                                                class="ml-3"
                                                                :disabled="item.inherited[tab.substring(0,1) + '_' + record] === 1"
                                                                @click="DeleteRelation((tab.substring(0,1)+'_'+record), i)" 
                                                            ><v-icon>delete</v-icon></v-btn>
                                                        </div>
                                                    </v-col>
                                                </v-row>

                                                <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                                                    <v-icon class="mr-5" v-text="record == 'monograms' ? 'functions' : 'flare'"></v-icon>
                                                    <div class="d-flex component-wrap align-start">
                                                        <Imager v-if="item[tab.substring(0,1)+'_'+record][i].image" :item="item[tab.substring(0,1)+'_'+record][i]" style="width: 75px; height: 75px" class="mr-5"></Imager>
                                                        <div v-html="item[tab.substring(0,1)+'_'+record][i].string" class="mb-1"></div>
                                                    </div>
                                                    <v-spacer></v-spacer>
                                                    <v-btn icon
                                                        :disabled="(item.inherited [tab.substring(0,1) + '_' + record] === 1) || (edit_relations[tab.substring(0,1)+'_'+record].includes(i))"
                                                        @click="edit_relations[tab.substring(0,1)+'_'+record].push(i)"
                                                    >
                                                        <v-icon>edit</v-icon>
                                                    </v-btn>
                                                    <v-btn icon class="ml-3"
                                                        :disabled="item.inherited [tab.substring(0,1) + '_' + record] === 1" 
                                                        @click="DeleteRelation((tab.substring(0,1)+'_'+record), i)" 
                                                    >
                                                        <v-icon>delete</v-icon>
                                                    </v-btn>
                                                </div>
                                            </div>

                                        </v-col>

                                        <!-- Controlmarks -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-6">
                                                <div class="title">Controlmarks &ensp;( {{ item[tab.substring(0,1)+'_controlmarks'] ? item[tab.substring(0,1)+'_controlmarks'].length : '--' }} )</div>
                                                <div class="caption ml-5">At least the Controlmark is required - otherwise input will be ignored.</div>
                                                <v-spacer></v-spacer>

                                                <v-btn text
                                                    @click="AddRelation ((tab.substring(0,1)+'_controlmarks'), {id: null, count: null, side: tab.substring(0,1) == 'o' ? 0 : 1})"
                                                >
                                                    <v-icon>add</v-icon>&ensp;Add
                                                </v-btn>
                                            </div>

                                            <div v-for="(iterator, i) in item[tab.substring(0,1)+'_controlmarks']" :key="i" class="mt-n3">
                                                <v-row v-if="edit_relations[tab.substring(0,1)+'_controlmarks'].includes(i)">
                                                    
                                                    <v-col cols="12" sm="8">
                                                        <InputForeignKey
                                                            entity="controlmarks" 
                                                            :label="(i+1)+'. '+(tab == 'obverse' ? 'Ob' : 'Re')+'verse Controlmark'" 
                                                            icon="control_point"
                                                            :key="item[tab.substring(0,1)+'_controlmarks'][i].id"
                                                            :selected="parseInt (item[tab.substring(0,1)+'_controlmarks'][i].id)"
                                                            search_mixed_chars
                                                            v-on:emitid="(emit) => { item[tab.substring(0,1)+'_controlmarks'][i].id = emit }"
                                                        ></InputForeignKey>
                                                    </v-col>

                                                    <v-col cols="12" sm="4">
                                                        <div class="d-flex component-wrap">
                                                            <v-text-field dense filled outlined clearable
                                                                v-model="item[tab.substring(0,1)+'_controlmarks'][i].count"
                                                                prepend-icon="filter_1"
                                                                label="count"
                                                                :rules="[rules.numeric_nz]"
                                                            ></v-text-field>

                                                            <v-btn icon 
                                                                class="ml-3"
                                                                @click="DeleteRelation((tab.substring(0,1)+'_controlmarks'), i)" 
                                                            ><v-icon>delete</v-icon></v-btn>
                                                        </div>
                                                    </v-col>
                                                </v-row>

                                                <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                                                    <v-icon class="mr-5">control_point</v-icon>
                                                    <div class="d-flex component-wrap align-start">
                                                        <Imager v-if="item[tab.substring(0,1)+'_controlmarks'][i].image" :item="item[tab.substring(0,1)+'_controlmarks'][i]" style="width: 75px; height: 75px" class="mr-5"></Imager>
                                                        <div v-html="item[tab.substring(0,1)+'_controlmarks'][i].name" class="mb-1"></div>
                                                    </div>
                                                    <v-spacer></v-spacer>
                                                    <v-btn icon
                                                        :disabled="edit_relations[tab.substring(0,1)+'_controlmarks'].includes(i)"
                                                        @click="edit_relations[tab.substring(0,1)+'_controlmarks'].push(i)"
                                                    >
                                                        <v-icon>edit</v-icon>
                                                    </v-btn>
                                                    <v-btn icon class="ml-3" 
                                                        @click="DeleteRelation((tab.substring(0,1)+'_controlmarks'), i)" 
                                                    >
                                                        <v-icon>delete</v-icon>
                                                    </v-btn>
                                                </div>
                                            </div>

                                        </v-col>
                                    </v-row>

                                    <!-- Countermark / Undertype -->
                                    <v-row v-for="(record) in ['countermarks', 'undertypes']" :key="record">
                                        <v-col cols="12" sm="12">                                        
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-6">
                                                <div class="title" v-text="record == 'countermarks' ? 'Countermark' : 'Undertype'"></div>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="6" v-for="(lang) in ['de', 'en']" :key="lang" class="mt-n5">
                                            <v-textarea dense outlined filled clearable no-resize 
                                                rows="3"
                                                v-model="item [tab.substring(0,1)+'_'+record+'_'+lang]"
                                                :label="'Description ('+lang+')'"
                                                prepend-icon="notes"
                                                counter=21845
                                            ></v-textarea>
                                        </v-col>

                                    </v-row>                                
                                </div>



                                <!-- References ----------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab == 'references'" class="pb-5" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'REFERENCES'"></div>

                                    <v-row>
                                        <v-col cols="12" sm="12" v-for="record in ['citations', 'literature']" :key="record">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-6">
                                                <div class="title">{{ record == 'citations' ? 'Citations' : 'Literature' }} &ensp;( {{ item[record] ? item[record].length : '--' }} )</div>
                                                <div class="caption ml-5">At least the title is required - otherwise input will be ignored.</div>
                                                <v-spacer></v-spacer>

                                                <v-btn text 
                                                    @click="AddRelation (record, {
                                                        id: null, 
                                                        string: null, 
                                                        page: null, 
                                                        number: null, 
                                                        plate: null, 
                                                        picture: null, 
                                                        annotation: null, 
                                                        comment_de: null, 
                                                        comment_en: null, 
                                                        this: record == 'citations' ? 1 : 0
                                                    })"
                                                >
                                                    <v-icon>add</v-icon>&ensp;Add
                                                </v-btn>
                                            </div>

                                            <div class="mt-n3" v-for="(iterator, i) in item[record]" :key="i">
                                                <v-row v-if="edit_relations[record].includes(i)">

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
                                                            sync 
                                                            :selected="item[record][i].id" 
                                                            :key="item[record][i].id"
                                                            v-on:emitid="(emit) => { item[record][i].id = emit; }"
                                                        ></InputForeignKey>
                                                    </v-col>

                                                    <v-col cols="12" sm="2" v-for="q in ['page', 'number', 'plate', 'picture', 'annotation']" :key="q">
                                                        <v-text-field dense outlined filled clearable class="mt-n5"
                                                            v-model="item[record][i][q]"
                                                            :label="q.substring(0,1).toUpperCase()+q.substring(1)"
                                                            prepend-icon="south_east"
                                                        ></v-text-field>
                                                    </v-col>

                                                    <v-col cols="12" sm="6">
                                                        <v-textarea dense outlined filled clearable no-resize 
                                                            height="100"
                                                            v-model="item[record][i].comment_de"
                                                            label="Comment (DE)"
                                                            prepend-icon="notes"
                                                            counter=21845
                                                        ></v-textarea>
                                                    </v-col>

                                                    <v-col cols="12" sm="6">
                                                        <v-textarea dense outlined filled clearable no-resize 
                                                            height="100"
                                                            v-model="item[record][i].comment_en"
                                                            label="Comment (EN)"
                                                            prepend-icon="notes"
                                                            counter=21845
                                                        ></v-textarea>
                                                    </v-col>
                                                </v-row>

                                                <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                                                    <v-icon class="mr-5">menu_book</v-icon>
                                                    <div> 
                                                        <div v-html="item[record][i].string" class="mb-1"></div>
                                                        <div v-if="item[record][i].comment_de || item[record][i].comment_en" class="pl-3">
                                                            <div v-html="'(DE)&ensp;'+(item[record][i].comment_de ? item[record][i].comment_de : '--')" class="caption"></div>
                                                            <div v-html="'(EN)&ensp;'+(item[record][i].comment_en ? item[record][i].comment_en : '--')" class="caption"></div>
                                                        </div>
                                                    </div>
                                                    <v-spacer></v-spacer>
                                                    <v-btn icon @click="edit_relations[record].push(i)" :disabled="edit_relations[record].includes(i)"><v-icon>edit</v-icon></v-btn>
                                                    <v-btn icon class="ml-3" @click="DeleteRelation (record, i)" ><v-icon>delete</v-icon></v-btn>
                                                </div>
                                            </div>

                                        </v-col>
                                        
                                        <!-- Web Links -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-8">
                                                <div class="title">Web Links &ensp;( {{ item.links ? item.links.length : '--' }} )</div>
                                                <v-spacer></v-spacer>
                                                <v-btn text 
                                                    @click="AddRelation ('links', {
                                                        id: null, 
                                                        string: null
                                                    })"
                                                >
                                                    <v-icon>add</v-icon>&ensp;Add
                                                </v-btn>
                                            </div>

                                            <div class="mt-n3" v-for="(iterator, i) in item.links" :key="i">                                                    
                                                <div v-if="edit_relations.links.includes(i)" class="d-flex component-wrap">
                                                    <v-text-field dense outlined filled clearable
                                                        v-model="item.links[i].link"
                                                        prepend-icon="link"
                                                        :rules="[rules.link]"
                                                    ></v-text-field>
                                                    <v-spacer></v-spacer>
                                                    <v-btn icon class="ml-3" @click="DeleteRelation ('links', i)" ><v-icon>delete</v-icon></v-btn>
                                                </div>

                                                <div v-else class="d-flex component-wrap align-center mb-7 mt-3">
                                                    <v-icon class="mr-5">link</v-icon>
                                                    <div v-html="item.links[i].string"></div>
                                                    <v-spacer></v-spacer>
                                                    <v-btn icon @click="edit_relations.links.push(i)" :disabled="edit_relations.links.includes(i)"><v-icon>edit</v-icon></v-btn>
                                                    <v-btn icon class="ml-3" @click="DeleteRelation ('links', i)" ><v-icon>delete</v-icon></v-btn>
                                                </div>
                                            </div>
                                        </v-col>

                                        <!-- Literature from Type -->
                                        <v-col cols="12" sm="12" class="mt-10">
                                            <v-divider class="mb-1"></v-divider>
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-6">
                                                <div class="title">Type References &ensp;( {{ item.literature_type ? item.literature_type.length : '--' }} )</div>
                                                <div class="caption ml-5">These titles will be displayed as literature on public website.</div>
                                                <v-spacer></v-spacer>
                                                <div class="caption ml-5">To edit these records check the linked types.</div>
                                            </div>

                                            <div class="mt-n3" v-for="(iterator, i) in item.literature_type" :key="i">
                                                <div class="d-flex component-wrap align-start mb-7 mt-3">
                                                    <v-icon class="mr-5">menu_book</v-icon>
                                                    <div> 
                                                        <div v-html="iterator.string" class="mb-1"></div>
                                                        <div v-if="iterator.comment_de || iterator.comment_en" class="pl-3">
                                                            <div v-html="'(DE)&ensp;'+(iterator.comment_de ? iterator.comment_de : '--')" class="caption"></div>
                                                            <div v-html="'(EN)&ensp;'+(iterator.comment_en ? iterator.comment_en : '--')" class="caption"></div>
                                                        </div>
                                                    </div>
                                                </div>                                            
                                            </div>
                                        </v-col>
                                        
                                    </v-row>
                                </div>



                                <!-- Individuals ----------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab == 'individuals'" class="ml-3" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'INDIVIDUALS'"></div>

                                    <v-row>

                                        <!-- Persons -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-6">
                                                <div class="title">Persons &ensp;( {{ item.persons ? item.persons.length : '--' }} )</div>
                                                <inheritor
                                                    class="ml-1 mb-n1"
                                                    keyname="persons"
                                                    :typename="InheritingTypesName"
                                                    :inherited="item.inherited.persons"
                                                    :inheritedvalue="!inheriting_type.persons ? null : (!inheriting_type.persons ? null : printTypeData('persons', inheriting_type.persons) )"
                                                    v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, 'persons') }"
                                                ></inheritor>
                                                <div v-if="item.inherited.persons === 1" class="caption ml-5" v-text="'Values inherited from Type'"></div>
                                                <div v-else class="caption ml-5" v-text="'At least the person is required - otherwise input will be ignored.'"></div>
                                                <v-spacer></v-spacer>

                                                <v-btn text
                                                    :disabled="item.inherited.persons === 1"
                                                    @click="AddRelation ('persons', {id: null, function: null, string: null})"
                                                >
                                                    <v-icon>add</v-icon>&ensp;Add
                                                </v-btn>
                                            </div>

                                            <div v-for="(iterator, i) in item.persons" :key="i" class="mt-n3">
                                                <v-row v-if="edit_relations.persons.includes(i)">
                                                
                                                    <v-col cols="12" sm="8">
                                                        <InputForeignKey
                                                            entity="persons" 
                                                            :label="(i+1)+'. Person'" 
                                                            icon="emoji_people"
                                                            sk="el_uc_adv" 
                                                            sync
                                                            :key="item.persons[i].id"
                                                            :selected="item.persons[i] ? parseInt (item.persons[i].id) : null"
                                                            v-on:emitid="(emit) => { item.persons[i].id = emit }"
                                                            :inherited="item.inherited.person === 1"
                                                            style="width: 100%"
                                                        ></InputForeignKey>
                                                    </v-col>

                                                    <v-col cols="12" sm="4">
                                                        <div class="d-flex component-wrap">
                                                            <InputForeignKey
                                                                search_mixed_words
                                                                entity="functions" 
                                                                label="Function" 
                                                                icon="event_seat"
                                                                sk="el_uc"
                                                                :selected="item.persons[i] ? parseInt (item.persons[i].function) : null" 
                                                                :key="item.persons[i].function" 
                                                                v-on:emitid="(emit) => { item.persons[i].function = emit }"
                                                                :inherited="item.inherited.persons === 1"
                                                                style="width: 100%"
                                                            ></InputForeignKey>

                                                            <v-btn icon class="ml-3"
                                                                :disabled="item.inherited.persons === 1"
                                                                @click="DeleteRelation ('persons', i)" 
                                                            >
                                                                <v-icon>delete</v-icon>
                                                            </v-btn>
                                                        </div>
                                                    </v-col>
                                                </v-row>

                                                <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                                                    <v-icon class="mr-5">emoji_people</v-icon>
                                                    <div v-html="item.persons[i].string"></div>
                                                    <v-spacer></v-spacer>
                                                    <v-btn icon
                                                        :disabled="item.inherited.persons === 1 || edit_relations.persons.includes(i)"
                                                        @click="edit_relations.persons.push(i)"
                                                    >
                                                        <v-icon>edit</v-icon>
                                                    </v-btn>
                                                    <v-btn icon class="ml-3"
                                                        :disabled="item.inherited.persons === 1"
                                                        @click="DeleteRelation ('persons', i)" 
                                                    >
                                                        <v-icon>delete</v-icon>
                                                    </v-btn>
                                                </div>
                                            </div>                                           

                                        </v-col>

                                    </v-row>                                
                                </div>



                                <!-- Miscellaneous ----------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab === 'miscellaneous'" class="ml-3" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'Miscellaneous'"></div>

                                    <v-row>
                                        <!-- About -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="title pt-2" v-text="'About'"></div>
                                        </v-col>

                                        <v-col cols="12" sm="12">
                                            <v-textarea dense outlined filled clearable no-resize 
                                                height="100"
                                                v-model="item.description"
                                                label="Private Description"
                                                prepend-icon="notes"
                                                counter=21845
                                            ></v-textarea>

                                            <v-text-field dense outlined filled clearable
                                                v-model="item.source"
                                                label="Source Link"
                                                prepend-icon="link"
                                                hint="The actual source of the imported data. Please provide any other links to the References Tab."
                                                :rules="[rules.link]"
                                            ></v-text-field>
                                        </v-col>

                                        <!-- Object Group -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-8">
                                                <div class="title">Object Groups (private) &ensp;( {{ item.groups ? item.groups.length : '--' }} )</div>
                                                <v-spacer></v-spacer>
                                                <v-btn text 
                                                    @click="AddRelation ('groups', {
                                                        id: null, 
                                                        name: null,
                                                        comment: null
                                                    })"
                                                >
                                                    <v-icon>add</v-icon>&ensp;Add
                                                </v-btn>
                                            </div>

                                            <div class="mt-n3" v-for="(iterator, i) in item.groups" :key="i">                                                    
                                                <div v-if="edit_relations.groups.includes(i)" class="d-flex component-wrap">                                                    
                                                    <InputForeignKey 
                                                        entity="objectgroups" 
                                                        label="Object Group" 
                                                        icon="control_camera" 
                                                        sync 
                                                        :selected="item.groups[i].id" 
                                                        :key="item.groups[i].id"
                                                        v-on:emitid="(emit) => { item.groups[i].id = emit; }"
                                                    ></InputForeignKey>
                                                    <v-spacer></v-spacer>
                                                    <v-btn icon class="ml-3" @click="DeleteRelation ('groups', i)" ><v-icon>delete</v-icon></v-btn>
                                                </div>

                                                <div v-else class="d-flex component-wrap align-center mb-7 mt-3">
                                                    <v-icon class="mr-5">link</v-icon>
                                                    <div v-html="item.groups[i].name"></div>
                                                    <v-spacer></v-spacer>
                                                    <v-btn icon @click="edit_relations.groups.push(i)" :disabled="edit_relations.groups.includes(i)"><v-icon>edit</v-icon></v-btn>
                                                    <v-btn icon class="ml-3" @click="DeleteRelation ('groups', i)" ><v-icon>delete</v-icon></v-btn>
                                                </div>
                                            </div>
                                        </v-col>

                                        <!-- Comments -->
                                        <v-col cols="12" sm="12">
                                            <v-row>
                                                <v-col cols="12" sm="12">
                                                    <v-divider></v-divider>
                                                    <div class="title pt-2" v-text="'Comments'"></div>
                                                </v-col>
                                            
                                                <v-col v-for="record in ['comment_public', 'comment_private']" :key="record" cols="12" sm="6">
                                                    <div :class="wraped">
                                                        <v-textarea dense outlined filled clearable no-resize 
                                                            height="200"
                                                            v-model="item[record]"
                                                            :label="(record === 'comment_public' ? 'Public' : 'Private') + ' Comment'"
                                                            :prepend-icon="'chat_bubble' + (record === 'comment_public' ? '' : '_outlined')"
                                                            counter=21845
                                                            :disabled="item.inherited[record] === 1"
                                                            :hint="item.inherited[record] === 1 ? 'Value inherited from type' : ''"
                                                            :persistent-hint="item.inherited[record] === 1"
                                                        ></v-textarea>
                                                        <inheritor
                                                            :keyname="(record === 'comment_public' ? 'public' : 'private') + ' comment'"
                                                            :typename="InheritingTypesName"
                                                            :inherited="item.inherited[record]"
                                                            :inheritedvalue="(record === 'comment_public' ? 
                                                                (!inheriting_type ? null : inheriting_type.comment) : 
                                                                (!inheriting_type ? null : (inheriting_type.dbi ? inheriting_type.dbi.comment : null))
                                                            )"
                                                            v-on:toggle="(emit) => { ToggleTypeCoinSync (emit, record) }"
                                                        ></inheritor>
                                                    </div>
                                                </v-col>
                                            </v-row>
                                        </v-col>
                                        
                                    </v-row>
                                </div>

                            </v-card>

                            <!-- Import Data --------------------------------------------- -------------------------------------------- -->
                            <v-expand-transition>
                                <v-card v-if="import_active" class="appbar mt-5" tile>
                                    <v-card-title>
                                        External Data
                                        <v-spacer></v-spacer>
                                        <v-btn icon @click="import_active = false">
                                            <v-icon>clear</v-icon>
                                        </v-btn>                             
                                    </v-card-title>

                                    <v-card-text>
                                        Feature not yet implemented
                                    </v-card-text>
                                </v-card>
                            </v-expand-transition>

                        </div>
                    </div>
                    
                </v-col>

                <!-- Image Preview --------------------------------------------- -------------------------------------------- -->
                <v-col cols="12" sm="12" :md="img_card.zoom" :class="'ma-0 pa-0'+($vuetify.breakpoint.mdAndUp ? ' pl-5' : ' mt-5')">
                    <v-card class="appbar" tile>
                        <!-- Header -->
                        <v-card-title>
                            <v-menu offset-y>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-card tile flat
                                        v-bind="attrs"
                                        v-on="on"
                                        class="title transparent"
                                        v-html="'Images &ensp;( ' + (item.images ? item.images.length : '--') + ' )'"
                                        :disabled="item.images ? (item.images.length > 1 ? false : true) : true"
                                    ></v-card>
                                </template>
                                <v-card tile>
                                    <v-list>
                                        <v-list-item-group>
                                            <v-list-item v-for="(iterator, i) in item.images" :key="i"
                                                :class="img_index === i ? 'font-weight-black' : ''"
                                                v-text="''+(i + 1)"
                                                @click="img_index = i"
                                            ></v-list-item>
                                        </v-list-item-group>
                                    </v-list>
                                </v-card>
                            </v-menu>
                            <v-spacer></v-spacer>
                            <v-btn icon 
                                @click="img_card.zoom = img_card.zoom - 1" 
                                :disabled="img_card.zoom < 3 || !$vuetify.breakpoint.mdAndUp">
                                <v-icon>zoom_out</v-icon>
                            </v-btn>                                
                            <v-btn icon 
                                @click="img_card.zoom = img_card.zoom + 1" 
                                :disabled="img_card.zoom > 3 || !$vuetify.breakpoint.mdAndUp">
                                <v-icon>zoom_in</v-icon>
                            </v-btn>
                        </v-card-title>

                        <!-- Body -->
                        <v-card-text>
                            <Imager vertical coin
                                :item="item.images ? item : {images: []}" 
                                :index="item.images ? (img_index < item.images.length ? img_index : 0) : 0" 
                                :key="item.id+' '+img_index"
                            ></Imager>
                            <!-- Open Image as new tab -->
                            <div class="pt-2 d-flex justify-space-between" v-if="item.images[img_index]">
                                <div v-for="(s) in ['obverse', 'reverse']" :key="s">
                                    <a v-if="item.images[img_index][s].link"
                                        :href="!item.images[img_index][s].link ? '' : (
                                            item.images[img_index][s].link.substring(0,4) === 'http' ? item.images[img_index][s].link : 
                                                ( 'https://digilib.bbaw.de/digitallibrary/digilib.html?fn=silo10/thrakien/' + item.images[img_index][s].link )
                                            )" 
                                        target="_blank"
                                        v-html="s.toUpperCase().slice(0, 1) + s.slice(1) +'&ensp;&#10064;'"
                                    ></a>
                                    <span v-else class="sysbar--text" v-text="s.toUpperCase().slice(0, 1) + s.slice(1)"></span>
                                </div>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>

            </v-row>
        </v-card-text>
    </v-card>

</div>
</template>



<script> // ------------------------------------------------------------------------------------------------------------------------------------

// JK: Set constant variables (outside of vue component scope)
const CompLabelS            = 'Coin';
const CompLabelP            = 'Coins';
const CompLabelParent       = 'Coins';
const CompName              = 'coins';

const entity                = 'coins'; // JK: change if needed
const directory             = 'storage/'+entity; // JK: upload path

// Import VueComponents
import toolbar      from './MainToolbar.vue';
import inheritor    from './../../modules/inheritor.vue';

// Export Default ---------------------------------------------------------------------------------------------------------------------------
export default
{
    components: {
        'toolbar':          toolbar,
        'inheritor':        inheritor
    },

    data ()
    {
        return {
            component:          CompLabelS,
            componentp:         CompLabelP,
            entity:             entity,

            attributes:         this.$store.state.attributes.types,

            loading:            false,
            refresh:            0,
            gallery_refresh:    0,
            img_refresh:        0,
            input_preview:      false,

            derive:             {entity: null, id: null},
            import_active:      false,

            item:               {},
            new_link:           null,
            new_inheritor:      null,
            wraped:             'd-flex component-wrap align-start',

            // Image Stuff
            files_dialog:       {active: false, key: null, id: null},
            upload_dialog:      {active: false, key: null},
            link_dialog:        {active: false, index: null, side: null, input: null},

            img_card:           {zoom: 2},
            img_index:          0,

            // Tab Components            
            tab:                'images',
            tabs:               {
                                    images:         'Images',
                                    types:          'Types',
                                    production:     'Production',
                                    basics:         'Basics',
                                    obverse:        'Obverse',
                                    reverse:        'Reverse',
                                    references:     'References',
                                    individuals:    'Individuals',
                                    miscellaneous:  'Miscellaneous',
                                },

            edit_relations:     {
                                    images: [],
                                    links: [],
                                    persons: [],
                                    o_monograms: [],
                                    o_symbols: [],
                                    r_monograms: [],
                                    r_symbols: [],
                                    citations: [],
                                    literature: [],
                                    groups: []
            },

            select_img_type:    [
                                    {value: 'original',     text: 'Original'},
                                    {value: 'plastercast',  text: 'Plastercast'}
                                ],
            select_centerhole:  [
                                    {value: null,   text: 'none'},
                                    {value: 1,      text: 'Obverse'},
                                    {value: 2,      text: 'Reverse'},
                                    {value: 3,      text: 'Both Sides'}
                                ],

            // Rules
            rules:              {
                                    link:       value => (value ? value.substring(0,4) == 'http' : true) || 'A valid links starts with \'http\' or \'https\'.',
                                    required:   value => !!value || 'Input required.',
                                    numeric:    value => {
                                                    const pattern = /^(0|[1-9][0-9]*)$/
                                                    return pattern.test(value) || 'Must be numeric.'},
                                    numeric_nz: value => (value != null ? value > 0 : true) || 'Must be numeric and not zero (use dots for decimals).',
                                    date:       value => {
                                                    const pattern = /^-?([1-9][0-9]*)$/
                                                    return pattern.test(value) || 'Must be numeric and not zero.'},
                                    axis:       value => (value ? (value > 0 && value < 13) : true) || 'Axis mus be between 1 and 12.'
                                },

            // TypeCoinSynchronisation
            inheriting_type:    {},
            inherited:          {
                id_type: null,

                mint: null,
                issuer: null,
                authority: null,
                authority_person: null,
                authority_group: null,
                date: null,
                period: null,

                material: null,
                denomination: null,
                standard: null,
                
                o_design: null,
                o_legend: null,
                o_symbols: null,
                o_monograms: null,

                r_design: null,
                r_legend: null,
                r_symbols: null,
                r_monograms: null,

                persons: null,

                comment_private: null,
                comment_public: null/*
                id_type: null,
                date: null,
                period: null,
                legend_o: null,
                symbol_o: null,
                design_o: null,
                monogram_o: null,
                legend_r: null,
                symbol_r: null,
                design_r: null,
                monogram_r: null,
                issuer: null,
                authority: null,
                authority_person: null,
                authority_group: null,
                mint: null,
                material: null,
                denomination: null,
                standard: null,
                person: null,
                comment_private: null,
                comment_public: null*/
            },
        }
    },

    props: 
    {
        parent_id:  {type: Number}, 
    },

    computed: 
    {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        // JK: Check if ID is given by URL or Parent
        GivenID () {
            return this.$route.params.id ? this.$route.params.id : (this.parent_id ? this.parent_id : null);
        },

        // JK: set last crumb for Breadcrumbs (--> Watch)
        crumb () {
            return this.list ? 'List' : (this.item.id ? ('ID '+this.item.id) : ('New '+this.component));
        },

        InheritingTypesName () {
            const mint = this.inheriting_type.mint ? this.inheriting_type.mint.link : null
            const id = this.inheriting_type.id ? this.inheriting_type.id : null
            return this.$editor_format.typeName(id, mint);
        }
    },

    watch:
    {
        GivenID: function () {
            if (this.GivenID) 
            {
                this.SetItem (this.GivenID);
            }
            else
            {
                this.SetItemToDefault ();
            }
        },

        crumb: function () {        
            this.$store.commit( 'setBreadcrumbs',[
                {label:CompLabelParent,to:''},
                {label:this.crumb,to:''},
            ]);
        }
    },

    created () 
    {        
        this.$store.commit( 'setBreadcrumbs',[
            {label: CompLabelParent, to:''},
            {label: this.list ? 'List' : this.crumb, to:''},
        ]);

        this.SetItemToDefault ();        
        if (this.GivenID) { this.SetItem (this.GivenID) }
    },
    
    // ----------------------------------------------------------------------------------------------------------------------------------------------------
    methods: 
    {
        ToolbarReceive (emit) {
            console.log (emit);
            if (emit.command == 'clear') 
            {
                if (this.$route.path != '/coins/edit') { this.$router.push ('/coins/edit'); }
                else { this.SetItemToDefault (); }
            }
            else if (emit.command == 'preview')
            {
                this.input_preview = emit.value;
            }
        },

        Refresh ()
        {
            this.refresh = this.refresh +1;
        },

        SetItemToDefault () {
            this.item = this.ProcessItem ();
            this.Refresh (); 
        },

        async SetItem (id) 
        {
            this.loading = this.$root.loading = true;

            // Get Coin Data from DBI
            const dbi_coin = await this.$root.DBI_SELECT_GET ('coins', id)
            this.item = this.ProcessItem (dbi_coin [0])

            // Set Relations
            this.edit_relations = {
                images: [],
                links: [],
                persons: [],
                o_monograms: [],
                o_symbols: [],
                r_monograms: [],
                r_symbols: [],
                citations: [],
                literature: [],
                groups: [],
                // coin specific
                o_controlmarks: [],
                r_controlmarks: []
            }

            // Get inheriting Type Date from DBI if inheritance is set
            const dbi_type = this.item.inherited.id_type ? await this.$root.DBI_SELECT_GET ('types', this.item.inherited.id_type) : null
            if (dbi_type) { this.inheriting_type = dbi_type [0] }
            this.new_inheritor = this.inheriting_type.id ? this.inheriting_type.id : null

            // Refresh
            this.Refresh ();

            this.loading = this.$root.loading = false;
        },

        ProcessItem (d)
        {
            const item = this.$handlers.create_item('coins', d)
            console.log(item.inherited)
            return item
            /*let item = {};

            // --------------------------------------------------------------------

            item.id                 =   !d ? null : d.id;
            item.public             =   !d ? null : (d.dbi          ?   d.dbi.public        : null);
            item.description        =   !d ? null : (d.dbi          ?   d.dbi.description   : null);
            item.source             =   !d ? null : (d.source       ?   d.source.link       : null);            
            item.comment_public     =   !d ? null : d.comment;
            item.comment_private    =   !d ? null : (d.dbi          ?   d.dbi.comment       : null);
            
            item.created_at         =   !d ? '--' :                     this.$editor_format.date(this.l, d.created_at, false);
            item.updated_at         =   !d ? '--' :                     this.$editor_format.date(this.l, d.updated_at, false);
            item.creator            =   !d ? null : (d.dbi          ?   d.dbi.creator       : null);            
            item.editor             =   !d ? null : (d.dbi          ?   d.dbi.editor        : null);
            
            item.types              =   !d ? []   : (d.types        ?   d.types.map (v => {return {type: v.id}})      : []);
            
            item.mint               =   !d ? null : (d.mint         ?   d.mint.id           : null);
            // item.region             =   !d ? null : (d.mint         ?   (d.mint.region      ? d.mint.region.id      : null) : null);
            item.issuer             =   !d ? null : (d.issuer       ?   d.issuer.id         : null);
            item.authority          =   !d ? null : (d.authority    ?   d.authority.kind.id : null);
            item.authority_person   =   !d ? null : (d.authority    ?   (d.authority.person ? d.authority.person.id : null) : null);
            item.authority_group    =   !d ? null : (d.authority    ?   (d.authority.group  ? d.authority.group.id  : null) : null);
            
            item.diameter_min       =   !d ? null : (d.diameter     ?   (d.diameter.value_min   ? d.diameter.value_min.toFixed(2)   : null) : null);
            item.diameter_max       =   !d ? null : (d.diameter     ?   (d.diameter.value_max   ? d.diameter.value_max.toFixed(2)   : null) : null);
            item.diameter_ignore    =   !d ? 0    : (d.diameter     ?   (d.diameter.ignore      ? 1 : 0) : 0);
            item.weight             =   !d ? null : (d.weight       ?   (d.weight.value         ? d.weight.value.toFixed(2)         : null) : null);
            item.weight_ignore      =   !d ? 0    : (d.weight       ?   (d.weight.ignore        ? 1 : 0) : 0);

            item.axis               =   !d ? null : (d.axis         ?   d.axis              : null);
            item.centerhole         =   !d ? null : (d.centerhole   ?   d.centerhole.value  : null);
            
            item.material           =   !d ? null : (d.material     ?   d.material.id       : null);
            item.denomination       =   !d ? null : (d.denomination ?   d.denomination.id   : null);
            item.standard           =   !d ? null : (d.standard     ?   d.standard.id       : null);
            
            item.period             =   !d ? null : (d.date         ?   (d.date.period      ? d.date.period.id      : null) : null);
            item.date_start         =   !d ? null : (d.dbi          ?   d.dbi.date_start    : null);
            item.date_end           =   !d ? null : (d.dbi          ?   d.dbi.date_end      : null);
            item.date_text_de       =   !d ? null : (d.date         ?   d.date.text.de      : null);
            item.date_text_en       =   !d ? null : (d.date         ?   d.date.text.en      : null);

            item.persons            =   !d ? []   : (d.persons      ?   d.persons.map (v => {return {
                id: v.id, 
                function: v.function.id,
                string: v.name + (v.alias ? (' ( '+ v.alias +' )') : '') + ' ( ' + v.id + ' )' + '<br />' + (v.function.id ? (v.function.text.de + ' ( ' + v.function.id + ' )') : '--')
            }}) : []);
            
            // Images
            item.images             =   !d ? []   : (d.dbi          ?   (d.dbi.images       ?   d.dbi.images : []) : []);

            ['citations', 'literature'].forEach (
                (key) => {
                    item [key]      =   !d ? []   : (d[key]         ? d[key].map (v => { return {
                        id: v.id,
                        string: v.title + ', ' + v.quote.text.de +'&emsp;( <a href="' + v.link + '" target="_blank">' + v.id + '</a> )',
                        page: v.quote.page, 
                        number: v.quote.number, 
                        plate: v.quote.plate, 
                        picture: v.quote.picture, 
                        annotation: v.quote.annotation,
                        comment_de: v.quote.comment.de,
                        comment_en: v.quote.comment.en,
                        this: key == 'citations' ? 1 : 0
                    }}) : []);
                }
            );

            item.links              =   !d ? []   : (d.web_references   ?   d.web_references.map (v => { return {
                link: v.link,
                string: '<a href="' + v.link + '" target="_blank">' + v.link + '</a>'                
            }}) : []);

            item.groups             =   !d ? []   : (d.dbi   ?   (d.dbi.groups      ?   d.dbi.groups : []) : []);

            item.literature_type    =   !d ? []   : (d.type_literature  ?   d.type_literature.map (v => { return {
                id: v.id,
                string: 'Type ' + v.id_type + ':&ensp;' + v.title + ', ' + v.quote.text.de +'&emsp;( <a href="' + v.link + '" target="_blank">' + v.id + '</a> )',
                comment_de: v.quote.comment.de,
                comment_en: v.quote.comment.en
            }}) : []);

            ['obverse', 'reverse'].forEach (
                (key) => {
                    item [key.substring(0,1)+'_design']             =   !d ? null : (d[key] ? d[key].design.id  : null);
                    item [key.substring(0,1)+'_legend']             =   !d ? null : (d[key] ? d[key].legend.id  : null);
                    item [key.substring(0,1)+'_legend_direction']   =   !d ? null : (d[key] ? (d[key].legend.direction ? d[key].legend.direction.id  : null) : null);

                    item [key.substring(0,1)+'_monograms']          =   !d ? []   : (d[key] ? (d[key].monograms ? d[key].monograms.map (v => { return {
                        id: v.id, 
                        position: v.position.id,
                        image: '/storage/' + v.link,
                        string: v.combination + ' ( ' + v.id + ' )<br />' + (v.position.id ? (v.position.text.de + ' ( ' + v.position.id + ' )' ) : '--'),
                        side: key == 'obverse' ? 0 : 1
                    }}) : []) : []);

                    item [key.substring(0,1)+'_symbols']            =   !d ? []   : (d[key] ? (d[key].symbols   ? d[key].symbols.map (v => { return {
                        id: v.id, 
                        position: 
                        v.position.id,
                        string: v.text.de + ' ( ' + v.id + ' )<br />' + (v.position.id ? (v.position.text.de + ' ( ' + v.position.id + ' )' ) : '--'),
                        side: key == 'obverse' ? 0 : 1
                    }}) : []) : []);

                    item [key.substring(0,1)+'_die']                =   !d ? null : ( d[key] ? ( d[key].die ? d[key].die.id : null ) : null )
                    
                    item [key.substring(0,1)+'_controlmarks']       =   !d ? []   : (d[key] ? (d[key].controlmarks ? d[key].controlmarks.map (v => { return {
                        id: v.id,
                        name: v.name,
                        image: '/storage/' + v.link,
                        count: v.count,
                        side: key == 'obverse' ? 0 : 1
                    }}) : []) : []);

                    item [key.substring(0,1)+'_countermark_en']     =   !d ? null : (d[key] ? d[key].countermark.text.en  : null);
                    item [key.substring(0,1)+'_countermark_de']     =   !d ? null : (d[key] ? d[key].countermark.text.de  : null);

                    item [key.substring(0,1)+'_undertype_en']       =   !d ? null : (d[key] ? d[key].undertype.text.en  : null);
                    item [key.substring(0,1)+'_undertype_de']       =   !d ? null : (d[key] ? d[key].undertype.text.de  : null);
                }
            );

            item.findspot           =   !d ? null : (d.findspot     ?   d.findspot.id  : null);
            item.hoard              =   !d ? null : (d.hoard        ?   d.hoard.id     : null);

            item.provenience        =   !d ? null : (d.owner        ?   (d.owner.provenience  ? d.owner.provenience : null) : null)
            item.owner_original     =   !d ? null : (d.owner        ?   (d.owner.original     ? d.owner.original.id : null) : null)
            item.owner_unsure       =   !d ? 0    : (d.owner        ?   (d.owner.original     ? (d.owner.original.is_unsure ? 1 : 0) : 0) : 0)
            item.collection         =   !d ? null : (d.owner        ?   (d.owner.original     ? d.owner.original.collection_nr : null) : null)
            item.owner_reproduction =   !d ? null : (d.owner        ?   (d.owner.reproduction ? d.owner.reproduction.id : null) : null)
            item.plastercast        =   !d ? null : (d.owner        ?   (d.owner.reproduction ? d.owner.reproduction.collection_nr : null) : null)
            item.forgery            =   !d ? 0    : (d.is_forgery   ?   1 : 0);

            item.inherited          =   !d ? null : (d.dbi          ?   (d.dbi.inherited    ?   d.dbi.inherited : this.inherited) : this.inherited);

            // --------------------------------------------------------------------
            // console.log (item);

            return item;*/
        },

        ToggleTypeCoinSync (emit, key) {
            this.item.inherited [key] = emit
            if (emit === 1) { this.TypeToCoin(key) }
        },

        TypeToCoin (key) {
            const d = this.inheriting_type

            if (key === 'comment_public') {
                this.item.comment_public = !d ? null : d.comment
            }
            else if (key === 'comment_private') {
                this.item.comment_private = !d ? null : (d.dbi ? d.dbi.comment : null)
            }
            else if (key === 'persons') {
                this.item.persons = !d ? []: (d.persons ? d.persons.map (v => {return {
                    id: v.id, 
                    function: v.function.id,
                    string: v.name + (v.alias ? (' ( '+ v.alias +' )') : '') + ' ( ' + v.id + ' )' + '<br />' + (v.function.id ? (v.function.text.de + ' ( ' + v.function.id + ' )') : '--')
                }}) : []);
            }
            else if (key === 'period') {
                this.item.period        =   !d ? null : (d.date ? (d.date.period   ? d.date.period.id : null) : null)
            }
            else if (key === 'date') {
                this.item.date_start    =   !d ? null : (d.dbi  ? d.dbi.date_start : null)
                this.item.date_end      =   !d ? null : (d.dbi  ? d.dbi.date_end   : null)
                this.item.date_text_de  =   !d ? null : (d.date ? d.date.text.de   : null)
            }
            else if (key === 'mint') {
                this.item.mint = !d ? null : (d.mint ? d.mint.id : null)
            }
            else if (key === 'issuer') {
                this.item.issuer = !d ? null : (d.issuer ? d.issuer.id : null)   
            }
            else if (key === 'authority') {
                this.item.authority = !d ? null : (d.authority ? d.authority.kind.id : null) 
            }
            else if (key === 'authority_person') {
                this.item.authority_person = !d ? null : (d.authority ? (d.authority.person ? d.authority.person.id : null) : null)
            }
            else if (key === 'authority_group') {
                this.item.authority_group = !d ? null : (d.authority ? (d.authority.group ? d.authority.group.id : null) : null)
            }
            // Standard, Denomination, Material
            else if (['standard', 'denomination', 'material'].includes(key)) {
                this.item[key] = !d ? null : (d[key] ? d[key].id: null);
            }
            // Obverse / Reverse: Design, Legend, Monograms, Symbols
            else if (key.includes('o_') || key.includes('r_')) {
                const explode = key.split('_')
                const record = explode[1]
                const s = explode[0]
                const side = s === 'o' ? 'obverse' : 'reverse'

                if (record === 'monograms') {
                    this.item[s + '_monograms'] = !d ? [] : (d[side] ? (d[side].monograms ? d[side].monograms.map (v => { return {
                        id: v.id, 
                        position: v.position.id,
                        image: '/storage/' + v.link,
                        string: v.combination + ' ( ' + v.id + ' )<br />' + (v.position.id ? (v.position.text.de + ' ( ' + v.position.id + ' )' ) : '--'),
                        side: key == 'obverse' ? 0 : 1
                    }}) : []) : []);
                }
                else if (record === 'symbols') {
                    this.item[s + '_symbols'] =   !d ? [] : (d[key] ? (d[side].symbols ? d[side].symbols.map (v => { return {
                        id: v.id, 
                        position: 
                        v.position.id,
                        string: v.text.de + ' ( ' + v.id + ' )<br />' + (v.position.id ? (v.position.text.de + ' ( ' + v.position.id + ' )' ) : '--'),
                        side: key == 'obverse' ? 0 : 1
                    }}) : []) : []);
                }
                else {
                    this.item[s + '_' + record] = !d ? null : (d[side] ? d[side][record].id  : null)
                }
            }
            
        },

        async SendItem (reset, del) {

            this.loading    = true;

            const mode      = this.item.id ? (del ? 'delete' : 'update') : 'insert';
            const response  = await this.$root.DBI_INPUT_POST ('coins', mode, this.item);

            if (response.success) 
            {
                this.$root.snackbar (response.success, 'success');

                if (mode == 'insert')
                {
                    if (reset)
                    {
                        if (this.$route.path != '/coins/edit')  { this.$router.push ('/coins/edit');}
                        else    { this.SetItemToDefault (); }
                    }
                    else
                    {
                        if (this.$route.path != '/coins/edit/'+response.id)  { this.$router.push ('/coins/edit/'+response.id);}
                        else    { this.SetItem (this.item.id); }
                    }
                }
                else if (mode == 'update') 
                {
                    if (reset)
                    {
                        if (this.$route.path != '/coins/edit')  { this.$router.push ('/coins/edit');}
                        else    { this.SetItemToDefault (); }
                    }
                    else
                    {
                        this.SetItem (this.item.id);
                    }
                }
                else
                {
                    if (this.$route.path != '/coins/edit')  { this.$router.push ('/coins/edit');}
                    else    { this.SetItemToDefault (); }
                }

                /*if (!this.item.id) {this.item.id = response.id;}

                this.$emit ('update', { refresh: true, item: (this.input_mode != 'delete' ? this.item : {}) });

                if (this.input_reset) { this.Clear (); }

                this.input_reset = false;*/
            }
            else
            {
                console.log ('Data Input Error: response was not ok');
            }

            this.loading = false;
        },

        async manage_link (mode, id_type) {

            if (mode && id_type) {

                let confirmed = true
                
                if (mode == 'unlink') {                    
                    confirmed = confirm ('Are you sure you want to unlink Type ' + id_type + '?')
                }
                
                if (confirmed === true) {
                    const response = await this.$root.DBI_INPUT_POST ('types', 'connect', {
                        mode:       mode,
                        id_type:    id_type,
                        id_coin:    this.item.id,
                    });

                    if (response.success) {
                        this.$root.snackbar (response.success, 'success')
                        ++this.gallery_refresh
                        this.new_link = null
                    }
                    else { console.log ('Data Input Error: response was not ok') }
                }
            } else { alert ('manage_link: missing paramater!') }
        },

        async manage_inheritance (mode, id_type) {

            if (mode && id_type)
            {
                /*let confirmed = false

                if( mode === 'link') {
                    confirmed = confirm('Setting a new inheriting Type requires a complete data reload. Please save all unstaged changes before confirming this dialog. Otherwise these data will be lost!')

                    if (confirmed === true) {
                        const response = await this.$root.DBI_INPUT_POST ('coins', 'connect', {
                            mode:       mode,
                            id_type:    id_type,
                            id_coin:    this.item.id,
                        });

                        if (response.success) {
                            this.$root.snackbar (response.success, 'success')
                            this.SetItem (this.GivenID)
                            ++this.gallery_refresh
                        }
                        else { 
                            console.log ('Data Input Error: response was not ok') 
                        }
                    }
                }

                else {
                    confirmed = confirm('Are you sure you want to unlink inheriting Type ' + this.InheritingTypesName + '? Future updates on the Type will be ignored!')

                    if (confirmed === true) {
                        this.new_inheritor = null
                        this.item.inherited = this.inherited
                        alert('Inheritance deactivated. Change not saved yet.')
                    }
                }*/

                let confirmed = false
                
                confirmed = confirm( mode === 'unlink' ? 
                    ('Are you sure you want to unlink inheriting Type ' + this.InheritingTypesName + '? Future updates on the Type will be ignored!') :
                    ('Setting a new inheriting Type requires a complete data reload. Please save all unstaged changes before confirming this dialog. Otherwise these data will be lost!')
                );
                
                if (confirmed === true) {

                    const response = await this.$root.DBI_INPUT_POST ('coins', 'connect', {
                        mode:       mode,
                        id_type:    id_type,
                        id_coin:    this.item.id,
                    });

                    if( mode === 'link') {
                        if (response.success) {
                            this.$root.snackbar (response.success, 'success')
                            await this.SetItem (this.GivenID)
                            ++this.gallery_refresh

                            // Set Inheritance
                           const self = this
                            Object.keys(self.item.inherited).forEach((k) => {
                                self.TypeToCoin(k)
                            })                     
                        }
                        else { 
                            console.log ('Data Input Error: response was not ok') 
                        }
                    }
                    else {
                        this.item.inherited = this.inherited
                        this.new_inheritor = null
                        ++this.gallery_refresh
                    }
                }
            }
            else { alert ('manage_link: missing paramater!') }
        },

        AddRelation (entity, fields) {
            if (this.item[entity]) {
                this.item[entity].push(fields);
            } else {
                this.item[entity] = [fields];
            }

            this.edit_relations[entity].push(this.item[entity].length - 1);
            console.log (this.edit_relations);
        },

        DeleteRelation (entity, index) {
            this.edit_relations[entity] = [];
            this.item[entity].splice(index, 1);
            //this.edit_relations[entity].splice(this.edit_relations[entity].indexOf(index), 1);
        },

        // JK: File Browser Receive
        ImgBrowser (emit) {
            let explode = emit.key.split('_');
            this.item.images [explode [0]] [explode [1]].link = emit.id;
            this.files_dialog = { active: false, key: null, id: null };
        },

        // JK: Upload Dialog
        ImgUpload (emit) {
            let explode = emit.key.split('_');
            this.item.images [explode [0]] [explode [1]].link = emit.url;
            this.upload_dialog = {active: false, key: null};
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

            this.link_dialog = {active: true, index: index, side: side, input: input};
        },

        SetLink () {
            this.item.images[this.link_dialog.index][this.link_dialog.side].link = this.link_dialog.input.trim();
            this.CloseLink ();
        },

        CloseLink () {
            this.link_dialog = {active: false, index: null, side: null, input: null};
        },

        printTypeData (key, data) {
            // Function to display the value of the inheriting type in inheritor control dialog
            let to_return = '<ol>'            
            data.forEach( (row) => {
                if (key === 'monograms' || key === 'symbols') {
                    to_return += '<li>&ensp;( ' + row.id + ' )&ensp;' + ( key === 'monograms' ? row.combination : row.text.de) + ', ' + row.position.text.de + '</li>'
                } 
                else if (key === 'persons') {
                    to_return += '<li>&ensp;( ' + row.id + ' )&ensp;' + row.name + ', ' + row.function.text.de + '</li>'
                }               
            })
            to_return += '</ol>' 
            return to_return
        },

        DataReceive (emit) {
            /*var self    = this;
            let item    = {};
            let i       = 0;
            
            if (this.$route.path != '/coins/edit') { this.$router.push ('/coins/edit'); }

            if (emit.mode == 'import')
            {
                Object.keys (self.item_default) .forEach (function (key) 
                {
                    if (emit.data [key] != undefined ) 
                    {
                        item [key] = emit.data [key];
                        i = i +1;
                    } 
                    else 
                    {
                        item [key] = self.item_default [key];
                    }
                });
            }

            self.$store.commit ('showSnackbar', {message: 'SUCCESS: received data for new Coin ('+i+' values written).', duration: 10000});
            self.item = item;
            this.Refresh ();*/
        },
    }
}

</script>