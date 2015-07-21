<?php
class totmailsupplierconfirmationconfirmationModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
      
    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }
      
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        if(Tools::isSubmit('id_supplier') && Tools::isSubmit('id_order')) {
            $id_supplier = Tools::getValue('id_supplier');
            $id_order = Tools::getValue('id_order');
            
            $sql = 'SELECT * FROM '._DB_PREFIX_.'totmailtosupplierconf WHERE id_supplier = '.$id_supplier.' AND id_order = '.$id_order;
            if ($results = Db::getInstance()->ExecuteS($sql)) {
                if($results[0]['see'])
                    $this->context->smarty->assign('message', $this->module->l('This order has already been confirmed.', 'confirmation'));
                else {
                    Db::getInstance()->update('totmailtosupplierconf', array('see' => true), 'id_supplier = '.$id_supplier.' AND id_order = '.$id_order);
                    $this->context->smarty->assign('message', $this->module->l('You have confirmed the order.', 'confirmation'));

                    //Envoi de l'email
                    global $cookie;
                    $supplier = new Supplier($id_supplier);
                    $subject = $this->module->l('Confirmation of the order n°', 'confirmation').$id_order;
                    $data = array('{id_order}'  => $id_order ,  '{supplier_name}'  => $supplier->name );
                    $dest = Configuration::get('TOTMAILSUPPLIERCONFIRMATION_EMAIL');
                    
                    Mail::Send(intval($cookie->id_lang), 'confirmation', $subject , $data, $dest, NULL, NULL, NULL, NULL, NULL, dirname(__FILE__).'/../../mails/');
                }
            }
            else   
                $this->context->smarty->assign('message', $this->module->l('This couple order/supplier doesn\'t exist.', 'confirmation'));
        } else 
            $this->context->smarty->assign('message', $this->module->l('Order id or supplier id is not specified.', 'confirmation'));
        $this->setTemplate('confirmation.tpl');
    }
}
