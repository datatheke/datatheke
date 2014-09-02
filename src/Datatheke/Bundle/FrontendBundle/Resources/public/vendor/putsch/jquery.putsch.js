(function($){

    var initialized = false;
    var ignoreEvent = false;

    $.fn.putsch = function(options) {
        var settings = $.extend({
            type: null,
            container: null,
            loader: loader
        }, options );

        if (!initialized) {
            initialize(settings);
        }

        if ('bootstrap-tab' == settings.type) {
            bootstrapHandler.configure(this, settings)
        } else {
            defaultHandler.configure(this, settings);
        }

        return this;
    }

    initialize = function(settings) {
        $(window).on('popstate', function(event) {
            popStateHandler(event, settings);
        });
        initialized = true;
    }

    popStateHandler = function(event, settings) {
        var state = event.originalEvent.state;

        if (state && state.source == 'putsch') {
            var event = $.Event('putsch:popstate', {state: state, settings: settings});
            $(window).trigger(event);
        }
    }

    loader = function(url, container, settings) {
        $.ajax({
            url: url,
            cache: false,
            type: 'get',
            success: function (html) {
                // If response starts with a doctype tag, stop ajax and reload entire page (ie. redirection to login form)
                // if (html.match(/^<!DOCTYPE html>/)) {
                //     window.location.reload();
                //     return false;
                // }

                $(container).html(html);
            },
        });
    }

    isHistoryAvalaible = function() {
        return window.history && window.history.pushState;
    }

    defaultHandler = {
        configure: function(elts, settings) {
            $(elts).on('click', function(event) {
                event.preventDefault();
                defaultHandler.handleEvent(event, settings);
            });

            $(window).on('putsch:popstate', defaultHandler.popState);
        },

        handleEvent: function(event, settings) {
            if (ignoreEvent) {
                return;
            }

            var $link     = $(event.target);
            var url       = $link.attr('href');
            var container = $link.data('putsch-target') || settings.container;

            if (isHistoryAvalaible()) {
                var state = {
                    source: 'putsch',
                    type: 'default',
                    container: container,
                    url: url
                }

                window.history.pushState(state, window.document.title, url);
            }

            settings.loader(url, container, settings);
        },

        popState: function (event) {
            var state = event.state;
            var settings = event.settings;

            if (!state || state.source != 'putsch' || state.type != 'default') {
                return;
            }

            settings.loader(state.url, state.container, settings);
        }
    }

    bootstrapHandler = {
        configure: function(elts, settings) {

            $(elts).find('li a').on('show show.bs.tab', function(event) {
                bootstrapHandler.handleEvent(event, settings);
            });

            // Init first state
            if (isHistoryAvalaible() && !window.history.state) {
                var url = null;
                var container = null;

                var $activeTab = $(elts).find('li.active a:first');
                if ($activeTab.length) {
                    var url = $activeTab.attr('href');
                    var container = $activeTab.data('target');
                    var content = $(container).html();
                }

                var state = {
                    source: 'putsch',
                    type: 'bootstrap-tab',
                    initialState: true,
                    selector: elts.selector,
                    url: url,
                    container: container
                }
                window.history.replaceState(state, window.document.title, url);
            }

            $(window).on('putsch:popstate', bootstrapHandler.popState);
        },

        handleEvent: function(event, settings) {
            if (ignoreEvent) {
                return;
            }

            var $link     = $(event.target);
            var url       = $link.attr('href');
            var container = $link.data('target');

            if (isHistoryAvalaible()) {
                var nextState = {
                    source: 'putsch',
                    type: 'bootstrap-tab',
                    container: container,
                    url: url
                }

                var currentState = window.history.state;

                if (nextState.source == currentState.source
                    && nextState.type == currentState.type
                    && nextState.container == currentState.container
                    && nextState.url == currentState.url
                    ) {
                    return;
                }

                window.history.pushState(nextState, window.document.title, url);
            }

            if ($link.data('target')) {
                settings.loader(url, container, settings);
            }
       },

        popState: function (event) {
            var state = event.state;
            var settings = event.settings;

            if (!state || state.source != 'putsch' || state.type != 'bootstrap-tab') {
                return;
            }

            if (state.container) {
                var $a = $('li a[data-target="'+state.container+'"]:first');

                if ($a.length) {
                    settings.loader(state.url, state.container, settings);
                    ignoreEvent = true;
                    $a.tab('show');
                    ignoreEvent = false;
                }
            } else if (state.url) {
                $('li a[href="'+state.url+'"]').tab('show');
            } else {
                $(state.selector).find('li.active').removeClass('active');
            }
        }
    }

})(jQuery);
