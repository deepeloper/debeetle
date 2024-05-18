<?php

use deepeloper\Debeetle\d;

require_once __DIR__ . "/../stub.php";

/**
 * @var string $tab
 */
d::t("debeetle|resourceUsage");
$bench = d::getInstance()->getInternalBenches();

?>
<table class="stats">
    <thead>
    <tr>
        <th rowspan="2">&nbsp;</th>
        <th colspan="3">Debeetle</th>
        <th rowspan="2">Script</th>
        <th rowspan="2">Overall</th>
    </tr>
    <tr>
        <th>initializing</th>
        <th>debugging</th>
        <th>total</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>methods called&nbsp;</td>
        <td>&nbsp;</td>
        <td><?= $bench['total']['qty'] ?></td>
        <td>&nbsp;</td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <?php
    $total = microtime(true) - $bench['scriptInitState']['time'];
    $debeetle = $bench['total']['time'] - $bench['onLoad']['time'];
    $script = $total - $bench['total']['time'];
    ?>
    <tr>
        <td>time, sec.&nbsp;</td>
        <td><?= number_format($bench['onLoad']['time'], 5, '.', ''), ' / ', number_format($bench['onLoad']['time'] * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= number_format($debeetle, 5, '.', ''), ' / ', number_format($debeetle * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= number_format($bench['total']['time'], 5, '.', ''), ' / ', number_format($bench['total']['time'] * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= number_format($script, 5, '.', ''), ' / ', number_format($script * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= number_format($total, 5, '.', '')?></td>
    </tr>
    <?php
    $total = memory_get_usage();
    $debeetle = $bench['total']['memoryUsage'] - $bench['onLoad']['memoryUsage'];
    $script = $total - $bench['total']['memoryUsage'];
    ?>
    <tr>
        <td>memory usage difference, bytes&nbsp;</td>
        <td><?= $bench['onLoad']['memoryUsage'], ' / ', number_format($bench['onLoad']['memoryUsage'] * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= $debeetle, ' / ', number_format($debeetle * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= $bench['total']['memoryUsage'], ' / ', number_format($bench['total']['memoryUsage'] * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= $script, ' / ', number_format($script * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= $total?></td>
    </tr>
    <?php if (function_exists('memory_get_peak_usage')):
        $total = memory_get_peak_usage();
        $debeetle = $bench['total']['peakMemoryUsage'] - $bench['onLoad']['peakMemoryUsage'];
        $script = $total - $bench['total']['peakMemoryUsage'];
        ?>
        <tr>
            <td>peak memory usage difference, bytes&nbsp;</td>
            <td><?= $bench['onLoad']['peakMemoryUsage'], ' / ', number_format($bench['onLoad']['peakMemoryUsage'] * 100 / $total, 2, '.', ''), '%'?></td>
            <td><?= $debeetle, ' / ', number_format($debeetle * 100 / $total, 2, '.', ''), '%'?></td>
            <td><?= $bench['total']['peakMemoryUsage'], ' / ', number_format($bench['total']['peakMemoryUsage'] * 100 / $total, 2, '.', ''), '%'?></td>
            <td><?= $script, ' / ', number_format($script * 100 / $total, 2, '.', ''), '%'?></td>
            <td><?= $total?></td>
        </tr>
    <?php endif?>
    <?php
    $total = sizeof(get_included_files());
    $debeetle = $bench['total']['includedFiles'] - $bench['onLoad']['includedFiles'];
    $script = $total - $bench['total']['includedFiles'];
    ?>
    <tr>
        <td>included files&nbsp;</td>
        <td><?= $bench['onLoad']['includedFiles'], ' / ', number_format($bench['onLoad']['includedFiles'] * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= $debeetle, ' / ', number_format($debeetle * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= $bench['total']['includedFiles'], ' / ', number_format($bench['total']['includedFiles'] * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= $script, ' / ', number_format($script * 100 / $total, 2, '.', ''), '%'?></td>
        <td><?= $total?></td>
    </tr>
    </tbody>
</table>
