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
        editor: {
            loading: false,
            width: 400,
            widthCached: 500,
            item: {}
        },
        galery: {
            zoom: 1
        }
    },

    mutations: {
        setDirectoryItems (state, input)        { state.directory.items = input?.[0] ? input : [] },

        setDirectoryExpanded (state, input)     { state.directory.expanded = input?.[0] ? input : [] },

        setDirectoryExpandedAll (state, input) { state.directory.expandedAll = input },

        setFilesItems (state, input)            { state.files.items = input?.[0] ? input : [] },
        setEditorItem (state, input)            { state.editor.item = input?.id ? input : {} },

        setLoading (state, input)               { state[input.key].loading = input.value },

        setWidth (state, input)                 { state[input.key].width = input.value },
        setWidthCached (state, input)           { state[input.key].widthCached = input.value },

        setGaleryZoom (state, input) {
            const newZoom = state.galery.zoom + input
            if (newZoom >= 0 && newZoom < 3) state.galery.zoom = newZoom
            //const newZoom = state.galery.zoom + input
            //if (newZoom >= 5 && newZoom < 100) state.galery.zoom = newZoom
        }
    },

    actions: {
        async fetchDirectories ({ commit }, payload) {
            console.log('Fetching Directories')
            commit('setLoading', { key: 'directory', value: true })
            commit('setDirectoryItems', [])

            const path = this.state.baseURL + '/storage-api/index'
            const response = await axios.get(path).catch((error) => console.error('DIRECTORY FETCH', error))

            const data = response?.data?.directories

            if (data?.[0]) commit('setDirectoryItems', data)

            commit('setLoading', { key: 'directory', value: false })
        },

        async fetchFiles ({ commit }, payload) {
            console.log('Fetching Files in ' + payload.directory)
            commit('setLoading', { key: 'files', value: true })
            commit('setFilesItems', [])

            const path = this.state.baseURL + '/storage-api/index/' + payload.directory
            const response = await axios.get(path).catch((error) => console.error('FILE FETCH', error))

            const data = response?.data?.files

            if (data?.[0]) commit('setFilesItems', data) //.filter((item) => item?.slice(-5) !== '.META').map((item) => item.slice(8)))

            commit('setLoading', { key: 'files', value: false })
        },

        async fetchMeta ({ commit }, payload) {
            console.log('Fetching Meta for ' + payload.path)
            commit('setLoading', { key: 'editor', value: true })
            commit('setEditorItem', {})

            const path = this.state.baseURL + '/storage-api/meta/' + payload.path + '.META'
            const response = await axios.get(path).catch((error) => console.error('FILE FETCH', error))

            const data = response?.data

            if (data?.id) commit('setEditorItem', data)

            commit('setLoading', { key: 'editor', value: false })
        },

        async appendMeta ({ commit }, payload) {
            console.log('Sending Metadata for ' + payload.path)
            commit('setLoading', { key: 'editor', value: true })

            const path = this.state.baseURL + '/storage-api/action/append-meta'

            await axios.post(path, Object.assign ({}, payload))
                .then(async (response) => {
                    const data = response?.data
                    if (data.success && data.meta) {
                        commit('setEditorItem', {})
                        commit('setEditorItem', data.meta)
                    }
                    else console.error(response)
                })
                .catch((error) => {
                    console.error(error)
                })

            commit('setLoading', { key: 'editor', value: false })
        },

        toggleDirectoryExpansion ({ commit }, payload) {
            const path = payload.path
            let expand = this.state.storage.directory.expanded
            const index = expand.indexOf(path)

            if (index < 0) expand.push(path)
            else if (expand.length > 1) expand.splice(index, 1)
            else expand = []

            commit('setDirectoryExpanded', expand)
        },

        toggleSidebar ({ commit }, payload) {
            const key = payload.key
            const self = this

            if (this.state.browser[key].width !== 0) {
                commit('setWidthCached', { key, value: this.state.browser[key].width })
                commit('setWidth', { key, value: 0 })
            }
            else commit('setWidth', { key, value: this.state.browser[key].widthCached })

            /*if (self.state.browser[key].width === 0) {
                for (let i = 1; i < 11; i++) {
                    setTimeout(() => {
                        commit('setWidth', { key, value: Math.ceil(self.state.browser[key].widthCached / 10 * (i)) })
                    }, 1)
                }
            }
            else {
                commit('setWidthCached', { key, value: self.state.browser[key].width })
                for (let i = 1; i < 11; i++) {
                    setTimeout(() => {
                        commit('setWidth', { key, value: Math.ceil(self.state.browser[key].widthCached / 10 * (10 - i)) })
                    }, 1)
                }
            }*/
        }
    }

});
