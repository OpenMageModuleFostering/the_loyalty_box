<?php
class Lb_Points_Model_Observer
{
    /**
    * earlySession.
    * 
    * earlySession function is used to initialise session.
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
    public function earlySession()
    {
        @session_start();
        Lb_Points_Helper_Data::debug_log("earlySession: SESSION HAS BEEN STARTED...", true);
    }
    
    /**
    * init.
    * 
    * init function used to initialise all configuration parameters.
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
   public function init() {
        $lb_request_id = 0;
        if (isset($_SESSION['LB_Session_RequestId'])) {
            if ($_SESSION['LB_Session_RequestId'] > 0) {
                $lb_request_id = $_SESSION['LB_Session_RequestId'];
            }
        }
        if (empty($lb_request_id)) {
            $clientId = Mage::getStoreConfig('lbconfig_section/lb_settings_group/client_id_field');
            $lb_request_id = Lb_Points_Helper_Data::getLbRequestId($clientId);
            $_SESSION['LB_Session_RequestId'] = $lb_request_id;
        }
        Lb_Points_Helper_Data::init($lb_request_id);
        //Lb_Points_Helper_Data::init();
    }
    
    /**
    * logCartUpdate.
    * 
    * logCartUpdate function used to update basket discount whenever product is added or removed.
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
   public function logCartUpdate() {
        self::init();
        $LB_Session = $_SESSION['LB_Session'];
        
        $quoteData = Mage::getSingleton('checkout/session')->getQuote();
        $cart = $quoteData->getAllItems();
        $CartContentsTotal = $quoteData['subtotal'];
        
        if (!empty($LB_Session) && $CartContentsTotal > 0) {
            $txtPhoneNumber = $LB_Session['Phone Number'];
            
            $CartContentsCount = Mage::helper('checkout/cart')->getSummaryCount();
            // Checked here if there is no change in cart
            if (isset($_SESSION['LB_Session']['CartContentsCount'])) {
                if ($_SESSION['LB_Session']['CartContentsCount'] == $CartContentsCount
                        && $_SESSION['LB_Session']['CartContentsTotal'] == $CartContentsTotal) {
                    if(isset($_SESSION['LB_Session']['CartAppliedRedeemPoints']) && isset($_SESSION['LB_Session']['totalRedeemPoints'])){
                        if($_SESSION['LB_Session']['CartAppliedRedeemPoints'] == $_SESSION['LB_Session']['totalRedeemPoints']){
                            return;
                        }
                    }
                    else
                    {
                        if(isset($_SESSION['LB_Session']['totalRedeemPoints'])){
                            if($_SESSION['LB_Session']['totalRedeemPoints'] > 0){
                                // allowed
                            }
                            else
                                return;
                        }
                        else
                            return;
                    }
                } else {
                    $_SESSION['LB_Session']['CartContentsCount'] = $CartContentsCount;
                    $_SESSION['LB_Session']['CartContentsTotal'] = $CartContentsTotal;
                }
            } else {
                $_SESSION['LB_Session']['CartContentsCount'] = $CartContentsCount;
                $_SESSION['LB_Session']['CartContentsTotal'] = $CartContentsTotal;
            }

            $lineItems = array();
            foreach ($cart as $item) {
                $product = $item->getProduct();
                $categories = $product->getCategoryIds();
                $itemId = $item->getItemId();

                $productData = array(
                    'productCode' => $item->getSku(),
                    'qty' => $item->getQty(),
                    'price' => $item->getProduct()->getPrice(),
                    'categoryCode' => Mage::getModel('catalog/category')->load($categories[0])->getName(),
                    'discountedPrice' => 0,
                    'description' => ''
                );
                array_push($lineItems, $productData);
            }
            $allowedDiscount = Lb_Points_Helper_Data::sendCartUpdate($txtPhoneNumber, $CartContentsTotal, $lineItems, 0);
            if ($allowedDiscount > 0) {

                $coupon_code = "";
                $coupon_name = "";
                if (isset($_SESSION['LB_Session']["LB_COUPON"])) {
                    if (!empty($_SESSION['LB_Session']["LB_COUPON"])) {
                        $coupon_code = $_SESSION['LB_Session']["LB_COUPON"];
                        $coupon_name = $_SESSION['LB_Session']["LB_COUPON_NAME"];
                    } else {
                        $coupon_code = "LB_" . rand(1111, 9999); //Generate code for coupon for current session. 
                        $_SESSION['LB_Session']["LB_COUPON"] = $coupon_code;
                        $coupon_name = "RULE_" . rand(1111, 9999); //Generate name for coupon for current session. 
                        $_SESSION['LB_Session']["LB_COUPON_NAME"] = $coupon_name;
                    }
                } else {
                    $coupon_code = "LB_" . rand(1111, 9999); //Generate code for coupon for current session. 
                    $_SESSION['LB_Session']["LB_COUPON"] = $coupon_code;
                    $coupon_name = "RULE_" . rand(1111, 9999); //Generate name for coupon for current session. 
                    $_SESSION['LB_Session']["LB_COUPON_NAME"] = $coupon_name;
                }
                // Check if therese Redeem points are availed for user if yes include its discount to cart.
                if($_SESSION['LB_Session']['totalRedeemPoints'] > 0){
                    $allowedDiscount = $_SESSION['LB_Session']['totalRedeemPoints'] + $allowedDiscount;
                    //$coupon_code = $_SESSION['LB_Session']["LB_COUPON"] . "_R_".$_SESSION['LB_Session']['totalRedeemPoints'];
                    //$_SESSION['LB_Session']["LB_COUPON"] = $coupon_code;
                    $_SESSION['LB_Session']['CartAppliedRedeemPoints'] = $_SESSION['LB_Session']['totalRedeemPoints'];
                }
                self::generateRule($coupon_name, $coupon_code, $allowedDiscount);
                Lb_Points_Helper_Data::debug_log("Generating coupon..." . $allowedDiscount, true);
            }
        }
        Lb_Points_Helper_Data::debug_log("Cart is updated...", true);
    }
    
    /**
    * generateRule.
    * 
    * generateRule function used to generate coupon code and apply it on customers cart.
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * @access public
    * 
    * @param type $name string
    * @param type $coupon_code string
    * @param type $discount double
    * 
    * @return void
    */
    public function generateRule($name = null, $coupon_code = null, $discount = 0)
    {
        if ($name != null && $coupon_code != null) {
            $oldRule = Mage::getModel('salesrule/rule')
                    ->getCollection()
                    ->addFieldToFilter('name', array('eq'=>$name))
                    ->addFieldToFilter('code', array('eq'=>$coupon_code))
                    ->getFirstItem();
            $oldRule->delete();
            
            $rule = Mage::getModel('salesrule/rule');
            $customer_groups = array(0, 1, 2, 3);
            $rule->setName($name)
                    ->setDescription($name)
                    ->setFromDate('')
                    ->setCouponType(2)
                    ->setCouponCode($coupon_code)
                    ->setUsesPerCustomer(1)
                    ->setUsesPerCoupon(1)
                    ->setCustomerGroupIds($customer_groups) //an array of customer grou pids
                    ->setIsActive(1)
                    ->setConditionsSerialized('')
                    ->setActionsSerialized('')
                    ->setStopRulesProcessing(0)
                    ->setIsAdvanced(1)
                    ->setProductIds('')
                    ->setSortOrder(0)
                    ->setSimpleAction('cart_fixed')
                    ->setDiscountAmount($discount)
                    ->setDiscountQty(null)
                    ->setDiscountStep(0)
                    ->setSimpleFreeShipping('0')
                    ->setApplyToShipping('0')
                    ->setIsRss(0)
                    ->setWebsiteIds(array(1));
            
            $item_found = Mage::getModel('salesrule/rule_condition_product_found')
                    ->setType('salesrule/rule_condition_product_found')
                    ->setValue(1) // 1 == FOUND
                    ->setAggregator('all'); // match ALL conditions
            $rule->getConditions()->addCondition($item_found);
           
            $rule->save();
            $discountWithoutRedeemPoints = $discount;
            
            if(isset($_SESSION['LB_Session']['totalRedeemPoints'])){
                if($_SESSION['LB_Session']['totalRedeemPoints'])
                    $discountWithoutRedeemPoints = $discount - $_SESSION['LB_Session']['totalRedeemPoints'];
            }
            
            $_SESSION['LB_Session']['loyaltybox_total_discount'] = $discountWithoutRedeemPoints;
            
            $message = Lb_Points_Helper_Data::$rewardProgrammeName." Discount of ".($discountWithoutRedeemPoints)." is applied to your cart.";
            Mage::getSingleton('core/session')->addSuccess($message);
            
            Mage::getSingleton('checkout/cart')
            ->getQuote()
            ->setCouponCode(strlen($coupon_code) ? $coupon_code: '')
            ->collectTotals()
            ->save();
            
            Lb_Points_Helper_Data::debug_log("Coupon generated..".$coupon_code, true);
        }
    }
    
    /**
    * orderProcesing.
    * 
    * orderProcesing function used to send final basket to the loyaltybox before checkout process.
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * 
    * @access public
    *
    * @param Varien_Event_Observer $observer 
    *
    * @return void
    */
   public function orderProcesing(Varien_Event_Observer $observer) {
        self::init();

        $LB_Session = $_SESSION['LB_Session'];
        if (!empty($LB_Session)) 
        {
            $apiserverBasketId = 0;
            if(isset($_SESSION['LB_Session']['basketId'])){
                $apiserverBasketId = $_SESSION['LB_Session']['basketId'];
            }
            //$totalItemsInCart = Mage::helper('checkout/cart')->getItemsCount(); //total items in cart
            $quoteData = Mage::getSingleton('checkout/session')->getQuote();
            $CartContentsTotal = $quoteData['subtotal'];
            
            // get all redeeem points by user.
            $totalRedeemPoints = 0;
            if(isset($_SESSION['LB_Session']['totalRedeemPoints']))
                $totalRedeemPoints = $_SESSION['LB_Session']['totalRedeemPoints'];
            
            $CartDiscount = 0;
            if(isset($_SESSION['LB_Session']['loyaltybox_total_discount'])){
                $CartDiscount = $_SESSION['LB_Session']['loyaltybox_total_discount'];
            }
            
            $CartDiscount = $CartDiscount + $totalRedeemPoints;
            
            // get coupon name which is applied on cart
            $coupon_code = "";
            $coupon_name = "";
            $discount_type = "cart_fixed";
            // built array for discount
            $CartAppliedCoupons = array();
            if($CartDiscount)
            if(isset($_SESSION['LB_Session']["LB_COUPON"]))
            {
                $coupon_name = $_SESSION['LB_Session']["LB_COUPON_NAME"];
                $coupon_code = $_SESSION['LB_Session']["LB_COUPON"];
                $cartCoupon = array(
                    "code" => $coupon_code,
                    "discount_type" => $discount_type,
                    "coupon_amount" => $CartDiscount,
                );
                array_push($CartAppliedCoupons,$cartCoupon);
            }
            
            
            $cartId = '';
            $merchantId = Lb_Points_Helper_Data::$clientId;
            $basketTotal = $CartContentsTotal;
            $lbRef = '--NA--';
   
            $discounts = array("cart_discount"=>$CartDiscount,"applied_coupon"=>$CartAppliedCoupons);
            $basketState = 'Checkout';
            $lbCustomerName = $LB_Session['Customer Name'];
            
            $result = Lb_Points_Helper_Data::newBasketState($cartId, $merchantId, $basketTotal, $lbRef, $lbCustomerName, $discounts, $basketState,0,$totalRedeemPoints,$apiserverBasketId);
            if(!empty($result)){
                if(array_key_exists('basket_id', $result)){
                    $_SESSION['LB_Session']['basketId'] = $result['basket_id'];
                }
            }
        }
        
   }
   
   /**
    * orderPlacedSuccessfully.
    * 
    * orderPlacedSuccessfully function used to send final basket to the loyaltybox before checkout processed.
    *
    * @version 1.0.0
    *
    * @author Double Eye
    *
    * @since 1.0.0
    * 
    * @access public
    *
    * @param Varien_Event_Observer $observer 
    * 
    * @return void
    */
   public function orderPlacedSuccessfully(Varien_Event_Observer $observer) {
        self::init();
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        try {
            
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
            $orderData = $order->debug();
           
            Lb_Points_Helper_Data::debug_log("Order process after checkout started order id: ".$orderIncrementId, true);

            $CartContents = $order->getItemsCollection();
            $CartContentsTotal = $orderData['subtotal'];
            $CartGrandTotal = $orderData['grand_total'];
            $usedCouponCode = $orderData['coupon_code'];
            $CartDiscount = $orderData['discount_amount'];
            $isLoyaltyIssued = 0;
            $cartCoupons = array();
            if (!empty($usedCouponCode)) {
                if (!empty($CartDiscount)) {
                    $cartCouponsChild = array(
                        'code' => $usedCouponCode,
                        'discount_type' => 'cart_fixed',
                        'coupon_amount' => $CartDiscount
                    );
                    array_push($cartCoupons, $cartCouponsChild);
                }
            }

            $apiserverBasketId = 0;

            $LB_Session = "";
            if (isset($_SESSION['LB_Session']))
                $LB_Session = $_SESSION['LB_Session'];

            Lb_Points_Helper_Data::debug_log("Order processed started", true);
            if (!empty($LB_Session)) {
                $txtPhoneNumber = $LB_Session['Phone Number'];
                $lbCustomerName = $LB_Session['Customer Name'];
                if (isset($_SESSION['LB_Session']['basketId'])) {
                    $apiserverBasketId = $_SESSION['LB_Session']['basketId'];
                }
                $_SESSION['LB_Session']['basketId'] = 0;
                $_SESSION['LB_Session']['CartContentsCount'] = 0;

                $lineItems = array();
                foreach ($CartContents as $item) {
                    $product_id = $item->product_id;
                    $_product = Mage::getModel('catalog/product')->load($product_id);
                    $cats = $_product->getCategoryIds();
                    $category_id = $cats[0];
                    $category = Mage::getModel('catalog/category')->load($category_id);
                    $category_name = $category->getName();
                    $lineItem = array(
                        'productCode' => $item->sku,
                        'categoryCode' => $category_name,
                        'qty' => $item->getData('qty_ordered'),
                        'price' => $item->getPrice(),
                        'discountedPrice' => 0,
                        'description' => $item->getName(),
                    );
                    array_push($lineItems, $lineItem);
                }
                
                $CommitTransaction = 0;
                $result = Lb_Points_Helper_Data::sendCartFinal($txtPhoneNumber, $CartContentsTotal, $lineItems, $CommitTransaction);

                $RequestLineItemRedemptionResult = $result->RequestLineItemRedemptionResult;
                $standardHeader = $RequestLineItemRedemptionResult->standardHeader;
                Lb_Points_Helper_Data::debug_log("LB API called sendCartFinal(uncommited): RequestLineItemRedemption", true);
                if ($standardHeader->status == 'A') {
                    $balances = $RequestLineItemRedemptionResult->balances;
                    $Balance = $balances->Balance;
                    $allowedDiscount = 0;

                    foreach ($Balance as $balValue) {
                        if ($balValue->valueCode == 'Discount') {
                            $allowedDiscount = $balValue->amount;
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

                    Lb_Points_Helper_Data::debug_log("LB API called : got Discount " . $allowedDiscount . "%", true);
                    //if ($allowedDiscount > 0) {
                            
                        $CommitTransaction = 1;
                        $result = Lb_Points_Helper_Data::sendCartFinal($txtPhoneNumber, $CartContentsTotal, $lineItems, $CommitTransaction);
                        Lb_Points_Helper_Data::debug_log("LB API called sendCartFinal(commited): RequestLineItemRedemption", true);

                        $RequestLineItemRedemptionResult = $result->RequestLineItemRedemptionResult;
                        $identification = $RequestLineItemRedemptionResult->identification;
                        
                        // REDEEM POINTS IF ANY
                        $totalRedeemPoints = $_SESSION['LB_Session']['totalRedeemPoints'];
                        $CardOrPhoneNumber = $LB_Session['Phone Number'];
                        if($totalRedeemPoints > 0)
                        {
                            $redeemResult = Lb_Points_Helper_Data::redeemPoints($CardOrPhoneNumber,$lineItems,$totalRedeemPoints);
                            $UpdateSaleResult = $redeemResult->UpdateSaleResult;
                            $standardHeader = $UpdateSaleResult->standardHeader;
                            $identification = $UpdateSaleResult->identification;
                            Lb_Points_Helper_Data::debug_log("LB API called : UpdateSale", true);
                            if ($standardHeader->status == 'A') {
                                $_SESSION['LB_Session']['totalRedeemPoints'] = 0;
                                $balances = $UpdateSaleResult->balances;
                                $Balance = $balances->Balance;
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

                                Lb_Points_Helper_Data::debug_log("LB API called : redeem points ".$totalRedeemPoints."", true);
                            }
                        }
                        else
                            $totalRedeemPoints = 0;
                        // END OF REDEEM 
                        // ISSUE DISCOUNTED AMOUNT AS LB POINTS 
                        //$issuePoints = $allowedDiscount;
                        $issueResult = Lb_Points_Helper_Data::issuePoints($txtPhoneNumber, $lineItems, $CartGrandTotal);
                        $UpdateSaleResult = $issueResult->UpdateSaleResult;
                        $standardHeader = $UpdateSaleResult->standardHeader;
                        $earnPoints = 0;
                        Lb_Points_Helper_Data::debug_log("LB API called issuePoints: UpdateSale", true);
                        if ($standardHeader->status == 'A') {
                            $balances = $UpdateSaleResult->balances;
                            $Balance = $balances->Balance;
                            foreach ($Balance as $balValue) {
                                if ($balValue->valueCode == 'Points') {
                                    $earnPoints = $balValue->difference;
                                    $_SESSION['LB_Session']['lb_points'] = $balValue->amount;
                                    $_SESSION['LB_Session']['lb_points_difference'] = $balValue->difference;
                                    $_SESSION['LB_Session']['lb_points_exchangeRate'] = $balValue->exchangeRate;
                                }
                            }
                        }
                        $isLoyaltyIssued = 1;
                        Lb_Points_Helper_Data::debug_log("Issued Gift Issued " . $issuePoints . ".", true);
                        Lb_Points_Helper_Data::debug_log("Earn loyalty points " . $earnPoints . ".", true);
                    
                        // STATE API CALL
                        $cartId = '';
                        $merchantId = Lb_Points_Helper_Data::$clientId;
                        $basketTotal = $CartContentsTotal;
                        $lbRef = $identification->transactionId;
                        $discounts = array("cart_discount" => $CartDiscount, "applied_coupon" => $CartAppliedCoupons);
                        $basketState = 'Paid';

                        // REMOVED CURRENT SESSION COUPON FROM DB.
                        $coupon_code = "";
                        if (isset($_SESSION['LB_Session']["LB_COUPON"])) {
                            if (!empty($_SESSION['LB_Session']["LB_COUPON"])) {
                                $coupon_code = $_SESSION['LB_Session']["LB_COUPON"];
                                $_SESSION['LB_Session']["LB_COUPON"] = "";
                                $_SESSION['LB_Session']["LB_COUPON_NAME"] = "";
                            }
                        }
                        
                        Lb_Points_Helper_Data::newBasketState($cartId, $merchantId, $basketTotal, $lbRef, $lbCustomerName, $discounts, $basketState, $earnPoints, $totalRedeemPoints, $apiserverBasketId, $order_id, $isLoyaltyIssued);
                        $_SESSION['LB_Session_RequestId'] = 0;
                        $_SESSION['LB_Session']['loyaltybox_total_discount'] = 0;
                        
                    //}
                }
            }
            
        } catch (Exception $e) {
            
            Lb_Points_Helper_Data::debug_log('Caught exception: ' .  $e->getMessage() . ".", true);
            Lb_Points_Helper_Data::handleError('Caught exception: ' .  $e->getMessage() . ".", true);
            
        }
        
   }
   
}