<?php

require_once __DIR__ . "/stub.php";

/**
 * @var array $settings
 */

?>
<!DOCTYPE html>
<html lang="<?= $settings['defaults']['language'] ?>">
<head>
  <title>Debeetle panel</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <script>

    function loadJs() {
      const node = document.createElement('SCRIPT');
      node.setAttribute('src', '<?= $settings['path']['script'] ?>?source=asset&type=js&' + args);
      node.async = false;
      node.defer = true;
      head.appendChild(node);
    }

    const
      args = document.location.href.replace(/^.*\?/, '').replace(/^source=frame&/, ''),
      defaultTheme = '<?= $settings['skin'][$settings['defaults']['skin']]['defaultTheme'] ?>',
      defaultThemeArgs = args.replace(/&theme=[^&]+/, '&theme=' + defaultTheme),
      head = document.getElementsByTagName('head')[0];
    let
      theme = '<?= $settings['defaults']['theme'] ?>',
      state = localStorage.getItem('<?= $settings['cookie']['name'] ?>'),
      node;

    if (null !== state) {
      state = JSON.parse(state);
      if ("undefined" !== typeof (state.theme)) {
        theme = state.theme;
      }
    }

    const notDefaultTheme = theme !== defaultTheme;

    if (notDefaultTheme) {
      node = document.createElement('LINK');
      node.setAttribute('rel', 'stylesheet/less');
      node.setAttribute('type', 'text/css');
      node.setAttribute('media', 'screen');
      node.setAttribute('href', '<?= $settings['path']['script'] ?>?source=asset&type=less&' + defaultThemeArgs);
      node.async = false;
      head.appendChild(node);
    }
    node = document.createElement('LINK');
    node.setAttribute('rel', 'stylesheet/less');
    node.setAttribute('type', 'text/css');
    node.setAttribute('media', 'screen');
    node.setAttribute('href', '<?= $settings['path']['script'] ?>?source=asset&type=less&' + args);
    node.async = false;
    head.appendChild(node);

    if (notDefaultTheme) {
      node = document.createElement('SCRIPT')
      node.setAttribute('src', '<?= $settings['path']['script'] ?>?source=asset&type=lessJs&' + defaultThemeArgs);
      node.async = false;
      node.defer = true;
      head.appendChild(node);
      loadJs();
      node = document.createElement('SCRIPT')
      node.setAttribute('src', '<?= $settings['path']['script'] ?>?source=asset&type=lessJs&noskin=1&' + args);
      node.async = false;
      node.defer = true;
      head.appendChild(node);
    } else {
      node = document.createElement('SCRIPT')
      node.setAttribute('src', '<?= $settings['path']['script'] ?>?source=asset&type=lessJs&' + args);
      node.async = false;
      node.defer = true;
      head.appendChild(node);
      loadJs();
    }
  </script>
</head>
<body onload="parent.$d.onload(this.window);"></body>
</html>
