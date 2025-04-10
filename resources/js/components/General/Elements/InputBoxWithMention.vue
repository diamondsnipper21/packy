<template>
    <textarea 
        :id="id" 
        class="textarea" 
        :class="styleClass"
        :placeholder="placeholder" 
        v-model="content" 
        :rows="rows"
        @input="onInputHandler"
    ></textarea>
</template>

<script>

import getMemberName from "../../../mixins/util";
import getMemberTag from "../../../mixins/util";
import getMemberGravatar from "../../../mixins/util";

export default {
    name: 'InputBoxWithMention',
    mixins: [
        getMemberName,
        getMemberTag,
        getMemberGravatar
    ],
    props: {
        id: {
            type: String
        },
        content: {
            type: String
        },
        owner: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String
        },
        action: {
            type: String
        },
        styleClass: {
            type: String,
            default: ''
        },
        rows: {
            type: Number,
            default: 1
        },
    },
    data () {
        return {
            properties: [
                'direction',
                'boxSizing',
                'width',
                'height',
                'overflowX',
                'overflowY',

                'borderTopWidth',
                'borderRightWidth',
                'borderBottomWidth',
                'borderLeftWidth',
                'borderStyle',

                'paddingTop',
                'paddingRight',
                'paddingBottom',
                'paddingLeft',

                'fontStyle',
                'fontVariant',
                'fontWeight',
                'fontStretch',
                'fontSize',
                'fontSizeAdjust',
                'lineHeight',
                'fontFamily',

                'textAlign',
                'textTransform',
                'textIndent',
                'textDecoration',

                'letterSpacing',
                'wordSpacing',

                'tabSize',
                'MozTabSize',
            ],

            isFirefox: false,

            ref: false,
            menuContainerRef: false,
            menuRef: false,
            replaceFn: false,
            menuItemFn: false,
            options: [],
            menuHeight: 223,
            desktopMenuItemHeight: 44,
            mobileMenuItemHeight: 38,
            menuOptionPage: 1,
            searchQuery: ''
        };
    },
    mounted() {
        this.isFirefox = typeof window !== 'undefined' && window['mozInnerScreenX'] != null;

        this.ref = document.getElementById(this.id);
        this.menuContainerRef = document.getElementById('mention_menu_container');
        this.menuRef = this.menuContainerRef.querySelector('.mention-menu');

        this.replaceFn = (member, trigger) => {
            let replace = trigger + this.getMemberName(member).replace(' ', '') + ' ';
            if (this.getMemberTag(member)) {
                replace = trigger + this.getMemberTag(member).replace(/^@/, '') + ' ';
            }

            return replace
        }

        this.menuItemFn = (member, setItem, selected) => {
            const div = document.createElement('div')
            div.setAttribute('role', 'option')
            div.className = 'mention-menu-item'
            if (selected) {
                div.classList.add('selected')
                div.setAttribute('aria-selected', '')
            }

            let avatar = 
            div.innerHTML = '<img src="' + this.getMemberGravatar(member) + '" class="customer-avatar-photo size-34px" /> <div class="mention-menu-item-name">' + this.getMemberName(member) + '</div>'
            div.onclick = setItem

            return div
        }

        this.ref.addEventListener('keydown', (e) => {
            this.onKeyDown(e);
        });

        this.emitter.on("closeMentionMenu", ev => {
            this.closeMentionMenu();
        });

        this.menuScrollToBottomHandler();
        this.containerScrollHandler();
    },
    computed: {
        /**
         * Return auth status
         */
        auth ()
        {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns allowedMembers
         */
        allowedMembers ()
        {
            return this.$store.state.communitycenter.allowedMembers;
        }
    },
    methods: {
        /**
         * Return menu options
         */
        resolveFn(prefix) {
            return prefix === ''
            ? this.allowedMembers.slice(0, 6 * this.menuOptionPage)
            : this.allowedMembers.filter(member => this.getMemberName(member).toLowerCase().startsWith(prefix.toLowerCase())).slice(0, 6 * this.menuOptionPage);
        },

        /**
         * get coordinates
         */
        getCaretCoordinates (element, position)
        {
            const div = document.createElement('div')
            document.body.appendChild(div)

            let style = div.style
            const computed = getComputedStyle(element)

            style.whiteSpace = 'pre-wrap'
            style.wordWrap = 'break-word'
            style.position = 'absolute'
            style.visibility = 'hidden'

            this.properties.forEach(prop => {
                style[prop] = computed[prop]
            })

            if (this.isFirefox) {
                if (element.scrollHeight > parseInt(computed.height))
                    style.overflowY = 'scroll'
            } else {
                style.overflow = 'hidden'
            }

            div.textContent = element.value.substring(0, position)

            const span = document.createElement('span')
            span.textContent = element.value.substring(position) || '.'
            div.appendChild(span)

            const coordinates = {
                top: span.offsetTop + parseInt(computed['borderTopWidth']),
                left: span.offsetLeft + parseInt(computed['borderLeftWidth']),
                height: parseInt(computed['lineHeight'])
                // height: span.offsetHeight
            }

            div.remove()

            return coordinates
        },

        async makeOptions() {
            const options = await this.resolveFn(this.searchQuery)
            if (options.length !== 0) {
                this.options = options
                this.renderMenu()
            } else {
                this.closeMentionMenu()
            }
        },

        closeMentionMenu() {
            setTimeout(() => {
                this.options = []
                this.left = undefined
                this.top = undefined
                this.triggerIdx = undefined
                this.menuOptionPage = 1
                this.renderMenu()
            }, 0)
        },

        selectItem(active) {
            return () => {
                const preMention = this.ref.value.substr(0, this.triggerIdx)
                const option = this.options[active]
                const mention = this.replaceFn(option, this.ref.value[this.triggerIdx])
                const postMention = this.ref.value.substr(this.ref.selectionStart)
                const newValue = `${preMention}${mention}${postMention}`

                this.$store.commit('addMentionedMember', {
                    id: option.id,
                    tag: this.getMemberTag(option)
                });

                this.ref.value = newValue;
                this.updateContent(this.ref.value);

                const caretPosition = this.ref.value.length - postMention.length

                // this.ref.setSelectionRange(preMention.length, caretPosition)
                this.closeMentionMenu()
                this.ref.focus()
            }
        },

        mentionMenuRenderHandler() {
            const positionIndex = this.ref.selectionStart
            const textBeforeCaret = this.ref.value.slice(0, positionIndex)
            const tokens = textBeforeCaret.split(/\s/)
            const lastToken = tokens[tokens.length - 1]
            const triggerIdx = textBeforeCaret.endsWith(lastToken)
            ? textBeforeCaret.length - lastToken.length
            : -1
            const maybeTrigger = textBeforeCaret[triggerIdx]

            const keystrokeTriggered = maybeTrigger === '@'

            if (!keystrokeTriggered) {
                this.closeMentionMenu()
                return
            }

            this.searchQuery = textBeforeCaret.slice(triggerIdx + 1);

            this.makeOptions();

            setTimeout(() => {
                this.active = 0;

                const coords = this.getCaretCoordinates(this.ref, positionIndex);
                const { top, left, height } = this.ref.getBoundingClientRect();

                this.left = window.scrollX + coords.left + left + this.ref.scrollLeft;
                this.top = window.scrollY + coords.top + top + coords.height - this.ref.scrollTop;
                this.triggerIdx = triggerIdx;

                if (this.ref.scrollTop + height < coords.top || this.ref.scrollTop > coords.top + coords.height) {
                    this.top = undefined;
                }

                var objDiv = document.getElementById("post_content_container");
                if (objDiv !== null) {
                    if (132 - top > coords.top + coords.height) {
                        this.top = undefined;
                    }
                }

                this.renderMenu();
                this.menuScroll(0);
            }, 500)
        },

        onKeyDown(ev) {
            let keyCaught = false
            if (this.triggerIdx !== undefined && typeof ev !== 'undefined') {
                switch (ev.key) {
                    case 'ArrowDown':
                        this.active = Math.min(this.active + 1, this.options.length - 1);
                        let overBelowCnt = this.active - 4;
                        if (overBelowCnt > 0) {
                            this.menuScroll(overBelowCnt);
                        }
                        this.renderMenu()
                        keyCaught = true
                        break

                    case 'ArrowUp':
                        this.active = Math.max(this.active - 1, 0);
                        let overUpCnt = this.active;
                        if (overUpCnt >= 0) {
                            this.menuScroll(overUpCnt);
                        }
                        this.renderMenu()
                        keyCaught = true
                        break

                    case 'Enter':
                    case 'Tab':
                        this.selectItem(this.active)()
                        keyCaught = true
                        break

                    case 'Escape':
                        this.closeMentionMenu()
                        keyCaught = true
                        break
                }
            }

            if (keyCaught) {
                ev.preventDefault()
            }
        },

        renderMenu() {
            if (this.top === undefined) {
                this.menuContainerRef.hidden = true
                return
            }

            this.menuContainerRef.style.left = this.left + 'px'
            this.menuContainerRef.style.top = this.top + 'px'
            this.menuRef.innerHTML = ''

            this.options.forEach((option, idx) => {
                this.menuRef.appendChild(this.menuItemFn(
                    option,
                    this.selectItem(idx),
                    this.active === idx))
            })

            this.menuContainerRef.hidden = false
        },

        updateContent(content) {
            // Update post / comment content
            if (this.action === 'add_comment') {
                if (this.owner === 'comment') {
                    this.$store.commit('setCommunityPostCommentProperty', {
                        key: 'content',
                        v: content
                    });
                } else if (this.owner === 'reply') {
                    this.$store.commit('setReplyCommentProperty', {
                        key: 'content',
                        v: content
                    });
                }
            } else if (this.action === 'edit_comment') {
                this.$store.commit('setEditedCommentProperty', {
                    key: 'content',
                    v: content
                });
            } else if (this.action === 'add_post' || this.action === 'edit_post') {
                this.$store.commit('setCommunityPostProperty', {
                    key: 'content',
                    v: content
                });
            } else if (this.action === 'add_chat') {
                this.$store.commit('setNewChatContent', content);
            }
        },

        /**
         * Show login modal
         */
        showLogin ()
        {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },

        onInputHandler ()
        {
            if (this.auth) {
                if (this.action === 'add_comment' || this.action === 'edit_comment') {
                    this.activeElement = document.activeElement;
                    this.activeElement.style.height = this.activeElement.scrollHeight + "px";

                    const parent = this.activeElement.parentNode;
                    const links = parent.querySelector('.textarea-links');
                    if (links) {
                        links.style.marginTop = "0px";
                    }
                } else if (this.action === 'add_chat') {
                    this.activeElement = document.activeElement;
                    this.activeElement.style.height = this.activeElement.scrollHeight + "px";
                }

                this.closeInputBoxHandler();

                this.scrollToBottom();

                this.updateContent(this.ref.value);
                this.mentionMenuRenderHandler();
            } else {
                this.showLogin();
            }
        },

        /**
         * Close input box handler
         */
        closeInputBoxHandler ()
        {
            if (this.action === 'add_comment') {
                if (this.owner === 'comment') {
                    this.$store.commit('setCommentShowModeToView');
                }
            }
        },

        /**
         * Scroll to bottom
         */
        scrollToBottom()
        {
            setTimeout(() => {
                // if (typeof document.getElementById('community_modal_container') !== 'undefined' && document.getElementById('community_modal_container') !== null) {
                //     document.getElementById('community_modal_container').scrollTop = document.getElementById('community_modal_container').scrollHeight;
                // }

                if (this.owner === 'comment') {
                    var objDiv = document.getElementById("post_content_container");
                    if (objDiv) {
                        objDiv.scrollTo({top: objDiv.scrollHeight, behavior: 'smooth'});
                    }
                } else if (this.owner === 'chat') {
                    var objContentDiv = document.getElementById("chat_detail_content");
                    if (objContentDiv) {
                        objContentDiv.scrollTo({top: objContentDiv.scrollHeight, behavior: 'smooth'});
                    }
                }
            }, 300);
        },

        /**
         * Menu Scroll
         */
        menuScroll(overCnt)
        {
            setTimeout(() => {
                if (this.menuRef) {
                    let stepHeight = this.desktopMenuItemHeight;
                    if (window.innerWidth < 600) {
                        stepHeight = this.mobileMenuItemHeight;
                    }
                    this.menuRef.scrollTo({top: stepHeight * overCnt, behavior: 'smooth'});
                }
            }, 200);
        },

        /**
         * mention menu scroll to bottom handler
         */
        menuScrollToBottomHandler() {
            if (this.menuRef !== null) {
                // scroll down handler
                this.menuRef.addEventListener("scroll", () => {
                    const element = this.menuRef;
                    if (element.scrollTop === (element.scrollHeight - element.offsetHeight)) {
                        this.menuRef.style.overflow = "hidden";
                        setTimeout(() => {
                            this.menuOptionPage ++;
                            this.makeOptions();
                            this.menuRef.style.overflow = "auto";
                        }, 0);
                    } else {
                        this.menuRef.style.overflow = "auto";
                    }
                }, { passive: false });
            }
        },

        /**
         * container scroll handler
         */
        containerScrollHandler() {
            if (this.top) {
                var objDiv = document.getElementById("post_content_container");
                if (objDiv !== null) {
                    objDiv.addEventListener("scroll", () => {
                        this.mentionMenuRenderHandler();
                    }, { passive: false });
                }

                this.ref.addEventListener("scroll", () => {
                    this.mentionMenuRenderHandler();
                }, { passive: false });
            }
        },
    }
}
</script>

<style scoped>

</style>