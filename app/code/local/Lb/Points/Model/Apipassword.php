<?php 
class Lb_Points_Model_Apipassword extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $apiPassword = $this->getValue(); //get the value from our config
        if(empty($apiPassword))
        {
             Mage::throwException("API Password should not be empty.");
        } 
        return parent::save();  //call original save method so whatever happened 
                                //before still happens (the value saves)
    }
}