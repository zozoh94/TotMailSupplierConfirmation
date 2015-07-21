# TotMailSupplierConfirmation
Module prestashop permettant d'améliorer le module totmailtosupplier avec un bouton de confirmation permettant d'alerter le gérant de la prise en charge de la commande.

Modifier le fichier new_order.html du module totmailtosupplier en ajoutant le bouton de confirmation où vous le souhaitez avec le code suivant :
`{confirmation_link}`

Puis dans le totmailsupplier.php après la définition de templateVats ajouter ceci :
 ```
if(Module::isEnabled('totmailsupplierconfirmation')) {
        $confirmation = Module::getInstanceByName('totmailsupplierconfirmation');
        $confirmation->addEntry($order->id, $supplier['id_supplier']);
        $templateVars['{confirmation_link}'] = '<tr><td>&nbsp;</td></tr>
		<tr><td style="text-align: center;"><a style="padding: 10px 16px;font-size: 12px;line-height: 1.33333;border-radius: 6px;color: #FFF;background-color: #2A9E40;border-color: #660718;display: inline-block;margin-bottom: 0px;font-weight: 400;text-align: center;white-space: nowrap;vertical-align: middle;cursor: pointer;-moz-user-select: none;background-image: none;border: 1px solid transparent;text-decoration: none; box-sizing: border-box;" href="'.$this->context->link->getModuleLink('totmailsupplierconfirmation','confirmation',array('id_order' => $order->id, 'id_supplier' => $supplier['id_supplier'])).'">'.$this->l('Click here to confirm the order taking over').'</a></td></tr>';
}
```		
