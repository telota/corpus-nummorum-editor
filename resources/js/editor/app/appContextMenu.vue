<template>
<div style="z-index: 500">

    <!-- ContextMenu -->
    <v-menu
        v-model="$root.contextMenu.show"
        :position-x="state.x"
        :position-y="state.y"
        absolute
        offset-y
        tile
        dis-transition="scale-transition"
        :transition="null"
    >
        <v-card
            tile
            width="200"
            @contextmenu="preventDefaultMenu"
        >
            <!-- Header -->
            <template v-if="label">
                <div class="pa-2 body-2 d-flex">
                    <div
                        v-if="parent"
                        class="text-truncate"
                        v-text="'/' + parent"
                    />
                    <div
                        class="font-weight-medium"
                        style="white-space: nowrap"
                        v-text="'/' + label"
                    />
                </div>

                <v-divider />
            </template>

            <!-- List -->
            <template v-if="options[0]">
                <v-hover
                    v-for="(item, i) in options"
                    :key="'option' + i"
                    :disabled="item.disabled"
                    v-slot="{ hover }"
                >
                    <div
                        class="pa-2 caption"
                        :class="hover ? 'grey lighten-3' : (item.disabled ? 'grey--text' : '')"
                        :style="'cursor:' + (hover ? 'pointer' : 'default')"
                        v-text="item.text"
                        @click="menuClick(item)"
                    />
                </v-hover>
            </template>
        </v-card>
    </v-menu>

    <!-- Menu Dialog -->
    <v-dialog
        v-model="dialog.show"
        persistent
        tile
        width="400"
    >
        <v-card tile>
            <!-- Sysbar -->
            <dialogbartop
                :icon="dialog.icon"
                :text="dialog.text"
                v-on:close="resetDialog()"
            ></dialogbartop>

            <div class="pa-5">

                <div v-if="['add', 'rename'].includes(dialog.action)">
                    Bitte {{ dialog.action === 'add' ? '' : 'neuen' }} Namen für
                    {{ dialog.action === 'add' ? 'neues' : '' }} Verzeichnis
                    {{ dialog.action === 'add' ? '' : ('"' + label + '"') }}
                    eingeben.
                </div>

                <v-text-field v-model="dialog.value" />
            </div>

            <div class="d-flex justify-center">
                <v-btn
                    icon
                    class="ma-2"
                    @click="resetDialog()"
                >
                    <v-icon v-text="'clear'" />
                </v-btn>
                <v-btn
                    icon
                    class="ma-2"
                    :disabled="!dialog.value"
                    @click="dispatchAction(dialog.action, dialog.path, dialog.value)"
                >
                    <v-icon v-text="'done'" />
                </v-btn>
            </div>
        </v-card>
    </v-dialog>


</div>
</template>

<script>

export default {
    data () {
        return {
            listItems: {
                directory: [
                    { action: 'create', text: 'Unterverzeichnis erstellen', icon: 'add' },
                    { action: 'rename', text: 'Umbenennen', icon: 'drive_file_rename_outline', disabled: false },
                    { action: 'move', text: 'Verschieben', icon: 'content_cut', disabled: false },
                    { action: 'copy', text: 'Kopieren', icon: 'content_copy', disabled: false },
                    { action: 'delete', text: 'Löschen', icon: 'delete', disabled: false }
                ],
            },

            dialog: {
                show: false,
                icon: null,
                text: null,
                action: null,
                path: null,
                value: null
            }
        }
    },

    computed: {
        state () {
            return this.$root.contextMenu
        },

        entity () {
            return this.state.entity
        },

        label () {
            return this.state.item?.label ?? null
        },

        options () {
            const items = this.listItems?.[this.entity]
            return !items?.[0] ? [] : items.map((item) => {
                if (item.disabled !== undefined) item.disabled = !this.state.item?.depth ? true : false
                return item
            })
        },

        path () {
            return this.state.item?.path ?? null
        },

        parent () {
            const split = this.path?.split('/')

            if (!split?.[1]) return null

            split.pop()
            return split.join('/')
        }
    },

    methods: {
        preventDefaultMenu (element) {
            element.preventDefault()
        },

        menuClick (item) {
            const action = item?.action
            if (action === 'delete') {
                this.dispatchAction('delete', this.path)
            }
            else {
                this.dialog = {
                    show: true,
                    icon: item.icon,
                    text: item.text,
                    action: item.action,
                    path: this.path,
                    value: null
                }
            }
        },

        async dispatchAction (action, path, value) {
            const url = this.$root.constructAxiosURL('storage-api/action/' + action)
            //console.log (url, path, value)

            let confirm = true
            if (action === 'delete') confirm = confirm('Are you sure you want to delete "' + this.label + '"?')

            if (confirm) {
                if (action === 'create') {
                    const item = {
                        name: value,
                        directory: path
                    }
                    await axios.post(url, Object.assign ({}, item))
                        .then(async (response) => {
                            const data = response?.data
                            if (data.success && data.path) {
                                await this.$store.dispatch('fetchDirectories')
                                this.$router.push(data.path)
                            }
                        })
                        .catch((error) => {
                            this.$root.axiosErrorHandler (error)
                        })
                }
            }

            this.resetDialog()
        },

        resetDialog () {
            this.dialog = {
                show: false,
                icon: null,
                text: null,
                action: null,
                path: null,
                value: null
            }
        }
    }
}
</script>
