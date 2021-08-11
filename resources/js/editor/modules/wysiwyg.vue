<template>
<div>

    <div
        class="mt-5"
        style="border-radius: 5px;"
        :style="'background-color: ' + colors.background + '; border: 1px solid ' + colors[mouseOver ? 'outlineHover' : 'outline']"
        mouseover="mouseOver = true"
        mouseout="mouseOver = false"
    >
        <!-- Header -->
        <div class="d-flex justify-space-between align-start">
            <!-- Toolbar Nodes -->
            <div class="d-flex flex-wrap">
                <template v-if="!showCode">
                    <div
                        v-for="(t, ti) in toolbar"
                        :key="'t' + ti"
                        class="d-flex"
                    >
                        <v-tooltip
                            v-for="(b, bi) in t"
                            :key="'t' + ti + 'b' + bi"
                            bottom
                        >
                            <template v-slot:activator="{ on }">
                                <v-hover v-slot="{ hover }">
                                    <div
                                        class="pa-2"
                                        style="cursor: pointer"
                                        v-on="on"
                                        @click="b.action"
                                    >
                                        <v-icon
                                            v-text="b.icon"
                                            :class="hover ? 'blue_sec--text' : ''"
                                        />
                                    </div>
                                </v-hover>
                            </template>
                            <span v-html="b.tooltip" />
                        </v-tooltip>
                        <div :style="'width: 1px; background-color: ' + colors.outline" />
                    </div>
                </template>
            </div>
            <!-- Toggle View Mode -->
            <div v-if="!hideCode" class="d-flex">
                <div :style="'width: 1px; background-color: ' + colors.outline" />
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-hover v-slot="{ hover }">
                            <div
                                class="pa-2"
                                style="cursor: pointer"
                                v-on="on"
                                @click="toggleCode()"
                            >
                                <v-icon
                                    v-text="(showCode ? 'visibility_off' : 'visibility')"
                                    :class="hover ? 'blue_sec--text' : ''"
                                />
                            </div>
                        </v-hover>
                    </template>
                    <span v-html="(showCode ? 'Hide' : 'Show') + ' HTML Code'" />
                </v-tooltip>
            </div>
        </div>

        <!-- Content -->
        <div
            style="resize: vertical; display: inline-block; width: 100%;"
            :style="
                'overflow-y: ' + (showCode ? 'hidden' : 'scroll') + ';' +
                'height: ' + height + 'px;' +
                'border-top: 1px solid ' + colors.outline + ';' +
                'border-bottom: 1px solid ' + colors.outline + ';'
            "
        >
            <v-fade-transition>
                <textarea
                    v-if="showCode"
                    :id="inputID + 'c'"
                    v-model="content"
                    class="pa-2 pt-3 invert--text transparent"
                    style="width: 100%; height: 100%; outline: none; resize: none"
                    @input="emitContent()"
                />
                <div
                    v-else
                    :id="inputID"
                    v-html="content"
                    contenteditable
                    class="pa-2 pt-3 invert--text"
                    style="width: 100%; height: 100%; outline: none; outline: none;"
                    @input="emitContent()"
                />
            </v-fade-transition>
        </div>

        <!-- Footer -->
        <div class="pa-2 pt-1 d-flex justify-space-between caption">
            <!-- Label -->
            <div v-html="label" />
            <!-- Counter -->
            <div class="d-flex">
                <div v-html="count.words + '&nbsp;Words'" />
                <div class="ml-1 mr-1" v-html="'|'" />
                <div
                    :class="this.counter && count.signs >  this.counter ? 'red--text' : ''"
                    v-html="count.signs + (this.counter ? ('/' + this.counter) : '') + '&nbsp;Signs'"
                />
            </div>
        </div>

    </div>

    <!-- LinkDialog -->
    <small-dialog
        :show="linkDialog.active"
        :text="$root.label('link')"
        icon="link"
        v-on:close="resetLinkDialog()"
    >
        <v-text-field
            dense outlined filled clearable
            v-model="linkDialog.url"
            label="URL"
            prepend-icon="link"
            @click:prepend="$root.openInNewTab(linkDialog.url)"
            :rules="[$handlers.rules.link]"
        />
        <v-select
            dense outlined filled
            v-model="linkDialog.blank"
            :items="[
                { value: true, text: 'new window' },
                { value: false, text: 'same window' }
            ]"
            label="Target"
            style="max-width: 50%"
            prepend-icon="tab"
        />
        <v-text-field
            dense outlined filled clearable
            v-model="linkDialog.mask"
            label="Text"
            prepend-icon="short_text"
        />
        <div class="d-flex justify-center">
            <v-btn
                text
                v-text="'OK'"
                :disabled="!linkDialog.url"
                @click="addLink()"
            />
        </div>
    </small-dialog>

</div>
</template>



<script>

export default {

    data () {
        return {
            content:    this.value,
            loading:    false,
            mouseOver:  false,

            count: {
                words: 0,
                signs: 0
            },

            showCode: false,
            linkDialog: {},

            toolbar: [
                [
                    {
                        tooltip: 'Format as Paragraph',
                        icon: 'notes',
                        action: () => { this.addParagraph() }
                    },
                    {
                        tooltip: 'Add Line Break',
                        icon: 'keyboard_return',
                        action: () => { this.addBreak() }
                    },
                ],
                [
                    {
                        tooltip: 'Format bold',
                        icon: 'format_bold',
                        action: () => { this.applyFormat('b') }
                    },
                    {
                        tooltip: 'Format Italic',
                        icon: 'format_italic',
                        action: () => { this.applyFormat('i') }
                    },
                    {
                        tooltip: 'Format Strikethrough',
                        icon: 'format_strikethrough',
                        action: () => { this.applyFormat('s') }
                    },
                    /*{
                        tooltip: 'Format Underlined',
                        icon: 'format_underlined',
                        action: () => { this.applyFormat('u') }
                    },*/
                    {
                        tooltip: 'Format Code',
                        icon: 'code',
                        action: () => { this.applyFormat('code') }
                    }
                ],
                [
                    {
                        tooltip: 'Insert Link',
                        icon: 'link',
                        action: () => { this.addLink(true) }
                    }
                ],
                [
                    {
                        tooltip: 'Add bulleted list',
                        icon: 'format_list_bulleted',
                        action: () => { this.addList('u') }
                    },
                    {
                        tooltip: 'Add numbered List',
                        icon: 'format_list_numbered',
                        action: () => { this.addList('o') }
                    },
                ],
                [
                    {
                        tooltip: 'Clear Format',
                        icon: 'format_clear',
                        action: () => { this.clearFormat() }
                    }
                ]
            ],

            currentSelection: {
                element: null,
                selection: null,
                range: null
            }
        }
    },

    props: {
        value:      { type: String,     default: '' },
        height:     { type: Number,     default: 250 },
        outlined:   { type: Boolean,    default: false },
        filled:     { type: Boolean,    default: false },
        label:      { type: String,     default: 'HTML Editor' },
        counter:    { type: Number,     default: 0 },
        hideCode:   { type: Boolean,    default: false },
        unique:     { type: [Number, String], default: '' }
    },

    computed: {
        inputID () {
            const string = 'htmlEditor_input_' + this.unique + '_'
            let i = 1

            // Ensure ID is unique in DOM
            while (document.getElementById(string + i)) { ++i }
            return string + i
        },

        colors () {
            const dm = this.$vuetify.theme.dark
            return {
                background: this.filled ? (dm ? '#303030' : '#d4d4d4') : 'transparent',
                outline: dm ? '#626262' : '#838383',
                outlineHover: dm ? '#eee' : '#111'
            }

        }
    },

    watch: {
    },

    created () {
    },

    mounted() {
        this.resetLinkDialog()
        const element = this.refreshContentInfo()

        element.addEventListener('paste', (event) => {
            const pasted = (event.clipboardData || window.clipboardData).getData('text/html')
            let html = document.createElement('html');
            html.innerHTML = pasted
            html = html.getElementsByTagName('body')?.[0]?.innerHTML

            if (html) {
                html = html
                    .split('<!--StartFragment-->')
                    .pop()
                    .split('<!--EndFragment-->')
                    .shift()
                    .replaceAll(/ class="[^"]*"/g, '')
                    .replaceAll(/ style="[^"]*"/g, '')
                    .replaceAll(/<o:p>/g, '')
                    .replaceAll(/<\/o:p>/g, '')
                    .trim()

                this.content = html
                this.count.signs = this.content.length
                this.count.words = this.content.trim().split(/\s+/).length
                this.emitContent(true)
                event.preventDefault()
            }
        });
    },

    methods: {
        refreshContentInfo () {
            const doc = document.getElementById(this.inputID)
            this.count.signs = doc.innerHTML.length
            this.count.words = doc.textContent.trim().split(/\s+/).length

            return doc
        },

        emitContent (forced) {
            let content = null

            if (forced || this.showCode) content = this.content
            else {
                const doc = this.refreshContentInfo()
                content = doc.innerHTML
            }

            if (content) {
                const lines = []

                content
                    .trim()
                    .replaceAll('<br>', '<br/>')
                    .replaceAll('<br/>', '<br/>\n')
                    .replaceAll('</p>', '</p>\n')
                    .replaceAll('</li>', '</li>\n')
                    .replaceAll('<ol>', '\n<ol>\n')
                    .replaceAll('</ol>', '\n</ol>\n')
                    .replaceAll('<ul>', '\n<ul>\n')
                    .replaceAll('</ul>', '\n</ul>\n')
                    .split(/\r\n|\r|\n/)
                    .forEach((line) => {
                        line = line?.trim()
                        if (line) {
                            if (line.slice(-1) !== '>') { line += '<br/>' }
                            lines.push(line)
                        }
                        else if (lines.end) {
                            lines.push('')
                        }
                    })

                this.$emit('input', lines.join('\n'))
            }
            else {
                this.$emit('input', null)
            }
        },

        getSelection () {
            const parent = document.getElementById(this.inputID)
            const selection = document.getSelection()

            if (selection.containsNode(parent, true)) {
                const range = selection.getRangeAt(0)
                return { parent, selection, range }
            }
            else return { parent }
        },

        sanitizeSelection (s) {
            const string = s.range.toString()
            // Handle White Spaces
            if (string.slice(0, 1) === ' ') s.range.setStart(s.range.startContainer, s.range.startOffset + 1)
            if (string.slice(-1) === ' ')   s.range.setEnd(s.range.endContainer, s.range.endOffset - 1)
            return string.trim()
        },

        /*setContent (s, tags) {
            const content = [
                s.textBefore,
                (s.string.slice(0, 1) === ' ' ? ' ' : ''),
                (tags?.[0] ? tags[0] : ''),
                s.string.trim(),
                (tags?.[1] ? tags[1] : ''),
                (s.string.slice(-1) === ' ' ? ' ' : ''),
                s.textAfter
            ]

            this.content = content.join('')

            this.highlightString(s.selection, content)
        },*/

        /*highlightString (selection, content) {
            const limit = Math.floor(content.length / 2)
            const start = content.splice(0, limit).join('').length
            const end = start + content.shift().length

            setTimeout(function () {
                selection.focus()
                selection.setSelectionRange(start, end)
            }, 0)
        },*/

        selectLastChild (parent) {
            const selection = window.getSelection()
            const range = document.createRange()
            range.selectNodeContents(parent.lastChild)
            selection.removeAllRanges()
            selection.addRange(range)
        },

        selectElement (element) {
            const selection = window.getSelection()
            const range = document.createRange()
            range.selectNodeContents(element)
            selection.removeAllRanges()
            selection.addRange(range)
        },

        applyFormat (tag) {
            const s = this.getSelection()
            const newElement = document.createElement(tag)

            if (s?.range) {
                const string = this.sanitizeSelection(s)
                if (string) {
                    newElement.append(string)
                    s.range.deleteContents()
                    s.range.insertNode(newElement)
                    this.selectElement(newElement)
                }
            }
            else {
                if (this.content) {
                    const newBreak = document.createElement('br')
                    s.parent.appendChild(newBreak)
                }
                newElement.append('... ENTER TEXT ...')
                s.parent.appendChild(newElement)
                this.selectLastChild(s.parent)
            }

            this.emitContent()
        },

        addBreak () {
            const s = this.getSelection()

            if (s?.range) {
                const newBreak = document.createElement('br')
                s.range.insertNode(newBreak)
                this.emitContent()
            }
            else this.addParagraph()
        },

        addList (type) {
            const s = this.getSelection()

            const list = document.createElement(type + 'l')
            const li = document.createElement('li')
            const string = s?.range ? this.sanitizeSelection(s) : ''
            li.append(string)
            list.appendChild(li)

            if (s?.range) {
                s.range.deleteContents()
                s.range.insertNode(list)
            }
            else s.parent.appendChild(list)

            this.selectElement(li)
            this.emitContent()
        },

        addLink (dialog) {
            if (dialog) {
                const selection = this.getSelection()
                const string = selection?.range ? this.sanitizeSelection(selection) : null

                this.linkDialog = {
                    active: true,
                    url: null,
                    mask: string,
                    blank: true,
                    selection
                }
            }
            else {
                const s = this.linkDialog.selection

                const newLink = document.createElement('a')
                newLink.setAttribute('href', this.linkDialog.url.trim())
                if (this.linkDialog.blank) newLink.setAttribute('target', '_blank')
                newLink.append(this.linkDialog.mask ? this.linkDialog.mask : this.linkDialog.url.trim())

                if (s?.range) {
                    s.range.deleteContents()
                    s.range.insertNode(newLink)
                }
                else s.parent.appendChild(newLink)

                this.selectElement(newLink)
                this.emitContent()
                this.resetLinkDialog()
            }
        },

        clearFormat () {
            const s = this.getSelection()

            if (s?.range) {
                const newElement = document.createElement('span')
                const string = this.sanitizeSelection(s)
                newElement.append(string)
                s.range.deleteContents()
                s.range.insertNode(newElement)

                this.emitContent()
            }
            else {
                this.content = s.parent?.textContent ? s.parent.textContent.trim() : null

                this.emitContent(true)
                this.content = this.value
            }
        },

        resetLinkDialog () {
            this.linkDialog = {
                active: false,
                url: null,
                mask: null,
                blank: true,
                selection: null
            }
        },

        toggleCode () {
            this.emitContent()
            this.content = this.value
            this.showCode = !this.showCode
        }
    }
}

</script>
