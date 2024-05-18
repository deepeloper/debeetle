<?php

if (empty($this)) {
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.0";
    header("$protocol 404 Not Found");
    die;
}

?>
/**
 * JavaScript's routines for Krumo
 *
 * Patched for Debeetle
 *
 * @version $Id: krumo.js 22 2007-12-02 07:38:18Z Mrasnika $
 * @link http://sourceforge.net/projects/krumo
 */

// Krumo {

/**
 * Krumo JS Class
 */
function krumo()
{
}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
 * Add a CSS class to an HTML element
 *
 * @param {HTMLElement} el
 * @param {string} className
 * @return void
 */
krumo.reclass = function(el, className)
{
    if (el.className.indexOf(className) < 0) {
        el.className += (' ' + className);
    }
}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
 * Remove a CSS class to an HTML element
 *
 * @param {HTMLElement} el
 * @param {string} className
 * @return void
 */
krumo.unclass = function(el, className)
{
    if (el.className.indexOf(className) > -1) {
        el.className = el.className.replace(className, '');
    }
}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
 * Toggle the nodes connected to an HTML element
 *
 * @param {HTMLElement} el
 * @return void
 */
krumo.toggle = function(el)
{
    var ul = el.parentNode.getElementsByTagName('ul');
    for (var i=0; i<ul.length; i++) {
        if (ul[i].parentNode.parentNode === el.parentNode) {
            ul[i].parentNode.style.display = ('none' === ul[i].parentNode.style.display)
            ? 'block'
            : 'none';
        }
    }

    // toggle class
    if ('block' === ul[0].parentNode.style.display) {
        krumo.reclass(el, 'krumo-opened');
    } else {
        krumo.unclass(el, 'krumo-opened');
    }

    if (null !== $d.Panel.Tab.lastTab) {
        $d.Panel.fixHeight($d.Panel.Tab.lastTab);
    }
}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
 * Hover over an HTML element
 *
 * @param {HTMLElement} el
 * @return void
 */
krumo.over = function(el)
{
    krumo.reclass(el, 'krumo-hover');
}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

/**
 * Hover out an HTML element
 *
 * @param {HTMLElement} el
 * @return void
 */
krumo.out = function(el)
{
    krumo.unclass(el, 'krumo-hover');
}

// } Krumo


$d.Plugins.TraceAndDump = {
    /**
     * @static
     */
    postStartup: function()
    {
        const options = $d.state.options;

        if (!options.dump.expand) {
            this.groupClick({id: 'dump-collapse'});
        }
        if (options.dump.expandEntities) {
            this.groupClick({id: 'dumpEntities-expand'});
        }
        if (!options.trace.expand) {
            this.groupClick({id: 'trace-collapse'});
        }
        if (options.dump.displayArgs && options.dump.expandArgs) {
            this.groupClick({id: 'traceArgs-expand'});
        }
    },

    onMouse: function(entity, add)
    {
        if (add) {
            $(entity).addClass('over');
        } else {
            $(entity).removeClass('over');
        }
    },

    click: function(legend)
    {
        const fieldset = legend.parentElement;

        legend.title = $d.View.Locale.get($(fieldset).hasClass('invisible') ? 'hide' : 'show');
        $(fieldset).toggleClass('invisible');
        if (null !== $d.Panel.Tab.lastTab) {
            $d.Panel.fixHeight($d.Panel.Tab.lastTab);
        }

        return false;
    },

    groupClick: function(button)
    {
        const action = button.id.split('-'), collapse = 'collapse' === action[1];

        switch (action[0]) {
            case 'dump':
            case 'trace':
                const cssToClick = collapse ? 'block' : 'none';
                $('#dPanel .' + action[0]).each(function() {
                    const legend = $(this).children()[0];
                    if ("LEGEND" === legend.tagName && $($(this).children()[1]).css('display') === cssToClick) {
                        $d.Plugins.TraceAndDump.click(legend);
                    }
                });
                break; // case 'dump', 'trace'
            case 'dumpEntities':
                $('#dPanel .dump').each(function() {
                    $('.krumo-expand', this).each(function() {
                        if (collapse ^ !$(this).hasClass('krumo-opened')) {
                            $(this).trigger('click');
                        }
                    });
                });
                break; // case 'dumpEntities'

            case 'traceArgs':
                $('.trace-args').find('div[onclick]').each(function() {
<?php
                    // const opened = $(this).hasClass('krumo-opened');
                    // collapse ? opened : !opened
?>
                    if (collapse ^ !$(this).hasClass('krumo-opened')) {
                        $(this).trigger('click');
                    }
                });
            // case 'traceArgs'
        }
    }
}
