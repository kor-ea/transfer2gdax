<?php 
include "autosell.class.php";
include "insights.class.php";
include "config.php";

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
use Sobit\Insights\Client\Client;
use Sobit\Insights\EventManager;

//Function for sending events to New Relic
function sendEvent2NR($name,$amount = null,$cost = null){
   
// initialize core classes
  $client       = new Client(new GuzzleHttp\Client(), ACCOUNTID, INSERTKEY, QUERYKEY);
  $eventManager = new EventManager($client, JMS\Serializer\SerializerBuilder::create()->build(),'autosell');
  
  // create your event
  $event = new AutoSellEvent($name,$amount,$cost);

  // submit event to Insights
  $eventManager->persist($event);
  $eventManager->flush();
}

echo "\n----START----\n";
sendEvent2NR('Start');

$autosell = new autosell(APIKEY,APISECRET,APIPASSWORD,MAXCAP,PAIR,PAYMENTMETHOD);

//For cancelling pending orders use 'cancelorders' argument in command line
$cancelorders = false;
if(!empty($argv[1])){
  switch ($argv[1]){
    case "paymentmethods":
      echo "getting withdrawal methods\n";
      try{
        $methods = $autosell->getpaymentmethods();
      } catch(\Exception $e) {
        exit('Error while getting payment methods: '.$e->getMessage());
      }
      foreach($methods as $method){
          echo "id: ".$method['id']." type: ".$method["type"]." name: ".$method["name"]."\n";
      }
      
      exit;
      
  }
}

echo "checking USD balance\n";
try{
  $usdbalance = floor($autosell->checkBalance('USD'));
} catch(\Exception $e) {
  sendEvent2NR('Balance checking error');  
  exit('Error while checking balance: '.$e->getMessage());
}

echo "available USD balance $usdbalance\n";
sendEvent2NR('USD balance',$usdbalance);

if($usdbalance >= MINWITHDRAWAL){
  echo "Withdraw $usdbalance\n";
  $result = $autosell->withdraw($usdbalance);
  sendEvent2NR('Withdraw',$usdbalance);
  //print_r($result);
}  


  
echo "----DONE----\n\n";

?>
