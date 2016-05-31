<?php

class Macopedia_Trbo_Model_Adminhtml_System_Config_Source_Attributes extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Get all catalog attributes
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_array($this->_options) === false) {
            $resourceKey = 'catalog/product_attribute_collection';
            $resource    = Mage::getResourceModel($resourceKey);
            $attributes  = $resource->getItems();
            $helper      = Mage::helper('hevelop_facebookpixel');

            $this->_options[] = array(
                'label' => $helper->__('Use default product ID'),
                'value' => '',
            );

            foreach ($attributes as $attribute) {
                $this->_options[] = array(
                   'label' => $attribute->getFrontendLabel(),
                   'value' => $attribute->getAttributeCode(),
                );
            }
        }
        return $this->_options;
    }

    /**
     * getAllOptions method
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();

    }
}