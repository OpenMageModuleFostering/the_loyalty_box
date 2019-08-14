<?php 
class Lb_Points_Model_Registrationmessage extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $registrationMessage = $this->getValue(); //get the value from our config
        if(empty($registrationMessage))
        {
          Mage::throwException("Registration Completed Message should not be empty.");
        }  
        return parent::save();  //call original save method so whatever happened 
                                //before still happens (the value saves)
    }
}