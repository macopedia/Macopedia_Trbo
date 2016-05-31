<?php
/**
 * Created by PhpStorm.
 * User: jidziak
 * Date: 30.05.16
 * Time: 17:07
 */

class Macopedia_Trbo_Block_Trbo extends Mage_Core_Block_Template
{
    const SYSTEM_CONFIG_PRODUCT_ATTRIBUTE_CODE = 'macopedia_trbo/general/product_attribute_code';

    protected $lastRealOrderId;
    protected $lastOrder;

    /**
     * Check is module enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::getStoreConfigFlag('macopedia_trbo/general/enable')) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }

    public function getAttributeCodeForCatalog()
    {
        $attributeCode = Mage::getStoreConfig(self::SYSTEM_CONFIG_PRODUCT_ATTRIBUTE_CODE);

        if (empty($attributeCode) === true) {
            $attributeCode = false;
        }

        return $attributeCode;
    }

    public function getGeneralConfig($code)
    {
        return Mage::getStoreConfig('macopedia_trbo/general/' . $code);
    }

    protected function _setLastOrderData()
    {
        $this->lastRealOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $this->lastOrder = Mage::getModel('sales/order')->loadByIncrementId($this->lastRealOrderId);
    }

    protected function _getLastOrderInfo()
    {
        if(!$this->lastRealOrderId) {
            $this->_setLastOrderData();
        }

        return array(
            'order_id' => $this->lastOrder->getId(),
            'value' => number_format($this->lastOrder->getGrandTotal(), 2, '.', ''),
            'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
            'coupon_code' => $this->lastOrder->getCouponCode(),
            'products' => $this->_prepareItemsData('sale', $this->lastOrder)
        );
    }

    protected function _getItemData($productId, $item)
    {
        return array(
            'product_id' => $productId,
            'name' => $item->getProduct()->getName(),
            'quantity' => (int) $item->getQty(),
            'price' => number_format($item->getPriceInclTax(), 2, '.', ''),
        );
    }

    protected function _prepareItemsData($type, $model)
    {
        $productsData = array();
        $attributeCode = $this->getAttributeCodeForCatalog();
        foreach ($model->getAllVisibleItems() as $item) {
            if ($attributeCode === false) {
                $productId = $item->getProduct()->getId();
            } else {
                $resource = Mage::getSingleton('catalog/product')->getResource();
                $productId = $resource->getAttributeRawValue($item->getProduct()->getId(), $attributeCode, Mage::app()->getStore());
            }
            if ($type == 'current_basket') {
                $productsData[] = $productId;
            } else {
                $productsData[] = $this->_getItemData($productId, $item);
            }
        }
        return $productsData;
    }

    protected function _getBasketInfo()
    {
        $productsInfo = array();
        if ($quote = Mage::getSingleton('checkout/session')->getQuote()) {
            $productsData = $this->_prepareItemsData('basket', $quote);
            $productsInfo = array(
                'value' => number_format($quote->getGrandTotal(), 2, '.', ''),
                'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'products' => $productsData,
            );
        }
        return $productsInfo;
    }

    protected function _getCurrentBasketInfo()
    {
        $productsInfo = array();
        if ($quote = Mage::getSingleton('checkout/session')->getQuote()) {
            $productsData = $this->_prepareItemsData('current_basket', $quote);
            $productsInfo = array(
                'value' => number_format($quote->getGrandTotal(), 2, '.', ''),
                'product_ids' => $productsData,
            );
        }
        return $productsInfo;
    }

    protected function _getProductInfo()
    {
        $product = Mage::registry('current_product');
        $attributeCode = $this->getAttributeCodeForCatalog();
        if ($attributeCode === false) {
            $productId = $product->getId();
        } else {
            $resource = Mage::getSingleton('catalog/product')->getResource();
            $productId = $resource->getAttributeRawValue($product->getId(), $attributeCode, Mage::app()->getStore());
        }
        return array(
            'product_id' => $productId,
            'name' => $product->getName(),
            'price' => number_format($product->getFinalPrice(), 2, '.', ''),
        );
    }

    protected function _getPageType()
    {
        switch (Mage::app()->getRequest()->getModuleName()) {
            case 'onestepcheckout' :
                return 'checkout';
        }

        switch (Mage::app()->getRequest()->getControllerName()) {
            case 'category' :
                return 'category';
            case 'page' :
                return 'home';
            case 'index' :
                return 'other';
            case 'result':
                return 'search';
            case 'product' :
            case 'cart' :
                return null;
            case 'onepage' :
            case 'checkout' :
                return 'checkout';
            default :
                return 'other';
        }
    }
}