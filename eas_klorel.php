<?php
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2023 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use Symfony\Component\HttpFoundation\Request;


if (!defined('_PS_VERSION_')) {
    exit;
}

class Eas_klorel extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'eas_klorel';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Klorel';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Easytis x Klorel ');
        $this->description = $this->l('Module custom développé par Klorel pour Eaystis');

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('EAS_KLOREL_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('actionFrontControllerSetVariables') &&
            $this->registerHook('actionDispatcher') &&
            $this->registerHook('actionDispatcherBefore') &&
            $this->registerHook('actionDispatcherAfter') &&
            $this->registerHook('displayPDFInvoice') &&
            $this->registerHook('actionProductGridDefinitionModifier') &&
            $this->registerHook('actionProductGridDataModifier') &&
            $this->registerHook('actionOrderGridDefinitionModifier') &&
            $this->registerHook('actionOrderGridDataModifier') &&
            $this->registerHook('actionOrderGridQueryBuilderModifier') &&
            $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('EAS_KLOREL_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitEas_klorelModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEas_klorelModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'EAS_KLOREL_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'EAS_KLOREL_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ],
                    [
                        'type' => 'password',
                        'name' => 'EAS_KLOREL_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'EAS_KLOREL_LIVE_MODE' => Configuration::get('EAS_KLOREL_LIVE_MODE', true),
            'EAS_KLOREL_ACCOUNT_EMAIL' => Configuration::get('EAS_KLOREL_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'EAS_KLOREL_ACCOUNT_PASSWORD' => Configuration::get('EAS_KLOREL_ACCOUNT_PASSWORD', null),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader($params)
    {
        if (Tools::getValue('controller') == 'AdminCarts') {
            $this->context->controller->addJS($this->_path . 'views/js/admincarts.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }

        $this->context->controller->addJS($this->_path . '/views/js/front.js');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function getPriceTTC($id_product)
    {
        $product = new Product($id_product);

        $priceTTC = $product->getPrice();

        return Tools::displayPrice($priceTTC);

    }

    public function hookActionFrontControllerSetVariables()
    {
        return [
            'module' => $this,
        ];
    }

    public function hookDisplayPDFInvoice($params)
    {
        $orderInvoice = $params['object'];
        $orderId = $orderInvoice->id_order;

        $order = new Order($orderId);
        $products = $order->getProducts();

        $customer = new Customer($order->id_customer);

        $customerSiret = $customer->siret;
        $totalTaxes5_5 = 0;

        foreach ($products as $product) {
            if ($product['tax_rate'] === 5.5) {
                $totalTaxes5_5 += $product['total_wt'] - $product['total_price'];
            }
        }

        if ($totalTaxes5_5 > 0) {
            /*            $this->context->smarty->assign('totalTaxes5_5', $totalTaxes5_5);*/
            Context::getContext()->smarty->assignGlobal('totalTaxes5_5', $totalTaxes5_5);
        }

        if ($customerSiret) {
            Context::getContext()->smarty->assignGlobal('customerSiret', $customerSiret);
        }

        $invoiceAddress = new Address($order->id_address_invoice);
        if ($invoiceAddress) {
            Context::getContext()->smarty->assignGlobal('invoiceAddress', $invoiceAddress);
        }

        $deliveryAddress = new Address($order->id_address_delivery);
        if ($deliveryAddress) {
            Context::getContext()->smarty->assignGlobal('deliveryAddress', $deliveryAddress);
        }

    }

    public function isAdminOrderController()
    {
        if (Tools::getValue('controller') == 'AdminOrders') {
            return true;
        }

        return false;
    }

    public function containQueryOrderID()
    {
        global $kernel;
        if ($kernel){
            $requestStack = $kernel->getContainer()->get('request_stack');
            $request = $requestStack->getCurrentRequest();

            if ($request) {
                $route = $request->attributes->get('_route');
                if (isset($route) && strpos($route, 'admin_orders') !== false) {
                    return true;
                }
            }
        }

        return false;
    }


    public function hookActionDispatcherBefore($params)
    {
        if ($this->isAdminOrderController() || $this->containQueryOrderID()) {
            if ($params['request']->query->get('orderId')) {
                $orderId = $params['request']->query->get('orderId');

                $sql = 'SELECT total_paid, total_paid_tax_incl, total_paid_tax_excl, total_shipping_tax_excl, total_shipping_tax_incl, total_shipping
                        FROM eas_orders
                        WHERE id_order = ' . $orderId;

                $result = Db::getInstance()->executeS($sql);

                if ($result) {
                    $_SESSION['eas_order_id'] = $orderId;
                    $_SESSION['eas_shipping_tax_excl'] = $result[0]['total_shipping_tax_excl'];
                    $_SESSION['eas_shipping_tax_incl'] = $result[0]['total_shipping_tax_incl'];
                    $_SESSION['eas_shipping'] = $result[0]['total_shipping'];
                    $_SESSION['eas_shipping_total_paid_tax_excl'] = $result[0]['total_paid_tax_excl'];
                    $_SESSION['eas_shipping_total_paid_tax_incl'] = $result[0]['total_paid_tax_incl'];
                    $_SESSION['eas_shipping_total_paid'] = $result[0]['total_paid'];
                }

            }
        }
    }

    public function hookActionDispatcherAfter($params)
    {
        if ($this->isAdminOrderController() || $this->containQueryOrderID()) {
            if (isset($_SESSION['eas_order_id'])) {

                $totalShippingTaxExcl = $_SESSION['eas_shipping_tax_excl'];
                $totalShippingTaxIncl = $_SESSION['eas_shipping_tax_incl'];
                $totalShipping = $_SESSION['eas_shipping'];
                $totalPaidTaxExcl = $_SESSION['eas_shipping_total_paid_tax_excl'];
                $totalPaidTaxIncl = $_SESSION['eas_shipping_total_paid_tax_incl'];
                $totalPaid = $_SESSION['eas_shipping_total_paid'];

                $order = new Order((int) $_SESSION['eas_order_id']);
                $order->total_shipping_tax_excl = $totalShippingTaxExcl;
                $order->total_shipping_tax_incl = $totalShippingTaxIncl;
                $order->total_shipping = $totalShipping;
                $order->total_paid_tax_excl = $totalPaidTaxExcl;
                $order->total_paid_tax_incl = $totalPaidTaxIncl;
                $order->total_paid = $totalPaid;
                $order->update();

                unset($_SESSION['eas_shipping_tax_excl']);
                unset($_SESSION['eas_shipping_tax_incl']);
                unset($_SESSION['eas_shipping']);
                unset($_SESSION['eas_shipping_total_paid_tax_excl']);
                unset($_SESSION['eas_shipping_total_paid_tax_incl']);
                unset($_SESSION['eas_shipping_total_paid']);
                unset($_SESSION['eas_order_id']);

            }
        }
    }

    public function hookActionDispatcher()
    {
        if (Tools::getValue('controller') == 'pdfinvoice') {

            $invoiceLogo = Configuration::get('PS_LOGO_INVOICE');

            if ($invoiceLogo) {
                $this->context->smarty->assign('invoiceLogo', $invoiceLogo);
            }

            $order = new Order(Tools::getValue('id_order'));

            $invoiceAddress = new Address($order->id_address_invoice);
            if ($invoiceAddress) {
                $this->context->smarty->assign('invoiceAddress', $invoiceAddress);
            }

            $deliveryAddress = new Address($order->id_address_delivery);
            if ($deliveryAddress) {
                $this->context->smarty->assign('deliveryAddress', $deliveryAddress);
            }

            $curency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            Context::getContext()->currency = $curency;

            $products = $order->getProducts();

            $totalTaxes5_5 = 0;

            foreach ($products as $product) {
                if ($product['tax_rate'] === 5.5) {
                    $totalTaxes5_5 += $product['total_wt'] - $product['total_price'];
                }
            }

            if ($totalTaxes5_5 > 0) {
                $this->context->smarty->assign('totalTaxes5_5', $totalTaxes5_5);
            }

        }


        if (Tools::getValue('controller') == 'printpdf') {

            $siret_client = Context::getContext()->customer->siret;
            $invoiceLogo = Configuration::get('PS_LOGO_INVOICE');

            if ($siret_client) {
                $this->context->smarty->assign('siret_client', $siret_client);
            }

            if ($invoiceLogo) {
                $this->context->smarty->assign('invoiceLogo', $invoiceLogo);
            }

            $cart = new Cart(Context::getContext()->cookie->id_cart);
            Context::getContext()->cart = $cart;
            $curency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            Context::getContext()->currency = $curency;

            $products = $cart->getProducts();

            $totalTaxes5_5 = 0;

            foreach ($products as $product) {
                if ($product['rate'] === 5.5) {
                    $totalTaxes5_5 += $product['total_wt'] - $product['total'];
                }
            }

            if ($totalTaxes5_5 > 0) {
                $this->context->smarty->assign('totalTaxes5_5', $totalTaxes5_5);
            }
        }
    }

    public function hookActionProductGridDefinitionModifier(array $params)
    {
        if (empty($params['definition'])) {
            return;
        }

        /** @var PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $column = new PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn('weight');
        $column->setName('Poids');
        $column->setOptions([
            'field' => 'weight',
        ]);

        $definition
            ->getColumns()
            ->addAfter('category', $column);

        $definition
            ->getColumns()
            ->remove('quantity');

    }

    public function hookActionProductGridDataModifier(array $params)
    {
        if (empty($params['data'])) {
            return;
        }

        /** @var PrestaShop\PrestaShop\Core\Grid\Data\GridData $gridData */
        $gridData = $params['data'];
        $modifiedRecords = $gridData->getRecords()->all();

        $newProducts = [];

        foreach ($modifiedRecords as $key => $product) {
            $weight = Db::getInstance()->executeS(
                'SELECT weight
             FROM eas_product
             WHERE id_product = ' . (int) $product['id_product']
            );

            $product['weight'] = $weight[0]['weight'];

            $newProducts[] = $product;
        }

        $params['data'] = new PrestaShop\PrestaShop\Core\Grid\Data\GridData(
            new PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection($newProducts),
            $gridData->getRecordsTotal(),
            $gridData->getQuery()
        );


    }

    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        if (empty($params['definition'])) {
            return;
        }

        /** @var PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $columnCompany = $definition->getColumnById("company");
        $definition
            ->getColumns()
            ->addAfter('customer', $columnCompany);

        $column = new PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn('note');
        $column->setName('Notes');
        $column->setOptions([
            'field' => 'note',
        ]);

        $definition
            ->getColumns()
            ->addAfter('company', $column);

        $definition->getFilters()->add(
            (new PrestaShop\PrestaShop\Core\Grid\Filter\Filter('note', Symfony\Component\Form\Extension\Core\Type\TextType::class))
                ->setAssociatedColumn('note')
                ->setTypeOptions([
                    'required' => false,
                    'translation_domain' => false,
                ])
        );

        $column = new PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn('invoice_date');
        $column->setName('Date Facture');
        $column->setOptions([
            'field' => 'invoice_date',
        ]);

        $definition
            ->getColumns()
            ->addAfter('date_add', $column);

        $definition->getFilters()->add(
            (new PrestaShop\PrestaShop\Core\Grid\Filter\Filter('invoice_date', PrestaShopBundle\Form\Admin\Type\DateRangeType::class))
                ->setAssociatedColumn('invoice_date')
                ->setTypeOptions([
                    'required' => false,
                ])
        );

        $column = new PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn('invoice_number');
        $column->setName('N° Facture');
        $column->setOptions([
            'field' => 'invoice_number',
        ]);

        $definition
            ->getColumns()
            ->addAfter('invoice_date', $column);

        $definition->getFilters()->add(
            (new PrestaShop\PrestaShop\Core\Grid\Filter\Filter('invoice_number', Symfony\Component\Form\Extension\Core\Type\TextType::class))
                ->setAssociatedColumn('invoice_number')
                ->setTypeOptions([
                    'required' => false,
                    'translation_domain' => false,
                ])
        );


        $definition
            ->getColumns()
            ->remove('new');

    }

    public function hookActionOrderGridDataModifier(array $params)
    {
        if (empty($params['data'])) {
            return;
        }

        /** @var PrestaShop\PrestaShop\Core\Grid\Data\GridData $gridData */
        $gridData = $params['data'];
        $modifiedRecords = $gridData->getRecords()->all();

        $newOrders = [];

        foreach ($modifiedRecords as $key => $order) {
            $note = Db::getInstance()->executeS(
                'SELECT note
             FROM eas_orders
             WHERE id_order = ' . (int) $order['id_order']
            );
            $order['note'] = $note[0]['note'];

            $invoice_date = Db::getInstance()->executeS(
                'SELECT invoice_date
             FROM eas_orders
             WHERE id_order = ' . (int) $order['id_order']
            );
            $order['invoice_date'] = $invoice_date[0]['invoice_date'];

            if ($order['invoice_date'] == "0000-00-00 00:00:00") {
                $order['invoice_date'] = '--';
            }

            if ($order['invoice_number'] == 0) {
                $order['invoice_number'] = '--';
            } else {
                $order['invoice_number'] = 'FA0' . $order['invoice_number'];
            }

            $newOrders[] = $order;
        }

        $params['data'] = new PrestaShop\PrestaShop\Core\Grid\Data\GridData(
            new PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection($newOrders),
            $gridData->getRecordsTotal(),
            $gridData->getQuery()
        );
    }

    public function hookActionOrderGridQueryBuilderModifier(array $params)
    {
        ini_set('memory_limit', '-1');

        if (empty($params['search_query_builder']) || empty($params['search_criteria'])) {
            return;
        }


        $countQueryBuilder = $params['count_query_builder'];

        /** @var Doctrine\DBAL\Query\QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        /** @var PrestaShop\PrestaShop\Core\Search\Filters\OrderFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];


        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {

//            if (isset($_GET['klinv'])) {
//                echo $filterName.' - ';
////            echo $searchQueryBuilder->__toString();
////                print_r($_GET);
////                die('!!!');
//            }

            if ('invoice_number' === $filterName) {
                $invoiceNumber = $filterValue;

                $invoiceNumber = substr($invoiceNumber, 2);

                if (strlen($invoiceNumber) === 6) {
                    $firstChar = $invoiceNumber[0];

                    if ($firstChar == 0) {
                        $invoiceNumber = substr($invoiceNumber, 1);
                    }
                }

                $searchQueryBuilder->andWhere('o.invoice_number LIKE :invoice_number');
                $countQueryBuilder->andWhere('o.invoice_number LIKE :invoice_number');

                $searchQueryBuilder->setParameter('invoice_number', '%' . $invoiceNumber . '%');
                $countQueryBuilder->setParameter('invoice_number', '%' . $invoiceNumber . '%');
            }

            if ('invoice_date' === $filterName) {
                $dateRange = $filterValue;

                if (!empty($dateRange)) {
                    if (!empty($dateRange['from'])) {
                        $searchQueryBuilder->andWhere('o.invoice_date >= :invoice_date_from');
                        $searchQueryBuilder->setParameter('invoice_date_from', $dateRange['from']);

                        $countQueryBuilder->andWhere('o.invoice_date >= :invoice_date_from');
                        $countQueryBuilder->setParameter('invoice_date_from', $dateRange['from']);
                    }

                    if (!empty($dateRange['to'])) {
                        $searchQueryBuilder->andWhere('o.invoice_date <= :invoice_date_to');
                        $searchQueryBuilder->setParameter('invoice_date_to', $dateRange['to']);

                        $countQueryBuilder->andWhere('o.invoice_date <= :invoice_date_to');
                        $countQueryBuilder->setParameter('invoice_date_to', $dateRange['to']);
                    }
                }
            }

            if ('note' === $filterName) {
                $note = $filterValue;

                $searchQueryBuilder->andWhere('o.note LIKE :note');
                $countQueryBuilder->andWhere('o.note LIKE :note');

                $searchQueryBuilder->setParameter('note', '%' . $note . '%');
                $countQueryBuilder->setParameter('note', '%' . $note . '%');
            }


        }


//        if (isset($_GET['klinv'])) {
//            echo $searchQueryBuilder->__toString();
////            print_r($_GET);
//            die('!!!');
//        }

    }
}
