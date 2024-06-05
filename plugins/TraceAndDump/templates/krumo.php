<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * Debeetle trace and dump methods template.
 * Krumo dump method template.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @author [Kaloyan K. Tsvetkov](mailto:kaloyan@kaloyan.info)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 * @see HTML::get()
 */

if (empty($this)) {
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.0";
    header("$protocol 404 Not Found");
    die;
}

/**
 * @var string $part
 * @var bool $even
 * @var array $locales
 * @var string $label
 * @var array $trace
 * @var string $content
 * @var string $location
 * @var string $caller
 * @var string $args
 * @var mixed $entity
 * @var string $caption
 * @var array $options
 * @var int $nesting
 * @var string $type
 */

switch ($part) {

    // settings {

    case "settings":
?>
<table class="trace-and-dump-buttons">
<tr>
    <td><button id="dump-collapse" class="locale-collapseDumps" onclick="$d.Plugins.TraceAndDump.groupClick(this);"></button></td>
    <td class="r"><button id="dump-expand" class="locale-expandDumps" onclick="return $d.Plugins.TraceAndDump.groupClick(this);"></button></td>
</tr>
<tr>
    <td><button id="dumpEntities-collapse" class="locale-collapseEntities" onclick="$d.Plugins.TraceAndDump.groupClick(this);"></button></td>
    <td class="r"><button id="dumpEntities-expand" class="locale-expandEntities" onclick="return $d.Plugins.TraceAndDump.groupClick(this);"></button></td>
</tr>
<tr>
    <td><button id="trace-collapse" class="locale-collapseTraces" onclick="$d.Plugins.TraceAndDump.groupClick(this);"></button></td>
    <td class="r"><button id="trace-expand" class="locale-expandTraces" onclick="return $d.Plugins.TraceAndDump.groupClick(this);"></button></td>
</tr>
<?php if (!empty($options["displayArgs"])): ?>
<tr>
    <td><button id="traceArgs-collapse" class="locale-collapseTraceArgs" onclick="$d.Plugins.TraceAndDump.groupClick(this);"></button></td>
    <td class="r"><button id="traceArgs-expand" class="locale-expandTraceArgs" onclick="$d.Plugins.TraceAndDump.groupClick(this);"></button></td>
</tr>
<?php endif; // !empty($options["displayArgs"]) ?>
</table>
<?php
        break; // case "settings"

    // } settings
    // trace {

    case "fieldset:trace":
?>
<fieldset class="trace">
    <legend class="title-hide" onclick="$d.Plugins.TraceAndDump.click(this);">&nbsp; trace ::
        <?php
            if ("" !== $label):
                echo $label, " ::";
            endif;
        ?>
        <span><?= $trace?></span>&nbsp;
    </legend>
    <div class="content"><?= $content ?></div>
</fieldset>
<?php
        break; // case "fieldset:trace"
    case "header:trace":
?>
<table class="trace">
<thead>
    <tr><th><?= $locales['location'] ?></th><th><?= $locales['caller'] ?></th><?php if (!empty($options['displayArgs'])): ?><th><?= $locales['arguments'] ?></th><?php endif; ?></tr>
</thead>
<tbody>
<?php
        break; // case "header:trace"
    case "row:trace":
?>
<tr<?= $even ? ' class="even"' : '' ?> onmouseover="$d.Plugins.TraceAndDump.onMouse(this, true);" onmouseout="$d.Plugins.TraceAndDump.onMouse(this, false);">
    <td class="td1"><?= $location ?></td>
    <td class="td2"><?= $caller ?></td>
    <?php if (!empty($options["displayArgs"])) { ?><td><?= $args ?></td><?php } ?>
</tr>
<?php
        break; // case "row:trace"
    case "footer:trace":
?>
</tbody>
</table>
<?php
        break; // case "footer:trace"

    // } trace
    // dump {

    case "fieldset:dump":
?>
<fieldset class="dump">
    <legend class="title-hide" onclick="$d.Plugins.TraceAndDump.click(this);">&nbsp; dump
        <?php
            if ("" !== $label):
                echo ":: $label";
            endif;
        ?>
        <?php
            if ("" !== $trace):
                echo ":: <span>$trace</span>";
            endif;
        ?>
    &nbsp;</legend>
    <div class="content"><?= $content ?></div>
</fieldset>
<?php
        break; // case "fieldset"
    case 'header:dump':
?>
<div class="krumo-root<?= empty($options["traceArgs"]) ? "" : " trace-args" ?>">
    <ul class="krumo-node krumo-first">
<?php
        break; // case "header:dump"
    case "footer:dump":
    case "node:end":
?>
    </ul>
</div>
<?php
        break; // case "footer:dump"
    case "recursion":
?>
<div class="krumo-nest" style="display:none;">
    <ul class="krumo-node">
        <li class="krumo-child">
            <div class="krumo-element" onMouseOver="krumo.over(this);" onMouseOut="krumo.out(this);">
                <a class="krumo-name"><b>&#8734;</b></a>
                (<em class="krumo-type">recursion</em>)
            </div>
        </li>
    </ul>
</div>
<?php
        break; // case "recursion"
    case "node:start":
?>
<div class="krumo-nest" style="display:none;">
    <ul class="krumo-node">
<?php
        break; // case "node:start"
    case "entity:array":
?>
<li class="krumo-child">
    <div class="krumo-element<?= sizeof($entity) > 0 ? " krumo-expand" : "" ?>"
        <?php if (sizeof($entity) > 0) {?> onClick="krumo.toggle(this);"<?php } ?>
        onMouseOver="krumo.over(this);" onMouseOut="krumo.out(this);">
        <a class="krumo-name"><?= $caption ?></a>
        (<em class="krumo-type">array, <strong class="krumo-array-length">
<?= (sizeof($entity)==1) ? ("1 element") : (sizeof($entity)." elements")?></strong></em>)
<?php
// callback ?
if (is_callable($entity)) {
        $_ = array_values($entity);
        ?>
        <span class="krumo-callback"> |
                (<em class="krumo-type">callable</em>)
                <strong class="krumo-string"><?= htmlSpecialChars($_[0]) ?>::<?= htmlSpecialChars($_[1]) ?>();</strong></span>
<?php
}
?>

    </div>
<?php
if (sizeof($entity)) {
    $this->krumoRenderVars($entity, $options, $nesting, "...");
}
?>
</li>
<?php
        break; // case "entity:array"
    case "entity:object":
        $vars = get_object_vars($entity);
?>
<li class="krumo-child">
    <div class="krumo-element<?= sizeof($vars) > 0 ? " krumo-expand" : "" ?>"
            <?php if (sizeof($vars) > 0) {?> onClick="krumo.toggle(this);"<?php } ?>
            onMouseOver="krumo.over(this);"
            onMouseOut="krumo.out(this);">
                    <a class="krumo-name"><?= $caption ?></a>
                    (<em class="krumo-type">object</em>)
                    <strong class="krumo-class"><?= get_class($entity) ?></strong>
    </div>
<?php
if (sizeof($vars) > 0) {
    $this->krumoRenderVars($entity, $options, $nesting, "...");
}
?>
</li>
<?php
        break; // case "entity:object"
    case "entity:resource":
?>
<li class="krumo-child">
    <div class="krumo-element" onMouseOver="krumo.over(this);" onMouseOut="krumo.out(this);">
        <a class="krumo-name"><?= $caption ?></a>
        (<em class="krumo-type">resource</em>)
        <strong class="krumo-resource"><?= get_resource_type($entity) ?></strong>
    </div>
</li>
<?php
        break; // case "entity:resource"
    case "entity:string":
        // extra ?
        $_extra = false;
        $_ = $entity;
        if (
            isset($options['maxStringLength']) &&
            $options['maxStringLength'] > 0 &&
            mb_strLen($entity) > $options['maxStringLength']
        ) {
            $_ = mb_substr($entity, 0, $options['maxStringLength']) . "...";
            $_extra = true;
        }
?>
<li class="krumo-child">
<div class="krumo-element<?= $_extra ? " krumo-expand" : "" ?>"
<?php if ($_extra) {?> onClick="krumo.toggle(this);"<?php } ?>
onMouseOver="krumo.over(this);"
onMouseOut="krumo.out(this);">
    <a class="krumo-name"><?= $caption ?></a>
    (<em class="krumo-type">string,
            <strong class="krumo-string-length"><?php
                    $length = mb_strlen($entity);
                    echo $length, ' char', $length > 1 ? 's' : '' ?></strong></em>)
    <strong class="krumo-string"><?= htmlSpecialChars($_) ?></strong>
<?php
// callback ?
if (is_callable($entity)) {
?>
    <span class="krumo-callback"> | (<em class="krumo-type">Callback</em>) <strong class="krumo-string"><?= htmlSpecialChars($_) ?>();</strong></span>
<?php
}
?>
</div>
<?php if ($_extra) { ?>
<div class="krumo-nest" style="display:none;">
    <ul class="krumo-node">
        <li class="krumo-child">
            <div class="krumo-preview"><?= htmlSpecialChars($entity) ?></div>
        </li>
    </ul>
</div>
<?php
}
?>
</li>
<?php
        break; // case "entity:string"
    case "entity:scalar":
?>
<li class="krumo-child">
    <div class="krumo-element" onMouseOver="krumo.over(this);" onMouseOut="krumo.out(this);">
        <a class="krumo-name"><?= $caption ?></a>
        <?php if($part !== 'null') { ?>(<em class="krumo-type"><?= $type ?></em>)<?php } ?>
        <strong class="krumo-float"><?= $entity ?></strong>
    </div>
</li>
<?php
        break; // case "entity:scalar"

    // } dump

    default:
        $e = new Exception;echo "<pre>";die($e->getTraceAsString());###
}
