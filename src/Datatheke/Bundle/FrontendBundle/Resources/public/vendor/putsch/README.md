Putsch
======

ABOUT
-----
Putsch is a simple jQuery plugin to load remote content and synchronize URL using pushState.

LICENSE
-------
MIT

USAGE
-----

Standard

```html
<script>
    jQuery(document).ready(function() {
        $('[data-putsch-target]').putsch();
    })
</script>

<div class="container">
    <a href="/link-content.html" data-putsch-target="#container">Test !</a>
    <div id="container">Nothing here.</div>
</div>
```

With Bootstrap tabs

```html
<script>
    jQuery(document).ready(function() {
        $('ul.nav-tabs').putsch({type: 'bootstrap-tab'});
    })
</script>

<div class="container">
    <ul class="nav nav-tabs">
        <li><a href="/tab1-content.html" class="tab" data-toggle="tab" data-target=".tab-content1">Tab 1</a></li>
        <li><a href="/tab2-content.html" class="tab" data-toggle="tab" data-target=".tab-content2">Tab 2</a></li>
        <li><a href="#tab-content3" class="tab" data-toggle="tab">Tab 3</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active tab-content1 tab-content2">No tab selected</div>
        <div class="tab-pane" id="tab-content3">Tab 3 static content</div>
    </div>
</div>
```