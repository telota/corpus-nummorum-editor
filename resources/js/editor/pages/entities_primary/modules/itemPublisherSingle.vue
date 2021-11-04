<template>
<div>
    <div v-if="commandbar">
        <adv-btn
            v-if="status > 2 || level < 19"
            icon="publish"
            disabled
            :small="small"
            :medium="!small"
        />
        <adv-btn
            v-else-if="status === 1"
            icon="get_app"
            :tooltip="'unpublish ' + label"
            color-main="red darken-4"
            color-hover="red darken-1"
            color-text="white"
            :disabled="loading"
            :small="small"
            :medium="!small"
            @click="withdrawPublishing()"
        />
        <!-- Publish -->
        <adv-btn
            v-else
            icon="publish"
            :tooltip="'Publish ' + label"
            color-main="blue_prim"
            color-hover="blue_sec"
            color-text="white"
            :disabled="loading"
            :small="small"
            :medium="!small"
            @click="++run"
        />
    </div>

    <!-- No Publisher -->
    <div v-else class="d-flex">
        <div>
            <a
                v-if="status === 1"
                :href="'https://www.corpus-nummorum.eu/' + entity + '/' + item.id"
                target="_blank"
            >
                <adv-btn
                    icon="public"
                    :tooltip="'Show ' + label + ' on public website'"
                    color-hover="header_hover"
                />
            </a>
            <adv-btn
                v-else
                icon="public_off"
                color-hover="header_hover"
                disabled
            />
        </div>

        <v-divider vertical />

        <div>
            <!-- Item is not published -->
            <adv-btn
                v-if="status === 0"
                icon="publish"
                :tooltip="'Request publishing of ' + label"
                color-hover="header_hover"
                :disabled="loading"
                @click="requestPublishing()"
            />
            <!-- Nonpublisher wants to withdraw publishing -->
            <adv-btn
                v-else-if="status === 2 && level < 19"
                icon="get_app"
                :tooltip="'Withdraw publishing of ' + label"
                color-hover="header_hover"
                :disabled="loading"
                @click="withdrawPublishing()"
            />
            <!-- Publish -->
            <adv-btn
                v-else-if="status === 2 && level > 18"
                icon="publish"
                :tooltip="'Publish ' + label"
                color-hover="header_hover"
                color-text="blue_sec"
                :disabled="loading"
                @click="++run"
            />
            <!-- Unpublish -->
            <adv-btn
                v-else-if="status === 1"
                icon="get_app"
                :tooltip="'Unpublish ' + label"
                color-hover="header_hover"
                color-text="red"
                :disabled="loading || level < 19"
                @click="withdrawPublishing()"
            />
            <adv-btn
                v-else
                icon="publish"
                disabled
            />
        </div>

        <adv-btn
            icon="delete"
            color-hover="header_hover"
            :tooltip="'Delete ' + label"
            :disabled="deleteDisabled"
            @click="deleteItem()"
        />
    </div>

    <item-publisher-dialog
        :entity="entity"
        :items="items"
        :run="run"
        @loading="(emit) => { loading = emit; $emit('loading', emit) }"
        @refresh="$emit('refresh')"
    />
</div>
</template>

<script>
import itemPublisherDialog     from './itemPublisherDialog.vue'

export default {
    components: {
        itemPublisherDialog
    },

    data () {
        return {
            run: 0,
            mode: null,
            loading: false
        }
    },

    props: {
        entity:     { type: String, default: null },
        item:       { type: Object, default: () => { return { id: null } }},
        commandbar: { type: Boolean, default: false },
        small:      { type: Boolean, default: false }
    },

    computed: {
        label () {
            return ['cn', this.entity.slice(0, -1), this.item.id].join(' ')
        },

        items () {
            if (this.item?.id) return [this.item]
            return []
        },

        status () {
            if (!this.item.id || this.item.dbi?.public === undefined) return null
            return this.item.dbi?.public ?? 0
        },

        level () {
            return this.$root.user?.level ?? 0
        },

        deleteDisabled () {
            if (this.loading) return true
            if (!this.item.id) return true
            if (this.status == 1 || this.status == 3) return true
            if (this.level < 11) return true
            if (this.level < 12 && this.item.dbi?.creator != this.$root.user?.id) return true
            return false
        }
    },

    methods: {
        async deleteItem () {
            const confirmed = confirm('Are you sure you want to request deletion of ' + this.label + '?')

            if (confirmed) {
                this.$root.loading = this.loading = true
                this.$emit('loading', true)

                const response = await this.$root.DBI_INPUT_POST(this.entity, 'delete', this.item)

                if (response.success) {
                    this.$store.dispatch('showSnack', { color: 'success', message: response.success })
                    this.$emit('refresh')
                }
                else console.error('Deleting Item: response was not ok')

                this.$root.loading = this.loading = false
                this.$emit('loading', false)
            }
        },

        requestPublishing () {
            this.updateStatus(2)
        },

        withdrawPublishing () {
            let confirmed = true
            if (this.status === 1) confirmed = confirm('Are you sure you want to unpublish ' + this.label + '?')

            if (confirmed) this.updateStatus(0)
        },

        async updateStatus (value) {
            this.$root.loading = this.loading = true
            this.$emit('loading', true)

            const response  = await this.$root.DBI_INPUT_POST(this.entity, 'input', {
                id: this.item.id,
                updatePublicationState: value
            })

            if (response.success) {
                let message = ''
                if (value === 2) {
                    message = 'Publishing requested for ' + this.label
                    if (this.level < 19 && response.type && ![1,2].includes(response.status)) alert('Please be aware that the inheriting cn type ' + response.type + ' is not published or marked as ready to be published yet!')
                }
                else message = this.label + ' has been set to unpublished'

                this.$store.dispatch('showSnack', { color: 'success', message })
                this.$emit('refresh')
            }
            else console.error('Updating publication state: response was not ok')

            this.$root.loading = this.loading = false
            this.$emit('loading', false)
        }
    }
}
</script>
