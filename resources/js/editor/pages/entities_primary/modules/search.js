const constructRequest = (input) => {
    const raw = input === null || input === undefined || typeof input !== 'object' ? {} : { ...input }
    const request = {}

    for (let [key, value] of Object.entries(raw)) {
        if (
            value !== undefined &&
            value !== null &&
            !(typeof value === 'object' && !value[0])
        ) request[key] = value
    }
    if ([undefined, null, [0, 1, 2]].includes(request.state_public)) request.state_public = 'all'

    return request
}

const constructParams = (input) => {
    const d = input === null || input === undefined || typeof input !== 'object' ? {} : { ...input }

    // Public
    let state_public = 'all'
    if (d.state_public !== undefined && d.state_public !== [0, 1, 2] && d.state_public !== 'all') state_public = parseInt(d.state_public)
    else if (d.public !== undefined && d.public !== [0, 1, 2] && d.public !== 'all') state_public = parseInt(d.public)

    return {
        id:                     toInt(d.id),
        q:                      d.q ?? null,

        collection_nr:          d.collection_nr ?? null,
        comment_private:        d.comment_private ?? null,
        comment_public:         d.comment_public ?? null,
        date_end:               toYear(d.date_end),
        date_start:             toYear(d.date_start),
        description_private:    d.description_private ?? null,
        diameter_end:           toFloat(d.diameter_end),
        diameter_start:         toFloat(d.diameter_start),
        has_images:             toBool(d.has_images),
        id_authority:           toArrayInt(d.id_authority),
        id_authority_group:     toArrayInt(d.id_authority_group),
        id_authority_person:    toArrayInt(d.id_authority_person),
        id_creator:             toArrayInt(d.id_creator),
        id_denomination:        toArrayInt(d.id_denomination),
        id_design:              toArrayInt(d.id_design),
        id_die:                 toArrayInt(d.id_die),
        id_editor:              toArrayInt(d.id_editor),
        id_findspot:            toArrayInt(d.id_findspot),
        id_group:               toArrayInt(d.id_group),
        id_hoard:               toArrayInt(d.id_hoard),
        id_legend:              toArrayInt(d.id_legend),
        id_material:            toArrayInt(d.id_material),
        id_mint:                toArrayInt(d.id_mint),
        id_monogram:            toArrayInt(d.id_monogram),
        id_owner:               toArrayInt(d.id_owner),
        id_period:              toArrayInt(d.id_period),
        id_person:              toArrayInt(d.id_person),
        id_reference:           toArrayString(d.id_reference),
        id_region:              toArrayInt(d.id_region),
        id_standard:            toArrayInt(d.id_standard),
        id_symbol:              toArrayInt(d.id_symbol),
        id_type:                toInt(d.id_type),
        imported:               toBool(d.imported),

        plastercast_nr:         d.plastercast_nr ?? null,
        provenience:            d.provenience ?? null,

        source:                 d.source ?? null,
        state_comment_private:  d.state_comment_private ?? null,
        state_comment_public:   d.state_comment_public ?? null,
        state_inherited:        toBool(d.state_inherited),
        state_linked:           toBool(d.state_linked),
        state_source:           toBool(d.state_source),
        weight_end:             toFloat(d.weight_end),
        weight_start:           toFloat(d.weight_start),

        o_design:               toArrayInt(d.o_design),
        o_id_design:            toArrayInt(d.o_id_design),
        o_id_legend:            toArrayInt(d.o_id_legend),
        o_id_monogram:          toArrayInt(d.o_id_monogram),
        o_id_symbol:            toArrayInt(d.o_id_symbol),

        r_design:               toArrayInt(d.r_design),
        r_id_design:            toArrayInt(d.r_id_design),
        r_id_legend:            toArrayInt(d.r_id_legend),
        r_id_monogram:          toArrayInt(d.r_id_monogram),
        r_id_symbol:            toArrayInt(d.r_id_symbol),

        state_public,

        offset:                 d.offset ? toInt(d.offset) : 0,
        limit:                  d.limit ? toInt(d.limit) : 12,
        sort_by:                d.sort_by ?? 'id.DESC',
    }
}

const toArrayInt =       (val) => val ? (typeof val === 'object' ? val : [val]).map((s) => parseInt(s)) : []
const toArrayString =    (val) => val ? (typeof val === 'object' ? val : [val]) : []
const toInt =            (val) => val ? parseInt(val) : null
const toFloat =          (val) => val ? parseFloat(val) : null
const toYear =           (val) => val ? (parseInt(val) ?? null) : null
const toBool =        (val) => {
    if (val === undefined)      return null
    if (val === null)           return null
    if (parseInt(val) === 1)    return 1
    if (parseInt(val) === 0)    return 0
    return null
}


export default {
    toArrayInt,
    toArrayString,
    toInt,
    toFloat,
    toBool,
    toYear,
    constructRequest,
    constructParams
}
