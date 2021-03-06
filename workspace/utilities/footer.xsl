<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform">


<xsl:template name="footer">

  <footer class="footer">
    <div class="contact">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <address>
              <strong>FIRE Restoration</strong><br/>
              PO Box 2133<br/>
              Oregon City, OR 97045<br/>
            </address>
          </div>
          <hr class="visible-xs visible-sm"/>
          <div class="col-md-6">
            <p>
              P: (503) 305-7285<br/>
              F: (503) 305-7284<br/>
              <a href="mailto:info@firexperts.net?Subject=Website Inquiry">info@firexperts.net</a>
            </p>
          </div>
        </div>
      </div>
    </div>
    <div class="baseline">
      <p>
        <xsl:choose>
          <xsl:when test="$this-year = 2013">
            <xsl:text>&#xa9;&#160;</xsl:text>
            <xsl:value-of select="$this-year" />
          </xsl:when>
          <xsl:otherwise>
            <xsl:text>&#xa9;&#160;2013–</xsl:text>
            <xsl:value-of select="$this-year" />
          </xsl:otherwise>
        </xsl:choose>
        <xsl:text>.&#160;</xsl:text>
        <xsl:value-of select="$website-name"/>
        <xsl:text>. All rights reserved.&#160;&#160;•&#160;&#160;</xsl:text>
        <a href="https://plus.google.com/108415741477626522676" rel="publisher">Google+</a>
        <xsl:text>&#160;&#160;•&#160;&#160;Web Design by </xsl:text>
        <a href="http://simko.io/" target="_blank" class="design-logo">&#160;<img alt="Simko Design" class="logo" src="{$workspace}/img/simko-design.svg"  height="30px"/></a>
       </p>
    </div>

  </footer>

  <script src="{$workspace}/build/main.min.js"></script>
  <xsl:call-template name="google-analytics"/>
  <xsl:call-template name="livereload"/>

</xsl:template>


</xsl:stylesheet>

