<!--  ===========================================================  -->
<!--  MODULE:    Legislative Branch Bills  XSLT                    -->
<!--                                                               -->
<!--  DATE:      December 4, 2013                                  -->
<!--  First version                                                -->
<!-- ============================================================= -->
<!-- details and actual transformation extracted to				   -->
<!--	the billres_details.xsl file							   -->		
<!-- Current file just a wrapper - envelope for the actual one	   -->
<!-- It was a request by Library of Congress – to remove from 	   -->
<!-- the actual document transformation result the main HTML tags  -->
<!-- as html, title, head, body. But also we wanted to keep 	   -->
<!-- backwards compatibility for the existing XML database. 	   -->
<!-- Two files – billres.xsl and billres_details.xsl – at the 	   -->
<!-- same folder will replace the previous billres.xsl			   -->
<!-- Cascading style sheet file BillResStyle.xsl may be extracted  -->
<!-- or not – according to the client requirements. 				   -->
<!-- ============================================================= -->
<!--                                                               -->
<!--                                                               -->
<!-- ========================== END ============================== -->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:ms="urn:schemas-microsoft-com:xslt" xmlns:dc="http://purl.org/dc/elements/1.1/">
	<xsl:include href="billres-details.xsl"/>
	<xsl:output method="html"/>

	<xsl:template match="/">
		<html encoding="utf-8">
			<head>

				<!-- Added meta tag to request IE to use the most recent version of it's rendering engine-->
				<!--http://stackoverflow.com/questions/4966952/ie-not-rendering-css-properly-when-the-site-is-located-at-networkdrive-->
				<meta http-equiv="X-UA-Compatible" content="IE=Edge"/>

				<title>
					<xsl:call-template name="printDocumentTitle"/>
				</title>
				<xsl:call-template name="defineDocumentStyle"/>
				 <style>
           .amount {
             background-color: yellow;
             color: black;
           }

					 .navigation {
             margin-left: 25%;
             background-color: #eeeeee;
             margin-right: 25%;
						 display: flex;
             text-align: center;
						 padding-top: 4px;
             position: fixed;
             bottom: 10px;
             width: 50%;
             //right: 10px;
           }
           
           button {
             padding: auto;
             margin: auto;
             background-color: #4CAF50;
             color: white;
             font-size: 18px;
             padding: 12px 24px;
             border: none;
             cursor: pointer;
             border-radius: 5px;
             text-decoration: none;
             width:20%;
             text-align: center;
           }

           .location {
             width:60%;
             color: red;
           }

           button:hover {
             background-color: #45a049;
           }

           .bodeh {
             height: 80%;
           }

				 </style>
			</head>
			<body class="lbexBody">
          <xsl:text disable-output-escaping="yes">
          <![CDATA[
          <div id="bodeh">
           ]]>
           </xsl:text>
          <xsl:call-template name="prePrintWebDocument"/>
          <xsl:text disable-output-escaping="yes">
          <![CDATA[
        </div>
           ]]>
				<![CDATA[
				<!-- Navigation buttons -->
				 <div class="navigation">
								 <button id="prevBtn" onclick=navigateAmounts(-1)>Prev</button>
                 <div id="location">
                   <h3 id="navLocation"></h3>
                 </div>
								 <button id="nextBtn" onclick=navigateAmounts(1)>Next</button>
				 </div>

				 <script>
								 const prevBtn = document.getElementById("prevBtn");
								 const nextBtn = document.getElementById("nextBtn");

								 // Wrap dollar amounts in custom tags
								 const wrapAmounts = (element) => {
                   element.innerHTML = element.innerHTML.replace(/(\$[0-9,.]+)/g, '<span class=\"amount\">$1</span>');
								 };

								 wrapAmounts(document.body);

								 let amounts = document.querySelectorAll('span.amount');
								 let currentIndex = 0;
                 navLocation = document.getElementById("navLocation");
                 if ( amounts.length > 0 ) {
                   navLocation.innerHTML = "1" + " / " + amounts.length.toString();
                 }
                 else {
                   navLocation.innerHTML = "1" + " / " + amounts.length.toString();
                 }

								 // Function to jump to next/previous amount
								 const navigateAmounts = (direction) => {
                   console.log("i'm happening");
                   currentIndex += direction;
                   if (currentIndex < 0) {
                     currentIndex = amounts.length - 1;
                   }
                   if (currentIndex >= amounts.length) {
                     currentIndex = 0;
                   }
                   navLocation.innerHTML = (currentIndex + 1).toString() + " / " + amounts.length.toString();

                   const target = amounts[currentIndex];
                   target.scrollIntoView({ behavior: "smooth" });
								 };

								 // Attach event listeners to navigation buttons
				 </script>
				 ]]>
				 </xsl:text>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
