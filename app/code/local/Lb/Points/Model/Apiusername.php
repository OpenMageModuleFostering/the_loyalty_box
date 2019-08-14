<?php 
class Lb_Points_Model_Apiusername extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $apiUsername = $this->getValue(); //get the value from our config
        if(!empty($apiUsername))
        {
            if (!preg_match('/^[0-9]*$/', $apiUsername))   
            {
                Mage::throwException("API Username should be numeric with no decimal points.");
            }
        }  else {
            Mage::throwException("API Username should not be empty.");
        }
 
        return parent::save();  //call original save method so whatever happened 
                                //before still happens (the value saves)
    }
}