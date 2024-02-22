<?php

class Eas_klorelActionModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();

        switch (Tools::getValue('action')) {
            case 'associate_cart_customer':
                $this->associateCartToCustomer();
                die();
                break;
        }

        switch (Tools::getValue('action')) {
            case 'edit_order_invoice_date':
                $this->editOrderInvoiceDate();
                die();
                break;
        }
    }

    private function associateCartToCustomer()
    {
        $email = Tools::getValue('email');
        $cartID = Tools::getValue('cartId');

        if (!empty($email) && !empty($cartID)) {
            $customer = Customer::getCustomersByEmail($email);

            $cart = new Cart($cartID);

            if ($cart->id_customer == 0) {
                $cart->id_customer = $customer[0]['id_customer'];

                if ($cart->save()) {
                    echo 'Le panier ' . $cart->id . ' a été associé avec succès au client id : ' . $cart->id_customer;
                } else {
                    echo 'Une erreur s\'est produite lors de l\'association panier/client';
                }
            } else {
                echo 'Attention, le panier est déjà associé au client id : ' . $cart->id_customer;
            }
        }
    }
    private function editOrderInvoiceDate()
    {
        $newInvoiceDate = Tools::getValue('newInvoiceDate');
        $orderId = Tools::getValue('orderId');

        if ($newInvoiceDate == 0){
            $new_invoice_date = '0000-00-00 00:00:00';

            $sql = "UPDATE `" . _DB_PREFIX_ . "orders` SET `invoice_date` = '" . pSQL($new_invoice_date) . "' WHERE `id_order` = " . (int)$orderId;

            if (Db::getInstance()->execute($sql)) {
                $sqlInvoice = "UPDATE `" . _DB_PREFIX_ . "order_invoice` SET `date_add` = '" . pSQL($new_invoice_date) . "' WHERE `id_order` = " . (int)$orderId;
                Db::getInstance()->execute($sqlInvoice);
                dump("La date de facture a été mise à jour avec succès dans les 2 tables.");
            } else {
                dump("Erreur lors de la mise à jour de la date de facture.");
            }
        }

        $dateObj = date_create_from_format('d/m/Y', $newInvoiceDate);
        if ($dateObj) {
            $formattedDate = date_format($dateObj, 'Y-m-d 00:00:00');
            echo $formattedDate;
            $sql = "UPDATE `" . _DB_PREFIX_ . "orders` SET `invoice_date` = '" . pSQL($formattedDate) . "' WHERE `id_order` = " . (int)$orderId;

            if (Db::getInstance()->execute($sql)) {
                $sqlInvoice = "UPDATE `" . _DB_PREFIX_ . "order_invoice` SET `date_add` = '" . pSQL($formattedDate) . "' WHERE `id_order` = " . (int)$orderId;
                Db::getInstance()->execute($sqlInvoice);
                dump("La date de facture a été mise à jour avec succès dans les 2 tables.");
            } else {
                dump("Erreur lors de la mise à jour de la date de facture.");
            }

        }

    }
}
