<?php 
class Lb_Points_Model_Locationid extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $locationid = $this->getValue(); //get the value from our config
        if(!empty($locationid))
        {
          if (!preg_match('/^[0-9]*$/', $locationid))   
            {
                Mage::throwException("Location Id should be numeric with no decimal points.");
            }
        }  else {
            Mage::throwException("Location Id should not be empty.");
        }
 
        return parent::save();  //call original save method so whatever happened 
                                //before still happens (the value saves)
    }
}