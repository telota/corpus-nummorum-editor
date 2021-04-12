export default ({

    state: {

        source_entity: null,
        target_entity: null,
            
        source_item: {},
        target_item: {}
    },

    actions: 
    {
        process_source_item ({ commit }, input) {

            const entity    = input.entity ? input.entity : null
            const d         = input.item
            const item      = {};


            // Coin and Type --------------------------------------------------------------------
            if(['coins', 'types'].includes(entity)) {

                item.id                 =   !d ? null : d.id;
                item.public             =   !d ? null : (d.dbi          ?   d.dbi.public        : null);
                item.description        =   !d ? null : (d.dbi          ?   d.dbi.description   : null);

                item.source             =   !d ? null : (d.source       ?   d.source.link       : null);            
                item.comment_public     =   !d ? null : d.comment;
                item.comment_private    =   !d ? null : (d.dbi          ?   d.dbi.comment       : null);
                
                item.created_at         =   !d ? null : d.created_at;
                item.updated_at         =   !d ? null : d.updated_at;
                item.creator            =   !d ? null : (d.dbi          ?   d.dbi.creator       : null);            
                item.editor             =   !d ? null : (d.dbi          ?   d.dbi.editor        : null);
                
                item.mint               =   !d ? null : (d.mint         ?   d.mint.id           : null);
                item.mint_name          =   !d ? null : (d.mint         ?   (entity === 'coins' ? d.mint.text.de : d.mint.link)     : null);
                
                item.issuer             =   !d ? null : (d.issuer       ?   d.issuer.id         : null);
                item.authority          =   !d ? null : (d.authority    ?   d.authority.kind.id : null);
                item.authority_person   =   !d ? null : (d.authority    ?   (d.authority.person ? d.authority.person.id : null) : null);
                item.authority_group    =   !d ? null : (d.authority    ?   (d.authority.group  ? d.authority.group.id  : null) : null);
                
                item.material           =   !d ? null : (d.material     ?   d.material.id       : null);
                item.denomination       =   !d ? null : (d.denomination ?   d.denomination.id   : null);
                item.standard           =   !d ? null : (d.standard     ?   d.standard.id       : null);
                
                item.period             =   !d ? null : (d.date         ?   (d.date.period      ? d.date.period.id      : null) : null);
                item.date_start         =   !d ? null : (d.dbi          ?   d.dbi.date_start    : null);
                item.date_end           =   !d ? null : (d.dbi          ?   d.dbi.date_end      : null);
                item.date_text_de       =   !d ? null : (d.date         ?   d.date.text.de      : null);
                item.date_text_en       =   !d ? null : (d.date         ?   d.date.text.en      : null);

                item.links              =   !d ? []   : (d.web_references   ?   d.web_references.map (v => { return {
                    link: v.link,
                    string: '<a href="' + v.link + '" target="_blank">' + v.link + '</a>'                
                }}) : []);

                item.groups             =   !d ? []   : (d.dbi   ?   (d.dbi.groups      ?   d.dbi.groups : []) : []);

                item.persons            =   !d ? []   : (d.persons      ?   d.persons.map (v => {return {
                    id: v.id, 
                    function: v.function.id,
                    string: v.name + (v.alias ? (' ( '+ v.alias +' )') : '') + ' ( ' + v.id + ' )' + '<br />' + (v.function.id ? (v.function.text.de + ' ( ' + v.function.id + ' )') : '--')
                }}) : []);

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

                        if(input.entity === 'coins') {

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
                    }
                );
            }


            // Coin specific --------------------------------------------------------------------
            if(entity === 'coins') {
                
                item.types              =   !d ? []   : (d.types        ?   d.types.map (v => {return {type: v.id}})      : []);
                item.inherited          =   !d ? null : (d.dbi          ?   (d.dbi.inherited    ?   d.dbi.inherited : this.state.inherited) : this.state.inherited);

                item.provenience        =   !d ? null : (d.owner        ?   (d.owner.provenience  ? d.owner.provenience : null) : null)
                item.owner_original     =   !d ? null : (d.owner        ?   (d.owner.original     ? d.owner.original.id : null) : null)
                item.owner_unsure       =   !d ? 0    : (d.owner        ?   (d.owner.original     ? (d.owner.original.is_unsure ? 1 : 0) : 0) : 0)
                item.collection         =   !d ? null : (d.owner        ?   (d.owner.original     ? d.owner.original.collection_nr : null) : null)
                item.owner_reproduction =   !d ? null : (d.owner        ?   (d.owner.reproduction ? d.owner.reproduction.id : null) : null)
                item.plastercast        =   !d ? null : (d.owner        ?   (d.owner.reproduction ? d.owner.reproduction.collection_nr : null) : null)
                
                item.diameter_min       =   !d ? null : (d.diameter     ?   (d.diameter.value_min   ? d.diameter.value_min.toFixed(2)   : null) : null);
                item.diameter_max       =   !d ? null : (d.diameter     ?   (d.diameter.value_max   ? d.diameter.value_max.toFixed(2)   : null) : null);
                item.diameter_ignore    =   !d ? 0    : (d.diameter     ?   (d.diameter.ignore      ? 1 : 0) : 0);
                item.weight             =   !d ? null : (d.weight       ?   (d.weight.value         ? d.weight.value.toFixed(2)         : null) : null);
                item.weight_ignore      =   !d ? 0    : (d.weight       ?   (d.weight.ignore        ? 1 : 0) : 0);

                item.axis               =   !d ? null : (d.axis         ?   d.axis              : null);
                item.centerhole         =   !d ? null : (d.centerhole   ?   d.centerhole.value  : null);

                item.images             =   !d ? []   : (d.dbi          ?   (d.dbi.images       ?   d.dbi.images : []) : []);

                item.literature_type    =   !d ? []   : (d.type_literature  ?   d.type_literature.map (v => { return {
                    id: v.id,
                    string: 'Type ' + v.id_type + ':&ensp;' + v.title + ', ' + v.quote.text.de +'&emsp;( <a href="' + v.link + '" target="_blank">' + v.id + '</a> )',
                    comment_de: v.quote.comment.de,
                    comment_en: v.quote.comment.en
                }}) : []);

                item.findspot           =   !d ? null : (d.findspot     ?   d.findspot.id  : null);
                item.hoard              =   !d ? null : (d.hoard        ?   d.hoard.id     : null);
                item.forgery            =   !d ? 0    : (d.is_forgery   ?   1 : 0);
            }


            // Type specific --------------------------------------------------------------------
            else if(entity === 'types') {                
                
                item.coins              =   !d ? []   : (d.coins        ?   d.coins.map (v => {return {coin: v.id}})      : []);
                item.name               =   !d ? null : (d.dbi          ?   d.dbi.name          : null);
                
                item.diameter_min       =   !d ? null : (d.diameter     ?   (d.diameter.value_min ? d.diameter.value_min          : null) : null);
                item.diameter_max       =   !d ? null : (d.diameter     ?   (d.diameter.value_max ? (d.diameter.value_min)        : null) : null);
                item.diameter_count     =   !d ? ''   : (d.diameter     ?   (d.diameter.count     ? ('('+d.diameter.count+')')  : '')   : '');
                item.weight             =   !d ? null : (d.weight       ?   (d.weight.value       ? d.weight.value                : null) : null);
                item.weight_count       =   !d ? ''   : (d.weight       ?   (d.weight.count       ? ('('+d.weight.count+')')    : '')   : '');
    
                item.axis               =   !d ? '--' : (d.axes         ?   d.axes.map (v => {return v.value+' ('+v.count+')'}).join(', ')       : '--');
    
                item.image              =   !d ? null : (d.images       ?   d.images[0].id      : null);
                item.images             =   !d ? []   : (d.images       ?   d.images            : []);
                
                item.variations         =   !d ? []   : (d.variations   ?   d.variations.map (v => {return {de: v.text.de, en: v.text.en, comment: v.comment}})        : []);
    
                item.findspots          =   !d ? []   : (d.dbi          ?   (d.dbi.findspots    ? d.dbi.findspots       : []) : []);
                item.hoards             =   !d ? []   : (d.dbi          ?   (d.dbi.hoards       ? d.dbi.hoards          : []) : []);
            }

            // -------------------------------------------------------------------
            commit('set_source', { 
                entity: entity, 
                item: item 
            })
        }
    },

    mutations: {

        set_source (state, input) {
            state.source_entity = input.entity;
            state.source_item   = input.item;
        },
    } 

});