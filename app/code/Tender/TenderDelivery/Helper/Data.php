<?php

/**
 * Tender data helper
 */
namespace Tender\TenderDelivery\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
      * @var Storage
      */
    private $session;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    
    /**
     * Region Factory
     *
     * @var \Magento\Directory\Model\RegionFactory 
     */
    protected $_regionFactory;
    
    /**
     * Country Factory
     *
     * @var \Magento\Directory\Model\CountryFactory 
     */
    protected $_countryFactory;
    
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Session\Storage $sessionStorage,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_countryFactory = $countryFactory;
        $this->_regionFactory = $regionFactory;
        $this->session = $sessionStorage;        
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }
    
    /**
     * Is Enable
     * @return string
     */
    public function isTookanEnabled()
    {
        return $this->_scopeConfig->getValue('tenderdelivery/tookan/enable',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    
     /**
     * Get Token
     * @return boolean
     */
    
    public function getTookanApiKey()
    {
        return $this->_scopeConfig->getValue('tenderdelivery/tookan/api_key',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    
    /**
     * Get Google Map Key
     * @return string
     */
    public function getGoogleMapKey()
    {
        return $this->_scopeConfig->getValue('tenderdelivery/tookan/google_map_key',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    
    /**
     * Get Endpoint Urls
     * @return string
     */
    public function getEndpointUrls()
    {
        $_urls = array();
        $urlObject = new \Magento\Framework\DataObject();
        $_urls = array ('task_create_api_url' => 'https://api.tookanapp.com/v2/create_task');
        $urlObject->setData($_urls);
        return $urlObject;
    }
    
    
    /**
    * Get Shipping origin data from store scope config
    * Displays data on storefront
    * @return array
    */
    
    
    public function getShippingOrigin(){
        
        $countryId = $this->_scopeConfig->getValue('shipping/origin/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $countryName = $this->getCountryName($countryId);
        
        $regionId = $this->_scopeConfig->getValue('shipping/origin/region_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(is_numeric($regionId)){
            $regionName = $this->getRegionName($regionId); 
        }
        
        return [
            'country_id' => $this->_scopeConfig->getValue('shipping/origin/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'country' => $countryName,
            'region_id' => $this->_scopeConfig->getValue('shipping/origin/region_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'region' => is_numeric($regionId)?$regionName:$regionId,
            'postcode' => $this->_scopeConfig->getValue('shipping/origin/postcode',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'city' => $this->_scopeConfig->getValue('shipping/origin/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'street_line1' => $this->_scopeConfig->getValue('shipping/origin/street_line1',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'street_line2' => $this->_scopeConfig->getValue('shipping/origin/street_line2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'store_name' => $this->_scopeConfig->getValue('general/store_information/name',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'store_phone' => $this->_scopeConfig->getValue('general/store_information/phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'store_email' => $this->_scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE )
        ];
    
    }
    
    
    
    
    /**
    * Get pickup point data from store scope config
    * Displays data on storefront
    * @return array
    */
    
    public function getPickupPoint(){
        
        $_urls = array();
        $pickupPoint = new \Magento\Framework\DataObject();
        
        $countryId = $this->_scopeConfig->getValue('tenderdelivery/pickup_point/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $countryName = $this->getCountryName($countryId);
        
        $regionId = $this->_scopeConfig->getValue('tenderdelivery/pickup_point/region_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(is_numeric($regionId)){
            $regionName = $this->getRegionName($regionId); 
        }
        
        $pickupData = [
            'country_id' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'country' => $countryName,
            'region_id' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/region_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'region' => is_numeric($regionId)?$regionName:$regionId,
            'postcode' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/postcode',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'city' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'street' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/street',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'name' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/name',  \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'phone' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'email' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE ),
            'latitude' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/latitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'longitude' => $this->_scopeConfig->getValue('tenderdelivery/pickup_point/longitude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE )
        ];
        $pickupPoint->setData($pickupData);
        return $pickupPoint;
    
    }
    
    
    
    
    /*
     * Call API using curl
     * @param  string $url
     * @param array $_data
     * @param integer $_scopeId
     * @return array|object
     */
    
    public function callApi($url,$_data){
        
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL,  $url);
        curl_setopt($curlHandle, CURLOPT_POST, true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $_data);
        curl_setopt($curlHandle, CURLOPT_HEADER, FALSE);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $result = curl_exec($curlHandle);
        $error = curl_error($curlHandle);
        return json_decode($result);
    }
    
    
    /*
     * Get Country Name
     * @param string $countryCode
     */
    
    public function getCountryName($countryCode){    
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }
    
    
    /*
     * Get Region Name
     * @param string $regionCode
     */
    
    public function getRegionName($regionCode){    
        $region = $this->_regionFactory->create()->load($regionCode);
        return $region->getName();
    }
    
    
    
     /**
     * Save Data to session
     *
     * @param array $data
     */
    public function setStorepickupDataToSession($data)
    {
        $this->session->setData($this->getStorepickupAttributesSessionKey(), $data);
    }

    /**
     * load Data to sassion
     *
     * @return array
     */
    public function getStorepickupDataFromSession()
    {
        return $this->session->getData($this->getStorepickupAttributesSessionKey());
    }

    /**
     * get Session Key
     *
     * @return string
     */
    public function getStorepickupAttributesSessionKey()
    {
        return 'cistorepickup';
    }
    
    
}