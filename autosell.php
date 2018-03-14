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
    case "cancelorders":
      echo "will cancel pending orders\n";
      $cancelorders = true;
      break;
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

// echo "checking USD balance\n";
// try{
//   $usdbalance = floor($autosell->checkBalance('USD'));
// } catch(\Exception $e) {
//   sendEvent2NR('Balance checking error');  
//   exit('Error while checking balance: '.$e->getMessage());
// }

// echo "available USD balance $usdbalance\n";
// sendEvent2NR('USD balance',$usdbalance);

// if($usdbalance >= MINWITHDRAWAL){
//   echo "Withdraw $usdbalance\n";
//   $result = $autosell->withdraw($usdbalance);
//   sendEvent2NR('Withdraw',$usdbalance);
//   //print_r($result);
// }  


echo "checking BTC balance\n";
try{
  $btcbalance = $autosell->checkBalance('BTC');
} catch(\Exception $e) {
  sendEvent2NR('Balance checking error');  
  exit('Error while checking balance: '.$e->getMessage());
}

if($btcbalance == 0){
  exit("no BTC, exiting\n");
}  

echo "available BTC balance $btcbalance\n";
sendEvent2NR('BTC balance',$btcbalance);

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
try{
  $curprice = $autosell->getRate();
}catch (\Exception $e){
  sendEvent2NR('Getting rate error');  
  exit('Error while getting rate: '.$e->getMessage());
} 
echo "current rate $curprice\n";
$amount = $btcbalance;
if($btcbalance*$curprice > $autosell->maxcap){
  $amount = round($autosell->maxcap / $curprice,8);
}
echo "amount to sell $amount\n";
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
