<?php
/**
 * Debeetle PHP debug
 *
 * Debeetle initialization code template.
 *
 * @copyright Copyright (c) XXI deepelopment.com (http://deepelopment.com/)
 * @author    Anton Leontiev (http://deepelopment.com/weregod)
 * @category  PHP_Debug
 * @package   Debeetle
 * @see       HTML::get()
 */

require_once __DIR__ . "/stub.php";

/**
 * @var array $scope
 * @var array $data
 * @var string $tabs
 * @var string $captions
 */

?>
<script type="text/javascript">
debeetleFrame = document.createElement('IFRAME');
debeetleFrame.id = 'debeetleFrame';
debeetleFrame.name = 'debeetleFrame';
<?php
if (empty($this->settings['developerMode'])):
?>
debeetleFrame.style.visibility = 'hidden';
<?php
endif;
/*
debeetleFrame.style.position = 'fixed';
debeetleFrame.style.top = 0;
debeetleFrame.style.left = 0;
debeetleFrame.style.width = '100%';
debeetleFrame.style.height = '100%';
debeetleFrame.style.border = 'none';
debeetleFrame.style.margin = 0;
debeetleFrame.style.padding = 0;
debeetleFrame.style.background = 'transparent';
debeetleFrame.style.zIndex = '19770404';
*/
?>
$d = {
  onload: function(iframe)
  {
    // console.log('iframe loaded');///
    $(function() {
      //console.log('timeout set to <?php //= $data['delayBeforeShowInBrowser'] ?>//');///
      setTimeout(
        function() {
          // console.log('running...');///
          iframe.$d.startup(
              <?= json_encode($data) ?>,
              <?= $tabs ?>,
              <?= $captions ?>
          );
        },
        <?= $data['delayBeforeShowInBrowser'] ?>
      )
    });
  }
}

let skin = '<?= $scope['skin'] ?>', theme = '<?= $scope['theme'] ?>', state = localStorage.getItem('<?= $data['cookie']['name'] ?>');
if (null !== state) {
  state = JSON.parse(state);
  if ("undefined" !== typeof(state.skin)) {
    skin = state.skin;
  }
  if ("undefined" !== typeof(state.theme)) {
    theme = state.theme;
  }
}
debeetleFrame.src = '<?= $this->settings['path']['script'] ?>?source=frame&skin=' + skin + '&theme=' + theme + '&v=<?= $data['version'] ?>&h=<?php

echo $data['hash'];
if (!empty($this->settings['developerMode'])) {
    echo "&dev=1";
}
?>';
document.body.appendChild(debeetleFrame);
</script>
