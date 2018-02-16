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

$autosell = new autosell(APIKEY,APISECRET,APIPASSWORD,MAXCAP,PAIR);

//For cancelling pending orders use 'cancelorders' argument in command line
$cancelorders = false;
if(!empty($argv[1])){
  switch ($argv[1]){
    case "cancelorders":
      echo "will cancel pending orders\n";
      $cancelorders = true;
      break;
  }
}


echo "checking balance\n";
try{
  $btcbalance = $autosell->checkBalance('BTC');
} catch(\Exception $e) {
  sendEvent2NR('Balance checking error');  
  exit('Error while checking balance: '.$e->getMessage());
}

if($btcbalance == 0){
  exit("no funds, exiting\n");
}  

echo "available balance $btcbalance\n";
sendEvent2NR('Balance',$btcbalance);

echo "checking for pending orders\n";
try {
  $orders = $autosell->getOrders();
}catch(\Exception $e){
  sendEvent2NR('Orders checking error');  
  exit('Error while checking orders: '.$e->getMessage());
}  
foreach($orders as $order){
  if($order["status"] != "closed"){
    if($cancelorders){
      echo "cancelling order ".$order['id']."\n";
      $cancelled = $autosell->cancelOrder($order['id']);
      echo "order ".$cancelled[0]." cancelled\n";
    }
    exit("pending order ".$order['id']." amount ".$order['amount'].". exiting...\n");
    sendEvent2NR('Pending orders exists');
  }
}


echo "no orders, can sell\n";
//Calculating sell amount
$amount = $btcbalance;
if($btcbalance > $autosell->maxcap){
  $amount = $autosell->maxcap;
}
//Creating market order
try{
  $order = $autosell->sellBtc($amount);  
}catch (\Exception $e){
  sendEvent2NR('Placing order error');  
  exit('Error while placing order: '.$e->getMessage());
} 

$orderid = $order['info']['id'];
echo "order $orderid created\n";

//Checking order status - filled or not
try{
  $result = $autosell->getOrder($orderid);
} catch (\Exception $e) {
  sendEvent2NR('Get order error');  
  exit('Error while checking order: '.$e->getMessage());
}

echo "order ". $result["status"]." amount ".$result['amount']." cost ".$result['cost']."\n";

if($result['status'] == 'closed'){
  sendEvent2NR('Sold',$result['amount'],$result['cost']);
}
  
echo "----DONE----\n\n";

?>
