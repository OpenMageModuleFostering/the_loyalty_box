<?php 
class Lb_Points_Model_Programname extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $programName = $this->getValue(); //get the value from our config
        if(empty($programName))
        {
          Mage::throwException("Reward Program Name should not be empty.");
        }  
        return parent::save();  //call original save method so whatever happened 
                                //before still happens (the value saves)
    }
}