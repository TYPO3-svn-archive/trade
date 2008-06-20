<map version="0.8.0">
<!-- To view this file, download free mind mapping software FreeMind from http://freemind.sourceforge.net -->
<node CREATED="1140577067242" ID="Freemind_Link_1903468558" MODIFIED="1141434156032" TEXT="Trade Manual">
<node CREATED="1140578201808" FOLDED="true" ID="Freemind_Link_362614928" MODIFIED="1140578206729" POSITION="left" TEXT="Introduction">
<node CREATED="1140577682712" ID="_" MODIFIED="1140577708692" TEXT="The trade extension provides a basic shopping cart that integrates with the typo3 framework."/>
<node CREATED="1140579627306" ID="Freemind_Link_285216252" MODIFIED="1140579763326" TEXT="This extension is inspired heavily from the tt_products extension. It is intended that this implementation of a shopping system is more flexible and easier to configure. Users familiar with tt_products will find configuration of the plugin very familiar."/>
<node CREATED="1140577709827" ID="Freemind_Link_1372075048" MODIFIED="1140578035954" TEXT="The extension is designed to be highly configurable, allowing users with experience in HTML to control the flow of the checkout process and users with typoscript experience to add stages to the checkout process, configure shipping, payment and price calculations and more."/>
<node CREATED="1140577786273" ID="Freemind_Link_778118699" MODIFIED="1140577903928" TEXT="The extension is designed to work out of the box for inexperienced users. Anyone with access to create content on a website has the ability to insert the shopping cart as a plugin content element and use flex forms to select available payment methods and shop owner details."/>
<node CREATED="1140577914218" ID="Freemind_Link_672499480" MODIFIED="1140578437319" TEXT="The trade extension does not provide functionality for product or order administration. It is intended that these functions are managed through the record editing features available in the back end of typo3 or through the front end using one of the many available record editing libraries. We suggest the admin_interface extension to make things (relatively) easy."/>
<node CREATED="1141354480502" ID="Freemind_Link_1771966419" MODIFIED="1141354513052" TEXT="This extension relies on creating records in the fe_users table."/>
<node CREATED="1140579829851" ID="Freemind_Link_682488551" MODIFIED="1141354539505" TEXT="Development of this extension was funded by Roger Bunyan of Redgum Soaps and implemented by Steve Ryan."/>
</node>
<node CREATED="1140578218383" FOLDED="true" ID="Freemind_Link_628173706" MODIFIED="1140578220958" POSITION="left" TEXT="Users Manual">
<node CREATED="1140578233894" FOLDED="true" ID="Freemind_Link_71393001" MODIFIED="1140578247194" TEXT="To use the trade extension you need to">
<node CREATED="1140578248428" ID="Freemind_Link_1981493043" MODIFIED="1140578259127" TEXT="Install the extension using the extension manager"/>
<node CREATED="1140578260365" ID="Freemind_Link_1336703603" MODIFIED="1140578288887" TEXT="Insert a content element of type plugin/trade into the page where you wish to create a shopping cart"/>
<node CREATED="1140578342598" ID="Freemind_Link_809499595" MODIFIED="1140578523746" TEXT="Use the flexform configuration to customise the shopping cart. Be sure to set shop owner details and restrict the payment options suitably for your circumstances. ">
<node CREATED="1141354411554" ID="Freemind_Link_1779102904" MODIFIED="1141354415647" TEXT="Using Paypal">
<node CREATED="1140578526571" ID="Freemind_Link_868200734" MODIFIED="1141264354663" TEXT="To use paypal as a payment option you must signup for a free paypal account. Go to http://paypal.com/. If you enable paypal as a payment option, you must also supply the email address that you used to sign up with paypal in flexform configuration.&#xa;Advanced Notes&#xa;If you wish to return to your website after payment or recieve notifications from paypal you will need to modify settings for your paypal account. Specifically in your profile under Website Payment Preferences. Enable return URL and set a return url to your website including post parameters id (shopping thanks page with trade plugin installed) and tx_trade_pi1[cmd]=checkout and tx_trade_pi1[external_payment_complete]=1. This will return to the order finalisation stage of the checkout process , create user and order records in the database and displays a thanks page. This approach could not be considered truly secure. Orders should be reconciled against your paypal account.&#xa;&#xa;It would also be possible to return to any page within your website if internal order tracking is not required. It would then be advisable to enable Payment Data Transfer to recieve emails from paypal after each transaction."/>
</node>
<node CREATED="1141354416771" ID="Freemind_Link_438519713" MODIFIED="1141354424128" TEXT="Using Eway">
<node CREATED="1140578560293" ID="Freemind_Link_224224277" MODIFIED="1140579315469" TEXT="Eway is an Australian payment gateway service providing a standard interface to a variety of banks as a paid service. You can find out more at https://www.eway.com.au/&#xa;&#xa;If you use eway as a payment option you will also need to purchase an SSL certificate and install and configure one of the typo3 extensions (suggest extension lz_https) that allows you to force the page to https. You must also provide your eway merchant number.&#xa;&#xa;It is possible to test your shopping cart against the eway test gateway by ticking the Eway test mode check box."/>
</node>
</node>
<node CREATED="1140578289983" ID="Freemind_Link_572105126" MODIFIED="1140578331585" TEXT="Create products and optionally product categories in the same page as the shopping cart (It is possible to set a different record storage page as described below)"/>
<node CREATED="1140578971968" ID="Freemind_Link_414762066" MODIFIED="1140579008142" TEXT="Advanced users may also like to customise the look and feel by adjusting the HTML template or tweak a range of other options using typoscript."/>
<node CREATED="1140578448387" ID="Freemind_Link_1337657690" MODIFIED="1140578452329" TEXT=" Voila !"/>
</node>
</node>
<node CREATED="1140580301066" ID="Freemind_Link_1299764984" MODIFIED="1140580304191" POSITION="left" TEXT="Screen Shots">
<node CREATED="1140580305268" FOLDED="true" ID="Freemind_Link_301463350" MODIFIED="1140580310169" TEXT="Configuration">
<node CREATED="1140580312950" ID="Freemind_Link_341801889" MODIFIED="1140580328191" TEXT="&lt;html&gt;&lt;img src=&quot;trade_manual/flexform-basic.png&quot;&gt;"/>
<node CREATED="1140580314720" ID="Freemind_Link_1591145371" MODIFIED="1140580350052" TEXT="&lt;html&gt;&lt;img src=&quot;trade_manual/flexform-advanced.png&quot;&gt;"/>
</node>
<node CREATED="1141434073156" FOLDED="true" ID="Freemind_Link_518089120" MODIFIED="1141434080245" TEXT="Front End Shop">
<node CREATED="1141354682603" ID="Freemind_Link_1111307865" MODIFIED="1141354696272" TEXT="&lt;html&gt;&lt;img src=&quot;trade_manual/product-list.png&quot;&gt;"/>
</node>
</node>
<node CREATED="1140579060244" FOLDED="true" ID="Freemind_Link_317101978" MODIFIED="1140579063576" POSITION="left" TEXT="Design Notes">
<node CREATED="1140579064925" ID="Freemind_Link_667154248" MODIFIED="1140579076173" TEXT="HTML template file">
<node CREATED="1140579077563" ID="Freemind_Link_1255086734" MODIFIED="1140579085830" TEXT="components vs template"/>
</node>
<node CREATED="1140579086780" ID="Freemind_Link_644938241" MODIFIED="1140579092648" TEXT="TS checkout process control">
<node CREATED="1140579094053" ID="Freemind_Link_25724965" MODIFIED="1140579118907" TEXT="The stages in the checkout process are configurable by typoscript."/>
</node>
<node CREATED="1140593724518" ID="Freemind_Link_1618540980" MODIFIED="1141354778668" TEXT="To create maximum flexibility in the extension, the piVar cmd is used almost unchanged to define which template section to render. Make a request to index.php?tx_trade_pi1[cmd]=singleview will attempt to lookup a suitable record based on other parameters and render the singleview template section. Only cmds/templates listed in the TS config validCmds is allowed.">
<node CREATED="1140593861722" ID="Freemind_Link_1962514561" MODIFIED="1140593892622" TEXT="To allow for various list configurations, any cmd starting with list is rewritten as list and an extra internal variable list type is saved."/>
<node CREATED="1140593894777" ID="Freemind_Link_1641985764" MODIFIED="1140593987947" TEXT="To provide the notion of state (where is the checkout process up to), where cmd is &quot;checkout&quot; a variety conditions are tested to rewrite the cmd so as to act/render appropriate actions and templates."/>
</node>
</node>
<node CREATED="1140580007692" FOLDED="true" ID="Freemind_Link_418259147" MODIFIED="1140580010100" POSITION="left" TEXT="FAQ">
<node CREATED="1140580011555" FOLDED="true" ID="Freemind_Link_1967956529" MODIFIED="1140580100503" TEXT="Q How do I add fields to the user information or the product information?">
<node CREATED="1140580029799" ID="Freemind_Link_1546985935" MODIFIED="1140580046041" TEXT="A">
<node CREATED="1140580059937" ID="Freemind_Link_190976461" MODIFIED="1141354316635" TEXT="Use the extension manager to create a new extension that adds fields to the relevant tables."/>
<node CREATED="1140580142831" ID="Freemind_Link_217561608" MODIFIED="1140580184387" TEXT="Customise the HTML template to include markers for your additional fields. "/>
<node CREATED="1141353805894" ID="Freemind_Link_855839685" MODIFIED="1141353831592" TEXT="Note that only text area type fields are supported at this stage."/>
</node>
</node>
<node CREATED="1140580082464" ID="Freemind_Link_1016767711" MODIFIED="1140580096470" TEXT="Q. How do I implement an alternate payment gateway?"/>
<node CREATED="1140580194283" FOLDED="true" ID="Freemind_Link_1398443062" MODIFIED="1140580233125" TEXT="Q. How can I customise the types of product listings.">
<node CREATED="1141353604614" ID="Freemind_Link_720103851" MODIFIED="1141353702052" TEXT="Add a subsection to the lists section in your TS temlate configuration.">
<node CREATED="1141353731486" ID="Freemind_Link_1306524354" MODIFIED="1141353783963" TEXT="specialoffers {&#xa;&#x9;title=Special Offers&#xa;&#x9;label=Special Offers&#xa;&#x9;extraWhere= special=1&#xa;&#x9;templateSubpart=ITEM_LIST&#xa;&#x9;search=0&#xa;&#x9;orderBy=title&#xa;}"/>
</node>
<node CREATED="1141353648195" ID="Freemind_Link_561903756" MODIFIED="1141353698251" TEXT="Add the title of the subsection (prepended by list_) to the cmdList in your TS configuration so that the list type appears in the automatic lists menu."/>
</node>
<node CREATED="1141298232270" FOLDED="true" ID="Freemind_Link_283176061" MODIFIED="1141353946877" TEXT="Q. What values do I need to place in my form templates to control the checkout process">
<node CREATED="1141298251789" ID="Freemind_Link_437753245" MODIFIED="1141298319980" TEXT="For the most part tx_trade_pi1[cmd] and id control the template and the page to post to respectively">
<node CREATED="1141353959704" ID="Freemind_Link_1150470995" MODIFIED="1141354020832" TEXT="The marker PID_&lt;CMD&gt; provides the ID of the configured page for this template"/>
<node CREATED="1141354022038" ID="Freemind_Link_314312285" MODIFIED="1141354116731" TEXT="SUBMIT_TO_&lt;CMD&gt; provides javascript to embed in a submit button that sets the cmd and id to the target command and submits the form."/>
<node CREATED="1141354033870" ID="Freemind_Link_1339607640" MODIFIED="1141354070098" TEXT="LINK_TO_&lt;CMD&gt; provides a relative url to the target command"/>
</node>
<node CREATED="1141298321226" ID="Freemind_Link_424289816" MODIFIED="1141298353208" TEXT="By default there are hidden form fields in the outermost wrapper template that need to be set with javascript on the buttons."/>
<node CREATED="1141298354085" ID="Freemind_Link_1673085283" MODIFIED="1141298421942" TEXT="Additionally">
<node CREATED="1141298526901" ID="Freemind_Link_184248624" MODIFIED="1141298550509" TEXT="The cmd parameter also drives certain activities. Specifically when">
<node CREATED="1141298551654" ID="Freemind_Link_1193572686" MODIFIED="1141298573726" TEXT="cmd=copy_address_to_shipping, the user details are copied to the shipping details"/>
<node CREATED="1141298577442" ID="Freemind_Link_1423582208" MODIFIED="1141298643196" TEXT="cmd=order_history_list or order_history_single (with id=) to use the order tracking"/>
</node>
<node CREATED="1141298397171" ID="Freemind_Link_1317534031" MODIFIED="1141298416281" TEXT="To move on from the basket page, you must set extrainfo=approvebasket using the next button."/>
<node CREATED="1141299170872" ID="Freemind_Link_1033119655" MODIFIED="1141299201643" TEXT="To request final payment processing, tx_trade_pi1[finalise_checkout]=1 must be passed as a hidden field from the confirmation or whatever other page."/>
<node CREATED="1141298467446" ID="Freemind_Link_1252862724" MODIFIED="1141299151729" TEXT="To validate user or shipping or payment details, the hidden value tx_trade_pi1[submit_shipping||user||payment_details]=1 must be present in the form"/>
</node>
</node>
<node CREATED="1141354148993" FOLDED="true" ID="Freemind_Link_525872955" MODIFIED="1141354161467" TEXT="Q. What markers are available in the templates.">
<node CREATED="1141354162630" ID="Freemind_Link_729892418" MODIFIED="1141354171600" TEXT="Look in the default templates"/>
<node CREATED="1141354172310" ID="Freemind_Link_635737885" MODIFIED="1141354195920" TEXT="Look in the main pi class for the methods get*Markers"/>
</node>
</node>
<node CREATED="1140580390081" FOLDED="true" ID="Freemind_Link_1770527309" MODIFIED="1140580392201" POSITION="left" TEXT="Reference">
<node CREATED="1140580395445" ID="Freemind_Link_712787865" MODIFIED="1140580404256" TEXT="Default Typoscript">
<node CREATED="1140580405442" ID="Freemind_Link_837567615" MODIFIED="1140580405442" TEXT="plugin.tx_trade_pi1 {">
<node CREATED="1140580405454" MODIFIED="1140580405454" TEXT="template=EXT:trade/res/trade_template.html"/>
<node CREATED="1140580405462" MODIFIED="1140580405462" TEXT="imageBasket=EXT:trade/res/cart.jpg"/>
<node CREATED="1140580405463" MODIFIED="1140580405463" TEXT="noImageAvailable=EXT:trade/res/no_picture.gif"/>
<node CREATED="1140580405464" MODIFIED="1140580405464" TEXT="adminEmail="/>
<node CREATED="1140580405464" MODIFIED="1140580405464" TEXT="maxListRows=20"/>
<node CREATED="1140580405465" MODIFIED="1140580405465" TEXT="currencyCode=AUD"/>
<node CREATED="1140580405465" MODIFIED="1140580405465" TEXT="currencySymbol=$"/>
<node CREATED="1140580405465" MODIFIED="1140580405465" TEXT="invoiceDescription="/>
<node CREATED="1140580405466" MODIFIED="1140580405466" TEXT="confirmEmailTitle=Regarding your order"/>
<node CREATED="1140580405467" MODIFIED="1140580405467" TEXT="plainTextEmails=1"/>
<node CREATED="1140580405467" MODIFIED="1140580405467" TEXT="shopOwnerDetails="/>
<node CREATED="1140580405467" MODIFIED="1140580405467" TEXT="userRequiredFields=username,password,email,name"/>
<node CREATED="1140580405469" MODIFIED="1140580405469" TEXT="shippingRequiredFields=tx_trade_shipping_name"/>
<node CREATED="1140580405470" MODIFIED="1140580405470" TEXT="cmdList=userstorage,list,list_search,list_recent,singleview,basket,basket_overview,checkout,user_details,user_shipping_details,user_shipping_payment_details,usersave,login,lost_password,confirm,thanks,order_history_list,order_history_single,copy_address_to_shipping,wishlist_list"/>
<node CREATED="1140580405513" ID="Freemind_Link_1551201193" MODIFIED="1140580405513" TEXT="checkout {">
<node CREATED="1140580405513" FOLDED="true" ID="Freemind_Link_1128851406" MODIFIED="1140580405513" TEXT="basket {">
<node CREATED="1140580405514" MODIFIED="1140580405514" TEXT="condition=if (sizeof($this-&gt;basket)&gt;0 &amp;&amp; $this-&gt;shipping[&apos;method&apos;]&gt;0 &amp;&amp; $this-&gt;payment[&apos;method&apos;]&gt;0 &amp;&amp; (($this-&gt;piVars[&apos;submit_payment_method&apos;]==1 &amp;&amp; $this-&gt;piVars[&apos;submit_shipping_method&apos;]==1)||$this-&gt;user[&apos;basket_approved&apos;]==1) ) $testResult=true;"/>
<node CREATED="1140580405520" MODIFIED="1140580405520" TEXT="next=user_shipping_details"/>
<node CREATED="1140580405521" MODIFIED="1140580405521" TEXT="templateSubpart=BASKET"/>
</node>
<node CREATED="1140580405521" MODIFIED="1140580405521" TEXT="}"/>
<node CREATED="1140580405522" FOLDED="true" ID="Freemind_Link_29878748" MODIFIED="1140580405522" TEXT="user_shipping_details {">
<node CREATED="1140580405522" MODIFIED="1140580405522" TEXT="condition=if ($this-&gt;user[&apos;valid&apos;]==1 &amp;&amp; $this-&gt;user[&apos;valid_shipping_details&apos;]==1) $testResult=true;"/>
<node CREATED="1140580405523" MODIFIED="1140580405523" TEXT="next=confirm"/>
<node CREATED="1140580405524" MODIFIED="1140580405524" TEXT="templateSubpart=USER_SHIPPING_DETAILS"/>
</node>
<node CREATED="1140580405524" MODIFIED="1140580405524" TEXT="}"/>
<node CREATED="1140580405525" FOLDED="true" ID="Freemind_Link_1838370954" MODIFIED="1140580405525" TEXT="confirm {">
<node CREATED="1140580405525" MODIFIED="1140580405525" TEXT="condition=if ($this-&gt;order[&apos;status&apos;]==1) $testResult=true;"/>
<node CREATED="1140580405526" MODIFIED="1140580405526" TEXT="next=thanks"/>
<node CREATED="1140580405526" MODIFIED="1140580405526" TEXT="templateSubpart=CONFIRM"/>
</node>
<node CREATED="1140580405526" MODIFIED="1140580405526" TEXT="}"/>
<node CREATED="1140580405527" FOLDED="true" ID="Freemind_Link_450347300" MODIFIED="1140580405527" TEXT="thanks {">
<node CREATED="1140580405527" MODIFIED="1140580405527" TEXT="condition=$testResult=false;"/>
<node CREATED="1140580405527" MODIFIED="1140580405527" TEXT="next=NOT USED"/>
<node CREATED="1140580405528" MODIFIED="1140580405528" TEXT="templateSubpart=THANKS"/>
</node>
<node CREATED="1140580405528" MODIFIED="1140580405528" TEXT="}"/>
<node CREATED="1140580405560" ID="Freemind_Link_689305141" MODIFIED="1140580405560" TEXT="# WARNING. DO NOT RENUMBER THE FOLLOWING SETTINGS OR YOU WILL BREAK THE FLEX FORM"/>
</node>
<node CREATED="1140580405561" FOLDED="true" ID="Freemind_Link_645728567" MODIFIED="1140580405561" TEXT="payment {">
<node CREATED="1140580405562" MODIFIED="1140580405562" TEXT="radio = 1"/>
<node CREATED="1140580405562" MODIFIED="1140580405562" TEXT="10.title = Credit Card (Eway)"/>
<node CREATED="1140580405562" MODIFIED="1140580405562" TEXT="10.description=Secure payment gateway, real time transaction."/>
<node CREATED="1140580405563" MODIFIED="1140580405563" TEXT="10.image.file = media/logos/mastercard.gif"/>
<node CREATED="1140580405563" MODIFIED="1140580405563" TEXT="10.priceTax ="/>
<node CREATED="1140580405564" MODIFIED="1140580405564" TEXT="#10.calculationScript = EXT:tt_products/pi1/products_comp_calcScript.inc"/>
<node CREATED="1140580405572" MODIFIED="1140580405572" TEXT="10.percentOfGoodsTotal = 5"/>
<node CREATED="1140580405573" MODIFIED="1140580405573" TEXT="10.detailsOK=if ($this-&gt;validateCreditCard($this-&gt;piVars[&apos;card_number&apos;],$this-&gt;payment[&apos;card_exp_month&apos;],$this-&gt;payment[&apos;card_exp_year&apos;],$this-&gt;payment[&apos;card_name&apos;])) $detailsOK=true; else  $detailsOK=false;"/>
<node CREATED="1140580405574" MODIFIED="1140580405574" TEXT="10.detailsError=Invalid credit card information. Please check and try again."/>
<node CREATED="1140580405575" MODIFIED="1140580405575" TEXT="10.handleScript=EXT:trade/lib/eway_payment.php"/>
<node CREATED="1140580405576" MODIFIED="1140580405576" TEXT="10.merchantCode="/>
<node CREATED="1140580405577" MODIFIED="1140580405577" TEXT="10.useTestGateway="/>
<node CREATED="1140580405577" MODIFIED="1140580405577" TEXT="20.title = Credit Card (Paypal)"/>
<node CREATED="1140580405578" MODIFIED="1140580405578" TEXT="20.description=Secure payment gateway, real time transaction."/>
<node CREATED="1140580405578" MODIFIED="1140580405578" TEXT="20.image.file = media/logos/mastercard.gif"/>
<node CREATED="1140580405579" MODIFIED="1140580405579" TEXT="20.priceTax ="/>
<node CREATED="1140580405579" MODIFIED="1140580405579" TEXT="#10.calculationScript = EXT:tt_products/pi1/products_comp_calcScript.inc"/>
<node CREATED="1140580405581" MODIFIED="1140580405581" TEXT="20.percentOfGoodsTotal = 1.4"/>
<node CREATED="1140580405581" MODIFIED="1140580405581" TEXT="20.handleScript=EXT:trade/lib/paypal.php"/>
<node CREATED="1140580405582" MODIFIED="1140580405582" TEXT="20.paypalEmail="/>
<node CREATED="1140580405583" MODIFIED="1140580405583" TEXT="40.title = Direct Deposit/Cheque/Money Order"/>
<node CREATED="1140580405583" MODIFIED="1140580405583" TEXT="40.description=Your order will be shipped when your cheque or money order is received."/>
<node CREATED="1140580405584" MODIFIED="1140580405584" TEXT="40.image.file = media/logos/money.gif"/>
<node CREATED="1140580405584" MODIFIED="1140580405584" TEXT="40.priceTax = 2"/>
<node CREATED="1140580405585" MODIFIED="1140580405585" TEXT="#40.percentOfGoodsTotal = 0"/>
</node>
<node CREATED="1140580405586" FOLDED="true" ID="Freemind_Link_1803784699" MODIFIED="1140580405586" TEXT="shipping {">
<node CREATED="1140580405586" MODIFIED="1140580405586" TEXT="10.title = Ground"/>
<node CREATED="1140580405587" MODIFIED="1140580405587" TEXT="10.image.file = media/logos/pakketrans.gif"/>
<node CREATED="1140580405587" MODIFIED="1140580405587" TEXT="10.priceTax = 5"/>
<node CREATED="1140580405588" MODIFIED="1140580405588" TEXT="10.hideDetails=0"/>
<node CREATED="1140580405588" MODIFIED="1140580405588" TEXT="20.title = Airmail"/>
<node CREATED="1140580405588" MODIFIED="1140580405588" TEXT="20.image.file = media/logos/postdanmark.gif"/>
<node CREATED="1140580405589" MODIFIED="1140580405589" TEXT="20.priceTax ="/>
<node CREATED="1140580405590" MODIFIED="1140580405590" TEXT="20.percentOfGoodsTotal=10"/>
<node CREATED="1140580405590" MODIFIED="1140580405590" TEXT="20.hideDetails=0"/>
<node CREATED="1140580405602" MODIFIED="1140580405602" TEXT="40.title = Pick up in store"/>
<node CREATED="1140580405602" MODIFIED="1140580405602" TEXT="40.excludePayment=10"/>
<node CREATED="1140580405602" MODIFIED="1140580405602" TEXT="40.hideDetails=1"/>
</node>
<node CREATED="1140580405530" FOLDED="true" ID="Freemind_Link_1691902357" MODIFIED="1140580405530" TEXT="lists {">
<node CREATED="1140580405534" FOLDED="true" ID="Freemind_Link_1317955417" MODIFIED="1140580405534" TEXT="default {">
<node CREATED="1140580405535" MODIFIED="1140580405535" TEXT="title="/>
<node CREATED="1140580405549" MODIFIED="1140580405549" TEXT="#extraWhere= category_uid=3"/>
<node CREATED="1140580405550" MODIFIED="1140580405550" TEXT="extraWhere="/>
<node CREATED="1140580405550" MODIFIED="1140580405550" TEXT="templateSubpart=ITEM_LIST"/>
<node CREATED="1140580405550" MODIFIED="1140580405550" TEXT="search=0"/>
<node CREATED="1140580405551" MODIFIED="1140580405551" TEXT="orderBy=sorting"/>
</node>
<node CREATED="1140580405551" MODIFIED="1140580405551" TEXT="}"/>
<node CREATED="1140580405551" FOLDED="true" ID="Freemind_Link_425812865" MODIFIED="1140580405551" TEXT="search {">
<node CREATED="1140580405551" MODIFIED="1140580405551" TEXT="title=Search"/>
<node CREATED="1140580405552" MODIFIED="1140580405552" TEXT="extraWhere="/>
<node CREATED="1140580405552" MODIFIED="1140580405552" TEXT="templateSubpart=ITEM_LIST"/>
<node CREATED="1140580405553" MODIFIED="1140580405553" TEXT="search=1"/>
<node CREATED="1140580405553" MODIFIED="1140580405553" TEXT="orderBy=title"/>
</node>
<node CREATED="1140580405553" MODIFIED="1140580405553" TEXT="}"/>
<node CREATED="1140580405553" FOLDED="true" ID="Freemind_Link_319340187" MODIFIED="1140580405553" TEXT="recent {">
<node CREATED="1140580405554" MODIFIED="1140580405554" TEXT="title=Recently Added Products"/>
<node CREATED="1140580405554" MODIFIED="1140580405554" TEXT="# last 7 days"/>
<node CREATED="1140580405554" MODIFIED="1140580405554" TEXT="extraWhere= DATE_SUB(CURDATE(),INTERVAL 7 DAY) &lt;= crdate"/>
<node CREATED="1140580405556" MODIFIED="1140580405556" TEXT="templateSubpart=ITEM_LIST"/>
<node CREATED="1140580405557" MODIFIED="1140580405557" TEXT="search=0"/>
<node CREATED="1140580405557" MODIFIED="1140580405557" TEXT="orderBy=crdate desc"/>
</node>
<node CREATED="1140580405557" MODIFIED="1140580405557" TEXT="}"/>
<node CREATED="1140580405558" FOLDED="true" ID="Freemind_Link_1835924932" MODIFIED="1140580405558" TEXT="specialoffers {">
<node CREATED="1140580405558" MODIFIED="1140580405558" TEXT="title=Special Offers"/>
<node CREATED="1140580405559" MODIFIED="1140580405559" TEXT="extraWhere= special=1"/>
<node CREATED="1140580405559" MODIFIED="1140580405559" TEXT="templateSubpart=ITEM_LIST"/>
<node CREATED="1140580405560" MODIFIED="1140580405560" TEXT="search=0"/>
<node CREATED="1140580405560" MODIFIED="1140580405560" TEXT="orderBy=title"/>
</node>
<node CREATED="1140580405560" MODIFIED="1140580405560" TEXT="}"/>
</node>
</node>
<node CREATED="1140580405603" MODIFIED="1140580405603" TEXT="}"/>
</node>
</node>
<node CREATED="1140580484039" FOLDED="true" ID="Freemind_Link_663879293" MODIFIED="1140580488076" POSITION="left" TEXT="TODO List">
<node CREATED="1140580744930" ID="Freemind_Link_385177504" MODIFIED="1140580752246" TEXT="!! Not necessarily in order"/>
<node CREATED="1140580752905" ID="Freemind_Link_852440164" MODIFIED="1140580763346" TEXT="next product in list from single view"/>
<node CREATED="1140580764168" ID="Freemind_Link_283429822" MODIFIED="1140580771274" TEXT="consumer wishlist"/>
<node CREATED="1140580773989" ID="Freemind_Link_1097546467" MODIFIED="1140580777420" TEXT="manual order approvals"/>
<node CREATED="1140580778285" ID="Freemind_Link_646881102" MODIFIED="1140580785464" TEXT="agreement to terms and conditions"/>
<node CREATED="1140580787860" ID="Freemind_Link_238868442" MODIFIED="1140580793784" TEXT="product comments"/>
<node CREATED="1140580796866" ID="Freemind_Link_40729806" MODIFIED="1140580806333" TEXT="&quot;also purchased products&quot;"/>
<node CREATED="1140580807861" ID="Freemind_Link_1242492166" MODIFIED="1140580813372" TEXT="related products"/>
<node CREATED="1140580814379" ID="Freemind_Link_1984688244" MODIFIED="1140580816856" TEXT="credit poings"/>
<node CREATED="1140580817802" ID="Freemind_Link_1052192043" MODIFIED="1140580824285" TEXT="drag and drop to cart"/>
<node CREATED="1140580825067" ID="Freemind_Link_664722388" MODIFIED="1140580827723" TEXT="vouchers"/>
<node CREATED="1140580828386" ID="Freemind_Link_1818758235" MODIFIED="1140580831095" TEXT="item variants"/>
<node CREATED="1140580831834" ID="Freemind_Link_863612852" MODIFIED="1140580836340" TEXT="gift certificates"/>
<node CREATED="1140580837149" ID="Freemind_Link_1313672171" MODIFIED="1140580840830" TEXT="discount codes"/>
<node CREATED="1140580844386" ID="Freemind_Link_1900167938" MODIFIED="1140580848086" TEXT="downloadable products"/>
<node CREATED="1140580851102" ID="Freemind_Link_543917775" MODIFIED="1140580857424" TEXT="product/order administration"/>
<node CREATED="1140580862108" ID="Freemind_Link_1318035349" MODIFIED="1140580873895" TEXT="import/export xml,csv,rss,qif"/>
<node CREATED="1140580874588" ID="Freemind_Link_659559473" MODIFIED="1140580877730" TEXT="accounting reports"/>
<node CREATED="1140580878434" ID="Freemind_Link_959547388" MODIFIED="1140580887784" TEXT="rss feed of latest products"/>
<node CREATED="1140580895407" ID="Freemind_Link_1102298156" MODIFIED="1140580898422" TEXT="order tracking"/>
</node>
<node CREATED="1140578083359" FOLDED="true" ID="Freemind_Link_319235059" MODIFIED="1140578086969" POSITION="left" TEXT="Known Problems">
<node CREATED="1141390158267" ID="Freemind_Link_699752030" MODIFIED="1141390173987" TEXT="There are fields in the database tables that are not yet in use."/>
</node>
<node CREATED="1140577230131" ID="Freemind_Link_1443446938" MODIFIED="1140577230131" POSITION="left" TEXT="Changelog"/>
<node CREATED="1140586131806" FOLDED="true" ID="Freemind_Link_1955610652" MODIFIED="1140586134118" POSITION="left" TEXT="Administration">
<node CREATED="1140586135433" ID="Freemind_Link_91557268" MODIFIED="1140591142090" TEXT="The plugin can be configured over multiple pages. ">
<node CREATED="1140591144025" ID="Freemind_Link_1282523259" MODIFIED="1141356508176" TEXT="Page ID for specific stages in the process can be configured by setting plugin.tx_trade_pi1.PIDS.&lt;cmd&gt; in your TS config"/>
<node CREATED="1140591160406" ID="Freemind_Link_1853250911" MODIFIED="1141356451869" TEXT="Records can be stored on a different page from the plugins by setting plugin.tx_trade_pi1.PID.&lt;user|order|product|category&gt;storage"/>
</node>
</node>
<node CREATED="1140593622555" FOLDED="true" ID="Freemind_Link_964203357" MODIFIED="1140593624451" POSITION="left" TEXT="Configuration">
<node CREATED="1140577230083" MODIFIED="1140577230083" TEXT="- Technical information; Installation, Reference of TypoScript, configuration options on system level, how to extend it, the technical details, how to debug it."/>
<node CREATED="1140586602308" FOLDED="true" ID="Freemind_Link_883979700" MODIFIED="1140586606793" TEXT="Code Structure">
<node CREATED="1140590417615" ID="Freemind_Link_107162503" MODIFIED="1140590608693" TEXT="As per a normal kickstarter extension, various typo3 files exist including database, plugin and extension configuration files to define a single plugin and three additional tables - products, categories and orders. Changes are also made to the system table fe_users."/>
<node CREATED="1140586706896" ID="Freemind_Link_112507866" MODIFIED="1140586764577" TEXT="The code is split into the main pi1 plugin class (main controller, validation), and multiple others in the lib directory."/>
<node CREATED="1140586765463" ID="Freemind_Link_1386960563" MODIFIED="1140586771856" TEXT="The lib files include">
<node CREATED="1140586773220" ID="Freemind_Link_483479410" MODIFIED="1140591036195" TEXT="class.tx_trade_div.php containing various static support functions, session management included."/>
<node CREATED="1140586805094" ID="Freemind_Link_1367391791" MODIFIED="1140591058689" TEXT="class.tx_trade_render.php  to wrap up rendering a command"/>
<node CREATED="1140586819397" ID="Freemind_Link_238389990" MODIFIED="1140591047357" TEXT="class.tx_trade_pricecalc.php where all price calculations are performed"/>
</node>
<node CREATED="1140590512646" ID="Freemind_Link_1417608822" MODIFIED="1140590631140" TEXT="Other additional/modified files include">
<node CREATED="1140590648310" ID="Freemind_Link_1187833032" MODIFIED="1140590733208" TEXT="ext_typoscript_setup.txt"/>
<node CREATED="1140590933348" ID="Freemind_Link_632223560" MODIFIED="1140590934225" TEXT="trade_template.html">
<node CREATED="1140591331089" ID="Freemind_Link_664191085" MODIFIED="1140591390508" TEXT="contains subparts for all possible commands and component templates for those command templates"/>
</node>
<node CREATED="1140591297949" ID="Freemind_Link_1291765873" MODIFIED="1140591300014" TEXT="flexforms">
<node CREATED="1140590764240" ID="Freemind_Link_1325655149" MODIFIED="1140590765802" TEXT="flexform_ds_pi1.xml"/>
<node CREATED="1140590778324" ID="Freemind_Link_1233709897" MODIFIED="1140590779397" TEXT="locallang_db.php"/>
<node CREATED="1140590861986" ID="Freemind_Link_1109566859" MODIFIED="1140590863115" TEXT="ext_tables.php"/>
</node>
<node CREATED="1140591300517" ID="Freemind_Link_1563794632" MODIFIED="1140591303189" TEXT="payment processing">
<node CREATED="1140590984486" ID="Freemind_Link_990086537" MODIFIED="1140590985473" TEXT="credit_card.php"/>
<node CREATED="1140590991563" ID="Freemind_Link_1015409335" MODIFIED="1140590992828" TEXT="epayment.php"/>
<node CREATED="1140591001767" ID="Freemind_Link_1475822195" MODIFIED="1140591002729" TEXT="paypal.php"/>
<node CREATED="1140591013534" ID="Freemind_Link_1515282927" MODIFIED="1140591014622" TEXT="eway_payment.php"/>
</node>
</node>
<node CREATED="1140586290310" ID="Freemind_Link_1661976276" MODIFIED="1140586297378" TEXT="Function Flow">
<node CREATED="1140586299055" ID="Freemind_Link_43067547" MODIFIED="1140586300109" TEXT="main">
<node CREATED="1140586300867" ID="Freemind_Link_1822998368" MODIFIED="1140586303720" TEXT="init">
<node CREATED="1140586341654" ID="Freemind_Link_1735734424" MODIFIED="1140586345927" TEXT="includeFFConf"/>
</node>
<node CREATED="1140586304354" ID="Freemind_Link_693412649" MODIFIED="1140586308201" TEXT="processPostData">
<node CREATED="1140586355588" ID="Freemind_Link_1524425505" MODIFIED="1140586399831" TEXT="// capture piVars -&gt; basket,user,shipping,order"/>
</node>
<node CREATED="1140586309914" ID="Freemind_Link_1213175142" MODIFIED="1140586310972" TEXT="validate">
<node CREATED="1140586407221" ID="Freemind_Link_978021129" MODIFIED="1140586479873" TEXT="// validate dependant on what is present in piVars[submit_*]"/>
</node>
<node CREATED="1140586311546" ID="Freemind_Link_1795342316" MODIFIED="1140586316613" TEXT="processUserInput">
<node CREATED="1140586490421" ID="Freemind_Link_312947156" MODIFIED="1140586494211" TEXT="// command processing"/>
<node CREATED="1140586539343" ID="Freemind_Link_835109120" MODIFIED="1140586545938" TEXT="processAddToBasket"/>
<node CREATED="1140586546493" ID="Freemind_Link_562670886" MODIFIED="1140586567780" TEXT="processProductSearch"/>
<node CREATED="1140586550235" ID="Freemind_Link_1430509130" MODIFIED="1140586564484" TEXT="processSaveUser"/>
<node CREATED="1140586569159" ID="Freemind_Link_1581446026" MODIFIED="1141255374211" TEXT="processFinaliseCheckout">
<node CREATED="1141255403591" ID="Freemind_Link_526000625" MODIFIED="1141255406298" TEXT="render templates">
<node CREATED="1141255379463" ID="Freemind_Link_1858898162" MODIFIED="1141255381866" TEXT="send emails"/>
<node CREATED="1141255382662" ID="Freemind_Link_1918179194" MODIFIED="1141255399843" TEXT="save HTML order table against orders"/>
</node>
</node>
<node CREATED="1140586576131" ID="Freemind_Link_549685410" MODIFIED="1140586581944" TEXT="processReset"/>
</node>
<node CREATED="1141255281211" ID="Freemind_Link_1001052657" MODIFIED="1141255283719" TEXT="rendering">
<node CREATED="1140586321130" ID="Freemind_Link_510254065" MODIFIED="1140586326331" TEXT="renderer-&gt;init">
<node CREATED="1140586502401" ID="Freemind_Link_93168987" MODIFIED="1140586506838" TEXT="// create marker arrays"/>
</node>
<node CREATED="1140586326767" ID="Freemind_Link_148548047" MODIFIED="1141255374215" TEXT="renderer-&gt;renderSection"/>
</node>
</node>
</node>
<node CREATED="1141255830375" ID="Freemind_Link_1035239059" MODIFIED="1141255842316" TEXT="Core Classes">
<node CREATED="1141255843321" ID="Freemind_Link_1714334288" MODIFIED="1141255844897" TEXT="pi1"/>
<node CREATED="1141255845369" ID="Freemind_Link_1595942722" MODIFIED="1141255846689" TEXT="renderer">
<node CREATED="1141255950570" ID="Freemind_Link_916118424" MODIFIED="1141255959080" TEXT="NON TEMPLATE FUNCTIONS">
<node CREATED="1141255935067" ID="Freemind_Link_1014308847" MODIFIED="1141255943385" TEXT="renderShippingMethod"/>
<node CREATED="1141255944026" ID="Freemind_Link_791866062" MODIFIED="1141255948186" TEXT="renderPaymentMethod"/>
</node>
<node CREATED="1141255969419" ID="Freemind_Link_1932865618" MODIFIED="1141255973884" TEXT="MAIN FUNCTIONS">
<node CREATED="1141255847883" ID="Freemind_Link_1315433919" MODIFIED="1141255852023" TEXT="renderSection">
<node CREATED="1141255852451" ID="Freemind_Link_1340961874" MODIFIED="1141256163091" TEXT="renderSectionNoWrap&#xa;// decides on template type - plain, list, categorised">
<node CREATED="1141255911272" ID="Freemind_Link_426587" MODIFIED="1141256790573" TEXT="renderCategorisedProductList&#xa;// render list of items with category headings">
<node CREATED="1141255919950" ID="Freemind_Link_1162009923" MODIFIED="1141255924092" TEXT="renderProductListItems"/>
</node>
<node CREATED="1141255925242" ID="Freemind_Link_1250585272" MODIFIED="1141256806195" TEXT="renderList&#xa;// render straight forward list of items">
<node CREATED="1141255927448" ID="Freemind_Link_1130431654" MODIFIED="1141255930395" TEXT="renderListItems"/>
</node>
<node CREATED="1141255856396" ID="Freemind_Link_1853895485" MODIFIED="1141256819284" TEXT="renderComponent&#xa;// single template replace"/>
</node>
<node CREATED="1141255903974" ID="Freemind_Link_592096353" MODIFIED="1141256835839" TEXT="renderFormWrap&#xa;// wrap into form template&#xa;"/>
</node>
</node>
</node>
</node>
<node CREATED="1140593323088" FOLDED="true" ID="Freemind_Link_1043440569" MODIFIED="1140593335754" TEXT="Data Structures">
<node CREATED="1140593329245" FOLDED="true" ID="Freemind_Link_439219195" MODIFIED="1140593329245" TEXT="// library instances">
<node CREATED="1140593329245" MODIFIED="1140593329245" TEXT="var $renderer;"/>
<node CREATED="1140593329245" MODIFIED="1140593329245" TEXT="var $TSParser;"/>
<node CREATED="1140593329246" MODIFIED="1140593329246" TEXT="var $LANG;"/>
</node>
<node CREATED="1140593329246" FOLDED="true" ID="Freemind_Link_280352259" MODIFIED="1140593329246" TEXT="// user session variables">
<node CREATED="1140593329246" MODIFIED="1140593329246" TEXT="var $basket;                        // an array of product records with array key basket_qty set"/>
<node CREATED="1140593329247" MODIFIED="1140593329247" TEXT="var $order;"/>
<node CREATED="1140593329247" MODIFIED="1140593329247" TEXT="var $user;                                // array corresponding to fe_users table"/>
<node CREATED="1140593329247" MODIFIED="1140593329247" TEXT="var $payment;                        // array containing payment selections and details"/>
<node CREATED="1140593329248" MODIFIED="1140593329248" TEXT="var $shipping;                        // user shipping details - array corresponding to extended fields in fe_users table"/>
</node>
<node CREATED="1140593329248" FOLDED="true" ID="Freemind_Link_182686492" MODIFIED="1140593329248" TEXT="// storage for database results between controller doing query and renderer-&gt; init creating markers">
<node CREATED="1140593329249" MODIFIED="1140593329249" TEXT="var $record;                // single view"/>
<node CREATED="1140593329249" MODIFIED="1140593329249" TEXT="var $list;                        // search"/>
</node>
<node CREATED="1140593329249" MODIFIED="1140593329249" TEXT="// control variables">
<node CREATED="1140593329250" MODIFIED="1140593329250" TEXT="var $cmd=&apos;list&apos;;                   // main action for this request">
<node CREATED="1140593329250" MODIFIED="1140593329250" TEXT="//set by post variables config or default action is list of items on this page"/>
</node>
<node CREATED="1140593329250" MODIFIED="1140593329250" TEXT="var $renderWith;                // template section to render"/>
<node CREATED="1140593329251" MODIFIED="1140593329251" TEXT="var $template=&apos;&apos;;                  // contains content of main template file"/>
<node CREATED="1140593329251" MODIFIED="1140593329251" TEXT="var $listType;                        // derived from cmd where cmd begins with list to select custom list configuration"/>
<node CREATED="1140593329252" MODIFIED="1140593329252" TEXT="var $doReset=false;                // set by controller if a complete session reset is required"/>
</node>
<node CREATED="1140593329252" MODIFIED="1140593329252" TEXT="// feedback variables to pass error/warning feedback from controller to renderer">
<node CREATED="1140593329253" MODIFIED="1140593329253" TEXT="var $errors;"/>
<node CREATED="1140593329253" MODIFIED="1140593329253" TEXT="var $messages;"/>
</node>
<node CREATED="1140593329253" MODIFIED="1140593329253" TEXT="// form constants for javascript">
<node CREATED="1140593329253" MODIFIED="1140593329253" TEXT="var $formName=&apos;myform&apos;;"/>
<node CREATED="1140593329253" MODIFIED="1140593329253" TEXT="var $searchButtonName=&apos;do_search&apos;;"/>
<node CREATED="1140593329254" MODIFIED="1140593329254" TEXT="var $saveUserButtonName=&apos;do_save_user&apos;;"/>
<node CREATED="1140593329254" MODIFIED="1140593329254" TEXT="var $finaliseButtonName=&apos;finalise_order&apos;;"/>
</node>
</node>
</node>
<node CREATED="1140586618979" FOLDED="true" ID="Freemind_Link_465970725" MODIFIED="1140591205742" TEXT="Data Model">
<node CREATED="1140586625990" ID="Freemind_Link_1897272656" MODIFIED="1140591242896" TEXT="The extension creates database tables for products, categories and orders and extends the fe_users table. See the sql definition file ext_tables.sql for details."/>
</node>
</node>
</node>
</map>
