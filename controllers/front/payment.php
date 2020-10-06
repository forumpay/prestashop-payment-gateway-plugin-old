<?php
/**
 * 2013-2017 Amazon Advanced Payment APIs Modul
 *
 * for Support please visit www.patworx.de
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2013-2017 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class ForumpayPaymentModuleFrontController extends ModuleFrontController
{

    public $ssl = true;

    public $isLogged = false;

    public $display_column_left = false;

    public $display_column_right = false;

    public $service;

    protected $ajax_refresh = false;

    protected $css_files_assigned = array();

    protected $js_files_assigned = array();

    public function __construct()
    {
        $this->controller_type = 'modulefront';

        $this->module = Module::getInstanceByName(Tools::getValue('module'));

        if (!$this->module->active) {
            Tools::redirect('index');
        }

        $this->page_name = 'module-' . $this->module->name . '-' . Dispatcher::getInstance()->getController();

        parent::__construct();
    }

    public function postProcess()
    {
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'forumpay') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'payment'));
        }

        $cart = $this->context->cart;
        $forumpay = new FORUMPAY();

        $amount = number_format($cart->getOrderTotal(true, Cart::BOTH), 2);
        $order_id = $cart->id;
        $new_order_status = 16;
        if ($order_id) {
            $this->context->cookie->__set('forumpaycartid', $order_id);
            $forumpay->validateOrder((int) $order_id, (int) $new_order_status, (float) $amount, $forumpay->displayName, null, array(), null, false, $cart->secure_key);

        } else {
            $order_id = $this->context->cookie->forumpaycartid;
        }

        $order = new Order((int) Order::getOrderByCartId($order_id));

        $cssurl = $this->context->shop->getBaseURL() . 'modules/forumpay/css/forumpay.css';
        $basepath = $this->context->shop->getBaseURL() . 'modules/forumpay';
        $apiurl = 'https://pay.limitlex.com/api/v2/GetCurrencyList/';
        $cForumPayParam = array();
        $CurrencyList = $forumpay->api_call($apiurl, $cForumPayParam);
        $sCurrencyList = array();

        foreach ($CurrencyList as $Currency) {
            if ($Currency['currency'] != 'USDT') {
                $sCurrencyList[] = array('code' => $Currency['currency'], 'desc' => $Currency['description'] . ' (' . $Currency['currency'] . ')');
            }

        }

        $getqrurl = $this->context->shop->getBaseURL() . 'modules/forumpay/forumpay-api.php?act=getqr';
        $getrateurl = $this->context->shop->getBaseURL() . 'modules/forumpay/forumpay-api.php?act=getrate';
        $getstausurl = $this->context->shop->getBaseURL() . 'modules/forumpay/forumpay-api.php?act=getst';
        $this->context->smarty->assign(array(
            'css_url' => $cssurl,
            'basepath' => $basepath,
            'currencylist' => $sCurrencyList,
            'getqrurl' => $getqrurl,
            'getrateurl' => $getrateurl,
            'getstausurl' => $getstausurl,

        ));

    }

    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('module:forumpay/tpl/payment.tpl');
    }

}
