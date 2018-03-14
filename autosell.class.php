<?php 
include "vendor/autoload.php";
use ccxt\ccxt;

class autoSell {

  public $maxcap = 0;
  public $paymentmethod = '';
  protected $pair = '';
  protected $gdax;
  
  public function __construct($apikey,$apisecret,$apipassword,$maxcap,$pair,$paymentmethod){
    date_default_timezone_set ('UTC');
    $this->maxcap = $maxcap;
    $this->pair = $pair;
    $this->paymentmethod = $paymentmethod;
    $ccxtclass = '\ccxt\gdax';
    $ccxt = new $ccxtclass(array('apiKey'=>$apikey,'secret'=>$apisecret,'password'=>$apipassword));;
    $ccxt->urls['api'] = $ccxt->urls['test'];
    $this->gdax = $ccxt;
 }
  
  public function sellBtc($amount){
    return $this->gdax->create_market_sell_order($this->pair,$amount);
  }

  public function checkBalance($symbol){
    $balance = $this->gdax->fetch_balance();
    return $balance['total'][$symbol];
  }
  
  public function getOrder($orderid){
    return $this->gdax->fetch_order($orderid,$this->pair);
  }

  public function getOrders(){
    return $this->gdax->fetch_orders($this->pair);
  }
  
  public function cancelOrder($orderid){
    return $this->gdax->cancel_order($orderid,$this->pair);
  }
   public function getRate(){
    return $this->gdax->fetch_ticker($this->pair)['bid'];
  }
   public function getpaymentmethods(){
    return $this->gdax->get_payment_methods();
  }
 public function withdraw($amount){
    return $this->gdax->withdraw('USD',$amount,null,null,['payment_method_id'=>$this->paymentmethod]);
  }
}


?>
