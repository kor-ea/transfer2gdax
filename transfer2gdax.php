<?php 
include "autosell.class.php";
include "insights.class.php";
include "config.php";

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
use Sobit\Insights\Client\Client;
use Sobit\Insights\EventManager;

//Function for sending events to New Relic
function sendEvent2NR($name,$amount = null,$cost = null){
return true;  
// initialize core classes
  $client       = new Client(new GuzzleHttp\Client(), ACCOUNTID, INSERTKEY, QUERYKEY);
  $eventManager = new EventManager($client, JMS\Serializer\SerializerBuilder::create()->build(),'transfer2gdax');
  
  // create your event
  $event = new AutoSellEvent($name,$amount,$cost);

  // submit event to Insights
  $eventManager->persist($event);
  $eventManager->flush();
}

echo "\n----START----\n";
sendEvent2NR('Start');

$autosell = new autosell(APIKEY,APISECRET,APIPASSWORD,MAXCAP,PAIR,PAYMENTMETHOD);

echo "checking balance\n";

exec('/usr/bin/electrum getbalance'.TESTNET,$output);
$balance = json_decode(implode('',$output));
$confirmed = $balance->confirmed;
$unconfirmed = (property_exists('balance','unconfirmed'))?$balance->unconfirmed:0;
echo "available balance $confirmed ($unconfirmed)\n";


sendEvent2NR('Balance',$confirmed);

if($confirmed == 0 ){
  exit("No funds, exiting\n");
}
//Calculating sell amount
try{
  $curprice = $autosell->getRate();
}catch (\Exception $e){
  sendEvent2NR('Getting rate error');  
  exit('Error while getting rate: '.$e->getMessage());
} 
echo "current rate $curprice\n";

if($confirmed*$curprice < MINFUNDS){
  exit("Available funds ".$confirmed*$curprice."USD < FINFUNDS ".MINFUNDS."USD, exiting\n");
}

echo "amount to transfer $confirmed\n";
$output = [];
exec("/usr/bin/electrum ".TESTNET." payto ".TRANSFERTO." $confirmed | /usr/bin/electrum ".TESTNET." broadcast -",$output);
$result = implode('',$output);
$result_json = json_decode($result);
if ($result_json[0] == 'true'){
  echo "Transfer of $confirmed BTC initiated\n";
  sendEvent2NR('Transferred',$confirmed);  
}else{
  echo "Transfer failed\n";
  sendEvent2NR('Transfer failed',0);  
}

  
echo "----DONE----\n\n";

?>
