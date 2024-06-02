<!--?xml version="1.0" encoding="UTF-8"?-->
<?php __halt_compiler(); die; ?>
<debeetle
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="debeetle.xsd"
    launch="true"
>

  <config name="common" use="true">

    <cookie>
      <name>debeetle</name>
      <path>/</path>
      <!-- In seconds, 0 means during session. -->
      <expires>0</expires>
    </cookie>

    <!-- Some pages modifyes its content after DOM loaded. -->
    <delayBeforeShowInBrowser>0</delayBeforeShowInBrowser>

    <path>
      <!-- Absolute path to Debeetle "assets" folder. -->
      <assets>/path/to/assets</assets>
      <!-- Path to debeetle script relative to www root. -->
      <script>/debeetle.php</script>
      <!-- optional, absolute www root path, used to cut it from trace paths. -->
      <root>/path/to/root</root>
    </path>

    <bench>

      <serverTime>
        <!-- See https://www.php.net/manual/en/datetime.format.php. -->
        <format>Y-m-d H:i:s P</format>
      </serverTime>

      <pageTotalTime>
        <!-- See https://www.php.net/manual/en/function.sprintf.php. -->
        <format>%.03f</format>
        <!-- Mark as warning if pageTotalTime greater than value. -->
        <warning>0.7</warning>
        <!-- Mark as critical if pageTotalTime greater than value. -->
        <critical>1</critical>
        <!--
          * scriptInit: exclude time before PHP script start execution;
          * debeetle: exclude time taken by Debeetle.
          Separated by comma.
        -->
        <exclude>scriptInit,debeetle</exclude>
      </pageTotalTime>

      <memoryUsage>
        <format>%.02f</format>
        <warning>10</warning>
        <critical>15</critical>
        <!-- Devide memoryUsage by value. -->
        <divider>1048576</divider>
        <unit>MB</unit>
        <exclude>debeetle</exclude>
      </memoryUsage>

      <peakMemoryUsage>
        <format>%.02f</format>
        <warning>30</warning>
        <critical>60</critical>
        <divider>1048576</divider>
        <unit>MB</unit>
        <exclude>debeetle</exclude>
      </peakMemoryUsage>

      <includedFiles>
        <warning>100</warning>
        <critical>120</critical>
        <!-- debeetle: exclude Debeetle files. -->
        <exclude>debeetle</exclude>
      </includedFiles>

    </bench>

    <defaults>

      <!-- Supports "en", "ru" for now. -->
      <language>en</language>

      <disabledPanelOpacity>0.7</disabledPanelOpacity>

      <!-- Max panel height koef from 0 to 1 of current window viewport. -->
      <maxPanelHeight>0.75</maxPanelHeight>

      <skin>deepeloper_default</skin>
      <theme>deepeloper_default_default</theme>

      <opacity applyOnChange="true">
        <properties>
          <type>number</type>
          <min>0.3</min>
          <max>1</max>
          <step>0.05</step>
          <parse>float</parse>
          <value>0.95</value>
        </properties>
        <selector>~$d.frame</selector><!-- Don't change. -->
      </opacity>

      <zoom>
        <properties>
          <type>number</type>
          <min>0.5</min>
          <max>3</max>
          <step>0.05</step>
          <parse>float</parse>
          <value>1</value>
        </properties>
        <selector>div.bar</selector><!-- Don't change. -->
        <selector>#dPanel</selector><!-- Don't change. -->
      </zoom>

      <options>
        <write>
          <!-- Page encoding, Debettle panel convert all to UTF-8 for output in the panel. -->
          <encoding>windows-1251</encoding>
          <!--
            Call https://www.php.net/manual/en/function.htmlentities.php in
              * deepeloper\Debeetle\d::w(),
              * deepeloper\Debeetle\Debeetle::write().
            Can be overriden by passing $options.
          -->
          <htmlEntities>true</htmlEntities>
          <!--
            Call https://www.php.net/manual/en/function.nl2br.php in
              * deepeloper\Debeetle\d::w(),
              * deepeloper\Debeetle\Debeetle::write().
            Can be overriden by passing $options.
          -->
          <nl2br>true</nl2br>
        </write>
      </options>

    </defaults>

    <history use="true">
      <records>20</records>
      <name>history</name>
      <!--
        "session/cookie", used to store last history record if page redirected before
         deepeloper\Debeetle\Debeetle::getView() called.
      -->
      <storage>session</storage>
    </history>
<!--
    <disabled>
      <tab>debeetle|about</tab>
    </disabled>
-->
    <skin id="deepeloper_default" use="true">
      <class>deepeloper\Debeetle\Skin\ByDefault\Controller</class>
      <name>
        <en>Default</en>
        <ru>По умолчанию</ru>
      </name>
      <assets>
        <template>skin.html</template>
        <js>addon.js.php</js>
        <lessJs>skin.less.js.php</lessJs>
        <less>skin.less</less>
      </assets>
      <defaultTheme>deepeloper_default_default</defaultTheme>

      <theme id="deepeloper_default_default" use="true">
        <class>deepeloper\Debeetle\Skin\ByDefault\Theme\ByDefault\Controller</class>
        <name>
          <en>Default</en>
          <ru>Стандартная</ru>
        </name>
        <assets>
          <lessJs>theme.less.js.php</lessJs>
          <less>theme.less</less>
        </assets>
      </theme>

      <theme id="deepeloper_default_green" use="true">
        <class>deepeloper\Debeetle\Skin\ByDefault\Theme\Green\Controller</class>
        <name>
          <en>Green</en>
          <ru>Зелёная</ru>
        </name>
        <assets>
          <lessJs>theme.less.js.php</lessJs>
          <less>theme.less</less>
        </assets>
      </theme>
    </skin>

    <plugin id="deepeloper_phpinfo" locale="true" use="true">
      <class>deepeloper\Debeetle\Plugin\PHPInfo\Controller</class>
      <assets>
        <js>addon.js.php</js>
      </assets>
    </plugin>

    <plugin id="deepeloper_traceanddump" locale="true" use="true">
      <class>deepeloper\Debeetle\Plugin\TraceAndDump\Controller</class>
      <assets>
        <js>addon.js.php</js>
        <!-- lessJs>styles.less.js</lessJs -->
        <less>styles.less</less>
      </assets>

      <method name="dump">
        <maxStringLength>200</maxStringLength>
        <maxNesting>0</maxNesting>
        <maxCount>0</maxCount>
        <expand>true</expand>
        <expandEntities>true</expandEntities>
      </method>

      <method name="trace">
        <expand>true</expand>
        <displayArgs>true</displayArgs>
        <expandArgs>true</expandArgs>
      </method>
    </plugin>

    <plugin id="deepeloper_reports" locale="true" use="true">
      <class>deepeloper\Debeetle\Plugin\Reports\Controller</class>
      <assets>
        <less>styles.less</less>
      </assets>

      <method name="errorHandler">
        <tabId>reports</tabId>
        <place>after:environment</place>
        <place>anywhere</place>
        <separateTabs>false</separateTabs>

        <!-- E_ALL -->
        <errorReporting>32767</errorReporting>
        <errorLevels>32767</errorLevels>

        <!-- date, report counter, level, message, file, line, backtrace -->
        <template><![CDATA[
          <div class="reports">
            <div class="message">
              <span class="date">[ %s ]</span>
              <span class="counter">#%03d</span>
              <span class="level level_%s">[ %s ]
                <span class="message">%s at</span>
                <span class="file">%s</span>
                <span class="line">(%d)</span>
              </span>
            </div>
            <code>%s</code>
          </div>
        ]]></template>

        <callPrevious>false</callPrevious>
      </method>
    </plugin>

    <plugin id="deepeloper_behcnmarks" use="true">
      <!--
      empty (ignore) |
      "exception" (deepeloper\Debeetle\Plugin\Benchmarks\Exception\Exception) |
      "E_USER_NOTICE/E_USER_WARNING/E_USER_ERROR"
      -->
      <onError>E_USER_NOTICE</onError>
      <!-- Flag specifying to store delays between calls of checkpoint, memory usage / peak memory usage. -->
      <checkpoint storeData="true"/>

      <class>deepeloper\Debeetle\Plugin\Benchmarks\Controller</class>
      <assets/>

      <method name="startBenchmark"/>

      <method name="endBenchmark"/>

    </plugin>

  </config>

  <!--
    debug: E_USER_NOTICE/E_USER_WARNING/E_USER_ERROR/exception;
    disableCaching: disable browser caching (JS/CSS).
  -->
  <config name="localhost" developerMode="false" debug="E_USER_WARNING" disableCaching="false" use="true">
    <limit source="SERVER" key="REMOTE_ADDR" value="127.0.0.1"/>
  </config>

</debeetle>
