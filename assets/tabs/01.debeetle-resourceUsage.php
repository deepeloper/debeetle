<?php

use deepeloper\Debeetle\d;

require_once __DIR__ . "/../stub.php";

/**
 * @var string $tab
 * @var array $locales
 */
d::t("debeetle|resourceUsage");
$bench = d::getInstance()->getInternalBenches();

?>
<table class="stats">
  <thead>
  <tr>
    <th>&nbsp;</th>
    <th><?= $locales['thInitializing'] ?></th>
    <th><?= $locales['thScript'] ?></th>
    <th><?= $locales['thDebbetle'] ?></th>
    <th><?= $locales['thTotal'] ?></th>
  </tr>
  </thead>
  <tbody>
    <?php
    $total = microtime(true) - $bench['initState']['time'];
    $debeetle = $bench['total']['time'] - $bench['onLoad']['time'];
    $script = $total - $bench['total']['time'];
    ?>
    <tr>
      <td><?= $locales['tdTime'] ?></td>
      <td><?=
        number_format($bench['onLoad']['time'], 5, ".", "'"), " (",
        number_format($bench['onLoad']['time'] * 100 / $total, 2, ".", "")
      ?>%)</td>
      <td><?=
        number_format($script, 5, ".", "'"), " (",
        number_format($script * 100 / $total, 2, ".", "")
      ?>%)</td>
      <td><?=
        number_format($debeetle, 5, ".", "'"), " (",
        number_format($debeetle * 100 / $total, 2, ".", "")
      ?>%)</td>
      <td><?= number_format($total, 5, ".", "'") ?></td>
    </tr>
    <?php
    $total = memory_get_usage();
    $debeetle = $bench['total']['memoryUsage'] - $bench['onLoad']['memoryUsage'];
    $script = $total - $bench['total']['memoryUsage'];
    ?>
    <tr>
      <td><?= $locales['tdMemoryUsage'] ?></td>
      <td><?=
        number_format($bench['onLoad']['memoryUsage'], 0, "", "'"), " (",
        number_format($bench['onLoad']['memoryUsage'] * 100 / $total, 2, ".", "")
      ?>%)</td>
      <td><?=
        number_format($script, 0, "", "'"), " (",
        number_format($script * 100 / $total, 2, ".", "")
      ?>%)</td>
      <td><?=
        number_format($debeetle, 0, "", "'"), " (",
        number_format($debeetle * 100 / $total, 2, ".", "")
      ?>%)</td>
      <td><?= number_format($total, 0, "", "'") ?></td>
    </tr>
    <?php
      $total = memory_get_peak_usage();
      $debeetle = $bench['total']['peakMemoryUsage'] - $bench['onLoad']['peakMemoryUsage'];
      $script = $total - $bench['total']['peakMemoryUsage'];
      ?>
      <tr>
        <td><?= $locales['tdPeakMemoryUsage'] ?></td>
        <td><?=
          number_format($bench['onLoad']['peakMemoryUsage'], 0, "", "'"), " (",
          number_format($bench['onLoad']['peakMemoryUsage'] * 100 / $total, 2, ".", "")
        ?>%)</td>
        <td><?=
          number_format($script, 0, "", "'"), " (",
          number_format($script * 100 / $total, 2, ".", "")
        ?>%)</td>
        <td><?=
          number_format($debeetle, 0, "", "'"), " (",
          number_format($debeetle * 100 / $total, 2, ".", "")
        ?>%)</td>
        <td><?= number_format($total, 0, "", "'") ?></td>
      </tr>
    <?php
    $total = sizeof(get_included_files());
    $script = sizeof(d::getInstance()->getExternalIncludedFiles());
    $debeetle = $total - $script - $bench['onLoad']['includedFiles'];
    ?>
    <tr>
      <td><?= $locales['tdIncludedFiles'] ?></td>
      <td><?=
        $bench['onLoad']['includedFiles'], " (",
        number_format($bench['onLoad']['includedFiles'] * 100 / $total, 2, ".", "")
      ?>%)</td>
      <td><?=
        $script, " (",
        number_format($script * 100 / $total, 2, ".", "")
      ?>%)</td>
      <td><?=
        $debeetle, " (",
        number_format($debeetle * 100 / $total, 2, ".", "")
      ?>%)</td>
      <td><?= $total?></td>
    </tr>
    </tbody>
</table>
