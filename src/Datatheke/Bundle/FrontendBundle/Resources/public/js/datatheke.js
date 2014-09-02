jQuery(document).ready(function() {
    var show = $.fn.show;
    $.fn.show = function() {
        this.removeClass('hidden');
        this.removeClass('hide');
        return show.call(this);
    };
});

function gloomyAjaxUpdater()
{
    var url         = arguments[0];
    var div         = arguments[1];
    var options     = arguments[2] || {};

    var spinner     = options['spinner'] || null;
    var onsuccess   = options['onsuccess'] || function() {};
    var type        = options['type'] || 'get';
    var data        = options['data'] || null;

    $(spinner).show();
    $.ajax({
              url: url,
              cache: false,
              type: type,
              data: data,
              success: function (html) {
                            $(div).html(html);
                            $(spinner).hide();
                            onsuccess();
                        },
              error: function () {
                            $(spinner).hide();
                            $.jGrowl('Une erreur est survenue', {theme: 'error'});
                        }

    });

    return ( false );
};

/**
 * TABS
 */

bindTabs = function(options) {
    var options   = options || {};
    var eventName = options['event'] || 'show';
    var tabs      = options['tabs'] || '.nav li a[data-toggle="tab"]';
    var show      = options['show'] || $('.nav li.active a[data-toggle^="tab"]:first').length
                                        ? $('.nav li.active a[data-toggle^="tab"]:first')
                                        : $('.nav li a[data-toggle^="tab"]:first');

    $(tabs).on(eventName, function (e) {
        var a = $(e.target);

        // Skip already loaded tab
        if (a.data('is-loaded') == '1') {
            return;
        }
        a.data('is-loaded', '1');

        // Loading style
        if (a.data('loader') == 'static') {
            return;
        }
        else if (a.data('loader') == 'with-callback') {
            gloomyAjaxUpdater(a.attr('href'), a.data('target'), {
                spinner: '.tab-spinner',
                onsuccess: function() { window[a.data('loader-callback')](a.attr('href'), a.data('target')); } }
                );
        }
        else if (a.data('loader') == 'custom') {
            window[a.data('loader-custom')](a.attr('href'), a.data('target'));
        }
        else {
            loadView(a.attr('href'), a.data('target'));
        }
    });

    // load active tabs
    // $(show).closest('li').removeClass('active');
    // $(show).tab('show');
}

/**
 * AJAX VIEWS
 */

datathekeLoader = function(url, container, settings) {
    loadView(url, container);
}

loadView = function(url, target, type, data) {
    var type = type || 'get';
    var data = data || [];

    gloomyAjaxUpdater(url, target, {spinner: '.tab-spinner', type: type, data: data, onsuccess: function() { viewReady(target, loadView) } });
}

viewReady = function(target, loader) {

    // Bind Forms
    $(target).find('form').on('submit', function(e) {
        e.preventDefault();
        loader($(this).attr('action'), target, $(this).attr('method'), $(this).serializeArray());
        return false;
    });

    // Bind Links
    $(target).find('a:not(.outbound, .dt-target-outside, [href^="#"])').on('click', function(e) {
        if (e.isDefaultPrevented()) {
            e.stopPropagation();
            return;
        }
        e.preventDefault();
        loader(this.href, target);
        return false;
    });

    // Improve UI
    $(target).find('.dt-tooltip').tooltip();
    $(target).find('.dt-popover').popover();

    $(target).find('.gloomy-filter-date-inline').datepicker({onSelect: function(date, datepicker) {
        $(this).closest('th').find('input[type=text]:first').val(date);
        $(this).closest('th').find('input[type=text]:first').trigger('keyup');
    }});
}

updatePager = function(url, postdata) {

    var postdata = postdata || {};

    $('#pager-view .spinner').show();

    $.ajax({
        url: url,
        type: 'post',
        data: postdata,

        cache: false,
        success: function (html) {
                      $('#pager-view .spinner').hide();
                      $('#pager-content').html(html);
                      pagerReady();
                  },
        error: function () {
                      $('#pager-view .spinner').hide();
                      $.jGrowl('Une erreur est survenue', {theme: 'error'});
                  }
    });

    return false;
}

pagerReady = function() {

//    $('#pager-view .dt-popover').popover('hide');
//    $('#pager-view .dt-popover').popover('destroy');

    $('#pager-view .dt-tooltip').tooltip();

    $('#pager-view .dt-popover').popover();

    $('#pager-view .gloomy-filter-date-inline').datepicker({ dateFormat: "dd/mm/yy" });

    $('#pager-view form').on(
        'submit',
        function(event) {
            event.preventDefault();
            updatePager($(this).attr('action'), $(this).serializeArray());
        }
    );

    $('#pager-view a:not(.dt-target-outside, [href^="#"])').on(
        'click',
        function(event) {
            event.preventDefault();
            updatePager(this.href, $('#columns input').serializeArray());
        }
    );
}

/**
 * LIBRARY
 */

initLibrary = function() {
    $('.dt-collapsed-more').find('.dt-tooltip').tooltip();

    $("#library-description-expand").on('click',
        function() {
            var div = $('#library-description');
            var img = $('#library-description-expand');
            if (div.hasClass('dt-collapsed')) {
                div.removeClass('dt-collapsed');
                img.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
            }
            else {
                div.addClass('dt-collapsed');
                img.removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
            }

            return false;
        }
    );
}

/**
 * MAPS
 */

var maps = new Array();

createMap = function(location, target) {

    i = target.replace('#', '');
    if (maps[i]) {
        return;
    }

    maps[i] = new OpenLayers.Map(i);

    var osm = new OpenLayers.Layer.OSM("OpenStreetMap", null, { transitionEffect:'resize' });
    maps[i].addLayer(osm);

    var markers = new OpenLayers.Layer.Markers( "Markers" );
    maps[i].addLayer(markers);

    $.ajax({
        url: location,
        dataType: 'json',
        success: function (data) {

            for (var j = 0; j < data.length; j++) {

                var longitude = data[j]['longitude'];
                var latitude  = data[j]['latitude'];
                var content   = data[j]['content']

                var pos = new OpenLayers.LonLat(longitude, latitude).transform(
                    new OpenLayers.Projection('EPSG:4326'), // transform from WGS 1984
                    maps[i].getProjectionObject() // to Spherical Mercator Projection
                );

                feature = new OpenLayers.Feature(markers, pos);
                feature.closeBox = true;
                feature.popupClass =  OpenLayers.Class(OpenLayers.Popup.FramedCloud, {'autoSize': true});
                feature.data.popupContentHTML = content;
                feature.data.overflow = 'auto';

                marker = feature.createMarker();
                marker.events.register('click', feature, function (evt) {
                    if (this.popup == null) {
                        this.popup = this.createPopup(this.closeBox);
                        maps[i].addPopup(this.popup);
                        this.popup.show();
                    }
                    else {
                        this.popup.toggle();
                    }
                    currentPopup = this.popup;
                    OpenLayers.Event.stop(evt);
                });

                markers.addMarker(marker);
            }

            var bounds = markers.getDataExtent();
            maps[i].zoomToExtent(bounds);
            maps[i].zoomOut();
        },

        error: function () {
            $.jGrowl('Une erreur est survenue', {theme: 'error'});
        }
    });

    maps[i].addControl(new OpenLayers.Control.PanZoomBar());
    maps[i].addControl(new OpenLayers.Control.LayerSwitcher());
    maps[i].addControl(new OpenLayers.Control.Scale());
    maps[i].addControl(new OpenLayers.Control.MousePosition());
    maps[i].addControl(new OpenLayers.Control.Navigation());
    maps[i].addControl(new OpenLayers.Control.ScaleLine());

    maps[i].updateSize();
}