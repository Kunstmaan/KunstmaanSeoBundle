<?php

namespace Kunstmaan\SeoBundle\Twig;

use Kunstmaan\SeoBundle\Helper\Order;
use Kunstmaan\SeoBundle\Helper\OrderItem;
use Twig_Extension;
use Twig_Environment;

/**
 * Twig extensions for Google Analytics
 */
class GoogleAnalyticsTwigExtension extends Twig_Extension
{

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'google_analytics_initialize' => new \Twig_Function_Method($this, 'renderInitialize', array('is_safe' => array('html'), 'needs_environment' => true)),
            'google_analytics_track_order' => new \Twig_Function_Method($this, 'renderECommerceTracking', array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }


    protected $accountVarName = 'account_id';

    protected $accountId;

    public function __construct($accountId = null)
    {
        $this->accountId = $accountId;
    }


    /**
     * Renders the default Google Analytics JavaScript.
     *
     * If the options are not set it'll try and load the account ID from your parameters (google.analytics.account_id)
     *
     * @param Twig_Environment $environment
     * @param array|null $options {account_id: 'UA-XXXXX-Y'}
     */
    public function renderInitialize(\Twig_Environment $environment, $options = null)
    {
        if (is_null($options)) {
            $options = array();
        }

        $defaults = array();

        $this->setOptionIfNotSet($defaults, $this->accountVarName, $this->accountId);
        // $this->setOptionIfNotSet($defaults, $this->accountVarName, $this->getGlobal($environment, 'ga_code')); // Global logic not working.

        // Things set in $options will override things set in $defaults.
        $options = array_merge($defaults, $options);

        if (!$this->isOptionSet($options, $this->accountVarName)) {
            throw new \Twig_Error_Runtime("The google_analytics_initialize function depends on a Google Analytics account ID. You can either pass this along in the initialize_google_analytics function ($this->accountVarName), provide a variable under 'parameters.google.analytics.account_id'.");
        }

        $template = $environment->loadTemplate('KunstmaanSeoBundle:GoogleAnalyticsTwigExtension:init.html.twig');
        return $template->render($options);
    }


    /**
     * @param Twig_Environment $environment
     * @param $order Order
     */
    public function renderECommerceTracking(\Twig_Environment $environment, $order) {
        // TODO: Split out formatting (add a formatter)
        // TODO: Ensure every orderItem has a unique SKU.
        // Only one request is made per order/SKU.
        // So the same SKUs on multiple lines need to be grouped.

        $orderItems = array();
        foreach ($order->orderItems as $orderItem) {
            /** @var $orderItem OrderItem */
            $orderItems[] = array(
                'sku' => $orderItem->getSKU(),
                'quantity' => $this->formatNumber($orderItem->getQuantity()),
                'unit_price' => $this->formatNumber($orderItem->getUnitPrice()),
                'taxes' => $this->formatNumber($orderItem->getTaxes()),
                'category_or_variation' => $orderItem->getCategoryOrVariation(),
                'name' => $orderItem->getName(),
            );
        }
        $options = array(
            'transaction_id' => $order->getTransactionID(),
            'store_name' => $order->getStoreName(),
            'total' => $this->formatNumber($order->getTotal()),
            'taxes_total' => $this->formatNumber($order->getTaxesTotal()),
            'shipping_total' => $this->formatNumber($order->getShippingTotal()),
            'city' => $order->getCity(),
            'state_or_province' => $order->getStateOrProvince(),
            'country' => $order->getCountry(),
            'order_items' => $orderItems
        );

        $template = $environment->loadTemplate('KunstmaanSeoBundle:GoogleAnalyticsTwigExtension:ecommerce_tracking.html.twig');
        return $template->render($options);
    }

    protected function formatNumber($number) {
        return number_format($number, 2, '.', '');
    }

    /**
     * Prefer the given
     * @param Twig_Environment $environment
     */
    private function setOptionIfNotSet(&$arr, $option, $value) {
        if ($this->isOptionSet($arr, $option)) {
            $arr[$option] = $value;
        }
    }

    private function isOptionSet($arr, $option) {
        return (!isset($arr[$option]) || !empty($arr[$option]));
    }

    /**
     * Not sure if this works ... doesn't appear to see all the globals.
     *
     * @param Twig_Environment $environment
     * @param $name
     * @return null
     */
    private function getGlobal(\Twig_Environment $environment, $name) {
        foreach ($environment->getGlobals() as $k => $v) {
            if ($k == $name) {
                return $v;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'kuma_google_analytics_twig_extension';
    }

}