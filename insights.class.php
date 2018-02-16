<?php 
include "vendor/autoload.php";

class AutoSellEvent extends Sobit\Insights\AbstractEvent
{
    private $typeOfEvent;
    private $sellAmount;
    private $sellCost;
    
    public function __construct($myAttribute,$sellAmount = null,$sellCost = null)
    {
        $this->typeOfEvent = $myAttribute;
        $this->sellAmount = $sellAmount;
        $this->sellCost = $sellCost;
    }
    
    public function getEventType()
    {
        return 'AutoSell';
    }
}

?>
