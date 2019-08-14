<?php
class Lb_Points_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
    * _construct.
    * 
    * Contructor function used to initialise all configuration parameters.
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * @access public
    *
    *
    * @return void
    */
    public function _construct(){
        $lb_request_id = 0;
         if (isset($_SESSION['LB_Session_RequestId']))
         {
             if($_SESSION['LB_Session_RequestId'] > 0)
             {
                 $lb_request_id = $_SESSION['LB_Session_RequestId'];
             }
         }
        if(empty($lb_request_id)){
            $clientId = Mage::getStoreConfig('lbconfig_section/lb_settings_group/client_id_field');
            $lb_request_id = Lb_Points_Helper_Data::getLbRequestId($clientId);
            $_SESSION['LB_Session_RequestId'] = $lb_request_id;
        }
        Lb_Points_Helper_Data::init($lb_request_id);
        //Lb_Points_Helper_Data::init();
    }
    
    /**
    * indexAction.
    * 
    * renders magento default layout.
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * @access public
    *
    *
    * @return void
    */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
    * registerAction.
    * 
    * Ajax request callback function to raise request to Loyaltybox API for register user.
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * @access public
    *
    *
    * @return void
    */
    public function registerAction()
    {
        Lb_Points_Helper_Data::debug_log("Registration request sent to LB", true);
        $txtName = $_POST['txtName'];
        $txtEmail = $_POST['txtEmail'];
        $txtPhoneNumber = $_POST['txtPhoneNumber'];
        $result = Lb_Points_Helper_Data::registerUser($txtName,$txtEmail,$txtPhoneNumber);
        echo json_encode($result);
    }
    
    /**
    * registerAction.
    * 
    * Used to get logged in to Loyaltybox Programme.
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * @access public
    *
    *
    * @return void
    */
    public function loginAction()
    {
        Lb_Points_Helper_Data::debug_log("Login :".$_POST['txtCardNumber'], true);
        $txtCardNumber = $_POST['txtCardNumber'];
        $result = Lb_Points_Helper_Data::verifyUser($txtCardNumber);
        echo json_encode($result);
        die;
        //echo "You have logged in successfully.";
    }
    
    /**
     * logoutAction.
     *
     * Unset loyaltybox user session
     * 
     * @version 1.0.0
     *
     * @author Double Eye
     *
     * @since 1.0.0
     * @access public
     */
    public function logoutAction(){
        $coupon_code = "";
        $coupon_name = "";
        if(isset($_SESSION['LB_Session']["LB_COUPON"])){
            if(!empty($_SESSION['LB_Session']["LB_COUPON"]))
            {
                $coupon_code = $_SESSION['LB_Session']["LB_COUPON"];
                $coupon_name = $_SESSION['LB_Session']["LB_COUPON_NAME"];
            }
        }
        if(!empty($coupon_code))
        {
            // delete coupon
            $oldRule = Mage::getModel('salesrule/rule')
                    ->getCollection()
                    ->addFieldToFilter('name', array('eq'=>$coupon_name))
                    ->addFieldToFilter('code', array('eq'=>$coupon_code))
                    ->getFirstItem();
            $oldRule->delete();
        }
        $message = "You have successfully logged out with ".Lb_Points_Helper_Data::$rewardProgrammeName.".";
        Mage::getSingleton('core/session')->addSuccess($message);
        $_SESSION['LB_Session'] = NULL;
        $_SESSION['LB_Session_RequestId'] = 0;
        echo json_encode(array('status' => 1, 'message' => "You have successfully logged out with ".Lb_Points_Helper_Data::$rewardProgrammeName."."));
        die;
    }
    
    /**
     * redeemAction.
     *
     * Used to redeem Loyalty box points for user.
     * 
     * @version 1.0.0
     *
     * @author Double Eye
     *
     * @since 1.0.0
     * @access public
     */
    public function redeemAction(){
        $txtRedeemPoints = $_POST['txtRedeemPoints'];
        $quoteData = Mage::getSingleton('checkout/session')->getQuote();
        
        $CartContentsTotal = $quoteData['subtotal'];
        $CartTotal = $quoteData['grand_total'];
        
        if(!empty($txtRedeemPoints)){
            $LB_Session = $_SESSION['LB_Session'];
            if (!empty($LB_Session)) {
                // confirm loyalty points available or not and update to session if available.
                $CardOrPhoneNumber = $LB_Session['Phone Number'];
                $CardPoints = Lb_Points_Helper_Data::getCardPoints($CardOrPhoneNumber);
                $InquiryResult = $CardPoints->InquiryResult;
                $balances = $InquiryResult->balances;
                $Balance = $balances->balance;
                foreach ($Balance as $balValue) {
                    if ($balValue->valueCode == 'Discount') {
                        $_SESSION['LB_Session']['lb_discount'] = $balValue->amount;
                        $_SESSION['LB_Session']['lb_discount_difference'] = $balValue->difference;
                        $_SESSION['LB_Session']['lb_discount_exchangeRate'] = $balValue->exchangeRate;
                    } elseif ($balValue->valueCode == 'Points') {
                        $_SESSION['LB_Session']['lb_points'] = $balValue->amount;
                        $_SESSION['LB_Session']['lb_points_difference'] = $balValue->difference;
                        $_SESSION['LB_Session']['lb_points_exchangeRate'] = $balValue->exchangeRate;
                    } elseif ($balValue->valueCode == 'ZAR') {
                        $_SESSION['LB_Session']['lb_zar'] = $balValue->amount;
                        $_SESSION['LB_Session']['lb_zar_difference'] = $balValue->difference;
                        $_SESSION['LB_Session']['lb_zar_exchangeRate'] = $balValue->exchangeRate;
                    }
                }
                Lb_Points_Helper_Data::debug_log("LB API called : Inquiry to check Points Balance", true);
                
                $totalRedeemPoints = 0;
                if(isset($_SESSION['LB_Session']['totalRedeemPoints']))
                {
                    if($_SESSION['LB_Session']['totalRedeemPoints'] > 0){
                        $totalRedeemPoints = $_SESSION['LB_Session']['totalRedeemPoints'] + $txtRedeemPoints;
                    }
                }
                
                if($totalRedeemPoints == 0){
                    $totalRedeemPoints = $txtRedeemPoints;
                }
                
                if ($txtRedeemPoints > 0 && $totalRedeemPoints <= $CartTotal && is_numeric($txtRedeemPoints)) {
                    
                    if ($_SESSION['LB_Session']['lb_points'] >= $totalRedeemPoints) {
                        //self::generate_discount_coupon($txtRedeemPoints, 'fixed_cart', 'REDEEM');
                        if(isset($_SESSION['LB_Session']['totalRedeemPoints']))
                        {
                            if($_SESSION['LB_Session']['totalRedeemPoints'] > 0){
                                $_SESSION['LB_Session']['totalRedeemPoints'] = $_SESSION['LB_Session']['totalRedeemPoints'] + $txtRedeemPoints;
                            }
                            else
                                $_SESSION['LB_Session']['totalRedeemPoints'] = $txtRedeemPoints;
                        }
                        else
                        {
                            $_SESSION['LB_Session']['totalRedeemPoints'] = $txtRedeemPoints;
                        }
                        $message = "You have redeemed ".$_SESSION['LB_Session']['totalRedeemPoints']." Loyalty Points successfully and applied discount to your cart.";
                        $messageJson = "You have redeemed ".$_SESSION['LB_Session']['totalRedeemPoints']." Loyalty Points successfully and discount is being applied to your cart.";
                        Mage::getSingleton('core/session')->addSuccess($message);
                        echo json_encode(array('status' => 1, 'message' => $messageJson));
                    }else {
                        echo json_encode(array('status' => 0, 'message' => "You don't have sufficient Loyalty Points to redeem."));
                    }
                } else {
                    echo json_encode(array('status' => 0, 'message' => "Please enter valid Loyalty Points."));
                }
            }
            else
                echo json_encode(array('status' => 0, 'message' => Lb_Points_Helper_Data::$rewardProgrammeName." session expired."));
        }
        else
            echo json_encode(array('status' => 0, 'message' => "Please enter loyalty points."));
        die;
    }
}