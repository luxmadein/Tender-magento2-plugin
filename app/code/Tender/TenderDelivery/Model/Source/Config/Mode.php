<?php

namespace Tender\TenderDelivery\Model\Source\Config;

/**
 * Mode Model
 */
class Mode extends \Magento\Framework\Model\AbstractModel
{
    
    public function toOptionArray()
    {
        return array(
            array('value' => 'sandbox', 'label' =>__('Sandbox')),
            array('value' => 'production', 'label' =>__('Production'))
        );
    }
    
   
}
