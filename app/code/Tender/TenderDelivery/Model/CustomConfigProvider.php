<?php
/**
 * @author Tender Team
 * @package Tender_TenderDelivery
 */
 
namespace Tender\TenderDelivery\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CustomConfigProvider implements ConfigProviderInterface
{
    protected $storeManager;
    protected $scopeConfig;
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Tender\TenderDelivery\Helper\Data $dataHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        $storepick_config = [];
        $storepick_info = [];
        $storepick_location = [];
        
        $config = [
            'storepick_config' => $storepick_config,
            'storepick_info' => [],
            'storepick_location' => [],
            'hour_min' => '11:00',
            'hour_max' => '22:00',
            'delivery_time_interval' => $this->getTimeIntervals(),
            'storepick_config_encode' => json_encode($storepick_config)
        ];
        return $config;
    }
    
    
    protected function getTimeIntervals(){
        $timearray = array();
        for($hours=10; $hours<22; $hours++){
            for($mins=0; $mins<60; $mins+=30){
                $time = str_pad($hours,2,'0',STR_PAD_LEFT).':'.str_pad($mins,2,'0',STR_PAD_LEFT);
                $timearray[] = array('label'=>$time, 'value'=> $time);
            }
        }
        return $timearray;
    }
    
}