jQuery.fn.visible = function () {
  return this.css('visibility', 'visible');
};

jQuery.fn.invisible = function () {
  return this.css('visibility', 'hidden');
};

jQuery.fn.toggleVisibility = function () {
  return this.css(
    'visibility',
    function (i, visibility) {
      return visibility === 'visible' ? 'hidden' : 'visible';
    }
  );
};

jQuery.fn.disableTextSelect = function () {
  return this.each(function () {
    if ($.browser.mozilla) { // Firefox
      $(this).css('MozUserSelect', 'none');
    } else if ($.browser.msie) { // IE
      $(this).bind(
        'selectstart',
        function () {
          return false;
        });
    } else { // Opera, etc.
      $(this).mousedown(function () {
        return false;
      });
    }
  });
}

c = console; // Short alias.

/**
 * @todo Complete docblocking.
 */

var $d = {
  /**#@+
   * @private
   */

  frame: top.frames ? top.frames['debeetleFrame'] : window,
  container: document.createElement('DIV'),
  panel: null,
  tab: null,
  tabs: null,
  captions: null,

  data: null,
  defaults: {},
  dictionary: null,

  state: {},
  launched: false,
  historyRecords: 0,

  /**#@-*/

  setDictionary: function (dictionary) {
    this.View.Locale.add(dictionary);
    this.dictionary = dictionary;
  },

  /**
   * Debeetle entry point
   *
   * @param  {object} data
   * @param  {object} tabs
   * @param  {object} captions
   * @return {void}
   * @todo   Process tabs argument
   */
  startup: function (data, tabs, captions) {
    this.tabs = tabs;
    this.data = data;
    this.captions = captions;

    // Load iframe related CSS-file into parent document {

    const
      parent = this.frame.parentNode.ownerDocument,
      link = document.createElement('LINK'),
      heads = $('head', parent);

    if (heads.length < 1) {
      parent.appendChild(document.createElement('HEAD'));
    }
    link.setAttribute('rel', 'stylesheet');
    link.setAttribute('type', 'text/css');
    link.setAttribute('media', 'screen');
    link.setAttribute(
      'href',
      document.location.href.replace(
        /source=frame&/,
        'source=asset&type=css&target=parent&'
      )
    );
    parent.getElementsByTagName('head')[0].appendChild(link);

    // } Load iframe related CSS-file into parent document
    // Load state {

    const launch = $.cookie(this.data.cookie.name);

    const state = localStorage.getItem(this.data.cookie.name);
    if (null !== state) {
      this.state = JSON.parse(state);
    }
    if (launch) {
      this.state.launch = 1;
    }
    Object.assign(this.defaults, this.data.defaults);
    for (let name of ['opacity', 'zoom']) {
      this.defaults[name] = this.defaults[name].properties.value;
    }
    this.historyRecords =
      this.data.history &&
      this.data.history.records &&
      this.data.history.records > 0
        ? this.data.history.records : 0;
    this.state = $.extend({}, this.defaults, this.state);

    // } Load state
    // Load panel template {

    const iframe = document.createElement('IFRAME');
    this.container.id = 'container';
    this.container.className = 'ui-widget-content';
    this.container.appendChild(iframe);
    document.body.appendChild(this.container);
    let src =
      this.data.path.script + '?source=template&type=html&skin=' +
      this.state.skin + '&theme=' + this.state.theme +
      '&v=' + data.version;
    if (this.data['developerMode']) {
      src += '&dev=1';
    }
    iframe.src = src;

    // } Load panel template
    // Initialize plugins {

    for (let plugin in $d.Plugins) {
      if ('function' === typeof ($d.Plugins[plugin].startup)) {
        $d.Plugins[plugin].startup();
      }
    }

    // } Initialize plugins
  },

  /**
   * ...
   *
   * @return {void}
   * @todo   Describe
   */
  postStartup: function () {
    let id, elements, i, j, elems;

    // Set locales from dictionary.
    for (i in this.dictionary) {
      $('.title-' + i).attr('title', this.dictionary[i]);
    }
    for (i in this.dictionary) {
      $('.locale-' + i).html(this.dictionary[i]);
    }

    // Set captions from dictionary/passed data.
    for (i in this.data) {
      elements = $('.title-' + i);
      for (j in elements) {
        const data = this.data[i];
        elements[j].innerHTML = data[0];
        if (typeof (data[1]) != 'undefined') {
          if (typeof (this.dictionary[data[1]]) != 'undefined') {
            elements[j].title =
              this.dictionary[data[1]] + ' ' + elements[j].title;
            elements[j].className += ' ' + data[1];
          }
          if (data[2]) {
            elements[j].title += ' ' + data[2];
          }
        }
      }
    }
    $('.visibleVersion').html($.parseHTML(this.data['visibleVersion']));
    if (this.data['developerMode']) {
      $('#developerMode').html($.parseHTML(this.dictionary['developerMode']));
    }

    // Cleanup memory
    this.dictionary = null;

    elements = {
      'dPanel': 'panel',
      'dTab': 'tab'
    };
    for (id in elements) {
      elems = $(`#${id}`);
      if (elems.length > 0) {
        this[elements[id]] = elems[0];
      } else {
        elems = $(`.${id}`);
        if (elems.length > 0) {
          this[elements[id]] = elems[0];
        }
      }
    }

    // Call plugins post startup.
    for (const plugin in $d.Plugins) {
      if ('function' === typeof ($d.Plugins[plugin].postStartup)) {
        $d.Plugins[plugin].postStartup();
      }
    }
  },

  clearHistory: function () {
    delete this.state.history;
    $('#history').parent().html('');
    $d.View.Container.displayHistory(false);
    $d.Panel.fixHeight($d.Panel.Tab.lastTab);
  },

  /**
   * @param {string} index
   * @param [value]
   * @param [checkFlag]
   */
  storeState: function (index, value, checkFlag) {
    if (checkFlag && !$d.Panel.storeState) {
      return;
    }
    if (value != null) {
      this.state[index] = value;
    } else {
      delete this.state[index];
    }
    const options = {
      path: this.data.cookie.path,
    };
    if (this.data.cookie.expires > 0) {
      options.expires = this.data.cookie.expires;
    }
    const state = this.getObjectDifference(this.defaults, this.state);
    delete state.launch;
    localStorage.setItem(this.data.cookie.name, JSON.stringify(state));
    if (!this.state.launch) {
      options.expires = -1;
    }
    $.cookie(this.data.cookie.name, parseInt(this.state.launch), options);
  },

  keys: function (object) {
    const keys = [];
    for (const key in object) {
      const type = typeof (object[key]);
      if (('undefined' !== type) && ('function' !== type)) {
        keys.push(key);
      }
    }
    return keys;
  },

  getObjectDifference: function (prev, now) {
    const isArray = now instanceof Array, changes = isArray ? [] : {};
    let prop, pc;
    for (prop in now) {
      if (!prev || (prev[prop] !== now[prop])) {
        if ('object' === typeof (now[prop])) {
          pc = this.getObjectDifference(prev ? prev[prop] : (isArray ? [] : {}), now[prop]);
          if ($d.keys(pc).length > 0) {
            changes[prop] = pc;
          }
        } else {
          changes[prop] = now[prop];
        }
      }
    }
    return changes;
  }
}

$d.Plugins =
  {}

$d.View =
  {
    templates: {},

    /**
     * Loads templates from element.
     *
     * @param  {HTMLElement} element
     * @param  {string=}     mode 'merge'|'add'|'set'
     * @return {void}
     */
    load: function (element, mode/* = 'merge'*/) {
      if ('undefined' === typeof (mode)) {
        mode = 'merge';
      }
      if ('set' === mode) {
        this.templates = {};
      }
      const
        templates =
          element
            ? $('.template', element)
            : [],
        merge = 'add' !== mode;
      for (let i = 0, q = templates.length; i < q; i++) {
        const template = templates[i];
        if (
          template.id && (
            merge ||
            typeof (this.templates[template.id]) == 'undefined'
          )
        ) {
          this.templates[template.id] =
            template.innerHTML.replace(/^\s*<!--\s*|\s*-->\s*$/g, '');
        }
      }
    },

    /**
     * Parses template
     *
     * @param  {string} id
     * @param  {object} [scope]
     * @return {string|null}
     */
    parse: function (id, scope) {
      if ('undefined' === typeof (this.templates[id])) {
        return null;
      }
      if ('undefined' === typeof (scope)) {
        scope = {};
      }
      let template = this.templates[id];
      for (let key in scope) {
        template = template.replace('{$' + key + '}', scope[key]);
      }
      return template;
    }
  }

$d.View.Locale =
  {
    /**
     * @private
     */
    dictionary: {},

    /**
     * @param  {object} dictionary
     * @param  {string=} mode        'merge'|'add'|'set'
     * @return {void}
     */
    add: function (dictionary, mode/* = 'merge'*/) {
      if (typeof (mode) == 'undefined') {
        mode = 'merge';
      }
      if ('set' === mode) {
        this.dictionary = dictionary;
      } else {
        const merge = 'merge' === mode;
        for (const key in dictionary) {
          if (merge || typeof (this.dictionary[key]) == 'undefined') {
            this.dictionary[key] = dictionary[key];
          }
        }
      }
    },

    /**
     * @param  {string} key
     * @param  {object} [args]
     * @return {string|null}
     */
    get: function (key, args) {
      let caption = null;
      if (typeof (this.dictionary[key])) {
        caption = this.dictionary[key];
        if ('object' === typeof (args)) {
          for (let name in args) {
            caption = caption.replace('{$' + name + '}', args[name]);
          }
        }
      }
      return caption;
    }
  }

$d.View.Container =
  {
    maxClientHeight: 0,

    /**
     * Display container: bar/panel
     *
     * @return {void}
     */
    display: function () {
      $d.container.innerHTML =
        $d.View.parse('bar') +
        $d.View.parse('panel', { tabs: this.getTab(1, '1', $d.tabs) });
      const tabIds = $('[tabid]', $d.panel);
      tabIds.each(function (index, element) {
        const caption = $(element).attr('tabid').split('|').pop();
        element.innerHTML = element.innerHTML.replace(caption, $d.captions[caption]);
      });

      $d.postStartup();

      $('.info').children().on('click', function (e) {
        e.stopPropagation();
        return false;
      });

      this.clickEachFirstTab($d.tabs, '');

      if (!$d.state.visible) {
        $d.Panel.toggleVisibility($('.title-panelHide')[0]);
      }

      if ('undefined' !== typeof($d.state.top)) {
        $d.Panel.changePosition($('.title-barToBottom')[0]);
      }
      if ($d.state.launch) {
        $d.launched = true;
        const $info = $('.info', $d.container);
        $info.addClass('panel-visibility');
      } else {
        $d.Panel.toggleClientLaunch($('.title-panelOff')[0]);
        $('.title-panelHide').hide();
      }
      if ($d.state.hideHomepage) {
        $d.Panel.toggleHomepageLink($('span.title-hide')[0]);
      }

      if ($d.state.launch) {
        if ($d.state.tab) {
          // Set active stored last tab including all its parent tabs
          const lastTab = $d.state.tab.split('|'), parentTabs = [];
          for (let i in lastTab) {
            parentTabs.push(lastTab[i]);
            let elements =
              $('[tabid="' + parentTabs.join('|').replace('\\', '\\\\') + '"]', $d.panel);
            if (elements.length) {
              $d.Panel.Tab.click(elements[0], false);
            } else {
              break;
            }
          }
        } else if ($d.tabs.tabs) {
          // Mark active first available tab
          const elements = $('[tabid="' + Object.keys($d.tabs.tabs)[0] + '"]', $d.panel);
          if (elements.length) {
            $d.Panel.Tab.click(elements[0], false);
          }
        }
      }

      if ($d.launched) {
        this.displayHistory();
      }

      if (document.forms['settings']) {
        let skin = document.forms['settings'].elements['skin'];
        $.each(
          $d.data.skins,
          function (key, value) {
            $(skin).append($('<option>', {value: key}).text(value['name']));
          }
        );
        skin.value = $d.state.skin;
        $d.Panel.onSelectSkin(skin, $d.state.theme);
        $d.Panel.highlightSettings(skin.form, true);
        for (let name of ['opacity', 'zoom']) {
          const value = $d.state[name];
          $d.Panel.applyParameter(name);
          if (value) {
            $(`input[name="${name}"]`, $('form[name="settings"]')).val(
              'object' === typeof (value) ? value.properties.value : value
            );
            $d.Panel.applyParameter(name, value);
          }
        }
      }
      $d.Panel.storeState = true;

      $($d.container).visible();
      $($d.container).show();

      if (null !== $d.Panel.Tab.lastTab) {
        $d.Panel.fixHeight($d.Panel.Tab.lastTab);
      }
      // @todo Avoid hardcoded heights.
      $d.frame.style.height = ($d.container.clientHeight - $d.container.clientTop + 3) + 'px';

      $($d.frame).visible();

      $(function () {
        $(window.parent).on('resize', function () {
          if ($d.Panel.visible) {
            $d.Panel.fixHeight($d.Panel.Tab.lastTab);
          }
        });
        $('.selectionDisabled').disableTextSelect();
      });
    },

    displayHistory: function (unshift) {
      if ($d.historyRecords < 1) {
        return;
      }
      if ('undefined' === typeof unshift) {
        unshift = true;
      }

      let history = $d.state.history ? $d.state.history : [];
      if (unshift) {
        if ('[' === $d.data.history.storage.substring(0, 1)) {
          history.unshift(JSON.parse($d.data.history.storage));
        }
        history.unshift([
          $('span.title-serverTime').html(),
          top.parent.location.href,
          $d.data.requestMethod,
          $('span.title-pageTotalTime').html(),
          $('span.title-memoryUsage').html(),
          $('span.title-peakMemoryUsage').html(),
          $('span.title-includedFiles').html(),
        ]);
      }
      while (history.length > $d.historyRecords) {
        history.pop();
      }
      $d.storeState('history', history);
      const scope = {}, fields = [
        'serverTime',
        'url',
        'requestMethod',
        'pageTotalTime',
        'memoryUsage',
        'peakMemoryUsage',
        'includedFiles',
      ];
      for (let name of fields) {
        scope[name] = $d.View.Locale.get(name).replace(new RegExp(',$'), '');
      }
      scope['memoryUsageUnit'] = $d.data.memoryUsage[2];
      scope['peakMemoryUsageUnit'] = $d.data.peakMemoryUsage[2];
      $d.Panel.Tab.write('debeetle|history', $d.View.parse('tabHistoryTable', scope));
      $('button.locale-clearHistory').html($d.View.Locale.get('clearHistory'));
      for (let i in history) {
        scope.i = parseInt(i) + 1;
        const record = history[i];
        for (let i in fields) {
          scope[fields[i]] = record[i];
        }
        $('#history > tbody:last-child').append($d.View.parse('tabHistoryRow', scope));
      }
    },

    clickEachFirstTab: function (tab, parentPrefix) {
      if ('undefined' === typeof (tab.tabs)) {
        return;
      }
      parentPrefix = parentPrefix ? parentPrefix + '|' : '';
      const captions = $d.keys(tab.tabs);

      for (let i = 0, q = captions.length; i < q; i++) {
        this.clickEachFirstTab(
          tab.tabs[captions[i]],
          parentPrefix + captions[i]
        );
        this.maxClientHeight =
          Math.max(this.maxClientHeight, $d.container.clientHeight);
      }

      const elements = $('[tabid="' + (parentPrefix + captions[0]).replace('\\', '\\\\') + '"]', $d.panel);
      if (elements.length) {
        $d.Panel.Tab.click(elements[0], false);
      }
    },

    /**
     * Returns tab HTML-code
     *
     * @param  {int} level
     * @param  {string} postfix
     * @param  {object} tab
     * @param  {string=} parentPrefix
     * @return {string}
     */
    getTab: function (level, postfix, tab, parentPrefix) {
      if (!tab) {
        return '';
      }

      const tabId = parentPrefix ? parentPrefix : '';
      let content = '';
      parentPrefix = parentPrefix ? parentPrefix + '|' : '';

      if (typeof (tab.content) != 'undefined') {
        content += $d.View.parse(
          'tabContent',
          {
            level: level,
            postfix: postfix,
            active: tab.active ? ' a' : '',
            content: tab.content,
            tabId: tabId
          }
        );
      } else if (tab.tabs) {

        // tab controls {

        const captions = $d.keys(tab.tabs);
        let classes, controls = '';

        for (let i = 0, q = captions.length; i < q; i++) {
          const caption = captions[i];
          let active = tab.tabs[caption].active, activeTail = '';

          if (!tab.tabs[caption]) {
            continue;
          }

          classes = ['selectionDisabled'];
          if (!i) {
            classes.push('l');
          }
          if ((q - i) === 1) {
            classes.push('r');
          }
          if (active) {
            classes.push('a');
            activeTail = $d.View.parse(
              'tabControlTail',
              {
                caption: caption
              }
            );
          }
          controls += $d.View.parse(
            'tabControl',
            {
              classes: classes.join(' '),
              caption: caption,
              tabId: parentPrefix + caption,
              activeTail: activeTail
            }
          );
        }

        content += $d.View.parse(
          'tabList',
          {
            even: level % 2 ? '' : ' even',
            level: level,
            postfix: postfix,
            controls: controls,
            tabId: tabId
          }
        );

        // } tab controls

        if (1 === (level % 2)) {
          content += $d.View.parse('level1Opener');
        }

        let index = 0;
        for (const tabName in tab.tabs) {
          index++;
          content +=
            this.getTab(
              level,
              postfix + '_' + index,
              typeof (tab.tabs[tabName].content) != 'undefined'
                ? tab.tabs[tabName]
                : {
                  content:
                    this.getTab(
                      level + 1,
                      postfix + '_' + (level + 1) + '_' +
                      index,
                      tab.tabs[tabName],
                      parentPrefix + tabName
                    )
                },
              parentPrefix + tabName
            );
        }

        if ((level % 2) === 1) {
          content += $d.View.parse('level1Closer');
        }
      }

      return content;
    }
  }

$d.Panel =
  {
    launched: true,
    visible: true,
    storedVisible: null,
    storeState: false,
    onTop: true,

    tabSettingsClicksInterrupted: false,

    reload: function () {
      $d.frame.ownerDocument.location.reload();
    },

    /**
     * Change bar & panel top/bottom position
     *
     * @param  {HTMLElement} control
     * @todo   Use my jQuery.iEvt plugin to calc height & etc.
     */
    changePosition: function (control) {
      this.onTop = !this.onTop;

      if (this.onTop) {
        // put to the top
        control.title = $d.View.Locale.get('barToBottom');
        $(control).removeClass('onBottom');
        $(control).addClass('onTop');
        $($d.container).removeClass('onBottom');
        $($d.container).addClass('onTop');
        $($d.frame).removeClass('onBottom');
        $($d.frame).addClass('onTop');
      } else {
        // put to the bottom
        control.title = $d.View.Locale.get('barToTop');
        $(control).removeClass('onTop');
        $(control).addClass('onBottom');
        $($d.container).removeClass('onTop');
        $($d.container).addClass('onBottom');
        $($d.frame).removeClass('onTop');
        $($d.frame).addClass('onBottom');
      }
      $d.frame.style.height = ($d.container.clientHeight - $d.container.clientTop + this.getFrameHeightDiff()) + 'px';
      control.blur();
      $d.storeState('top', this.onTop ? null : 0, true);
    },

    /**
     * Toggle debugger launching.
     *
     * @param  {HTMLElement} control
     */
    toggleClientLaunch: function (control) {
      const
        infoCell = $('.info', $d.container)[0],
        opacity = $d.state.opacity || $d.defaults.opacity;

      control.title =
        $d.View.Locale.get(this.launched ? 'panelOn' : 'panelOff');
      // $('#developerMode').toggleClass('blink');
      $(infoCell).toggleClass('panel-visibility');

      if (this.launched) {
        this.storedVisible = this.visible;
        if (this.visible) {
          this.toggleVisibility(infoCell); // Hide panel.
        }
        $(infoCell.children[0]).hide();
        infoCell.title = '';
        $(infoCell).removeClass('panel-visibility');
      } else {
        if ($d.panel) {
          $(infoCell.children[0]).show();
          infoCell.title = $d.View.Locale.get('panelShow');
        }
        if (this.storedVisible) {
          this.launched = true;
          this.toggleVisibility(infoCell);
          this.launched = false;
        }
      }
      this.launched = !this.launched;
      $d.frame.style.opacity = this.launched ? opacity : $d.defaults.disabledPanelOpacity;
      control.blur();
      if (this.storeState) {
        $d.storeState('launch', this.launched ? 1 : null);
        if (!$d.launched) {
          $('.locale-reloadPage').toggle();
        }
      }
    },

    /**
     * Toggle homeoage link visibility.
     *
     * @param  {HTMLElement} control
     */
    toggleHomepageLink: function (control) {
      if (this.storeState) {
        $d.state.hideHomepage = !$d.state.hideHomepage;
        $d.storeState('hideHomepage', $d.state.hideHomepage ? 1 : null);
      }
      control.title = $d.View.Locale.get($d.state.hideHomepage ? 'show' : 'hide');
      control.innerHTML = $d.state.hideHomepage ? '&laquo;&laquo;' : '&raquo;&raquo;';
      const $a = $('a.title-visitHomepage');
      $d.state.hideHomepage ? $a.hide() : $a.show();
      control.blur();
    },

    /**
     * Toggle panel visibility.
     *
     * @param  {HTMLElement} cell
     * @return void
     */
    toggleVisibility: function (cell) {
      if (!this.launched || !$d.panel) {
        return;
      }

      this.visible ? $($d.panel).hide() : $($d.panel).show();
      if (this.visible) {
        // hide
        cell.title = $d.View.Locale.get('panelShow');
        $d.container.style.height = 'auto';
      } else {
        // show
        cell.title = $d.View.Locale.get('panelHide');
      }

      // $('#resize-line').toggle();

      // @todo Avoid hardcoded heights.
      $d.frame.style.height = ($d.container.clientHeight - $d.container.clientTop + 3) + 'px';

      this.visible = !this.visible;

      if (this.visible) {
        this.fixHeight($d.Panel.Tab.lastTab);
      }

      $d.storeState('visible', this.visible ? 1 : null, true);
    },

    onSelectSkin: function (skin, forceTheme) {
      const theme = skin.form.elements['theme'];
      theme.length = 0;
      $.each(
        $d.data.skins[skin.value]['themes'],
        function (key, value) {
          $(theme).append($('<option>', {value: key}).text(value));
        }
      );
      if (forceTheme) {
        theme.value = forceTheme;
      }
      $(theme.options[theme.selectedIndex]).addClass('selected');
      this.onSelectTheme(theme);
    },

    onSelectTheme: function (select) {
      if (!$d.Panel.storeState) {
        return;
      }

      // Remove less CSS.
      // $('style', $('head')).remove();

      const
        head = document.getElementsByTagName('head')[0],
        ts = `&ts=${Date.now()}`,
        skin = select.form.elements['skin'].value,
        theme = select.value,
        dev = document.location.search.indexOf('&dev=1') < 0 ? '' : '&dev=1';

      let node = document.createElement('SCRIPT');
      node.setAttribute(
        'src',
        $d.data.path.script + '?source=asset&type=lessJs&skin=' + skin + '&theme=' +
        $d.data.skins[skin].defaultTheme + '&noskin=1' + '&v=' + $d.data.version + '&h=' + $d.data.hash + ts + dev
      );
      node.async = false;
      head.appendChild(node);
      // $(node).remove();
      node = document.createElement('SCRIPT');
      node.setAttribute(
        'src',
        $d.data.path.script + '?source=asset&type=lessJs&skin=' + skin + '&theme=' + theme + '&noskin=1' +
        '&v=' + $d.data.version + '&h=' + $d.data.hash + ts + dev
      );
      node.async = false;
      head.appendChild(node);
    },

    highlightSettings: function (form) {
      const selectors = ['skin', 'theme'];

      for (let i in selectors) {
        const select = form.elements[selectors[i]];
        for (let j = 0, q = select.options.length; j < q; j++) {
          if (j !== select.selectedIndex) {
            $(select.options[j]).removeClass('selected');
          } else {
            $(select.options[j]).addClass('selected');
          }
        }
      }
    },

    /**
     * @param {string} name
     * @param {string} [value]
     */
    applyParameter: function (name, value) {
      const
        config = $d.data.defaults[name],
        $option = $(`input[name="${name}"]`, $('form[name="settings"]'));

      if (undefined === value) {
        value = config.properties.value;
        for (let property in config.properties) {
          $option.prop(property, config.properties[property]);
        }
      }
      if ('string' === typeof (config.selector)) {
        config.selector = [config.selector];
      }
      for (let selector of config.selector) {
        if (selector.indexOf('~') === 0) {
          selector = eval(selector.substring(1));
        }
        $(selector).css(name, value);
      }
      this.fixHeight($d.Panel.Tab.lastTab);
    },

    /**
     * @param {HTMLElement} element
     * @return {boolean}
     */
    validateParameter: function (element) {
      const config = $d.data.defaults[element.name];
      let value = element.value;

      if (config.parse) {
        switch (config.parse) {
          case 'int':
            value = parseInt(value);
            break;
          case 'float':
            value = parseFloat(value.replace(",", "."));
            break;
        }
        if (isNaN(value)) {
          element.focus();
          return false;
        }
      }
      $d.state[element.name] = value;
      if ("undefined" !== typeof config.applyOnChange && config.applyOnChange) {
        this.applyParameter(element.name, value);
      }

      return true;
    },

    saveSettings: function (button) {
      const
        form = button.form,
        elements = form.elements;

      switch (form.name) {
        case 'settings':
          $d.storeState('skin', elements['skin'].value, true);
          $d.storeState('theme', elements['theme'].value, true);
          for (let name of ['opacity', 'zoom']) {
            $d.Panel.applyParameter(name, elements[name].value);
            $d.storeState(name, elements[name].value, true);
          }
          this.highlightSettings(form);
          break; // case 'settings'
      }

      return false;
    },

    resetSettings: function (button) {
      const form = button.form;

      switch (form.name) {
        case 'settings':
          const buttons = ['skin', 'theme', 'opacity'];
          for (const i in buttons) {
            button.form.elements[buttons[i]].value =
              $d.state[buttons[i]];
            }
          this.onSelectSkin(button.form.elements['skin']);
          this.highlightSettings(button.form);
          break; // case 'settings'

        case 'tabSettings':
          $('input[type="checkbox"]', form).each(
            function () {
              $(this).prop(
                'checked',
                $(this).attr('source-checked') > 0
              );
              $(this).prop(
                'disabled',
                $(this).attr('source-disabled') > 0
              );
            }
          );
          alert('Reload page to view changes.');
          break; // case 'tabSettings'
      }

      return false;
    },

    /**
     * Limits panel max height
     *
     * @return {void}
     */
    fixHeight: function (tabId) {
      if (null === tabId) {
        if ($d.data['developerMode']) {
          console.warn('$d.Panel.fixHeight(): missed tab id');
        }
        return;
      }
      const $source = $(`div[ptabid="${tabId.replace('\\', '\\\\')}"]`);

      let offset = (this._getOffset($source[0])).top; /// @todo Avoid?
      offset = offset + parseInt(
        $(document.body).css('border-top-width').replace(/[a-z]+$/, '')
      );

      const maxHeight =
        Math.floor(
          (window.parent.innerHeight - offset) * $d.defaults.maxPanelHeight
        ) + 'px';
      $('div.tab').css('max-height', 'none');
      $source.css({
        'max-height': maxHeight,
        'height': 'auto',
      });
      $d.frame.style.height =
        ($d.container.clientHeight - $d.container.clientTop + $d.Panel.getFrameHeightDiff()) + 'px';
    },

    _getOffset: function (element) {
      const rect = element.getBoundingClientRect();
      return {
        left: rect.left + window.scrollX,
        top: rect.top + window.scrollY
      };
    },
  }

$d.Panel.Tab =
  {
    lastTab: null,

    /**
     * @param {HTMLElement} sourceControl
     * @param {boolean} [storeState] true by default
     */
    click: function (sourceControl, storeState) {
      const tabId = $(sourceControl).attr('tabid');

      if ('undefined' === typeof (storeState)) {
        storeState = true;
      }

      if ((tabId === this.lastTab) || (storeState && $(sourceControl).hasClass('a'))) {
        // Same or active tab, no action
        return false;
      }

      const
        tabChildren = $('[ptabid="' + tabId.replace('\\', '\\\\') + '"]', $d.panel),
        caption = sourceControl.children[0].innerHTML;
      let lastTabId = tabId;

      // If last tab isn't parent of current tab hide previous tabs
      if (this.lastTab && tabId.indexOf(this.lastTab + '|') !== 0) {
        // .hide() doesn't work !!!
        $('[ptabid="' + this.lastTab.replace('\\', '\\\\') + '"]', $d.panel).hide();
      }

      // Set activity class and bold caption
      $(sourceControl)
        .addClass('a')
        .append('<div><div>' + caption + '</div>');

      // All same level tabs loop
      $(sourceControl).parent().children().each(function () {
        const child = $(this);

        if (child.attr('tabid') !== tabId) {
          // Hide tab
          $('[ptabid="' + child.attr('tabid').replace('\\', '\\\\') + '"]', $d.panel).hide();
          // Normalize caption
          child.html(child.html().replace(/<div>.*/ig, ''));
          child.removeClass('a');
        }
      });

      // Click all active subtabs and choose the deepest active one
      $('li.a', tabChildren).each(function () {
        const activeTabId = $(this).attr('tabid');
        $d.Panel.Tab.lastTab = null;
        $d.Panel.Tab.click(this, false);
        if (activeTabId.indexOf(lastTabId) === 0) {
          lastTabId = activeTabId;
        }
      });

      tabChildren.show();

      this.lastTab = lastTabId;
      if (storeState) {
        $d.Panel.fixHeight(lastTabId);
        $d.storeState('tab', lastTabId, true);
      }

      return false;
    },

    /**
     * @param {string} tabId
     * @param {string} string
     */
    write: function (tabId, string) {
      const $tab = $(`div[ptabid="${tabId.replace('\\', '\\\\')}"]`);
      $tab.html($tab.html() + string);
    }
  }
