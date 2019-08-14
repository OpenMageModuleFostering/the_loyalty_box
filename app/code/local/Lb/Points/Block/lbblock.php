<?php

class Lb_Points_Block_Lbblock extends Mage_Core_Block_Template {

    /**
    * registration_button.
    *
    * This method is used to show connect with loyaltybox button
    * which will open registration / login popup
    * if session is already runnig for logged in user it will show users account details with available loyalty points.
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * @access public
    *
    * @static
    */
    public function registration_button() {

        $rewardProgrammeName = Mage::getStoreConfig('lbconfig_section/lb_settings_group/reward_programme_name_field');
        Lb_Points_Helper_Data::$rewardProgrammeName = $rewardProgrammeName;
        if(isset($_SESSION['LB_Session']))
        {
            if(!empty($_SESSION['LB_Session']))            
            {
                $LB_Session = $_SESSION['LB_Session'];
                Lb_Points_Helper_Data::debug_log("Rendered session user with his LB Points", true);
                ?>
                <div class="connectlbbtn lb-box">
                    <span class="h2"><?php echo Lb_Points_Helper_Data::$rewardProgrammeName;?></span>
                    <div class="loyaltybox-info-contain">
                        <?php 
                        if(isset($_SESSION['LB_Session']['totalRedeemPoints']))
                            $remainingPoints = $LB_Session['lb_points'] - $_SESSION['LB_Session']['totalRedeemPoints'];
                        else
                            $remainingPoints = $LB_Session['lb_points'];
                        ?>
                        <?php echo "<strong>Hi ".$LB_Session['Customer Name']."</strong> | <a id='lbLogout' href='javascript:void(0);'>Logout(".Lb_Points_Helper_Data::$rewardProgrammeName.")</a></br>You have ".$remainingPoints." Points in your <strong>".Lb_Points_Helper_Data::$rewardProgrammeName."</strong> account."; ?>
                    </div>
                    <div class="lbMsg"></div>
                </div>
                <?php
            } 
            else 
            {
                Lb_Points_Helper_Data::debug_log("Rendered Connect with Loyalty Box button", true);
                ?>
                <div class="connectlbbtn registrationBtn lb-box">
                    <button class="button btn-cart" onclick="return showForm('popup_form_registration')" title="Connect with <?php echo Lb_Points_Helper_Data::$rewardProgrammeName;?>" type="button"><span><span>Connect with <?php echo Lb_Points_Helper_Data::$rewardProgrammeName;?></span></span></button>
                    <div class="lbMsg"></div>
                </div>
                
                <script type="text/javascript">
                    function showForm(id){
                        win = new Window({ title: "Connect with Loyalty Box", zIndex:3000, destroyOnClose: true, recenterAuto:true, resizable: false, width:400, height:'auto', minimizable: true, maximizable: false, draggable: true});
                        win.setContent(id, false, false);
                        win.showCenter();
                    }
                </script>
            <?php
                Lb_Points_Helper_Data::debug_log("End: Rendered Connect with Loyalty Box button", true);
            }
        }
        else 
        {
            Lb_Points_Helper_Data::debug_log("Rendered Connect with Loyalty Box button", true);
            ?>
            <div class="connectlbbtn registrationBtn lb-box">
                <button class="button btn-cart" onclick="return showForm('popup_form_registration')" title="Connect with <?php echo Lb_Points_Helper_Data::$rewardProgrammeName;?>" type="button"><span><span>Connect with <?php echo Lb_Points_Helper_Data::$rewardProgrammeName;?></span></span></button>
                <div class="lbMsg"></div>
            </div>
            <script type="text/javascript">
                function showForm(id){
                    win = new Window({ title: "Connect with <?php echo Lb_Points_Helper_Data::$rewardProgrammeName;?>", zIndex:3000, destroyOnClose: true, recenterAuto:true, resizable: false, width:400, height:'auto', minimizable: true, maximizable: false, draggable: true});
                    win.setContent(id, false, false);
                    win.showCenter();
                }
            </script>
        <?php
            Lb_Points_Helper_Data::debug_log("End: Rendered Connect with Loyalty Box button", true);
        }
    }

    /**
    * registration_popup.
    *
    * This method is used to render registration/login dialog box.
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * @access public
    *
    * @static
    */
    public function registration_popup() {
        ?>
        <div style="display:none;">
            <div id="popup_form_registration" class="main">
                <div class="col-main" style="float: none;width:100%;">
                    <div class="page-title">
                        <h1>Connect with <?php echo Lb_Points_Helper_Data::$rewardProgrammeName;?></h1>
                    </div>
                    <div id="registerLB">
                        <form id="registrationForm">
                            <div class="fieldset">
                                <h2 class="legend">Registration</h2>
                                <p class="required">* Required Fields</p>
                                <div id="formRegisterSuccess" ></div>
                                <ul class="form-list">
                                    <li class="fields">
                                        <div class="field">
                                            <label class="required" for="name"><em>*</em>Name</label>
                                            <div class="input-box">
                                                <input type="text" class="input-text required-entry" value="" title="Name" id="name" name="name">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label class="required" for="email"><em>*</em>Email Address</label>
                                            <div class="input-box">
                                                <input type="email" spellcheck="false" autocorrect="off" autocapitalize="off" class="input-text required-entry validate-email" value="" title="Email" id="email" name="email">
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <label class="required" for="phonenumber"><em>*</em>Phone number</label>
                                        <div class="input-box">
                                            <input type="tel" class="input-text required-entry validate-number IsValidCellNumber" value="" title="Phone number" id="phonenumber" name="phonenumber">
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="buttons-set lb-btn-register">
                                <button class="button" title="Submit" type="submit"><span><span>Submit</span></span></button>
                            </div>
                            <div class="lb-links">
                                <div >
                                    Or <a href="javascript:void(0);" id="lnkLBLogin" style="color:burlywood;">login</a> if already registered.
                                </div>
                                <div>
                                    download our <a href="javascript:void(0);" id="lnkDownloadApp" style="color:burlywood;">mobile application</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div style="display:none;" id="loginLB">
                        <form id="loginForm">
                            <div class="fieldset">
                                <h2 class="legend">Login</h2>
                                <p class="required">* Required Fields</p>
                                <div id="formLoginLowestSuccess" ></div>
                                <ul class="form-list">
                                    <li>
                                        <label class="required" for="txtCardNumber"><em>*</em>Card Number / Phone number / OTP</label>
                                        <div class="input-box">
                                            <input type="tel" class="input-text required-entry validate-number IsValidCartNumber" value="" title="Telephone" id="txtCardNumber" name="txtCardNumber" >
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="buttons-set lb-btn-register">
                                <button class="button" title="Submit" type="submit"><span><span>Submit</span></span></button>
                            </div>
                            <div class="lb-links">
                                <div>
                                    Or <a href="javascript:void(0);" id="lnkLBRegister" style="color:burlywood;">click here</a> to register.
                                </div>
                                <div>
                                    download our <a href="javascript:void(0);" id="lnkDownloadApp" style="color:burlywood;">mobile application</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <span id="formLoader" >
                        <img src="<?php echo $this->getSkinUrl("images/opc-ajax-loader.gif") ?>">
                    </span>
                </div>
            </div>
        </div>
        <style>
            .frm_error{
                color:red;
                font-size: 13px;
                margin: 5px 0 0;
            }
            .frm_success{
                color:green;
                font-size: 13px;
                margin: 5px 0 0;
            }
            #formLoader{
                display:none;
            }
            .lb-btn-register{
                margin-bottom: 5px;
            }
            .lb-btn-register::after {
                clear: none;
                content: "";
                display: table;
            }
            .lb-links{
                color: #636363;
                font-family: "Helvetica Neue",Verdana,Arial,sans-serif;
                font-size: 13px;
                line-height: 1.5;
                margin-top: -10px;
            }
            
            @media screen and (max-width: 479px) {
                .lb-btn-register::after {
                    clear: both;
                    content: "";
                    display: table;
                }
            }
            #popup_form_registration {
                width: auto;
            }
            .connectlbbtn.lb-box {
                clear: both;
                padding:5px;
            }

            .registrationBtn h2 {
                font-size: 12px;
                font-weight: bold;
                margin: 0 0 5px;
            }
            
            #formRegisterSuccess{
                font-size: 13px;
                margin: 5px 0 0;
                color: green;
            }
            
            .dialog_content {
                background-color: #F4F4F4;
                color: #636363;
                font-family: Tahoma,Arial,sans-serif;
                font-size: 10px;
                overflow: auto;
                padding-bottom: 5px!important;
            }
           
            .redeem_lbpoints input {
                margin-bottom: 5px;
            }
            .lb-redeem-wrapper{
                margin-top: 5px;
                display: none;
            }
            <?php if(strpos($_SERVER['REQUEST_URI'], '/checkout/cart/') !== false){?>
            .registrationBtn{
                background-color: #f4f4f4;
                border: 1px solid #cccccc;
                padding: 10px;
                margin-bottom: 20px;
            }
            <?php }?>
            
        </style>
        <script type="text/javascript">
           
            jQuery("#loginLB").hide();
            jQuery("#lnkLBLogin").click(function(){
                jQuery("#loginLB").show();
                jQuery("#registerLB").hide();
                jQuery(".dialog_content").css('height','auto');
            });

            jQuery("#lnkLBRegister").click(function(){
                jQuery("#loginLB").hide();
                jQuery("#registerLB").show();
                jQuery(".dialog_content").css('height','auto');
            });
            
            Validation.add('IsValidCartNumber', 'Only 10 or 15 digits are allowed.', function(v) {
                return  (v.length == 10 || v.length == 15); // || /^\s+$/.test(v));
            }); 
            
            Validation.add('IsValidCellNumber', 'Only 10 digits are allowed.', function(v) {
                return  (v.length == 10); // || /^\s+$/.test(v));
            }); 
               
            
            //var dataForm = new VarienForm('lowest-form-validate', true);
            var formId = 'registrationForm';
            var myForm = new VarienForm(formId, true);
            var handleSubmit = true;
            function doAjax() {
                var postUrl = "<?php echo Mage::getBaseUrl() . 'lb/index/register' ?>";
                jQuery(".dialog_content").css('height','auto');
                if (myForm.validator.validate()) {
                    var txtName =  jQuery("#name");
                    var txtEmail = jQuery("#email");
                    var txtPhoneNumber = jQuery("#phonenumber");
                    var tips = jQuery("#formRegisterSuccess");
                    tips.text('');
                    var data = {
                        'txtName': txtName.val(),
                        'txtEmail': txtEmail.val(),
                        'txtPhoneNumber': txtPhoneNumber.val()
                    };
                    if(handleSubmit){
                        handleSubmit = false;
                        jQuery("#formLoader").show();
                        jQuery.post(postUrl, data, function(response) {
                            handleSubmit = true;
                            if(response.status == '1'){
                                jQuery("#formLoader").hide();
                                tips.text(response.message).addClass( "frm_success" );
                                txtName.val('');
                                txtEmail.val('');
                                txtPhoneNumber.val('');
                                jQuery(".dialog_close").trigger('click');
                                window.location.reload();
                            }
                            else{
                                tips.text(response.message).addClass( "frm_error" );
                                jQuery("#formLoader").hide();
                            }
                        },'JSON');
                    }
                }
            }
            // REGISTRATION CALL BACK
            new Event.observe('registrationForm', 'submit', function(e){
                e.stop();
                doAjax();
            });
            
            
            // login js
            var loginFormId = 'loginForm';
            var loginForm = new VarienForm(loginFormId, true);
            jQuery("#loginForm button").click(function(){
                var postUrl = "<?php echo Mage::getBaseUrl() . 'lb/index/login' ?>";
                var txtCardNumber = jQuery("#txtCardNumber").val();
                jQuery(".dialog_content").css('height','auto');
                if (loginForm.validator.validate()) {
                jQuery("#formLoader").show();
                        jQuery.post(postUrl,{'txtCardNumber':txtCardNumber}, function(response) {
                            if(response.status == '1'){
                                jQuery("#formLoader").hide();
                                jQuery(".dialog_close").trigger('click');
                                Element.show('formLoginLowestSuccess');
                                jQuery("#formLoginLowestSuccess").html(response.message).addClass( "frm_success" );
                                jQuery(".connectlbbtn").html(response.replaceBtn);
                                window.location.reload();
                            }
                            else{
                                jQuery("#formLoader").hide();
                                Element.show('formLoginLowestSuccess');
                                jQuery("#formLoginLowestSuccess").html(response.message).addClass( "frm_error" );
                            }
                        },'JSON');
                }
                return false;
            });
                
                        
            // Add connect with loyaltybox button to right side bar
                /*if(jQuery(".cart-forms").length == 1)
                {
                    var btnHtml = jQuery(".connectlbbtn").html();
                    jQuery(".connectlbbtn").html('');
                    jQuery(".cart-forms").prepend("<div class='discount connectlbbtn'>"+btnHtml+"</div>");

                }*/
            // End : Add connect with loyaltybox button to right side bar.
                  
            // LOGOUT CALL BACK
            jQuery("#lbLogout").click(function(){
                var postUrl = "<?php echo Mage::getBaseUrl() . 'lb/index/logout' ?>";
                        jQuery.post(postUrl, function(response) {
                            if(response.status == '1'){
                                window.location.reload();
                            }
                            else{
                                jQuery("lbMsg").html(response.message).addClass( "frm_error" );
                            }
                        },'JSON');
            });
            // end of logout
            //end of javascript code
            <?php if(strpos($_SERVER['REQUEST_URI'], '/checkout/cart/') !== false){?>
                jQuery(".connectlbbtn").parent().addClass('cart-forms');
            <?php }else{?>
                var regBox = jQuery(".connectlbbtn");
                jQuery(".add-to-cart").append(regBox);
                jQuery(".add-to-cart").css("clear","both");
            <?php }?>
        </script>
        <?php 
    }

    /**
    * redeem_points.
    *
    * This method is used to render Redeem Points form
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * @access public
    *
    * @static
    */
    public function redeem_points() {
        $rewardProgrammeName = Mage::getStoreConfig('lbconfig_section/lb_settings_group/reward_programme_name_field');
        Lb_Points_Helper_Data::$rewardProgrammeName = $rewardProgrammeName;
        if(isset($_SESSION['LB_Session']))
        {
            if(!empty($_SESSION['LB_Session']))            
            {
                $LB_Session = $_SESSION['LB_Session'];
                Lb_Points_Helper_Data::debug_log("Rendered session user with his LB Points", true);
                ?>
            <div class="redeem_lbpoints lb-box">
                <form id="frmRedeemPoints" onsubmit=" return false;">
                    <label>
                        Want to Redeem Loyalty Points? <a class="btnShowRedeem" href="javascript:void(0);" >Click here</a> to redeem.
                    </label>
                    <div class="lb-redeem-wrapper">
                        <label>Enter Loyalty Points</label>
                        <input type="text" class="input-text required-entry lb-double" value="" title="Enter Loyalty points" placeholder="Enter Loyalty points" id="txtRedeemPoints" name="txtRedeemPoints">
                        <div class="redeemMsg"></div>
                        <div class="button-wrapper">
                            <button id="btnRedeemPoints" value="Redeem" class="button" title="Redeem" type="button"><span><span>Redeem</span></span></button>
                            <!-- button value="Cancel" class="button btnShowRedeem" title="Cancel" type="button"><span><span>Cancel</span></span></button -->
                        </div>
                        
                    </div>
                </form>
            </div>
        <script>
            // Redeem points
            jQuery(".btnShowRedeem").click(function(){
                jQuery(".lb-redeem-wrapper").toggle('display');
            });
            
            var frmRedeemPoints = 'frmRedeemPoints';
            var redeemPoints = new VarienForm(frmRedeemPoints, true);
            Validation.add('lb-double', 'Please enter a number greater than 0 in this field.', function(v) {
                return  (v > 0); // || /^\s+$/.test(v));
            });
            jQuery("#btnRedeemPoints").click(function(){
                if (redeemPoints.validator.validate()) {
                    jQuery(this).attr('disabled','disabled');
                
                var postUrl = "<?php echo Mage::getBaseUrl() . 'lb/index/redeem' ?>";
                var txtRedeemPoints = jQuery("#txtRedeemPoints").val();
                        jQuery.post(postUrl,{'txtRedeemPoints':txtRedeemPoints},function(response) {
                            if(response.status == '1'){
                                jQuery(".redeemMsg").html(response.message).addClass( "frm_success" );
                                window.location.reload();
                            }
                            else{
                                jQuery(".redeemMsg").html(response.message).addClass( "frm_error" );
                                jQuery("#btnRedeemPoints").removeAttr('disabled');
                            }
                        },'JSON');
                }
            });
        </script>
        <style>
            .lb-box
            {
                background-color: #f4f4f4;
                border: 1px solid #cccccc;
                padding: 10px;
                margin-bottom: 20px;
            }
            .lb-redeem-wrapper label,.redeemMsg{
                padding-bottom: 5px;
            }
        </style>
        <?php 
            }
        }
    }

}