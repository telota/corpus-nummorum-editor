// Entity Lists Module

export default ({

    state: {
        /*data: {
            authorities: [],
            denominations: [],
            designs: [],
            designs_obverse: [],
            designs_reverse: [],
            dies: [],
            dies_obverse: [],
            dies_reverse: [],
            epochs: [],
            findspots: [],
            functions: [],
            hoards: [],
            legend_directions: [],
            legends: [],
            legends_obverse: [],
            legends_reverse: [],
            materials: [],
            mints: [],
            monograms: [],
            objectgroups: [],
            owners: [],
            persons: [],
            positions: [],
            references: [],
            regions: [],
            standards: [],
            symbols: [],
            tribes: [],
            controlmarks: [],
            users: []
        },*/

        cache: {},

        manual: {
            imports: [
                { value: null, text: 'none' },
                { value: 'TypeCopy', text: 'TypeCopy' },
                { value: 'Import', text: 'Münzkabinett 1' },
                { value: 'Import2', text: 'Münzkabinett 2' },
                { value: 'Import3', text: 'Münzkabinett3' },
                { value: 'Import4', text: 'Münzkabinett 4' },
                { value: 'Import5', text: 'Münzkabinett 5' },
                { value: 'Import6', text: 'Münzkabinett 6' },
                { value: 'Import7', text: 'Münzkabinett 7' },
                { value: 'IKMK Import 8', text: 'Münzkabinett 8' },
                { value: 'ImportParis', text: 'Paris 1' },
                { value: 'ImportParis2', text: 'Paris 2' },
                { value: 'RPC 2021-02', text: 'RPC 2021-02' }
            ]
        },

        dropdowns: {
            legends_languages: [
                { value: 'el', text: 'greek' },
                { value: 'la', text: 'latin' }
            ],
            sides: [
                { value: 0, text: 'obverse' },
                { value: 1, text: 'reverse' },
                { value: 2, text: 'obverse,&,reverse' }
            ],
            typeCoin: [
                { value: 0, text: 'coin' },
                { value: 1, text: 'type' },
                { value: 2, text: 'coin,&,type' }
            ],
            yesNo: [
                { value: 0, text: 'no'},
                { value: 1, text: 'yes'}
            ],
            link_semantics: [
                { value: 'skos:exactMatch',         text: 'same as, no deviations'},
                { value: 'skos:relatedMatch',       text: 'related, deviating'},
                { value: 'dcmi:source',             text: 'import source'},
                { value: 'dcmi:source+ownership',   text: 'ownership'}
            ]
        }
    },


    mutations:
    {
        SET_ENTITYLIST (state, input) {
            state.data [input.entity] = input.data;
        },
        SET_LIST (state, input) {
            state.cache[input.entity] = input.data;
        },
    }

});
