<?php
if (!defined('_PS_VERSION_'))
    exit;

class TotMailSupplierConfirmation extends Module
{
    public function __construct()
    {
        $this->name = 'totmailsupplierconfirmation';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'Enzo Hamelin';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6'); 

        parent::__construct();

        $this->displayName = $this->l('Confirmation reception email suppliers');
        $this->description = $this->l('Allows the manager to see which suppliers read the email about an order.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Confirmation reception email suppliers ?');

        $this->controllers = array('confirmation');

        if (!Configuration::get('TOTMAILSUPPLIERCONFIRMATION_EMAIL'))      
            $this->warning = $this->l('No manager email provided');
        /*if (!Configuration::get('TOTMAILSUPPLIERCONFIRMATION_SENDEMAIL'))      
            $this->warning = $this->l('No choice has been made about send an email.');
        if (!Configuration::get('TOTMAILSUPPLIERCONFIRMATION_ORDERBACK'))      
            $this->warning = $this->l('No choice has been made about displaying informations in the order\'s backoffice.');*/
    }

    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() ||
        !Configuration::updateValue('TOTMAILSUPPLIERCONFIRMATION_EMAIL', 'example@example.com') /*||
          !Configuration::updateValue('TOTMAILSUPPLIERCONFIRMATION_SENDEMAIL', serialize(array(true))) ||
          !Configuration::updateValue('TOTMAILSUPPLIERCONFIRMATION_ORDERBACK', serialize(array(false))) */
        )
            return false;

        $sql =
            'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'totmailtosupplierconf
			(
                                id int auto_increment NOT NULL,
				id_supplier int NOT NULL,
				id_order int NOT NULL,
				see tinyint(1) NOT NULL,
				PRIMARY KEY (id)
			)';
        if(!Db::getInstance()->Execute($sql))
            return false;
 
        return true;
    }

    public function uninstall()
    {
        if(!parent::uninstall() ||
        !Configuration::deleteByName('TOTMAILSUPPLIERCONFIRMATION_EMAIL') /*||
          !Configuration::deleteByName('TOTMAILSUPPLIERCONFIRMATION_SENDEMAIL') ||
          !Configuration::deleteByName('TOTMAILSUPPLIERCONFIRMATION_ORDERBACK')*/
        )
            return false;

        if(Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'totmailtosupplierconf`'))
            return false;
        
        return true;
    }

    public function addEntry($id_order, $id_supplier)
    {
        Db::getInstance()->insert('totmailtosupplierconf', array(
            'id_supplier' => $id_supplier,
            'id_order'      => $id_order,
            'see' => false,
        ));        
    }

    public function getContent()
    {
        $output = null;
 
        if (Tools::isSubmit('submit'.$this->name)) {
            $email = strval(Tools::getValue('TOTMAILSUPPLIERCONFIRMATION_EMAIL'));
            /*$sendemail = Tools::getValue('TOTMAILSUPPLIERCONFIRMATION_SENDEMAIL');
              $orderback = Tools::getValue('TOTMAILSUPPLIERCONFIRMATION_ORDERBACK');*/
            if (!$email  || empty($email) || !Validate::isMailName($email))
                $output .= $this->displayError( $this->l('Invalid email value') );
            /*elseif (!isset($sendemail) || !Validate::isBool($sendemail) || !isset($orderback) || !Validate::isBool($orderback)) 
              $output .= $this->displayError( $this->l('Invalid Configuration value') );*/
            else {
                Configuration::updateValue('TOTMAILSUPPLIERCONFIRMATION_EMAIL', $email);
                /*Configuration::updateValue('TOTMAILSUPPLIERCONFIRMATION_SENDEMAIL', $sendemail);
                  Configuration::updateValue('TOTMAILSUPPLIERCONFIRMATION_ORDERBACK', $orderback);*/
                $output .= $this->displayConfirmation($this->l('Settings updated '));
            }
        }
        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default Language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
     
        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Email value'),
                    'name' => 'TOTMAILSUPPLIERCONFIRMATION_EMAIL',
                    'required' => true
                )/*,
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Do you want to send an email after a supplier\'s confirmation ?'),
                    'name' => 'TOTMAILSUPPLIERCONFIRMATION_SENDEMAIL',
                    'values' => array(
                        'query' => array(
                            array(
                                'id' => 'sendemail',
                                'name' => ''
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Do you want to display informations in the order\'s backoffice ?'),
                    'name' => 'TOTMAILSUPPLIERCONFIRMATION_ORDERBACK',
                    'values' => array(
                        'query' => array(
                            array(
                                'id' => 'orderbackoffice',
                                'name' => ''
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                    )*/
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );
     
        $helper = new HelperForm();
     
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
     
        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
     
        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );
     
        // Load current value
        $helper->fields_value['TOTMAILSUPPLIERCONFIRMATION_EMAIL'] = Configuration::get('TOTMAILSUPPLIERCONFIRMATION_EMAIL');
        //$helper->fields_value['TOTMAILSUPPLIERCONFIRMATION_SENDEMAIL'] = Configuration::get('TOTMAILSUPPLIERCONFIRMATION_SENDEMAIL');
        //$helper->fields_value['TOTMAILSUPPLIERCONFIRMATION_ORDERBACK'] = Configuration::get('TOTMAILSUPPLIERCONFIRMATION_ORDERBACK');
        
        return $helper->generateForm($fields_form);
    }
}
?>