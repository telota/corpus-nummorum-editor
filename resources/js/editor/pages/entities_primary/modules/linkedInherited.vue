<template>
    <div 
        v-if="entity === 'types' || !select"
        v-html="$root.label('linked_' + (entity === 'coins' ? 'types' : 'coins')) + ':&nbsp;' + length"
    ></div>
    <v-hover v-else>
        <template v-slot:default="{ hover }" >
            <div
                :class="hover ? 'blue_sec--text' : ''"
                style="cursor: pointer;"
                v-text="(select.inherited ? 'Inherited from' : 'Linked to') + ' cn type ' + select.id"
                @click="$emit('details', { entity: 'types', id: select.id })"
            ></div>
        </template>
    </v-hover>
</template>


<script>

export default {
    data () {
        return {}        
    },

    props: {
        entity:         { type: String, required: true },
        item:           { type: Object }
    },

    computed: {
        // Localization
        l () { return this.$root.language },
        labels () { return this.$root.labels },

        length () {
            const length = this.item[this.entity === 'coins' ? 'types' : 'coins']?.length
            return length ? length : 0
        },
        
        select () {
            if (this.entity === 'coins' && this.length) {
                const inherited = this.item.types?.find(type => type.inherited === 1)
                return  { 
                    id: inherited?.id ? inherited.id : this.item.types[0].id,
                    inherited: inherited?.id ? 1 : 0
                }
            }
            else {
                return null
            }
        }
    },

    created () {},

    methods: {}
}

</script>