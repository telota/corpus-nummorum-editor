export default ({

    state: {
        files: {
            loading: false,
            items: []
        },

        directory: {
            loading: false,
            width: 300,
            widthCached: 300,
            items: [],
            expanded: [],
            expandedAll: false
        },
        details: {
            loading: false,
            item: {}
        }
    },

    mutations: {
        setDirectoryItems (state, input)        { state.directory.items = input?.[0] ? input : [] },

        setDirectoryExpanded (state, input)     { state.directory.expanded = input?.[0] ? input : [] },

        setDirectoryExpandedAll (state, input)  { state.directory.expandedAll = input },

        setFilesItems (state, input)            { state.files.items = input?.[0] ? input : [] },
        setDetailsItem (state, input)           { state.details.item = input?.path ? input : {} },

        setLoading (state, input)               { state[input.key].loading = input.value }
    },

    actions: {
        async fetchDirectories ({ commit }, payload) {
            console.log('Fetching Directories')
            commit('setLoading', { key: 'directory', value: true })
            commit('setDirectoryItems', [])

            const path = this.state.baseURL + '/storage-api/index'
            const response = await axios.get(path).catch((error) => console.error((error?.response?.status ?? 'unknown') + ' DIRECTORY FETCH', path))

            const data = response?.data?.directories

            if (data?.[0]) commit('setDirectoryItems', data)

            commit('setLoading', { key: 'directory', value: false })
        },

        async fetchFiles ({ commit }, payload) {
            console.log('Fetching Files in ' + payload.directory)
            commit('setLoading', { key: 'files', value: true })
            commit('setFilesItems', [])

            const path = this.state.baseURL + '/storage-api/index/' + payload.directory
            const response = await axios.get(path).catch((error) => console.error((error?.response?.status ?? 'unknown') + ' FILE FETCH ERROR for ', path))

            const data = response?.data?.files

            if (data?.[0]) commit('setFilesItems', data) //.filter((item) => item?.slice(-5) !== '.META').map((item) => item.slice(8)))

            commit('setLoading', { key: 'files', value: false })
        },

        async fetchDetails ({ commit }, payload) {
            console.log('Fetching Details for ' + payload.path)
            commit('setLoading', { key: 'details', value: true })
            commit('setDetailsItem', {})

            const path = this.state.baseURL + '/storage-api/details/' + payload.path
            const response = await axios.get(path).catch((error) => console.error((error?.response?.status ?? 'unknown') + ' DETAILS FETCH ERROR for ', path))

            const data = response?.data

            if (data?.path) commit('setDetailsItem', data)

            commit('setLoading', { key: 'details', value: false })
        },
    }

});
