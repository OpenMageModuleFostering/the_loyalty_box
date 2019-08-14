<?php 
class Lb_Points_Model_Clientid extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $clientid = $this->getValue(); //get the value from our config
        if(!empty($clientid))
        {
            if (!preg_match('/^[0-9]*$/', $clientid))    
            {
                Mage::throwException("Client Id should be numeric with no decimal points.");
            }
        }  else {
            Mage::throwException("Client Id should not be empty.");
        }
 
        return parent::save();  //call original save method so whatever happened 
                                //before still happens (the value saves)
    }
}