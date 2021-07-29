<template>
<div class="whitespace-nowrap">
    <a
        v-if="Text.slice(0, 4) === 'http'"
        :href="Text"
        :target="Text.includes($root.baseURL) ? '' : '_blank'"
        v-html="Text"
    />
    <span
        v-else
        v-html="Text"
    />
    <v-hover v-slot="{ hover }">
        <span
            v-html="'&nbsp;&#x2398;'"
            :class="hover ? 'blue_sec--text' : ''"
            style="cursor: pointer"
            title="Copy to Clipboard"
            @click="$root.copyToClipboard(value)"
        />
    </v-hover>
</div>
</template>

<script>
export default {
    props: {
        value: { type: [Number, String], default: 'No value' },
        text: { type: [Number, String], default: null },
    },

    computed: {
        Text () { return this.text ?? this.value }
    }
}
</script>
