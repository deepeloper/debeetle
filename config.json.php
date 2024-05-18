/* <?php __halt_compiler(); die; ?> */
{
  "launch": true,
  "config": {
    "common": {
      "use": true,
      "cookie": {
        "name": "debeetle",
        "path": "/",
        "expires": 0 /* In seconds, 0 means during session */
      },
      "path": {
        "assets": "E:/repos/deepeloper/debeetle/assets",
        "script": "/debeetle.php",
        "root": "E:/repos/deepeloper/debeetle/public" /* Optional, root path used to cut it from trace paths */
      },
      "bench": {
        "serverTime": {
          "format": "Y-m-d H:i:s P"
        },
        "pageTotalTime": {
          "format": "%.03f",
          "warning": 0.7,
          "critical": 1,
          "exclude": "scriptInit,debeetle",
        },
        "memoryUsage": {
          "format": "%.02f",
          "warning": 10,
          "critical": 15,
          "divider": 1048576,
          "unit": "MB",
          "exclude": "scriptInit,debeetle",
        },
        "peakMemoryUsage": {
          "format": "%.02f",
          "warning": 30,
          "critical": 60,
          "divider": 1048576,
          "unit": "MB",
          "exclude": "scriptInit,debeetle",
        },
        "includedFiles": {
          "warning": 100,
          "critical": 120,
          "exclude": "debeetle",
        }
      },
      "defaults": {
        "language": "en",
        "disabledPanelOpacity": 0.7,
        "maxPanelHeight": 75, /* in percents of current window */
        "skin": "ByDefault",
        "theme": "ByDefault",
        "opacity": {
          "applyOnChange": true,
          "properties": {
            "type": "number",
            "min": 0.3,
            "max": 1,
            "step": 0.05,
            "parse": "float",
            "value": 0.95,
          },
          "selector": [
            "~$d.frame",
          ]
        },
        "zoom": {
          "properties": {
            "type": "number",
            "min": 0.5,
            "max": 3,
            "step": 0.05,
            "parse": "float",
            "value": 1,
          },
          "selector": [
            "div.bar",
            "#dPanel",
          ]
        },
        "options": {
          "write": {
            "encoding": "windows-1251",
            "htmlEntities": true,
            "nlToBr": true,
          }
        }
      },
      "history": {
        "use": true,
        "records": 20,
        "name": "history",
        "storage": "session", /* session|cookie */
      },
/*
      "disabled": {
        "tab": [
          "debeetle|about",
        ],
      },
*/
      "skin": {
        "deepeloper_default": {
          "use": true,
          "class": "deepeloper\\Debeetle\\Skin\\ByDefault\\Controller",
          "name": {
            "en": "Default",
            "ru": "Умолчанец",
          },
          "assets": {
            "template": "skin.html",
            "js": "addon.js.php",
            "lessJs": "skin.less.js.php",
            "less": "skin.less",
          },
          "defaultTheme": "deepeloper_default_default",
          "theme": {
            "deepeloper_default_default": {
              "use": true,
              "class": "deepeloper\\Debeetle\\Skin\\ByDefault\\Theme\\ByDefault\\Controller",
              "name": {
                "en": "Default",
                "ru": "Стандартная",
              },
              "assets": {
                "lessJs": "theme.less.js.php",
                "less": "theme.less",
              },
            },
            "deepeloper_default_green": {
              "use": false,
              "class": "deepeloper\\Debeetle\\Skin\\ByDefault\\Theme\\Green\\Controller",
              "name": {
                "en": "Green",
                "ru": "Зелёная",
              },
              "assets": {
                "lessJs": "theme.less.js.php",
                "less": "theme.less",
              }
            }
          }
        }
      },
      "plugin": {
        "deepeloper_phpinfo": {
          "use": true,
          "locale": true,
          "class": "deepeloper\\Debeetle\\Plugin\\PHPInfo\\Controller",
          "assets": {
            "js": "addon.js.php",
          },
        },
        "deepeloper_traceanddump": {
          "use": true,
          "locale": true,
          "class": "deepeloper\\Debeetle\\Plugin\\TraceAndDump\\Controller",
          "assets": {
            "js": "addon.js.php",
/*
            "lessJs": "styles.less.js",
*/
            "less": "styles.less",
          },
          "method": {
            "dump": {
              "maxStringLength": 200,
              "maxNesting": 0,
              "maxCount": 0,
              "expand": true,
              "expandEntities": true,
            },
            "trace": {
              "expand": true,
              "displayArgs": true,
              "expandArgs": true,
            },
          },
        },

        "deepeloper_reports": {
          "use": true,
          "locale": true,
          "class": "deepeloper\\Debeetle\\Plugin\\Reports\\Controller",
          "assets": {
            "less": "styles.less",
          },
          "method": {
            "errorHandler": {
              "tabId": "reports",
              "place": [
                "after:environment",
                "anywhere"
              ],
              "separateTabs": false,

              /* PHP 5.4 E_ALL: 32767 */
              "errorReporting": 32767,
              "errorLevels": 32767,
              /* date, report counter, level, message, file, line, backtrace */
              "template": "<div class=\"reports\">\n            <div class=\"message\">\n              <span class=\"date\">[ %s ]</span>\n              <span class=\"counter\">#%03d</span>\n              <span class=\"level level_%s\">[ %s ]\n                <span class=\"message\">%s at</span>\n                <span class=\"file\">%s</span>\n                <span class=\"line\">(%d)</span>\n              </span>\n            </div>\n            <code>%s</code>\n          </div>",
              "callPrevious": false
            },
          },
        }
      },
    },
    "deepeloper_local": {
      "use": true,
      "limit": [
        {
          "source": "SERVER",
          "key": "REMOTE_ADDR",
          "value": "127.0.0.1",
        },
      ],
    },
  },
}
