<template>
<div :class="noGrow ? '' : 'flex-grow-1'">
    <div :class="classes" :style="styles">

        <div v-if="icon" :style="'margin-left: -' + halfHeight">
            <v-icon
                class="ml-3 mr-2"
                v-text="icon"
            />
        </div>

        <slot name="input">
            <input
                v-model="Value"
                :style="inputStyles"
                class="text-truncate flex-grow-1"
            />
        </slot>

        <div v-if="clearable" :style="'margin-right: -' + halfHeight">
            <v-icon
                class="ml-2 mr-3"
                v-text="'clear'"
                @click="$emit('input', null)"
            />
        </div>
    </div>
</div>
</template>

<script>
export default {
    data () {
        return {
        }
    },

    props: {
        value:      { type: [String, Number], default: null },
        height:     { type: [String, Number], default: 38 },
        width:      { type: [String, Number], default: '100%' },
        icon:       { type: String, default: null },
        clearable:  { type: Boolean, default: false },
        noGrow:     { type: Boolean, default: false }
    },

    computed: {
        Value: {
            get: function () { return this.value },
            set: function (value) { this.$emit('input', value) }
        },

        Height () {
            return typeof this.height === 'number' ? (parseInt(this.height) + 'px') : this.height
        },

        halfHeight () {
            return (typeof this.height === 'number' ? (Math.floor(parseInt(this.height) / 2)) : 0 ) + 'px'
        },

        Width () {
            return typeof this.width === 'number' ? (parseInt(this.width) + 'px') : this.width
        },

        dark () {
            return this.$vuetify.theme.dark
        },

        classes () {
            return [
                'input-template',
                'd-flex',
                'align-center',
                (this.dark ? 'white--' : 'black--') + 'text'
            ].join(' ')
        },

        styles () {
            return [
                'height:' + this.Height,
                'width:' + this.Width,
                'border-radius:' + this.halfHeight,
                'padding: 0 ' + this.halfHeight + ' 0 ' + this.halfHeight,
            ].join(';\n')
        },

        inputStyles () {
            return [
                'outline: none',
                'color:' + (this.dark ? '#fff' : '#000')
            ].join(';\n')
        }
    }
}
</script>

<style scoped>

.input-template {
    position: relative;
    background-color: rgba(0,0,0,0.2);
}

.input-template:hover {
    background-color: rgba(0,0,0,0.1);
}

.input-template:focus-within {
    background-color: rgba(0,0,0,0.1);
}
</style>
