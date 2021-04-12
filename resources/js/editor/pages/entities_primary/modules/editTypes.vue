<template>
<div>

    <!-- JK: Input Mask -->
    <v-card tile class="mt-3" :loading="loading">

        <!-- JK: Appbar / Title -->
        <v-app-bar color="sysbar">
            <span :class="'headline'+(item.public == 3 ? ' text-decoration-line-through' : '')">
                {{ !GivenID ? ('Create new '+component) : (component+'&ensp;'+GivenID+( item.public == 1 ? '&ensp;*' : '' )) }}
            </span>

            <v-spacer></v-spacer>

            <v-btn v-if="!parent_id" tile color="blue_prim" class="white--text" @click="$router.push ('/types/search'+(item.id ? ('/'+item.id) : ''))">
                List all {{ componentp }}
            </v-btn>
            <v-btn v-else tile color="blue_prim" class="white--text" @click="$emit ('close')">
                Back to Search Results
            </v-btn>
        </v-app-bar>

        <!-- JK: Toolbar
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
        ></toolbar> -->

        <!-- JK: Input Form body ------------------------------------------- -------------------------------------------- -------------------------------------------- --> 
        <v-card-text>
            <v-row class="ma-0 pa-0">

                <!-- Tabs Area -->
                <v-col cols="12" sm="12" :md="(!import_active ? 12 : 9)-img_card.zoom" class="ma-0 pa-0">
                    <div :class="$vuetify.breakpoint.mdAndUp ? 'd-flex component-wrap' : ''" style="width: 100%">

                        <!-- Tab Menu -->
                        <div :class="$vuetify.breakpoint.mdAndUp ? 'mr-5' : 'mb-5'">
                            <v-card tile class="sysbar">                            
                                <v-list class="transparent" :disabled="!GivenID">                                    
                                    <v-list-item-group class="mt-n2 mb-n2">
                                        <v-list-item @click="tab = key"
                                            v-for="(text, key) in tabs" :key="key"
                                            :disabled="tab == key" 
                                            class="pr-9"
                                            v-text="text"
                                            style="white-space: nowrap;"
                                        ></v-list-item>
                                    </v-list-item-group>
                                </v-list>
                            </v-card>
                        </div>

                        <!-- -------------------------------------------------------------------------------------------- -->

                        <div style="width: 100%">
                            <v-card tile class="appbar pt-5 pl-5 pr-5" min-height="500px">

                                <!-- New Type ----------------------------------------- -------------------------------------------- -->
                                <div v-if="!GivenID" style="width: 100%; padding-left: 15%; padding-right: 15%" class="d-flex align-center">
                                    <div style="width: 100%;">
                                        <v-btn tile block color="blue_prim" class="mb-5" v-text="'New empty Type'" @click="SendItem (false, false)"></v-btn>
                                        <v-btn tile block color="blue_prim" class="mb-5" v-text="'New Type from CN Type'" disabled></v-btn>
                                        <v-btn tile block color="blue_prim" class="mb-5" v-text="'New Type from CN Coin'" disabled></v-btn>
                                        <v-btn tile block color="blue_sec" v-text="'New Type from Import'" disabled></v-btn>
                                    </div>
                                </div>

                                <!-- Coins ----------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab == 'coins'" class="pb-5" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'TYPE COIN RELATIONS'"></div>
                                    
                                    <v-row>
                                        <v-col cols="12" sm="4">
                                            <div class="d-flex component-wrap mb-n5">
                                                <v-text-field dense outlined filled clearable
                                                    v-model="new_link"
                                                    label="Coin ID"
                                                    prepend-icon="copyright"
                                                    hint="Link a valid Coin ID on this Type." persistent-hint
                                                    :rules="[value => { const pattern = /^(null|[1-9][0-9]*)$/; return pattern.test(value) || 'Must be numeric.'}]"
                                                ></v-text-field>
                                                <v-btn icon @click="manage_link ('link', new_link)">
                                                    <v-icon v-text="'link'"></v-icon>
                                                </v-btn>
                                            </div>
                                        </v-col>
                                    </v-row>

                                    <gallery 
                                        entity="coins"
                                        coins_only
                                        show_toolbar
                                        search_key="id_type" 
                                        :search_val="item.id" 
                                        :key="item.id+' '+gallery_refresh"
                                        v-on:select="function (emit) {}"
                                        
                                        v-on:delete="emit => {manage_link ('unlink', emit)}"
                                        v-on:image="emit => {set_image (emit.id, emit.img)}"
                                    ></gallery>
                                </div><!-- v-on:edit="emit => "-->



                                <!-- Basics ---------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab == 'basics'" class="pb-5" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'BASICS'"></div>

                                    <v-row>                                    
                                        <!-- About -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end">
                                                <div class="title pt-2" v-text="'About'"></div>
                                                <v-spacer></v-spacer>
                                                <div class="caption" style="white-space: nowrap;">                                                
                                                    created: {{ item.created_at+' ('+(item.creator ? item.creator : '--') }}) 
                                                    &ensp;|&ensp; 
                                                    updated: {{ item.updated_at+' ('+(item.editor ? item.editor : '--') }})
                                                    &ensp;|&ensp;
                                                    <span v-if="item.public == 1" v-text="'published'" class="green--text"></span>
                                                    <span v-else-if="item.public == 3" v-text="'deleted'" class="red--text"></span>
                                                    <span v-else v-text="'not published'"></span>
                                                </div>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="12">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item.name"
                                                label="Private Name"
                                                prepend-icon="label"
                                            ></v-text-field>

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
                                                :rules="[rules.link]"
                                            ></v-text-field>
                                        </v-col>

                                        <!-- Comments -->
                                        <v-col cols="12" sm="12">
                                            <v-row>
                                                <v-col cols="12" sm="12">
                                                    <v-divider></v-divider>
                                                    <div class="title pt-2" v-text="'Comments'"></div>
                                                </v-col>
                                            
                                                <v-col cols="12" sm="6">
                                                    <v-textarea dense outlined filled clearable no-resize 
                                                        height="200"
                                                        v-model="item.comment_public"
                                                        label="Public Comment"
                                                        prepend-icon="chat_bubble"
                                                        counter=21845
                                                    ></v-textarea>
                                                </v-col>

                                                <v-col cols="12" sm="6">                                        
                                                    <v-textarea dense outlined filled clearable no-resize 
                                                        height="200"
                                                        v-model="item.comment_private"
                                                        label="Private Comment"
                                                        prepend-icon="chat_bubble_outline"
                                                        counter=21845
                                                    ></v-textarea>
                                                </v-col>
                                            </v-row>
                                        </v-col>

                                    </v-row>
                                </div>



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
                                            <InputForeignKey 
                                                entity="mints" 
                                                label="Mint" 
                                                icon="museum"
                                                :selected="parseInt (item.mint)" 
                                                :key="item.mint"
                                                v-on:emitid="function (emit) { item.mint = emit }"
                                            ></InputForeignKey>
                                        </v-col>
                                        <v-col cols="12" sm="6">
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey class="mt-n5"
                                                entity="authorities" 
                                                label="Type of Coinage" 
                                                icon="toll"
                                                :selected="parseInt (item.authority)" 
                                                :key="item.authority"
                                                v-on:emitid="function (emit) { item.authority = emit }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey class="mt-n5"
                                                entity="persons" 
                                                label="Issuer" 
                                                icon="record_voice_over" 
                                                sk="el_uc" 
                                                sync 
                                                :selected="parseInt (item.issuer)" 
                                                :key="item.issuer"
                                                v-on:emitid="function (emit) { item.issuer = emit }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey class="mt-n5"
                                                entity="persons" 
                                                label="Authority Person" 
                                                icon="how_to_reg" 
                                                sk="el_uc" 
                                                sync 
                                                :selected="parseInt (item.authority_person)" 
                                                :key="item.authority_person"
                                                v-on:emitid="function (emit) { item.authority_person = emit }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey class="mt-n5"
                                                entity="tribes" 
                                                label="Authority Group" 
                                                icon="sports_kabaddi"
                                                :selected="parseInt (item.authority_group)" 
                                                :key="item.authority_group"
                                                v-on:emitid="function (emit) { item.authority_group = emit }"
                                            ></InputForeignKey>
                                        </v-col>
                                    </v-row>
                                    
                                    <!-- Shape -->
                                    <v-row>
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end">
                                                <div class="title pt-2" v-text="'Shape'"></div>
                                                <v-spacer></v-spacer>
                                                <div class="caption">&bigodot;
                                                    {{ item.diameter_min && item.diameter_max && item.diameter_min != item.diameter_max ? (
                                                    item.diameter_min+'&ndash;'+item.diameter_max+' mm') : (
                                                    item.diameter_min ? (item.diameter_min+' mm') : (item.diameter_max ? (item.diameter_max+' mm') : '--') )}} 
                                                    {{ item.diameter_count }}
                                                    &ensp;|&ensp;
                                                    {{ item.weight ? (item.weight+' g') : '--' }} 
                                                    {{ item.weight_count }}
                                                    &ensp;|&ensp;
                                                    &#9719; {{ item.axis }}
                                                </div>
                                            </div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey 
                                                entity="materials" 
                                                label="Material" 
                                                icon="palette"
                                                :selected="parseInt (item.material)" 
                                                :key="item.material"
                                                v-on:emitid="function (emit) { item.material = emit }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey class="mt-n5"
                                                entity="denominations" 
                                                label="Denomination" 
                                                icon="bubble_chart"
                                                :selected="parseInt (item.denomination)" 
                                                :key="item.denomination"
                                                v-on:emitid="function (emit) { item.denomination = emit }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey class="mt-n5"
                                                entity="standards" 
                                                label="Standard" 
                                                icon="group_work"
                                                :selected="parseInt (item.standard)" 
                                                :key="item.standard"
                                                v-on:emitid="function (emit) { item.standard = emit }"
                                            ></InputForeignKey>
                                        </v-col>
                                    </v-row>
                                    
                                    
                                    <!-- Date -->
                                    <v-row>
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="title pt-2" v-text="'Date'"></div>
                                        </v-col>

                                        <v-col cols="12" sm="6">
                                            <InputForeignKey 
                                                entity="epochs" 
                                                label="Epoch" 
                                                icon="watch_later"
                                                :selected="item.period ? parseInt (item.period) : null" 
                                                :key="item.period"
                                                v-on:emitid="function (emit) { item.period = emit }"
                                            ></InputForeignKey>
                                        </v-col>

                                        <v-col cols="12" sm="3" v-for="record in ['date_start', 'date_end']" :key="record">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item [record]"
                                                :label="'Date '+(record == 'date_start' ? 'Start' : 'End')"
                                                :prepend-icon="record == 'date_start' ? 'first_page' : 'last_page'"
                                                :rules="[rules.date]"
                                            ></v-text-field>
                                        </v-col>

                                        <v-col cols="12" sm="6" v-for="record in ['de', 'en']" :key="record" class="mt-n5">
                                            <v-text-field dense outlined filled clearable
                                                v-model="item ['date_text_'+record]"
                                                :label="'Date Text ('+record.toUpperCase()+')'"
                                                prepend-icon="settings_ethernet"
                                                :disabled="record == 'en'"
                                            ></v-text-field>
                                        </v-col>

                                    </v-row>
                                </div>


                                <!-- Obverse / Reverse ----------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab == 'obverse' || tab == 'reverse'" class="ml-3" style="width:100%" >
                                    <div class="headline text-center mt-n1" v-text="(tab == 'obverse' ? 'OB' : 'RE')+'VERSE'"></div>

                                    <v-row>
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="title pt-2" v-text="'Description'"></div>
                                        </v-col>

                                        <!-- Legends / Designs -->
                                        <v-col cols="12" sm="12" v-for="record in ['legend', 'design']" :key="record">
                                            <InputForeignKey 
                                                :entity="record+'s'" 
                                                :label="(tab == 'obverse' ? 'Ob' : 'Re')+'verse '+(record == 'legend' ? 'Legend' : 'Design')" 
                                                :icon="record == 'legend' ? 'translate' : 'notes'" 
                                                :sk="record == 'legend' ? 'el_uc_adv' : null" 
                                                sync 
                                                :selected="parseInt (item[tab.substring(0,1)+'_'+record])" 
                                                :key="item[tab.substring(0,1)+'_'+record]"
                                                v-on:emitid="function (emit) {item[tab.substring(0,1)+'_'+record] = emit}"
                                            ></InputForeignKey>
                                        </v-col>

                                        <!-- Symbols / Monograms -->
                                        <v-col cols="12" sm="12" v-for="record in ['symbols', 'monograms']" :key="record">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-6">
                                                <div class="title">{{ record == 'monograms' ? 'Monograms' : 'Symbols' }} &ensp;( {{ item[tab.substring(0,1)+'_'+record] ? item[tab.substring(0,1)+'_'+record].length : '--' }} )</div>
                                                <div class="caption ml-5">At least the {{ record == 'monograms' ? 'Monogram' : 'Symbol' }} is required - otherwise input will be ignored.</div>
                                                <v-spacer></v-spacer>

                                                <v-btn text @click="AddRelation ((tab.substring(0,1)+'_'+record), {id: null, position: null, side: tab.substring(0,1) == 'o' ? 0 : 1})">
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
                                                            search_mixed_chars
                                                            v-on:emitid="function (emit) {item[tab.substring(0,1)+'_'+record][i].id = emit}"
                                                        ></InputForeignKey>
                                                    </v-col>

                                                    <v-col cols="12" sm="4">
                                                        <div class="d-flex component-wrap">
                                                            <InputForeignKey style="width: 100%"
                                                                search_mixed_words
                                                                entity="positions" 
                                                                label="Position" 
                                                                icon="motion_photos_on"                    
                                                                :selected="parseInt (item[tab.substring(0,1)+'_'+record][i].position)" 
                                                                :key="item[tab.substring(0,1)+'_'+record][i].position" 
                                                                v-on:emitid="function (emit) {item[tab.substring(0,1)+'_'+record][i].position = emit}"
                                                            ></InputForeignKey>

                                                            <v-btn icon class="ml-3" @click="DeleteRelation((tab.substring(0,1)+'_'+record), i)" ><v-icon>delete</v-icon></v-btn>
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
                                                    <v-btn icon @click="edit_relations[tab.substring(0,1)+'_'+record].push(i)" :disabled="edit_relations[tab.substring(0,1)+'_'+record].includes(i)"><v-icon>edit</v-icon></v-btn>
                                                    <v-btn icon class="ml-3" @click="DeleteRelation((tab.substring(0,1)+'_'+record), i)" ><v-icon>delete</v-icon></v-btn>
                                                </div>
                                            </div>

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
                                                <v-row v-if="edit_relations [record].includes(i)">

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
                                                            v-on:emitid="function (emit) {item[record][i].id = emit;}"
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
                                                <div class="caption ml-5">At least the person is required - otherwise input will be ignored.</div>
                                                <v-spacer></v-spacer>

                                                <v-btn text @click="AddRelation ('persons', {id: null, function: null, string: null})">
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
                                                            v-on:emitid="function (emit) { item.persons[i].id = emit }"
                                                        ></InputForeignKey>
                                                    </v-col>

                                                    <v-col cols="12" sm="4">
                                                        <div class="d-flex component-wrap">
                                                            <InputForeignKey style="width: 100%"
                                                                search_mixed_words
                                                                entity="functions" 
                                                                label="Function" 
                                                                icon="event_seat"
                                                                sk="el_uc"
                                                                :selected="item.persons[i] ? parseInt (item.persons[i].function) : null" 
                                                                :key="item.persons[i].function" 
                                                                v-on:emitid="function (emit) { item.persons[i].function = emit }"
                                                            ></InputForeignKey>

                                                            <v-btn icon class="ml-3" @click="DeleteRelation ('persons', i)" ><v-icon>delete</v-icon></v-btn>
                                                        </div>
                                                    </v-col>
                                                </v-row>

                                                <div v-else class="d-flex component-wrap align-start mb-7 mt-3">
                                                    <v-icon class="mr-5">emoji_people</v-icon>
                                                    <div v-html="item.persons[i].string"></div>
                                                    <v-spacer></v-spacer>
                                                    <v-btn icon @click="edit_relations.persons.push(i)" :disabled="edit_relations.persons.includes(i)"><v-icon>edit</v-icon></v-btn>
                                                    <v-btn icon class="ml-3" @click="DeleteRelation ('persons', i)" ><v-icon>delete</v-icon></v-btn>
                                                </div>
                                            </div>                                           

                                        </v-col>

                                    </v-row>                                
                                </div>



                                <!-- Variations ----------------------------------------- -------------------------------------------- -->
                                <div v-else-if="tab == 'varia'" class="ml-3" style="width:100%">
                                    <div class="headline text-center mt-n1" v-text="'VARIA'"></div>

                                    <v-row>

                                        <!-- Variations -->
                                        <v-col cols="12" sm="12">
                                            <v-divider></v-divider>
                                            <div class="d-flex component-wrap align-end pt-2 pb-6">
                                                <div class="title">Variations &ensp;( {{ item.variations ? item.variations.length : '--' }} )</div>
                                                <div class="caption ml-5">German and English Description are required - otherwise input will be ignored.</div>
                                                <v-spacer></v-spacer>

                                                <v-btn text @click="AddRelation ('variations', {de: null, en: null, comment: null})">
                                                    <v-icon>add</v-icon>&ensp;Add
                                                </v-btn>
                                            </div>

                                            <v-row v-for="(iterator, i) in item.variations" :key="i" class="mt-n3">

                                                <v-col cols="12" sm="12" class="mb-n3 mt-1">
                                                    <div class="d-flex component-wrap">
                                                        <span class="body-1" v-text="(i+1)+'. Variation'"></span>
                                                        <v-spacer></v-spacer>
                                                        <v-btn icon @click="edit_relations.variations.push(i)" :disabled="edit_relations.variations.includes(i)"><v-icon>edit</v-icon></v-btn>
                                                        <v-btn icon class="ml-3" @click="DeleteRelation ('variations', i)" ><v-icon>delete</v-icon></v-btn>
                                                    </div>
                                                </v-col>
                                                
                                                <v-col cols="12" sm="6" v-for="record in ['de', 'en']" :key="record">
                                                    <v-textarea dense outlined filled clearable no-resize
                                                        v-if="edit_relations.variations.includes(i)"
                                                        height="100"
                                                        v-model="item.variations[i][record]"
                                                        :label="'Description ('+record.toUpperCase()+')'"
                                                        prepend-icon="notes"
                                                        counter=21845
                                                    ></v-textarea>

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
                                                        v-if="edit_relations.variations.includes(i)"
                                                        height="50"
                                                        v-model="item.variations[i].comment"
                                                        label="Comment"
                                                        prepend-icon="drag_handle"
                                                        counter=21845
                                                    ></v-textarea>

                                                    <div v-else class="d-flex component-wrap align-start mb-4">
                                                        <v-icon class="mr-5">drag_handle</v-icon>
                                                        <div>
                                                            <div class="font-weight-thin text-uppercase" v-text="'Comment'"></div>
                                                            {{ item.variations[i].comment ? item.variations[i].comment : '--' }}
                                                        </div>
                                                    </div>
                                                </v-col>

                                            </v-row>

                                        </v-col>

                                    </v-row>
                                </div>

                            </v-card>
                        </div>
                    </div>
                </v-col>

                <!-- Import Information----------------------------------------- -------------------------------------------- 
                <v-col cols="12" sm="12" :md="3" :class="'ma-0 pa-0'+($vuetify.breakpoint.mdAndUp ? ' pl-5' : ' mt-5')" v-if="import_active">
                    <v-card class="appbar" tile>
                        <v-card-title>
                            External Data<v-spacer></v-spacer>
                            <v-btn icon @click="import_active = false" disabled>
                                <v-icon>clear</v-icon>
                            </v-btn>                             
                        </v-card-title>

                        <v-card-text>
                            <div v-for="(value, key, i) in imports" :key="i" class="mb-4">
                                <div class="font-weight-thin">{{ value }}</div>
                                <div class="caption">{{ item [key] }}</div>
                            </div>
                        </v-card-text>
                    </v-card>
                </v-col>-->

                <!-- Image Preview --------------------------------------------- -------------------------------------------- -->
                <v-col cols="12" sm="12" :md="img_card.zoom" :class="'ma-0 pa-0'+($vuetify.breakpoint.mdAndUp ? ' pl-5' : ' mt-5')">
                    <v-card class="appbar" tile>
                        <v-card-title>
                            Images&ensp;( {{item.images.length}} )
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

                        <v-card-text>
                            <Imager vertical coin :item="item.images ? item : {images: []}" :key="item.id+' '+img_refresh"></Imager>
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
const CompLabelS            = 'Type';
const CompLabelP            = 'Types';
const CompLabelParent       = 'Types';
const CompName              = 'types';

const entity                = 'types'; // JK: change if needed
const directory             = 'storage/'+entity; // JK: upload path

// Import VueComponents
//import toolbar from './MainToolbar.vue';

// Export Default ---------------------------------------------------------------------------------------------------------------------------
export default
{
    components: {
        //'toolbar':          toolbar
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

            img_card:           {zoom: 2},
            input_preview:      false,

            derive:             {entity: null, id: null},
            import_active:      false,

            item:               {},
            new_link:           null,

            // Tab Components            
            tab:                'coins',
            tabs:               {
                                    coins:          'Linked Coins',
                                    basics:         'Basics',
                                    production:     'Production',
                                    obverse:        'Obverse',
                                    reverse:        'Reverse',
                                    references:     'References',
                                    individuals:    'Individuals',
                                    varia:          'Varia',
                                },

            edit_relations:     {
                                    links: [],
                                    persons: [],
                                    o_monograms: [],
                                    o_symbols: [],
                                    r_monograms: [],
                                    r_symbols: [],
                                    citations: [],
                                    literature: [],
                                    variations: [],
                                    findspots: [],
                                    hoards: [],
            },

            // Rules
            rules:              {
                                    link:       value => (value ? value.substring(0,4) == 'http' : true) || 'A valid links starts with \'http\' or \'https\'.',
                                    required:   value => !!value || 'Input required.',
                                    numeric:    value => {
                                                    const pattern = /^(0|[1-9][0-9]*)$/
                                                    return pattern.test(value) || 'Must be numeric.'},
                                    date:       value => {
                                                    const pattern = /^-?([1-9][0-9]*)$/
                                                    return pattern.test(value) || 'Must be numeric and not zero.'},
                                    axis:       value => (value > 0 && value < 13) || 'Axis mus be between 1 and 12.'
                                },
            
        }
    },

    props: 
    {
        parent_id:  {type: Number}, 
    },

    computed: 
    {
        // JK: Check if ID is given by URL or Parent
        GivenID () {
            return this.$route.params.id ? this.$route.params.id : (this.parent_id ? this.parent_id : null);
        },

        // JK: set last crumb for Breadcrumbs (--> Watch)
        crumb () {
            return this.list ? 'List' : (this.item.id ? ('ID '+this.item.id) : ('New '+this.component));
        },

        // JK: Check if needed filds are filled
        check () {
            let result = {};

            if (this.item.id) {result.basics = true};

            result.all = false;

            return result;
        },
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

        /*tab: function () {
            console.log(this.tab);
        },*/

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
        if (this.GivenID) {this.SetItem (this.GivenID);}
    },
    
    // ----------------------------------------------------------------------------------------------------------------------------------------------------
    methods: 
    {
        ToolbarReceive (emit) {
            console.log (emit);
            if (emit.command == 'clear') 
            {
                if (this.$route.path != '/types/edit') { this.$router.push ('/types/edit'); }
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
            this.loading = true;

            // Get Data from DBI
            const dbi = await this.$root.DBI_SELECT_GET ('types', id);

            this.item = this.ProcessItem (dbi [0]);

            this.edit_relations = {
                links: [],
                persons: [],
                o_monograms: [],
                o_symbols: [],
                r_monograms: [],
                r_symbols: [],
                citations: [],
                literature: [],
                // type specific
                variations: [],
                findspots: [],
                hoards: [],
            },

            this.Refresh ();

            this.loading = false;
        },

        ProcessItem (d)
        {
            let item = {};

            // --------------------------------------------------------------------

            item.id                 =   !d ? null : d.id;
            item.public             =   !d ? null : (d.dbi          ?   d.dbi.public        : null);
            item.name               =   !d ? null : (d.dbi          ?   d.dbi.name          : null);
            item.description        =   !d ? null : (d.dbi          ?   d.dbi.description   : null);
            item.source             =   !d ? null : (d.source       ?   d.source.link       : null);            
            item.comment_public     =   !d ? null : d.comment;
            item.comment_private    =   !d ? null : (d.dbi          ?   d.dbi.comment       : null);
            
            item.created_at         =   !d ? '--' :                     this.MakeDate (d.created_at, 'de');
            item.updated_at         =   !d ? '--' :                     this.MakeDate (d.updated_at, 'de');
            item.creator            =   !d ? null : (d.dbi          ?   d.dbi.creator       : null);            
            item.editor             =   !d ? null : (d.dbi          ?   d.dbi.editor        : null);
            
            item.coins              =   !d ? []   : (d.coins        ?   d.coins.map (v => {return {coin: v.id}})      : []);
            
            item.mint               =   !d ? null : (d.mint         ?   d.mint.id           : null);
            item.region             =   !d ? null : (d.mint         ?   (d.mint.region      ? d.mint.region.id      : null) : null);
            item.issuer             =   !d ? null : (d.issuer       ?   d.issuer.id         : null);
            item.authority          =   !d ? null : (d.authority    ?   d.authority.kind.id : null);
            item.authority_person   =   !d ? null : (d.authority    ?   (d.authority.person ? d.authority.person.id : null) : null);
            item.authority_group    =   !d ? null : (d.authority    ?   (d.authority.group  ? d.authority.group.id  : null) : null);
            
            item.diameter_min       =   !d ? null : (d.diameter     ?   (d.diameter.value_min ? d.diameter.value_min          : null) : null);
            item.diameter_max       =   !d ? null : (d.diameter     ?   (d.diameter.value_max ? (d.diameter.value_min)        : null) : null);
            item.diameter_count     =   !d ? ''   : (d.diameter     ?   (d.diameter.count     ? ('('+d.diameter.count+')')  : '')   : '');
            item.weight             =   !d ? null : (d.weight       ?   (d.weight.value       ? d.weight.value                : null) : null);
            item.weight_count       =   !d ? ''   : (d.weight       ?   (d.weight.count       ? ('('+d.weight.count+')')    : '')   : '');

            item.axis               =   !d ? '--' : (d.axes         ?   d.axes.map (v => {return v.value+' ('+v.count+')'}).join(', ')       : '--');
            
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

            item.image              =   !d ? null : (d.images       ?   d.images[0].id      : null);
            item.images             =   !d ? []   : (d.images       ?   d.images            : []);

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

            ['obverse', 'reverse'].forEach (
                function (key) {
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
                }
            );
            
            item.variations         =   !d ? []   : (d.variations   ?   d.variations.map (v => {return {de: v.text.de, en: v.text.en, comment: v.comment}})        : []);

            item.findspots          =   !d ? []   : (d.dbi          ?   (d.dbi.findspots    ? d.dbi.findspots       : []) : []);
            item.hoards             =   !d ? []   : (d.dbi          ?   (d.dbi.hoards       ? d.dbi.hoards          : []) : []);

            // --------------------------------------------------------------------
            // console.log (item);

            return item;
        },

        MakeDate (date, lang) {

            if (lang == 'de')
            {
                return date ? (date.substring(8,10)+'.'+date.substring(5,7)+'.'+date.substring(0,4)) : '--.--.----';
            }
            else
            {
                return date ? (date.substring(5,7)+'/'+date.substring(8,10)+'/'+date.substring(0,4)) : '--/--/----'; 
            }            
        },

        async SendItem (reset, del) {

            this.loading    = true;

            const mode      = this.item.id ? (del ? 'delete' : 'update') : 'insert';
            const response  = await this.$root.DBI_INPUT_POST ('types', mode, this.item);

            if (response.success) 
            {
                this.$root.snackbar (response.success, 'success');

                if (mode == 'insert')
                {
                    if (reset)
                    {
                        if (this.$route.path != '/types/edit')  { this.$router.push ('/types/edit');}
                        else    { this.SetItemToDefault (); }
                    }
                    else
                    {
                        if (this.$route.path != '/types/edit/'+response.id)  { this.$router.push ('/types/edit/'+response.id);}
                        else    { this.SetItem (this.item.id); }
                    }
                }
                else if (mode == 'update') 
                {
                    if (reset)
                    {
                        if (this.$route.path != '/types/edit')  { this.$router.push ('/types/edit');}
                        else    { this.SetItemToDefault (); }
                    }
                    else
                    {
                        this.SetItem (this.item.id);
                    }
                }
                else
                {
                    if (this.$route.path != '/types/edit')  { this.$router.push ('/types/edit');}
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

        async manage_link (mode, id_coin) {

            if (mode && id_coin)
            {
                let confirmed = true;
                
                if (mode == 'unlink')
                {                    
                    confirmed = confirm ('Are you sure you want to unlink Coin '+id_coin+'?');
                }
                
                if (confirmed === true) 
                {
                    const response = await this.$root.DBI_INPUT_POST ('types', 'connect', {
                        mode:       mode,
                        id_type:    this.item.id,
                        id_coin:    id_coin,
                    });

                    if (response.success)
                    {
                        this.$root.snackbar (response.success, 'success');
                        ++this.gallery_refresh;
                        this.new_link = null;
                    }
                    else
                    {
                        console.log ('Data Input Error: response was not ok');
                    }
                }
            }
            else
            {
                alert ('manage_link: missing paramater!')
            }
        },

        set_image (id, image) {
            let confirmed = confirm ('Are you sure you want to set this Coin '+id+' as representing for this Type?');
            
            if (confirmed === true) 
            {
                this.item.image = image[0].id;
                this.item.images = image;
                ++this.img_refresh;
            }
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