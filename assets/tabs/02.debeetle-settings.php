<?php require_once __DIR__ . "/../stub.php"; ?>
<form name="settings" action="#">
  <table class="settings">
    <tbody>
    <tr>
      <td class="left"><label for="skin" class="locale-skin"></label></td>
      <td>
        <select id="skin" name="skin" class="iSelect" onchange="$d.Panel.onSelectSkin(this);"></select>
      </td>
    </tr>
    <tr>
      <td class="left"><label for="theme" class="locale-colorTheme"></label></td>
      <td>
        <select id="theme" name="theme" class="iColorTheme" onchange="$d.Panel.onSelectTheme(this);"></select>
      </td>
    </tr>
    <tr>
      <td class="left"><label for="opacity" class="locale-panelOpacity"></label></td>
      <td>
        <input
          id="opacity"
          name="opacity"
          onchange="return $d.Panel.validateParameter(this);"
          onblur="return $d.Panel.validateParameter(this);"
          required
        />
      </td>
    </tr>
    <tr>
      <td class="left"><label for="zoom" class="locale-panelZoom"></label></td>
      <td>
        <input
          id="zoom"
          name="zoom"
          onchange="return $d.Panel.validateParameter(this);"
          onblur="return $d.Panel.validateParameter(this);"
          required
        />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="buttons">
        <button onclick="return $d.Panel.saveSettings(this);" class="locale-apply"></button> &nbsp; &nbsp; &nbsp;
        <button onclick="return $d.Panel.resetSettings(this);" class="locale-discard"></button>
      </td>
    </tr>
    </tbody>
  </table>
</form>
