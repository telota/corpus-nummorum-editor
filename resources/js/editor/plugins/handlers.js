export default {

    install(Vue, options) {

        Vue.prototype.$handlers = {

            create_item: (entity, data) => {

                const d         = data
                const item      = {};

                // Coin and Type --------------------------------------------------------------------
                if(['coins', 'types'].includes(entity)) {

                    item.id                 =   d?.id                       ? d.id : null
                    item.public             =   d?.dbi?.public              ? d.dbi.public : null
                    item.description        =   d?.dbi?.description         ? d.dbi.description : null

                    item.source             =   d?.source?.link             ? d.source.link : null
                    item.comment_public_de  =   d?.comment?.de              ? d.comment.de : null
                    item.comment_public_en  =   d?.comment?.en              ? d.comment.en : null
                    item.comment_private    =   d?.dbi?.comment             ? d.dbi.comment : null

                    item.created_at         =   d?.created_at               ? d.created_at : null
                    item.updated_at         =   d?.updated_at               ? d.updated_at : null
                    item.creator            =   d?.dbi?.creator             ? d.dbi.creator : null
                    item.editor             =   d?.dbi?.editor              ? d.dbi.editor : null

                    item.mint               =   d?.mint?.id                 ? d.mint.id : null

                    item.issuer             =   d?.issuer?.id               ? d.issuer.id : null
                    item.authority          =   d?.authority?.kind?.id      ? d.authority.kind.id : null
                    item.authority_person   =   d?.authority?.person?.id    ? d.authority.person.id : null
                    item.authority_group    =   d?.authority?.group?.id     ? d.authority.group.id : null

                    item.material           =   d?.material?.id             ? d.material.id : null
                    item.denomination       =   d?.denomination?.id         ? d.denomination.id : null
                    item.standard           =   d?.standard?.id             ? d.standard.id : null

                    item.period             =   d?.date?.period?.id         ? d.date.period.id : null
                    item.date_start         =   d?.dbi?.date_start          ? d.dbi.date_start : null
                    item.date_end           =   d?.dbi?.date_end            ? d.dbi.date_end : null
                    item.date_text_de       =   d?.date?.text?.de           ? d.date.text.de : null
                    item.date_text_en       =   d?.date?.text?.en           ? d.date.text.en : null

                    item.pecularities_de    =   d?.pecularities?.de         ? d.pecularities.de : null
                    item.pecularities_en    =   d?.pecularities?.en         ? d.pecularities.en : null

                    item.links              =   !d?.web_references?.[0]     ? [] : d.web_references.map((v) => {
                        return {
                            link: v.link,
                            semantics: v.semantics,
                            comment_de: v.comment.de,
                            comment_en: v.comment.en,
                        }
                    })

                    item.groups             =   d?.dbi?.groups?.[0] ? d.dbi.groups : []

                    item.persons            =   !d?.persons?.[0] ? [] : d.persons.map((v) => {
                        return {
                            id: v.id,
                            function: v.function.id
                        }
                    });

                    ['citations', 'literature'].forEach((key) => {
                        item [key]      =   !d?.[key]?.[0] ? [] : d[key].map((v) => {
                            return {
                                id: v.id,
                                page: v.quote.page,
                                number: v.quote.number,
                                plate: v.quote.plate,
                                picture: v.quote.picture,
                                annotation: v.quote.annotation,
                                comment_de: v.quote.comment.de,
                                comment_en: v.quote.comment.en,
                                this: key == 'citations' ? 1 : 0
                            }
                        });
                    });

                    ['obverse', 'reverse'].forEach (
                        (key) => {
                            const s = key.slice(0, 1)

                            item[s + '_design'] = d?.[key]?.design?.id ? d[key].design.id : null
                            item[s + '_legend'] = d?.[key]?.legend?.id ? d[key].legend.id : null
                            //item [s+'_legend_direction']   =   !d ? null : (d[key] ? (d[key].legend.direction ? d[key].legend.direction.id  : null) : null);

                            item[s + '_monograms'] = !d?.[key]?.monograms ? [] : d[key].monograms.map((v) => {
                                return {
                                    id: v.id,
                                    position: v.position.id,
                                    image: v.link,
                                    side: key == 'obverse' ? 0 : 1
                                }
                            })

                            item[s + '_symbols'] = !d?.[key]?.symbols ? [] : d[key].symbols.map((v) => {
                                return {
                                    id: v.id,
                                    position:
                                    v.position.id,
                                    side: key == 'obverse' ? 0 : 1
                                }
                            })

                            if(entity === 'coins') {
                                item[s + '_die'] =   d?.[key]?.die?.id ? d[key].die.id : null

                                item[s + '_controlmarks'] = !d?.[key]?.controlmarks ? [] : d[key].controlmarks.map ((v) => {
                                    return {
                                        id: v.id,
                                        name: v.name,
                                        image: v.link,
                                        count: v.count,
                                        side: key == 'obverse' ? 0 : 1
                                    }
                                })

                                item[s + '_countermark_en'] = d?.[key]?.countermark?.text?.en ? d[key].countermark.text.en : null
                                item[s + '_countermark_de'] = d?.[key]?.countermark?.text?.de ? d[key].countermark.text.de : null

                                item[s + '_undertype_en'] = d?.[key]?.undertype?.text?.en ? d[key].undertype.text.en : null
                                item[s + '_undertype_de'] = d?.[key]?.undertype?.text?.de ? d[key].undertype.text.de : null
                            }
                        }
                    )

                    item.public = !d?.dbi?.public ? 0 : d.dbi.public
                }


                // Coin specific --------------------------------------------------------------------
                if(entity === 'coins') {

                    item.types              =   !d?.types ? [] : d.types.map (v => {
                        return {
                            type: v.id
                        }
                    })
                    item.inherited          =   !d?.dbi?.inherited?.id_type ? inheritance_object() : d.dbi.inherited

                    item.provenience        =   !d?.owner?.provenience                  ? null : d.owner.provenience
                    item.owner_original     =   !d?.owner?.original?.id                 ? null : d.owner.original.id
                    item.owner_unsure       =   !d?.owner?.original?.is_unsure          ? 0 : 1
                    item.collection         =   !d?.owner?.original?.collection_nr      ? null : d.owner.original.collection_nr
                    item.owner_reproduction =   !d?.owner?.reproduction?.id             ? null : d.owner.reproduction.id
                    item.plastercast        =   !d?.owner?.reproduction?.collection_nr  ? null : d.owner.reproduction.collection_nr

                    item.diameter_min       =   !d?.diameter?.value_min ? null : d.diameter.value_min.toFixed(2)
                    item.diameter_max       =   !d?.diameter?.value_max ? null : d.diameter.value_max.toFixed(2)
                    item.diameter_ignore    =   !d?.diameter?.ignore    ? 0 : 1

                    item.weight             =   !d?.weight?.value       ? null : d.weight.value.toFixed(2)
                    item.weight_ignore      =   !d?.weight?.ignore      ? 0 : 1

                    item.axis               =   !d?.axis                ? null : d.axis
                    item.centerhole         =   !d?.centerhole?.value   ? 0 : d.centerhole.value

                    item.images             =   !d?.dbi?.images ? [] : d.dbi.images

                    /*item.literature_type    =   !d ? []   : (d.type_literature  ?   d.type_literature.map (v => { return {
                        id: v.id,
                        //string: 'Type ' + v.id_type + ':&ensp;' + v.title + ', ' + v.quote.text.de +'&emsp;( <a href="' + v.link + '" target="_blank">' + v.id + '</a> )',
                        comment_de: v.quote.comment.de,
                        comment_en: v.quote.comment.en
                    }}) : []);*/

                    item.findspot           =   d?.findspot?.id ? d.findspot.id : null
                    item.hoard              =   d?.hoard?.id ? d.hoard.id : null
                    item.forgery            =   d?.is_forgery ? 1 : 0
                }


                // Type specific --------------------------------------------------------------------
                else if(entity === 'types') {

                    item.coins          =   !d?.coins ? [] : d.coins.map ((v) => {
                        return {
                            coin: v.id
                        }
                    })
                    //item.inherited      =   {}
                    item.name           =   !d?.dbi?.name ? null : d.dbi.name

                    item.image          =   !d?.images?.[0]?.id     ? null : d.images[0].id
                    item.images         =   !d?.images              ? [] : d.images

                    item.variations     =   !d?.variations ? [] : d.variations.map((v) => {
                        return {
                            de: v.text.de,
                            en: v.text.en,
                            comment: v.comment
                        }
                    })

                    item.findspots      =   !d?.dbi?.findspots  ? [] : d.dbi.findspots
                    item.hoards         =   !d?.dbi?.hoards     ? [] : d.dbi.hoards
                }

                return item
            },

            copy_item_data: (item, copy, key) => {

                // Detect Copy Mode
                const mode = (item.types != undefined ? 'c' : 't') + (copy.types != undefined ? 'c' : 't')

                // Write Inheritance
                if(mode === 'ct' && inheritance_keys.includes(key)) { item.inherited[key] = 1 }

                // Copy Value
                if (key === 'date') { ['date_start', 'date_end', 'date_text_de', 'date_text_en'].forEach((k) => { item[k] = copy[k] })}
                else if (key === 'comment_public') { ['comment_public_de', 'comment_public_en'].forEach((k) => { item[k] = copy[k] })}
                else if (key === 'pecularities') { ['pecularities_de', 'pecularities_en'].forEach((k) => { item[k] = copy[k] })}
                else { item[key] = copy[key] }

                return item
            },

            show_item_data: (language, entity, item_data, key, section) => {

                const d     = item_data

                // Coin and Type --------------------------------------------------------------------
                if(['coins', 'types'].includes(entity)) {

                    // Production
                    if (key === 'mint')                     { return to_string.mint(d?.mint, language) }
                    else if (key === 'issuer')              { return to_string.individual(d?.issuer) }
                    else if (key === 'authority')           { return to_string.basic(d?.authority?.kind, language, key) }
                    else if (key === 'authority_person')    { return to_string.individual(d?.authority?.person) }
                    else if (key === 'authority_group')     { return to_string.individual(d?.authority?.group) }
                    else if (key === 'date')                { return to_string.date(d?.date, d?.dbi?.date_start, d?.dbi?.date_end, language) }
                    else if (key === 'period')  	        { return to_string.basic(d?.date?.period, language, key) }

                    // Basics
                    else if (['material', 'denomination', 'standard'].includes(key)) { return to_string.basic(d?.[key], language, key) }
                    else if (key === 'weight')              { return to_string.weight(d?.weight, entity, language) }
                    else if (key === 'diameter')            { return to_string.diameter(d?.diameter, entity, language) }
                    else if (key === 'axis')                { return to_string.axis(entity === 'coins' ? d?.axis : d?.axes) }
                    else if (key === 'centerhole')          { return to_string.centerhole(d?.centerhole) }

                    // Depiction
                    else if (['design', 'legend'].includes(key))        { return to_string.design_legend(key, d?.[section]?.[key], language) }
                    else if (['monograms', 'symbols'].includes(key))    { return !d?.[section]?.[key] ? '--' : d[section][key].map((data) => { return to_string.monogram_symbol(key, data, language) }).join('\n') }
                    else if (key === 'controlmarks')                    { return !d?.[section]?.[key] ? '--' : d[section][key].map((data) => { return to_string.controlmark(data, language) }).join('\n') }
                    else if (['countermark', 'undertype'].includes(key)){ return to_string.countermark_undertype(key, d?.[section], language) }
                    else if (key === 'pecularities')        { return to_string.simple_text(d?.pecularities?.[language]) }

                    // Owners
                    else if (['owner_original', 'owner_reproduction'].includes(key)) { return to_string.owner(d?.owner?.[key.split('_').pop()], language) }
                    else if (key === 'collection_id')       { return to_string.simple_text(d?.owner?.original?.collection_nr) }
                    else if (key === 'plastercast_id')      { return to_string.simple_text(d?.owner?.reproduction?.collection_nr) }
                    else if (key === 'provenience')         { return to_string.simple_text(d?.owner?.provenience) }

                    // Individuals
                    else if (key === 'persons')             { return !d?.persons ? '--' : d.persons.map((data) => { return to_string.individual(data, language) }).join('\n') }

                    // References
                    else if (['citations', 'literatur'].includes(key)) { return !d?.[key] ? '--' : d[key].map((data) => { return to_string.reference(data, language) }).join('\n') }
                    else if (key === 'web_references')      { return !d?.[key] ? '--' : d[key].map((data) => { return to_string.weblink(data, language) }).join('\n')}
                    else if (key === 'objectgroups')        { return !d?.dbi?.groups ? '--' : d.dbi.groups.map((data) => { return to_string.objectgroup(data, language) }).join('\n')}

                    // Hoard and Findsport
                    else if (['hoards', 'findspots'].includes(key)) { return !d?.[key] ? '--' : d[key].map((data) => { return to_string.hoard_findspot(data, language, key) }).join('\n') }
                    else if (['hoard', 'findspot'].includes(key)) { return to_string.hoard_findspot(d?.[key], language, key) }

                    // About
                    else if (key === 'comment_public')      { return to_string.simple_text(d?.comment?.[language]) }
                    else if (key === 'comment_public_de')   { return to_string.simple_text(d?.comment?.de) }
                    else if (key === 'comment_public_en')   { return to_string.simple_text(d?.comment?.en) }
                    else if (key === 'comment_private')     { return to_string.simple_text(d?.dbi?.comment) }

                    else if (key === 'type_comment_public')      { return to_string.simple_text(d?.type_comment?.[language]) }
                    else if (key === 'type_comment_public_de')   { return to_string.simple_text(d?.type_comment?.de) }
                    else if (key === 'type_comment_public_en')   { return to_string.simple_text(d?.type_comment?.en) }
                    else if (key === 'type_comment_private')     { return to_string.simple_text(d?.dbi?.type_comment) }

                    else if (key === 'name_private')        { return to_string.simple_text(d?.dbi?.name) }
                    else if (key === 'description_private') { return to_string.simple_text(d?.dbi?.description) }

                    else if (key === 'source') { return d?.source?.link ? make_element.resource_link(d.source.link, true) : '--'}

                    // System History
                    else if (key === 'system') {
                        let color = 'grey'

                        if (d?.dbi?.public === 1) {
                            color = 'green'
                        }
                        else if (d?.dbi?.public === 2) {
                            color = 'blue_sec'
                        }
                        else if (d?.dbi?.public === 3) {
                            color = 'red'
                        }

                        const created = format_date(language, d?.created_at, true) + '&ensp;(&nbsp;' + (d?.dbi?.creator_name ? d.dbi.creator_name : '???') + '&nbsp;) '
                        const edited = format_date(language, d?.updated_at, true) + '&ensp;(&nbsp;' + (d?.dbi?.editor_name ? d.dbi.editor_name : '???') + '&nbsp;) '
                        const state = '<span class="' + color + '--text"> &#11044;</span>'

                        return state + '&emsp;' + created + ',&ensp;' + edited
                    }

                    // Card Header & Footer
                    else if (key === 'card_header') {
                        const header = []

                        if (d?.diameter?.value_max) {
                            let diameter = /*'&bigodot;&nbsp;' +*/ format_decimal(language, d.diameter.value_max) + '&nbsp;mm'
                            diameter += (d.diameter?.count ? (' (' + d.diameter.count + ')') : '')
                            header.push(diameter)
                        }
                        if (d?.weight?.value) {
                            let weight = /*'&#9878;&nbsp;' +*/ format_decimal(language, d.weight.value) + '&nbsp;g'
                            weight += (d.weight?.count ? (' (' + d.weight.count + ')') : '')
                            header.push(weight)
                        }

                        return header[0] ? header.join('&ensp;') : '--'
                    }
                    else if (key === 'card_footer') {
                        let footer = []

                        //if (d?.is_forgery) { footer.push('<span class="red--text">' + (language === 'de' ? 'Fälschung' : 'Forgery') + '</span>') }
                        if (d?.date?.text?.[language] || d?.date?.text?.de) { footer.push(d?.date?.text?.[language] ? d.date.text[language] : d.date.text.de) }
                        if (d?.material?.text?.[language]) { footer.push(d.material.text[language]) }
                        if (d?.denomination?.text?.[language]) { footer.push(d.denomination.text[language].split('(')[0].trim()) }
                        if (d?.owner?.city) { footer.push(d.owner.city.split('(')[0].trim()) }

                        footer = footer.join('; ')
                        return footer.length > 1 ? footer : '--'
                    }
                    // Forgers
                    else if (key === 'forgery') {
                        return d?.is_forgery  ? ('<span class="red--text">' + (language === 'de' ? 'Fälschung' : 'Forgery') + '!</span>') : ''
                    }
                }
                else {
                    return 'invalid entity'
                }
            },

            // Formatters
            format: {
                resource_link:  (link, showLink)            => { return make_element.resource_link(link, showLink) },
                editor_link:    (entity, id)                => { return make_element.editor_link(entity, id) },
                image_link:     (link, size, raw)           => { return format_image_path(link, size, raw) },
                digilib_link:   (link, size)                => { return link ? format_digilib_link(link, size)[size ? 'scaler' : 'viewer'] : '' },
                date:           (language, date, minutes)   => { return format_date(language, date, minutes) },
                number:         (language, number)          => { return format_number(language, number) },
                year:           (language, year)            => { return format_year(language, year) },
                file_format:    (path, UpperCase)           => { return file_format(path, UpperCase) },
                img_placeholder:(link)                      => { return format_image_placeholder(link) },
                counter:        (array)                     => { return format_counter(array) },
                inherited:      ()                          => { return inheritance_object() },
                image_tile:     (link, size)                => { return make_element.image_tile(link, size) },
                stringify_data: to_string,
                geonames_link:  (link)                      => { return link ? (link + make_element.resource_link(urls.geonames + link)) : '--' },
                nomisma_link:   (link)                      => { return link ? (link + make_element.resource_link(urls.nomisma + link)) : '--' },
                zotero_link:    (link)                      => { return link ? (link + make_element.resource_link(urls.zotero + link)) : '--' },
                cn_public_link: (item, mask)                => {
                    if (item?.id && (item?.public === 1 || item?.dbi?.public === 1)) {
                        return '<a href="' + urls.cn + (item.kind?.toLowerCase() === 'coin' ? 'coins?id=' : 'types/') + item.id + '" target="_blank">' +
                        (mask ? mask : '') +
                        '&nbsp;&ast;</a>'
                    }
                    else {
                        return ''
                    }
                },
                cn_coin_type_relation: (entity, item)               => {
                    if (entity === 'coins') {
                        const length = item.types?.length
                        if (length) {
                            const inherited = item.types?.find(type => type.inherited === 1)
                            return  {
                                id: inherited?.id ? inherited.id : item.types[0].id,
                                inherited: inherited?.id ? 1 : 0
                            }
                        }
                        else {
                            return null
                        }
                    }
                    else if (entity === 'types') {
                        const length = item.coins?.length
                        return length ? length : 0
                    }
                },
                creation: (language, d)                  => {
                    const created = format_date(language, d?.created_at, true) + '&ensp;(' + (d?.creator ? d.creator : (d?.id_creator ? d.id_creator : '???')) + ')'
                    const edited = format_date(language, d?.updated_at, true) + '&ensp;(' + (d?.editor ? d.editor : (d?.id_editor ? d.id_editor : '???')) + ')'

                    return created + ',&ensp;' + edited
                },
                cn_entity: (entity, id) => {
                    entity = (entity.slice(-1) === 's' ? entity.slice(0, -1) : entity)
                    return 'cn ' + entity + (id ? (' ' + id) : '')
                },
                email_link:     (link, showLink)            => { return make_element.email_link(link, showLink) },
                query: (query) => {
                    const printed = []

                    if (query) {
                        Object.keys(query).forEach((key) => {
                            let value = query[key]
                            if (key !== 'i' && value !== undefined && value !== null && value !== []) {
                                if (typeof value === 'object') value = value.join(', ')
                                printed.push(key + ': ' + value)
                            }
                        })
                    }

                    return printed.join('; ')
                }
            },

            // Constants
            constant: {
                inheritance_keys: inheritance_keys,
                baseURL: baseURL,
                digilib: digilib,
                image_formats: image_formats,
                placeholder_directory: placeholder_directory,
                placeholder_fallback: placeholder_fallback,
                url: urls
            },

            // Rules
            rules: {
                link: (v) => (v ? (v.slice(0, 7) === 'http://' || v.slice(0, 8) === 'https://') : true) || 'A valid links starts with \'http\' or \'https\'.',
                required: (v) => !!v || 'Input required.',
                numeric: (v) => {
                    const pattern = /^(0|[1-9][0-9]*)$/
                    return pattern.test(v) || 'Must be numeric.'
                },
                id: (v) => {
                    const pattern = /^(null|[1-9][0-9]*)$/
                    return (v !== null ? pattern.test(v) : true) || 'ID must be numeric.'
                },
                numeric_nz: (v) => (v === null ? true : v > 0) || 'Must be numeric and not zero (use dots for decimals).',
                date: (v) => {
                    const pattern = /^-?([1-9][0-9]*)$/
                    return v === null ? true : pattern.test(v) || 'Must be numeric and not zero (there is no Year 0).'
                },
                axis: (v) => (v ? (v> 0 && v < 13) : true) || 'Axis mus be between 1 and 12.'
            }
        }
    }
}

// -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------


// General Constants
const inheritance_keys = [
    'id_type', 'mint', /*'issuer',*/ 'authority', 'authority_person', 'authority_group', 'date', 'period',
    'material', 'denomination', 'standard',
    'o_design', 'o_legend', 'o_symbols', 'o_monograms',
    'r_design', 'r_legend', 'r_symbols', 'r_monograms',
    'persons',
    //'comment_private', 'comment_public'
]
const inheritance_object = () => {
    const item = {}
    inheritance_keys.forEach((key) => {
        item[key] = null
    })
    return item
}

const image_formats = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'tif', 'tiff']
const placeholder_directory = 'https://data.corpus-nummorum.eu/placeholder/'
const placeholder_fallback = 'https://data.corpus-nummorum.eu/placeholder/placeholder/placeholder_not_found.png'

const tiff = ['tiff', 'tif']

const baseURL = 'https://data.corpus-nummorum.eu/'

const digilib = {
    viewer: 'https://digilib.bbaw.de/digitallibrary/digilib.html?fn=silo10/thrakien/',
    scaler: 'https://digilib.bbaw.de/digitallibrary/servlet/Scaler?fn=silo10/thrakien/'
}

const urls = {
    cn: 'https://www.corpus-nummorum.eu/',
    geonames: 'https://www.geonames.org/',
    nomisma: 'http://nomisma.org/id/',
    zotero: 'https://zotero.org/groups/163139/items/'
}


// Stringify object property
const to_string = {

    mint: (data, language) => {
        return !data?.id ? '--' :
        (//format_id(data.id) +
        make_element.editor_link('mints', data.id) +
        data.text[language] +
        (data.region?.id ? (', ' + data.region.text[language]) : '') +
        make_element.resource_link(data.link))
    },
    individual: (data, language) => { // works for persons, issuer, authority_person, authority_group
        return !data?.id ? '--' :
        (//format_id(data.id) +
        make_element.editor_link('persons', data.id) +
        data.name +
        //(person.alias ? (' | ' + person.alias) : '') +
        make_element.resource_link(data.link)) +
        (!data.function?.id ? '' : ('<br />( ' + data.function.id + ' )&ensp;' + data.function.text[language]))
    },
    date: (date, start, end, language) => {
        return '[&nbsp;' + (start ? start : '--') + '&nbsp;|&nbsp;' + (end ? end : '--') + '&nbsp;]&ensp;' +
        (!date?.text ? '--' : (date.text[language] ? date.text[language] : (date.text.de ? date.text.de : '--')))
    },

    basic: (data, language, key) => { // works for authority, period, material, denomination, standard
        return !data?.id ? '--' : (
            (['period', 'material', 'denomination', 'standard'].includes(key) ? make_element.editor_link(key + 's', data.id) : format_id(data.id)) +
            data.text[language] + make_element.resource_link(data.link)
        )
    },

    design_legend: (key, data, language) => {
        return !data?.id ? '--' :
        (//format_id(data.id) +
            make_element.editor_link(key + 's', data.id) +
            (key === 'design' ? data.text[language] : ('<span class="font-weight-thin">' + data.string + '</span>&ensp;' + make_element.image_tile(data?.direction?.link, 15)))
        )
    },
    monogram_symbol: (key, data, language) => {
        return !data?.id ? '--' : '<div class="d-flex component-wrap align-start pr-2">' +
            make_element.image_link(data.link, 40) +
            '<div>' +
            //format_id(data.id) +
            make_element.editor_link(key, data.id) +
            (key === 'monograms' ? data.combination?.replaceAll(' ', '') : data.text[language]) +
            (data.position?.id ? ('&ensp;(' + data.position.text[language].slice(0, 1).toLowerCase() + data.position.text[language].slice(1) + ')') : '') +
            '</div>' +
        '</div>'
    },
    controlmark: (data, language) => {
        return !data?.id ? '--' : '<div class="d-flex component-wrap align-start pr-2">' +
            make_element.image_link(data.link, 40) +
            '<div>' +
            format_id(data.id) +
            data.count + 'x ' +
            (data.name ? data.name : '--') +
            '</div>' +
        '</div>'
    },
    countermark_undertype (key, data, language) {
        return data?.[key]?.text?.[language === 'de' ? 'de' : 'en'] ? data[key].text[language === 'de' ? 'de' : 'en'] : '--'
    },
    text: (data, language) => {
        return !data?.text ? '--' : ( data.text[language] ? data.text[language] : ( data.text.en ? data.text.en : '--'))
    },
    simple_text: (string) => {
        return string ? string : '--'
    },
    weight: (data, entity, language) => {
        let add = ''
        if (entity === 'types' && data?.count) add =  ' (' + data.count + ')'
        else if (data?.ignore) add =  ' (<i>ignore</i>)'
        return !data?.value ? '--' : (format_decimal(language, data.value) + '&nbsp;g' + add)
    },
    diameter: (data, entity, language) => {
        if (!data?.value_min && !data?.value_max) return '--'
        else {
            let add = ''
            if (entity === 'types' && data?.count) add =  ' (' + data.count + ')'
            else if (data?.ignore) add =  ' (<i>ignore</i>)'

            if ( data.value_min && data.value_max && data.value_min != data.value_max ) {
                return format_decimal(language, data.value_min) + '&ndash;' + format_decimal(language, data.value_max) + '&nbsp;mm' + add
            }
            else if ( data.value_min && data.value_max && data.value_min === data.value_max ) {
                return format_decimal(language, data.value_max) + '&nbsp;mm' + add
            }
            else {
                return format_decimal(language, (data.value_max ? data.value_max : data.value_min)) + '&nbsp;mm' + add
            }
        }
    },
    axis: (data) => {
        if (!data) { return '--' }
        else if (Number.isInteger(data)) return '&#9719;&nbsp;' + data
        else { return '&#9719;&nbsp;' + (data.map((v) => (v.value + ' (' + v.count + ')')).join(',&ensp;')) }
    },
    centerhole: (data) => {
        return !data?.value ? '--' : (format_id(data.value) + (data.value === 1 ? 'O' : (data.value === 2 ? 'R' : 'O&nbsp;/&nbsp;R')))
    },
    owner: (data, language) => {
        if (!data.id) { return '--' }
        else {
            return make_element.editor_link('owners', data.id) + //format_id(data.id) +
            (data.name ? data.name : '--') +
            make_element.resource_link(data.link) +
            (data.is_unsure ? '&nbsp;(?)' : '') +
            (data.city ? (', ' + data.city) : '') +
            (data.country ? (', ' + data.country.toUpperCase()) : '')
        }

    },
    reference: (data, language) => {
        if (!data.id) { return '--' }
        else {
            language = language === 'de' ? 'de' : 'en'

            return '<div>' + make_element.zotero_link(data.id) +
            (data.title ? data.title : '--') +
            make_element.resource_link(data.link) +
            (data.quote?.text?.[language] ? (', ' + data.quote.text[language]) : '') + '</div>' +
            (data.quote?.comment?.[language] ? ('<div class="mt-1"><i>' + data.quote.comment[language] + '</i></div>') : '')
        }
    },
    weblink: (data, language) => {
        return '<div>' +
        make_element.resource_link(data.link, true) + (data.semantics ? (', ' + data.semantics) : '') + '</div>' +
        (data.comment?.[language] ? ('<div class="mt-1"><i>' + data.comment[language] + '</i></div>') : '')
    },
    objectgroup: (data, language) => {
        if (!data.id) { return '--' }
        else {
            language = language === 'de' ? 'de' : 'en'

            return '<div>' + make_element.editor_link('objectgroups', data.id) + //format_id(data.id) +
            (data.name ? data.name : '--') + '</div>' +
            (data.text?.[language] ? ('<div class="mt-1"><i>' + data.text[language] + '</i></div>') : '')
        }
    },
    hoard_findspot: (data, language, key) => {
        if (!data) { return '--' }
        else {
            return make_element.editor_link(key, data.id) +
            (data.name ? data.name : '--') +
            make_element.resource_link(data.link) +
            (data.country ? (', ' + data.country) : '')
        }
    },
    rgew: (material, language) => {

    },
}


// Handy HTML Generators for specific elements like a tags or images
const make_element = {
    resource_link: (link, showLink) => {
        return !link ? '' : ((showLink ? '' : '&nbsp;') + '<a href="' + link + '" target="_blank">' + (showLink ? link.split('?')[0] + ('&nbsp;') : '') + '&#10064;</a>')
    },
    image_link: (link, size) => {

        if (link) {
            const format = extract_file_format(link)
            const img_size = parseInt(size) - (format === 'svg' ? 4 : 0)

            return '<a href="' + format_image_path(link) + '" target="_blank" class="mr-2 mt-1">' +
                '<div class="white d-flex justify-center align-center" style="width: ' + size + 'px; height: ' + size + 'px;">' +
                    '<img src="' + format_image_path(link, img_size) + '" loading="lazy" style="width: ' + img_size + 'px; height: ' + img_size + 'px; object-fit: contain">' +
                '</div>' +
            '</a>'
        }
        else {
            return ''
        }
    },
    image_tile: (link, size) => {
        if (link) {
            const format = extract_file_format(link)
            const img_size = parseInt(size) - (format === 'svg' ? 4 : 0)

            return '<div class="white d-flex justify-center align-center" style="width: ' + size + 'px; height: ' + size + 'px;">' +
                    '<img src="' + format_image_path(link, img_size) + '" loading="lazy" style="width: ' + img_size + 'px; height: ' + img_size + 'px; object-fit: contain">' +
                '</div>'
        }
        else {
            return ''
        }
    },
    editor_link: (entity, id) => {
        if (entity && id) {
            return '<a href="https://data.corpus-nummorum.eu/editor#/' + entity.trim() + '/' + id + '" >' + format_id(id) + '</a>' // target="_blank"
        }
        else {
            return ''
        }
    },
    zotero_link: (id) => {
        if (id) {
            return '<a href="' + urls.zotero + id + '" target="_blank">' + format_id(id) + '</a>'
        }
        else {
            return ''
        }
    },
    email_link: (link, showLink) => {
        return !link ? '' : ('<a href= "mailto:' + link.trim() + '">' + (showLink ? (link + '&nbsp;') : '') + '&#9993;</a>')
    }
}

// Formatters and other helpers
const format_typeName = (id, mint) => {
    if (id) {
        return 'cn.' + (!mint ? 'unknown' : (mint.split('/').pop().toLowerCase())) + '.' + id
    } else {
        return 'Unknown Type'
    }
}

const format_coinName = (id, mint) => {
    if (id) {
        return (!mint ? 'unknown' : mint) + ' CN_' + id
    } else {
        return 'Unknown Coin'
    }
}

const format_id = (id) => { return !id ? '' : ('ID&nbsp;' + id + '&ensp;') }

const format_date = (language, date, giveminutes) => {
    language = language ? language : 'en'

    const year      = date ? date.slice(0,4)    : '----'
    const month     = date ? date.slice(5,7)    : '--'
    const day       = date ? date.slice(8,10)   : '--'
    const hour      = date && giveminutes ? date.slice(11,13)   : '--'
    const minutes   = date && giveminutes ? date.slice(14,16)   : '--'

    if (language === 'de') {
        return day + '.' + month + '.' + year + (giveminutes ? (', ' + hour + ':' + minutes) : '')
    }
    else {
        return month + '/' + day + '/' + year + (giveminutes ? (', ' + hour + ':' + minutes) : '')
    }
}

const format_year = (language, year) => {
    if (year) {
        language = language ? language : 'en'
        year = String(year)
        if (year.slice(0, 1) === '-') {
            return year.slice(1) + '&nbsp;' + (language === 'de' ? 'v. Chr.' : 'BC')
        }
        else {
            return year + '&nbsp;' + (language === 'de' ? 'n. Chr.' : 'AD')
        }
    }
    else {
        return '--'
    }
}

const format_number = (language, number) => {
    language = language ? language : 'en'

    if (parseInt(number)) {

        number = String(number).split('').reverse().join('')
        const delimiter = language === 'de' ? '.' : ','

        const splits = []

        for (let i = 0; i <= number.length; i = i + 3) {
            const to_push = number.substr(i, (i+3)).split('').reverse().join('')
            if(to_push) { splits.push(to_push)}
        }

        return splits.reverse().join(delimiter)
    }
    else {
        return number
    }
}

const format_decimal = (language, number) => {
    if (language === 'de' && number) { number = number.toString().replaceAll('.', ',')}
    return number
}

const format_counter = (array) => {
    return '&ensp;(&nbsp;' + (array?.[0] ? array.length : '-') + '&nbsp;)'
}

const format_image_path = (link, size, raw) => {
    if(link) {
        link = link.trim()

        // Link is external
        if(link.slice(0,4) === 'http' && link.slice(0, baseURL.length) != baseURL) {
            return link
        }

        // Link is internal
        else {
            const format = extract_file_format(link)

            // file is TIFF and shall be given as digilib link
            if (tiff.includes(format) && !raw) {

                return link ? format_digilib_link(link, size)[size ? 'scaler' : 'viewer'] : ''
            }
            // file is no TIFF or shall be given as raw
            else {

                if (link.slice(0, baseURL.length) === baseURL) {
                    return link
                }
                else {
                    return baseURL + (!link.includes('storage/') ? 'storage/' : '') + link
                }
            }
        }
    }
    else {
        return ''
    }
}

const format_image_placeholder = (link) => {
    if (!link) {
        return placeholder_directory + 'placeholder_not_found.png'
    }
    else {
        const extension = extract_file_format(link)
        return placeholder_directory + 'placeholder_' + (image_formats.includes(extension) ? extension : 'not_supported') + '.png'
    }
}

const format_digilib_link = (link, scale) => {

    if (link) {
        if (link.includes('storage/'))                      { link = link.split('storage/').pop() }
        else if (link.slice(0, baseURL.length) === baseURL) { link = link.slice(baseURL.length) }

        scale = scale ? (Number.isInteger(scale) ? scale : 500) : 500

        return {
            scaler: digilib.scaler + link.trim() + '&dw=' + scale + '&dh=' + scale,
            viewer: digilib.viewer + link.trim()
        }
    }
}

const extract_file_format = (path, UpperCase) => {
    path = path ? path.trim().split('.').pop() : null

    return !path ? null : (UpperCase ? path.toUpperCase() : path.toLowerCase())
}
