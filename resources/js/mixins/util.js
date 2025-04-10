import md5 from 'md5'
import moment from 'moment'
import { MemberRole } from '../data/enums';

export default {
    data () {
        return {
            MemberRole
        };
    },
    computed: {
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
         * Returns member name
         */
        getMemberName(member) {
            let name = '';

            if (typeof member !== 'undefined' && member) {
                if (typeof member.name !== 'undefined' && member.name) {
                    name = member.name;
                }

                if (name === '' && typeof member.user !== 'undefined' && member.user) {
                    if (typeof member.user.firstname !== 'undefined' && member.user.firstname) {
                        name = member.user.firstname;
                    }

                    if (typeof member.user.lastname !== 'undefined' && member.user.lastname) {
                        name = (name ? name + ' ' : '') + member.user.lastname;
                    }

                    if (name === '' && typeof member.user.email !== 'undefined' && member.user.email) {
                        name = member.user.email;
                    }
                }
            }

            return name.trim();
        },

        /**
         * Returns user name
         */
        getUserName(user) {
            let name = '';

            if (typeof user.name !== 'undefined' && user.name) {
                name = user.name;
            }

            if (name === '') {
                if (typeof user.firstname !== 'undefined' && user.firstname) {
                    name = user.firstname;
                }

                if (typeof user.lastname !== 'undefined' && user.lastname) {
                    name = (name ? name + ' ' : '') + user.lastname;
                }

                if (name === '' && typeof user.email !== 'undefined' && user.email) {
                    name = user.email;
                }
            }

            return name.trim();
        },

        /**
         * Returns member tag
         */
        getMemberTag(member) {
            let tag = '';
            if (typeof member !== 'undefined' && member) {
                if (typeof member.tag !== 'undefined' && member.tag) {
                    tag = '@' + member.tag.replace(/^@+/, '');
                }

                if (tag === '' && typeof member.user !== 'undefined' && member.user) {
                    if (typeof member.user.tag !== 'undefined' && member.user.tag) {
                        tag = '@' + member.user.tag.replace(/^@+/, '');
                    }
                }
            }

            return tag.trim();
        },

        /**
         * Returns user tag
         */
        getUserTag(user) {
            let tag = '';
            if (typeof user.tag !== 'undefined' && user.tag) {
                tag = '@' + user.tag.replace(/^@+/, '');
            }

            return tag.trim();
        },

        /**
         * Return the member Gravatar, if it exists
         */
        getMemberGravatar(member) {
            let gravatar = '';

            if (typeof member !== 'undefined' && member !== null) {
                if (typeof member.photo !== 'undefined' && member.photo !== null) {
                    gravatar = member.photo;
                }

                if (gravatar === '' && typeof member.user !== 'undefined' && member.user) {
                    if (typeof member.user.photo !== 'undefined' && member.user.photo) {
                        gravatar = member.user.photo;
                    }

                    if (gravatar === '' && typeof member.user.email !== 'undefined' && member.user.email) {
                        let email = member.user.email.toLowerCase();
                        if (email) {
                            gravatar = 'https://www.gravatar.com/avatar/' + md5(email) + '?s=48&d=identicon';
                        }
                    }
                }
            }

            if (gravatar === '') {
                gravatar = '/assets/img/default.png';
            }

            return gravatar;
        },

        /**
         * Return the user Gravatar, if it exists
         */
        getUserGravatar(user) {
            let gravatar = '';

            if (typeof user !== 'undefined' && user !== null) {
                if (typeof user.photo !== 'undefined' && user.photo !== null) {
                    gravatar = user.photo;
                }

                if (gravatar === '') {
                    let email = user.email.toLowerCase();
                    if (email) {
                        gravatar = 'https://www.gravatar.com/avatar/' + md5(email) + '?s=48&d=identicon';
                    }
                }
            }

            if (gravatar === '') {
                gravatar = '/assets/img/default.png';
            }

            return gravatar;
        },

        /**
         * Return created diff time from now
         */
        getCreatedInfo(item) {
            return moment(item.created_at).fromNow();
        },

        /**
         * Linkify text
         */
        linkify(inputText) {
            const pattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
            let text = inputText.replace(pattern1, '<a href="$1" target="_blank">$1</a>');

            const pattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
            text = text.replace(pattern2, '$1<a href="http://$2" target="_blank">$2</a>');

            return text;
        },
        
        /**
         * Prevent special characters from inputing
         */
        validateUrl(event) {
            event = (event) ? event : window.event;
            var regex = new RegExp("^[a-z0-9\-]*$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);

            if (!regex.test(key)) {
                event.preventDefault();
                return false;
            }
        },

        /**
         * Return inputed url validation
         */
        checkUrlValidation(url) {
            const urlRegex = /^(http(s)?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
            return urlRegex.test(url);
        },

        /**
         * Extract video ID from urls (youtube, vimeo)
         */        
        getVideoIdFromUrl(url) {
            url = url.split('&')[0];

            if (url.includes("youtube") || url.includes("youtu.be")) {
                let regExp = /.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|youtube-|watch\?v=)([^#\&\?]*).*/;
                let match = url.match(regExp);

                return (match && match[1].length === 11) ? 'youtube-' + match[1] : null;
            }

            if (url.includes("vimeo.com")) {
                // let regExp = /\d+/;
                let regExp = /(http|https)?:\/\/(www\.|player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|video\/|)(\d+)(?:|\/\?)/;
                let match = url.match(regExp);
                if (match) {
                    let index = url.lastIndexOf("/");
                    let id = url.substr(index);
                    return 'vimeo-' + id.replace('/', '');
                }

                return null;
            }

            if (!url.includes("youtube") && !url.includes("youtu.be") && !url.includes("vimeo.com")) {
                url = url.split('?')[0];
            }

            return url;
        },

        /**
         * Format amount with currency
         */
        formatAmountWithCurrency(currency, amount)
        {
            return new Intl.NumberFormat(this.$i18n.locale, {
                style: 'currency',
                currency: currency
            }).format(amount);
        },

        /**
         * Check whether member is admin or not
         */
        isAdmin(role)
        {
            return MemberRole.OWNER === role || MemberRole.ADMIN === role;
        },

        /**
         * Check whether member is moderator or not
         */
        isModerator(role)
        {
            return MemberRole.MODERATOR === role;
        },

        /**
         * Check whether member is manager or not
         */
        isManager(role)
        {
            return this.isAdmin(role) || this.isModerator(role);
        },

        /**
         * Get data attribute from html string
         */
        getDataPropertyValue(str, dataAttributeName) {
            const regex = new RegExp(`data-${dataAttributeName}="([^"]*)"`);
            const match = str.match(regex);

            if (match && match[1]) {
                return match[1];
            } else {
                return null; // No match found
            }
        },

        /**
         * Update view content for mention
         */
        updateViewContentForMention(content) {
            let members = JSON.parse(JSON.stringify(this.allowedMembers));
            let matches = content.match(/<span class="mentioned-name"[^>]*>\@[^@]+<\/span>/gim);
            if (matches) {
                for (let i = 0; i < matches.length; i++) {
                    const match = matches[i];
                    let matchReplace = match;
                    const memberId = this.getDataPropertyValue(match, 'id');
                    const tag = match.replace(/<\/?span[^>]*>/g, "");
                    for (var j = 0; j < members.length; j++) {
                        if (parseInt(memberId) === members[j].id) {
                            matchReplace = match.replace(tag, this.getMemberTag(members[j]));
                            break;
                        }
                    }

                    content = content.replace(match, matchReplace);
                }
            }

            return content;
        },

        /**
         * Update edit content for mention
         */
        updateEditContentForMention(content) {
            let members = JSON.parse(JSON.stringify(this.allowedMembers));
            if (content) {
                let matches = content.match(/<span class="mentioned-name"[^>]*>\@[^@]+<\/span>/gim);
                if (matches) {
                    for (let i = 0; i < matches.length; i++) {
                        const match = matches[i];
                        const memberId = this.getDataPropertyValue(match, 'id');
                        let tag = match.replace(/<\/?span[^>]*>/g, "");
                        for (var j = 0; j < members.length; j++) {
                            if (parseInt(memberId) === members[j].id) {
                                tag = this.getMemberTag(members[j]);
                                break;
                            }
                        }

                        content = content.replace(match, tag);

                        this.$store.commit('addMentionedMember', {
                            id: parseInt(memberId),
                            tag: tag + ' '
                        });
                    }
                }
            }

            return content;
        },
    }
}
