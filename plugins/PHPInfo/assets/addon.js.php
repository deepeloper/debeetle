<?php

if (empty($this)) {
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.0";
    header("$protocol 404 Not Found");
    die;
}

/**
 * @var array $settings
 * @var array $struct
 */
?>
$d.Plugins.PHPInfo = {
  postStartup: function()
  {
    c.log($d.data);///
    $('body').append(
      '<a id="id_phpinfo" style="display: none;" href="' +
      '<?= $settings['path']['script'] ?>?plugin=<?= rawurlencode($struct['plugin']['id']) ?>' +
      `&v=${$d.data.version}&h=${$d.data.hash}" target="_blank"></a>`
    );

    const $phpVersion = $('.title-phpVersion');
    $phpVersion.css('fontWeight', 'bold');
    $phpVersion.attr('title', $phpVersion.attr('title') + $d.View.Locale.get('clickToViewPhpInfo'));
    $phpVersion.on('click', function() {
      $('#id_phpinfo')[0].click();
      return false;
    });
  }
}
