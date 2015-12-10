(function ($) {
    "use strict";
    $.storage = new $.store();
    $.discountcards = {
        options: {},
        // last list view user has visited: {title: "...", hash: "..."}
        lastView: null,
        init: function (options) {
            var that = this;
            that.options = options;
            if (typeof ($.History) != "undefined") {
                $.History.bind(function () {
                    that.dispatch();
                });
            }
            $.wa.errorHandler = function (xhr) {
                if ((xhr.status === 403) || (xhr.status === 404)) {
                    var text = $(xhr.responseText);
                    if (text.find('.dialog-content').length) {
                        text = $('<div class="block double-padded"></div>').append(text.find('.dialog-content *'));

                    } else {
                        text = $('<div class="block double-padded"></div>').append(text.find(':not(style)'));
                    }
                    $("#discountcards-content").empty().append(text);
                    that.onPageNotFound();
                    return false;
                }
                return true;
            };
            var hash = this.getHash();
            if (hash === '#/' || !hash) {
                this.dispatch();
            } else {
                $.wa.setHash(hash);
            }

        },
        // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
        // *   Dispatch-related
        // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

        // if this is > 0 then this.dispatch() decrements it and ignores a call
        skipDispatch: 0,
        /** Cancel the next n automatic dispatches when window.location.hash changes */
        stopDispatch: function (n) {
            this.skipDispatch = n;
        },
        /** Force reload current hash-based 'page'. */
        redispatch: function () {
            this.currentHash = null;
            this.dispatch();
        },
        dispatch: function (hash) {
            if (this.skipDispatch > 0) {
                this.skipDispatch--;
                return false;
            }

            if (hash === undefined || hash === null) {
                hash = this.getHash();
            }
            if (this.currentHash == hash) {
                return;
            }

            this.currentHash = hash;
            hash = hash.replace('#/', '');

            if (hash) {
                hash = hash.split('/');
                if (hash[0]) {
                    var actionName = "";
                    var attrMarker = hash.length;
                    for (var i = 0; i < hash.length; i++) {
                        var h = hash[i];
                        if (i < 2) {
                            if (i === 0) {
                                actionName = h;
                            } else if (parseInt(h, 10) != h && h.indexOf('=') == -1) {
                                actionName += h.substr(0, 1).toUpperCase() + h.substr(1);
                            } else {
                                attrMarker = i;
                                break;
                            }
                        } else {
                            attrMarker = i;
                            break;
                        }
                    }
                    var attr = hash.slice(attrMarker);
                    this.preExecute(actionName);
                    if (typeof (this[actionName + 'Action']) == 'function') {
                        $.shop.trace('$.discountcards.dispatch', [actionName + 'Action', attr]);
                        this[actionName + 'Action'].apply(this, attr);
                    } else {
                        $.shop.error('Invalid action name:', actionName + 'Action');
                    }
                    this.postExecute(actionName);
                } else {
                    this.preExecute();
                    this.defaultAction();
                    this.postExecute();
                }
            } else {
                this.preExecute();
                this.defaultAction();
                this.postExecute();
            }


        },
        preExecute: function (actionName, attr) {
        },
        postExecute: function (actionName, attr) {
            this.actionName = actionName;
        },
        defaultAction: function () {
            var self = this;
            this.load('?plugin=discountcards&action=list&' + $('#discountcard-search').serialize(), function () {
                self.initButton();
                self.initSearchHandler();
            });
        },
        ordersAction: function () {
            var self = this;
            this.load('?plugin=discountcards&action=orders&' + $('#discountcard-search-orders').serialize(), function () {
                self.initSearchOrdersHandler();
            });
        },
        initSearchHandler: function () {
            var self = this;
            $('#discountcard-search').submit(function () {
                self.defaultAction();
                return false;
            });
        },
        initSearchOrdersHandler: function () {
            var self = this;
            $('#discountcard-search-orders').submit(function () {
                self.ordersAction();
                return false;
            });
        },
        initButton: function () {
            var self = this;

            $('.delete-selected').click(function () {
                var post_data = $('input.delete-checkbox:checked').serialize();
                $.ajax({
                    url: '?plugin=discountcards&action=deleteDiscountcard',
                    data: post_data,
                    type: 'post',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'ok') {
                            $('input.delete-checkbox:checked').each(function () {
                                $(this).closest('tr').remove();
                            });
                        } else {
                            alert(response.errors.join(','));
                        }
                    },
                    error: function (response) {

                    }
                });
            });

            $(document).off('click', '.delete-buttom').on('click', '.delete-buttom', function () {
                var id = $(this).closest('tr').data('id');
                self.deleteDiscountcard(id);
                return false;
            });
            $(document).off('click', '.add-button').on('click', '.add-button', function () {
                self.addDiscountcard();
                return false;
            });

            $(document).off('click', '.save-button').on('click', '.save-button', function () {
                var id = $(this).closest('tr').data('id');
                self.saveDiscountcard(id);
                return false;
            });
            $(document).off('click', '.edit-button').on('click', '.edit-button', function () {
                var id = $(this).closest('tr').data('id');
                self.editDiscountcard(id);
                return false;
            });

        },
        addDiscountcard: function () {
            if ($('tr[data-id="0"]').length) {
                return false;
            }
            var data = {
                'id': 0,
                'discountcard': '',
                'discount': 0,
                'amount': 0,
            };
            var tmpl = $('#discountcard-edit-tr-tmpl').tmpl(data);
            $('#discountcards-list tbody').prepend(tmpl);
        },
        deleteDiscountcard: function (id) {
            if (!confirm('Вы уверены')) {
                return false;
            }
            var tr = $('tr[data-id="' + id + '"]');
            tr.find('td:last').append('<i class="icon16 loading"></i>');
            $.ajax({
                type: 'POST',
                url: '?plugin=discountcards&action=deleteDiscountcard',
                dataType: 'json',
                data: {
                    id: id
                },
                success: function (data, textStatus, jqXHR) {
                    if (data.status == 'ok') {
                        tr.remove();
                    } else {
                        alert(data.errors.join(','));
                    }
                }
            });
        },
        editDiscountcard: function (id) {
            var tr = $('tr[data-id="' + id + '"]');
            tr.find('td:last').append('<i class="icon16 loading"></i>');
            $.ajax({
                url: '?plugin=discountcards&action=getDiscountcard',
                data: {
                    id: id
                },
                type: 'post',
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'ok') {
                        var tmpl = $('#discountcard-edit-tr-tmpl').tmpl(response.data);
                        tr.replaceWith(tmpl);
                    } else {
                        alert(response.errors.join(','));
                    }
                },
                error: function (response) {
                }
            });
        },
        saveDiscountcard: function (id) {
            var tr = $('tr[data-id="' + id + '"]');
            tr.find('td:last').append('<i class="icon16 loading"></i>');
            var post_data = $(tr).find('input').serialize();
            $.ajax({
                url: '?plugin=discountcards&action=saveDiscountcard',
                data: post_data,
                type: 'post',
                dataType: 'json',
                success: function (response) {

                    if (response.status == 'ok') {
                        var tmpl = $('#discountcard-tr-tmpl').tmpl(response.data);
                        tr.replaceWith(tmpl);
                    } else {
                        alert(response.errors.join(','));
                    }
                },
                error: function (response) {

                }
            });
        },
        initLazyLoad: function (options) {

            var count = options.count;
            var offset = count;
            var total_count = options.total_count;
            var url = (options.url || '?plugin=discountcards&action=list') + '&' + $(options.search_form).serialize();
            var target = $(options.target || '#discountcards-list');

            $(window).lazyLoad('stop');  // stop previous lazy-load implementation

            if (offset < total_count) {
                $(window).lazyLoad({
                    container: target,
                    state: (typeof options.auto === 'undefined' ? true : options.auto) ? 'wake' : 'stop',
                    load: function () {
                        $(window).lazyLoad('sleep');
                        $('.lazyloading-link').hide();
                        $('.lazyloading-progress').show();
                        $.get(
                                url + '&lazy=1&offset=' + offset,
                                function (html) {
                                    var data = $('<div></div>').html(html);
                                    var children = data.find('#discountcards-list tbody').children();
                                    offset += count;
                                    target.append(children);
                                    $('.lazyloading-progress-string').replaceWith(data.find('.lazyloading-progress-string'));
                                    $('.lazyloading-progress').replaceWith(data.find('.lazyloading-progress'));
                                    if (offset >= total_count) {
                                        $(window).lazyLoad('stop');
                                        $('.lazyloading-link').hide();
                                    } else {
                                        $('.lazyloading-link').show();
                                        $(window).lazyLoad('wake');
                                    }
                                    data.remove();
                                },
                                "html"
                                );
                    }
                });
                $('.lazyloading-link').unbind('click').bind('click', function () {
                    $(window).lazyLoad('force');
                    return false;
                });
            }
        },
        /** Current hash */
        getHash: function () {
            return this.cleanHash();
        },
        /** Make sure hash has a # in the begining and exactly one / at the end.
         * For empty hashes (including #, #/, #// etc.) return an empty string.
         * Otherwise, return the cleaned hash.
         * When hash is not specified, current hash is used. */
        cleanHash: function (hash) {
            if (typeof hash == 'undefined') {
                hash = window.location.hash.toString();
            }

            if (!hash.length) {
                hash = '' + hash;
            }
            while (hash.length > 0 && hash[hash.length - 1] === '/') {
                hash = hash.substr(0, hash.length - 1);
            }
            hash += '/';

            if (hash[0] != '#') {
                if (hash[0] != '/') {
                    hash = '/' + hash;
                }
                hash = '#' + hash;
            } else if (hash[1] && hash[1] != '/') {
                hash = '#/' + hash.substr(1);
            }

            if (hash == '#/') {
                return '';
            }

            return hash;
        },
        load: function (url, options, fn) {
            if (typeof options === 'function') {
                fn = options;
                options = {};
            } else {
                options = options || {};
            }
            var r = Math.random();
            this.random = r;
            var self = this;



            (options.content || $("#discountcards-content")).html('<div class="block triple-padded"><i class="icon16 loading"></i>Loading...</div>');
            return  $.get(url, function (result) {
                if ((typeof options.check === 'undefined' || options.check) && self.random != r) {
                    // too late: user clicked something else.
                    return;
                }

                (options.content || $("#discountcards-content")).removeClass('bordered-left').html(result);
                if (typeof fn === 'function') {
                    fn.call(this);
                }

            });
        },
        onPageNotFound: function () {
            //this.defaultAction();
        }
    };



})(jQuery);