<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

return [
    'barToBottom'   => "Переместить панель вниз",
    'barToTop'      => "Переместить панель вверх",
    'panelOff'      => "Отключить панель",
    'panelOn'       => "Включить панель",
    'panelShow'     => "Нажмите, чтобы отобразить панель",
    'panelHide'     => "Нажмите, чтобы скрыть панель",
    'visitHomepage' => "Посетить домашнюю страницу проекта",
    'reloadPage'    => "<a href=\"//reload\" onclick=\"\$d.Panel.reload();\">Обновите</a> страницу, чтобы увидеть отладочную выдачу.",

    'serverTime'      => "Время сервера",
    'phpVersion'      => "Версия PHP",
    'pageTotalTime'   => "Полное время генерации страницы, сек.",
    'memoryUsage'     => "Использование памяти,",
    'peakMemoryUsage' => "Максимальное использование памяти,",
    'includedFiles'   => "Количество подключенных файлов",
    'warning'         => "[Предупреждение]",
    'critical'        => "[Критично]",
    'hide'            => "Скрыть",
    'show'            => "Отобразить",
    'url'             => "URL",
    'requestMethod'   => "Метод запроса",
    'clearHistory'    => "Очистить историю",
    'developerModeTooltip' => "Режим разработчика",

    // Tabs.
    'tab-includedFiles' => "Загруженные файлы",
    'tab-history' => "История",
    'tab-settings' => "Настройки",
    'tab-panel' => "Панель",
    'tab-about' => "О программе",
    'tab-loadedConfig' => "Загруженная конфигурация",

    // "debeetle|resourceUsage" tab.
    'tab-resourceUsage' => "Использование ресурсов",
    'thInitializing' => "Инициализация",
    'thScript' => "Скрипт",
    'thOverall' => "Всего",
    'tdTime' => "Время, сек.",
    'tdMemoryUsage' => "Использование памяти, байтов",
    'tdPeakMemoryUsage' => "Пиковое использование памяти, байтов",
    'tdIncludedFiles' => "Подключенные файлы",

    // "debeetle|settings" tab.
    'skin' => "Скин:",
    'colorTheme' => "Цветовая схема:",
    'panelOpacity' => "Прозрачность панели:",
    'panelZoom' => "Увеличение панели:",
    'apply' => "Применить",
    'discard' => "Отменить",
    'defaults' => "По умолчанию",

    'tab-plugins' => "Плагины",
] + require __DIR__ . "/en.php";
