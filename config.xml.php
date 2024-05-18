<!--?xml version="1.0" encoding="UTF-8"?-->
<?php __halt_compiler(); die; ?>
<debeetle
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="debeetle.xsd"
    launch="true"
>
<!-- xmlns:xi="http://www.w3.org/2001/XInclude" -->

  <config name="common" use="true">

    <cookie>
      <name>debeetle</name>
      <path>/</path>
      <expires>0</expires>
    </cookie>

    <path>
      <assets>E:/repos/deepeloper/debeetle/assets</assets>
      <script>/debeetle.php</script>
      <!-- optional, root path is used to cut it from trace paths -->
      <root>E:/repos/deepeloper/debeetle/public</root>
    </path>

    <bench>

      <serverTime>
        <format>Y-m-d H:i:s P</format>
      </serverTime>

      <pageTotalTime>
        <format>%.03f</format>
        <warning>0.7</warning>
        <critical>1</critical>
        <exclude>scriptInit,debeetle</exclude>
      </pageTotalTime>

      <memoryUsage>
        <format>%.02f</format>
        <warning>10</warning>
        <critical>15</critical>
        <divider>1048576</divider>
        <unit>MB</unit>
        <exclude>scriptInit,debeetle</exclude>
      </memoryUsage>

      <peakMemoryUsage>
        <format>%.02f</format>
        <warning>30</warning>
        <critical>60</critical>
        <divider>1048576</divider>
        <unit>MB</unit>
        <exclude>scriptInit,debeetle</exclude>
      </peakMemoryUsage>

      <includedFiles>
        <warning>100</warning>
        <critical>120</critical>
        <exclude>debeetle</exclude>
      </includedFiles>

    </bench>

    <defaults>

      <language>en</language>

      <disabledPanelOpacity>0.7</disabledPanelOpacity>

      <!-- Max panel height in percents of current window viewport -->
      <maxPanelHeight>75</maxPanelHeight>

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
        <selector>~$d.frame</selector>
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
        <selector>div.bar</selector>
        <selector>#dPanel</selector>
      </zoom>

      <options>
        <write>
          <encoding>windows-1251</encoding>
          <htmlEntities>true</htmlEntities>
          <nl2br>true</nl2br>
        </write>
      </options>

    </defaults>

    <history use="true">
      <records>20</records>
      <name>history</name>
      <!-- session/cookie -->
      <storage>session</storage>
    </history>

<!--    <disabled>
      <tab>debeetle|about</tab>
    </disabled>
-->
    <skin id="deepeloper_default" use="true">
      <class>deepeloper\Debeetle\Skin\ByDefault\Controller</class>
      <name>
        <en>Default</en>
        <ru>Умолчанец</ru>
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

    <!--        <xi:include href="Plugin_TraceAndDump.xml.php"/>-->

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

        <!-- PHP 5.4 E_ALL: 32767 -->
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

  </config>

  <config name="localhost" use="true">
    <limit source="SERVER" key="REMOTE_ADDR" value="127.0.0.1" />
  </config>

</debeetle>
