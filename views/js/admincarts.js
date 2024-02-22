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

    const targetH2 = $('.col h2:contains("Visiteur non enregistré")');

    if (targetH2.length) {
        const formHTML = '<form class="mt-2">' +
            '<input type="text" id="associate_cart_customer" name="associate_cart_customer" placeholder="Entrez l\'email d\'un client" class="form-control" style="width:85%; float:left;">' +
            '<input type="submit" value="Associer" class="btn btn-primary" style="width:12%; float:right; margin-left:15px;">' +
            '</form>';

        targetH2.after(formHTML);

        const subtitleText = $('.kpi-description .subtitle').text();
        const parts = subtitleText.split('Panier n°');
        const numeroPanier = parts[1];
        console.log("Numéro du panier :", numeroPanier);

        $('form').submit(function (event) {
            event.preventDefault();

            const emailValue = $('#associate_cart_customer').val();
            const emailValueLowercase = emailValue.toLowerCase();

            $.ajax({
                url: '/module/eas_klorel/action',
                method: 'POST',
                data: {
                    action: 'associate_cart_customer',
                    email: emailValueLowercase,
                    cartId: numeroPanier
                },
                success: function (response) {
                    console.log(response);
                    location.reload();
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });
    }
})