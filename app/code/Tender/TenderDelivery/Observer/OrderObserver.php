<?php
namespace Tender\TenderDelivery\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
class OrderObserver implements ObserverInterface
{
    
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;
    
    /**
     * @var \Tender\TenderDelivery\Helper\Data
     */
    protected $tenderDeliveryHelper;

    public function __construct(
        \Tender\TenderDelivery\Helper\Data $tenderDeliveryHelper,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->tenderDeliveryHelper = $tenderDeliveryHelper;
        $this->quoteRepository = $quoteRepository;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->tenderDeliveryHelper->isTookanEnabled()){
            return false;
        }
        
       	$order = $observer->getEvent()->getOrder();
        $shippingAddress = $order->getShippingAddress();
        
        if($order->getShippingMethod()!='tenderschedule_tenderschedule'){
            return false;
        }
        
        $customerAddressData = array();        
        $customerAddressData['street'] = implode(',', $shippingAddress->getStreet());
        $customerAddressData['city'] = $shippingAddress->getCity();
        $customerAddressData['region'] = $shippingAddress->getRegion();
        $customerAddressData['country'] = $this->tenderDeliveryHelper->getCountryName($shippingAddress->getCountryId());
        $customerAddressData['postcode'] = $shippingAddress->getPostcode();
        
        $customerAddress = implode(',', $customerAddressData);
        
        $googleApi = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($customerAddress)."&key=".$this->tenderDeliveryHelper->getGoogleMapKey();
				
        $addressJsonData =  file_get_contents($googleApi);
        $addressDatas = json_decode($addressJsonData, 1);
        $customerAddressLat = '';
        $customerAddressLng = '';
        foreach($addressDatas['results'] as $addressData){    
           $customerAddressLat = $addressData['geometry']['location']['lat'];
           $customerAddressLng = $addressData['geometry']['location']['lng'];
        }
        
        $pickupPoint = $this->tenderDeliveryHelper->getPickupPoint();
        $pickupAddress = array();        
        $pickupAddress['street'] = $pickupPoint->getStreet();
        $pickupAddress['city'] = $pickupPoint->getCity();
        $pickupAddress['region'] = $pickupPoint->getRegion();
        $pickupAddress['country'] = $pickupPoint->getCountry();
        $pickupAddress['postcode'] = $shippingAddress->getPostcode();
        $pickupAddress = implode(',',$pickupAddress);
        
        $deliveryTime = $order->getDeliveryDatetime();
        $pickupTime = strtotime("-45 minutes", strtotime($deliveryTime));
        //exit;
        $pickupdata = array(
            "api_key"=> $this->tenderDeliveryHelper->getTookanApiKey(),
            "order_id"=> $order->getIncrementId(),
            "team_id"=> "",
            "auto_assignment"=> "0",
            "job_description"=> "groceries delivery",
            "job_pickup_phone"=> $pickupPoint->getPhone(),
            "job_pickup_name"=> $pickupPoint->getName(),
            "job_pickup_email"=> $pickupPoint->getEmail(),
            "job_pickup_address"=> $pickupAddress,
            "job_pickup_latitude"=> $pickupPoint->getLatitude(),
            "job_pickup_longitude"=> $pickupPoint->getLongitude(),
            "job_pickup_datetime"=> $pickupTime,
            "customer_email" => $order->getCustomerEmail(),
            "customer_username"=> $order->getCustomerName(),
            "customer_phone"=> $shippingAddress->getTelephone(),
            "customer_address"=> $customerAddress,
            "latitude"=> $customerAddressLat,
            "longitude"=> $customerAddressLng,
            "job_delivery_datetime"=> $order->getDeliveryDatetime(),
            "has_pickup"=> "1",
            "has_delivery"=> "1",
            "layout_type"=> "0",
            "tracking_link"=> 1,
            "timezone"=> "-330",
            "notify"=> 1,
            "tags"=> "",
            "geofence"=> 0,
            "ride_type"=> 0
        );
        
        $tookanEndPoints = $this->tenderDeliveryHelper->getEndpointUrls();
        $tookanTaskCreateApiUrl = $tookanEndPoints->getTaskCreateApiUrl();
        $tookanApiRequest = $this->tenderDeliveryHelper->callApi($tookanTaskCreateApiUrl,json_encode($pickupdata));
        
        
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/tender.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($pickupdata,true));
        $logger->info(print_r($tookanApiRequest,true));
        
        //exit;
 
    }
}