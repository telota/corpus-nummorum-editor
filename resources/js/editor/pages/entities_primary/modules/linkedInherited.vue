<template>
    <div
        v-if="entity === 'types' || !select"
        v-html="$root.label('linked_' + (entity === 'coins' ? 'types' : 'coins')) + ':&nbsp;' + length"
    />
    <v-hover v-else>
        <template v-slot:default="{ hover }" >
            <div
                class="d-flex"
                :class="hover ? 'blue_sec--text' : ''"
                style="cursor: pointer"
                @click="$store.commit('setDetailsDialog', { entity: 'types', id: select.id })"
            >
                <div class="text-truncate" v-text="(select.inherited ? 'Inherited from' : 'Linked to') + ' cn'" />
                <div v-html="'&nbsp;' + entity.slice(0, -1) + '&nbsp;' + select.id" />
            </div>
        </template>
    </v-hover>
</template>


<script>

export default {
    props: {
        entity:     { type: String, required: true },
        item:       { type: Object, default: () => {} }
    },

    computed: {
        length () {
            const length = this.item[this.entity === 'coins' ? 'types' : 'coins']?.length
            return length ?? 0
        },

        select () {
            if (this.entity === 'coins' && this.length) {
                const inherited = this.item.types?.find(type => type.inherited === 1)
                return  {
                    id: inherited?.id ? inherited.id : this.item.types[0].id,
                    inherited: inherited?.id ? 1 : 0
                }
            }
            else return null
        }
    }
}

</script>
