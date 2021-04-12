// Entity Attributes

export default ({

    state: 
    {
        // COINS -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
        coins:
        {
            id:                     {default: null,         text: 'ID',                 sortable: true,    },

            // Images
            images:                 {text: 'Images', nested: {
                o_img:                  {default: null,         text: 'Obverse Image' },
                o_img_photographer:     {default: null,         text: 'Ob. Photographer' },
                r_img:                  {default: null,         text: 'Reverse Image' },
                r_img_photographer:     {default: null,         text: 'Re. Photographer' },
                img_type:               {default: 'plastercast',text: 'Image Type' },
                img_bg:                 {default: null,         text: 'BG Color' },
                img_public:             {default: 1,            text: 'Image is public?' },
            }},

            // Persons
            persons:                {text: 'Persons', nested: {
                id_person:              {default: null,         text: 'Person ID' },
                person:                 {default: null,         text: 'Person' },
                id_function:            {default: null,         text: 'Function ID' },
                function:               {default: null,         text: 'Function' },
            }},

            // Types
            id_type:                {text: 'Types', nested: {
                id:                     {default: null,         text: 'ID',                 sortable: false,    },
            }},
            
            //Orign
            title:                  {default: null,         text: 'Title',              sortable: false,    clone: false },
            plastercast:            {default: null,         text: 'Plaster Cast Nr.',   sortable: false,    },
            collection:             {default: null,         text: 'Collection Nr.',     sortable: false,    },
            id_owner:               {default: null,         text: 'Owner ID',           sortable: false,    },
            provenience:            {default: null,         text: 'Provenience',        sortable: false,    },
            source:                 {default: null,         text: 'Source',             sortable: false,    },

            // Basics
            diameter_max:           {default: null,         text: 'Diameter Max',       sortable: false,    },
            diameter_min:           {default: null,         text: 'Diameter Min',       sortable: true,    },
            weight:                 {default: null,         text: 'Weight',             sortable: true,    },
            id_material:            {default: null,         text: 'Material ID',        sortable: false,    },
            material_nomisma_id:    {default: null,         text: 'Nomisma Material',   sortable: false,    },
            id_standard:            {default: null,         text: 'Standard ID',        sortable: false,    },
            standard:               {default: null,         text: 'Standard',           sortable: false,    },
            id_denomination:        {default: null,         text: 'Denomination ID',    sortable: false,    },
            denomination:           {default: null,         text: 'Denomination',       sortable: false,    },
            id_ruler:               {default: null,         text: 'Ruler ID',           sortable: false,    },
            id_tribe:               {default: null,         text: 'Tribe ID',           sortable: false,    },
            id_mint:                {default: null,         text: 'Mint ID',            sortable: true,    },
            authority:              {default: null,         text: 'Authority',          sortable: false,    },
            is_forgery:             {default: 0,            text: 'Is Forgery?',        sortable: false,    },

            // Obverse
            o_struck:               {default: null,         text: 'Obverse Struck',     sortable: false,    },
            o_design:               {default: null,         text: 'Obverse Design',     sortable: false,    },
            o_id_design:            {default: null,         text: 'Obverse Design ID',  sortable: false,    },
            o_legend:               {default: null,         text: 'Obverse Legend',     sortable: false,    },
            o_id_legend:            {default: null,         text: 'Obverse Legend ID',  sortable: false,    },
            o_countermark:          {default: null,         text: 'Obverse Countermark',sortable: false,    },

            // Reverse
            r_struck:               {default: null,         text: 'Reverse Struck',     sortable: false,    },
            r_design:               {default: null,         text: 'Reverse Design',     sortable: false,    },
            r_id_design:            {default: null,         text: 'Reverse Design ID',  sortable: false,    },
            r_legend:               {default: null,         text: 'Reverse Legend',     sortable: false,    },
            r_id_legend:            {default: null,         text: 'Reverse Legend ID',  sortable: false,    },
            r_countermark:          {default: null,         text: 'Reverse Countermark',sortable: false,    },

            // Production
            id_epoch:               {default: null,         text: 'Epoch ID',           sortable: false,    },
            epoch:                  {default: null,         text: 'Epoch Name',         sortable: false,    },
            year_start:             {default: null,         text: 'Year starting',      sortable: false,    },
            year_end:               {default: null,         text: 'Year ending',        sortable: false,    },
            year_string:            {default: null,         text: 'Year Text',          sortable: false,    },
            
            id_findspot:            {default: null,         text: 'Findspot ID',        sortable: false,    },
            id_hoard:               {default: null,         text: 'Hoard ID',           sortable: false,    },

            // Public
            public:                 {default: 0,            text: 'Public',             sortable: false,    },
        },
        
        
        // Types -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
        types:
        {
            id:                     {default: null,         text: 'ID',                 sortable: true,     },
        }


        // ----------------------------------------------------------------------------------------------------------------------------------------------------------------
    },


    /*actions: 
    {
        
    },


    mutations: 
    {

    }*/

});