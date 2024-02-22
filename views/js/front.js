/**
 * 2007-2023 PrestaShop
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2023 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

$(document).ready(function () {
    const editButton = $('.edit-button');
    const editableField = $('.editable');

    const originalValue = editableField.text();

    const orderIdElement = $('strong[data-role="order-id"]');

    let orderId = orderIdElement.text();
    orderId = orderId.replace("#", "");

    editButton.on('click', function () {
        if (editableField.attr('contentEditable') === 'true') {
            editableField.attr('contentEditable', 'false');
            editableField.css('padding', '');

            editButton.text('Edit');

            const newInvoiceDate = editableField.text();
            console.log('newInvoiceDate : ', newInvoiceDate)

            $.ajax({
                url: '/module/eas_klorel/action',
                method: 'POST',
                data: {
                    action: 'edit_order_invoice_date',
                    newInvoiceDate: newInvoiceDate,
                    orderId: orderId,
                },
                success: function () {
                    console.log('La date de facture a été mise à jour avec succès dans les 2 tables.');
                    location.reload();
                },
                error: function (error) {
                    console.log('on est dans error bb');
                    console.error(error)
                    location.reload();
                }
            });

        } else {
            const legend = $('<br /><span style="font-size: 12px; color: #777;padding-left: 8px">Format JJ/MM/AAAA ou 0.</span>');
            editButton.after(legend);

            editableField.attr('contentEditable', 'true');
            editableField.focus();
            editableField.css('padding', '8px 16px');

            editButton.text('Save');
            console.log('je suis éditable')
        }
    });
});
