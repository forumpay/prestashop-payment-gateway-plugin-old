<?php
/**
 * FORUMPAY
 */
include dirname(__FILE__) . '/version.php';

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_'))
    exit;


class Forumpay extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'forumpay';
        $this->tab = 'payments_gateways';
        $this->version = FORUMPAY_VERSION;
        $this->author = 'ForumPay';
        $this->need_instance = 1;
        $this->bootstrap = true;        
		
        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_
        );


        parent::__construct();
		
		$this->meta_title = $this->l('ForumPay');
		$this->displayName = $this->l('ForumPay');		                
        
        $this->description = $this->l('Pay with Crypto (by ForumPay)');	

    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('paymentOptions') || !$this->registerHook('adminOrder') || !$this->registerHook('orderConfirmation')           
        ) {
            return false;
        }

        return true;

    }
	
	
    public function hookPaymentOptions($params)
    {
        return $this->forumpayPaymentOptions($params);
    }
	
    public function hookPaymentReturn($params)
    {
        $this->forumpayPaymentReturnNew($params);
        return $this->display(dirname(__FILE__), '/tpl/order-confirmation.tpl');
    }	
	
		
    public static function setOrderStatus($oid, $status)
    {
        $order_history = new OrderHistory();
        $order_history->id_order = (int)$oid;
        $order_history->changeIdOrderState((int)$status, (int)$oid, true);
        $order_history->addWithemail(true);        
    }
	

	public function hookOrderConfirmation($params)
    {
		if ($params['order']->module != $this->name)
			return false;
		if ($params['order'] && Validate::isLoadedObject($params['order']) && isset($params['order']->valid))
		{
			
			if (version_compare(_PS_VERSION_, '1.5', '>=') && isset($params['order']->reference))
				$this->smarty->assign('forumpay_order', array('id' => $params['order']->id, 'reference' => $params['order']->reference, 'valid' => $params['order']->valid));
			else
				$this->smarty->assign('forumpay_order', array('id' => $params['order']->id, 'valid' => $params['order']->valid));

			return $this->display(__FILE__, '/tpl/order-confirmation.tpl');
		}
    }
	
	public function returnsuccess($payres){
		
	$oid = $payres['orderid'];
	
	//Context::getContext()->cart = new Cart((int)$oid);				
	//$cart = new Cart((int)$oid);						
	//$total = $payres['invoice_amount'];
		
	$order = new Order((int)$oid);
	
    
	if ($payres['status'] == 'Confirmed') {
		
        $new_history = new OrderHistory();
        $new_history->id_order = (int)$order->id;        
        $new_history->changeIdOrderState((int)Configuration::get('FORUMPAY_OSID'), $order, true);
        $new_history->addWithemail(true);

        $redirect = $this->context->shop->getBaseURL().'index.php?controller=order-confirmation&id_cart='.(int)$order->id_cart.'&id_module='.(int)$this->id.'&id_order='.(int)$oid.'&key='.$this->context->customer->secure_key;

        return $redirect;		
    }
		else if ($payres['status'] == 'Cancelled') {
			$cancel_stid = 6;
			
				$new_history = new OrderHistory();
        		$new_history->id_order = (int)$order->id;        
        		$new_history->changeIdOrderState((int)$cancel_stid, $order, true);
        		$new_history->addWithemail(true);
			
				$url = $this->context->link->getModuleLink('forumpay', 'paymentfail');								 
				$redirect = $url.'?id_cart='.(int)$order->id_cart.'&id_module='.(int)$this->id.'&id_order='.(int)$oid.'&key='.$this->context->customer->secure_key;
						

				return $redirect;
		}
						
				
		
	}
	

		
	

	function api_call($rest_url, $ForumPay_Params)
	{		
	 	$ForumPay_Qr	= http_build_query($ForumPay_Params);		   
	   
	   $curl = curl_init(trim($rest_url));	   
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	   curl_setopt($curl, CURLOPT_USERPWD, Configuration::get('FORUMPAY_APIUSER') . ":" . Configuration::get('FORUMPAY_APIKEY'));
	   if (!empty($ForumPay_Qr)) {		   
		   curl_setopt($curl, CURLOPT_POST, true);
	       curl_setopt($curl, CURLOPT_POSTFIELDS, $ForumPay_Qr);
	   }
       $response = curl_exec($curl);
	
       curl_close($curl);	   
		
	   return json_decode($response, true);
	}
	

    /**
     * Uninstall and clean the module settings
     *
     * @return	bool
     */
    public function uninstall()
    {
        parent::uninstall();

        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'module_country` WHERE `id_module` = '.(int)$this->id);

        return (true);
    }

	
    public function getContent()
    {
        if (Tools::isSubmit('submit' . $this->name)) {
			
		$forumpay_name = Tools::getValue('forumpay_name');
		$saveOpt = false;
		$err_msg = '';
		if (empty(Tools::getValue('forumpay_posid'))) $err_msg = 'POS ID must have value';
		if (empty(Tools::getValue('forumpay_apiuser'))) $err_msg = 'API User must have value';			
		if (empty(Tools::getValue('forumpay_apikey'))) $err_msg = 'API secret must have value';						
			
		if (empty($err_msg)) $saveOpt = true;
			
        if ($saveOpt)
		{							
			
			Configuration::updateValue('FORUMPAY_POSID', pSQL(Tools::getValue('forumpay_posid')));
			Configuration::updateValue('FORUMPAY_APIUSER', pSQL(Tools::getValue('forumpay_apiuser')));
			Configuration::updateValue('FORUMPAY_APIKEY', pSQL(Tools::getValue('forumpay_apikey')));			
			Configuration::updateValue('FORUMPAY_OSID', pSQL(Tools::getValue('forumpay_order_status')));
																		
			$html = '<div class="alert alert-success">'.$this->l('Configuration updated successfully').'</div>';			
		}
		else
		{
				$html = '<div class="alert alert-warning">'.$this->l($err_msg).'</div>';			
		}
        }

		$states = 	OrderState::getOrderStates((int) Configuration::get('PS_LANG_DEFAULT'));
		foreach ($states as $state)		
		{
			$OrderStates[$state['id_order_state']] = $state['name'];
			}
		$orderstatusid = Configuration::get('FORUMPAY_OSID');			
		if (empty($forumpay_apikey)) 	$orderstatusid = '2';
		
        $data    = array(
            'base_url'    => _PS_BASE_URL_ . __PS_BASE_URI__,
            'module_name' => $this->name,            
			'forumpay_prefix' => Configuration::get('FORUMPAY_PREFIX'),					
			'forumpay_posid' => Configuration::get('FORUMPAY_POSID'),		
            'forumpay_apiuser' => Configuration::get('FORUMPAY_APIUSER'),			
            'forumpay_apikey' => Configuration::get('FORUMPAY_APIKEY'),                        
            'forumpay_order_status' => $orderstatusid,			
			'forumpay_confirmation' => $html,			
            'orderstates' => $OrderStates,	
        );


        $this->context->smarty->assign($data);	
        $output = $this->display(__FILE__, 'tpl/admin.tpl');

        return $output;
    }
	
	
	//1.7

    public function forumpayPaymentOptions($params)
    {

        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $payment_options = [
            $this->forumpayExternalPaymentOption(),
        ];
        return $payment_options;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function forumpayExternalPaymentOption()
    {
        $lang = Tools::strtolower($this->context->language->iso_code);
		if (isset($_GET['forumpayerror'])) $errmsg = $_GET['forumpayerror'];
        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'errmsg' => $errmsg,			
        ));		
		
		$url = $this->context->link->getModuleLink('forumpay', 'payment');
		
        $newOption = new PaymentOption();
        $newOption->setCallToActionText($this->l('Pay with Crypto (by ForumPay)'))
			->setAction($url)
            ->setAdditionalInformation($this->context->smarty->fetch('module:forumpay/tpl/payment_infos.tpl'));

        return $newOption;
    }

    public function forumpayPaymentReturnNew($params)
    {
        // Payement return for PS 1.7
        if ($this->active == false) {
            return;
        }
        $order = $params['order'];
        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }
        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total_to_pay' => Tools::displayPrice($order->total_paid, null, false),
            'shop_name' => $this->context->shop->name,
        ));
        return $this->fetch('module:' . $this->name . '/tpl/order-confirmation.tpl');
    }


}
