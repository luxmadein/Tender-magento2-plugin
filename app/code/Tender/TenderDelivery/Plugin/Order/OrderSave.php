<?php
/**
 * @author Tender
 * @package Tender_TenderDelivery
 */
namespace Tender\TenderDelivery\Plugin\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Session\Storage;

class OrderSave
{
    protected $helper;
    protected $storepickuporderFactory;
    protected $objectManager = null;
    protected $sessionStorage;

    public function __construct(
        \Tender\TenderDelivery\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Storage $sessionStorage
    ) {
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        $this->sessionStorage = $sessionStorage;
    }

    public function afterSave(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $data = $this->helper->getStorepickupDataFromSession();
		
        if(!empty($data['store_pickup'])){
		
			$orderIncrementId = $order->getIncrementId();
			$orderId = $order->getId();
			$store_pickup_id = $data['store_pickup'];
			
			if (is_array($data) && !($data === null)) {
				$data['order_id'] = $orderId;
				
				$pickup_address = $this->getPickupAddress($store_pickup_id);
				$data['pickup_address'] = $pickup_address;
				
				$obj = $this->objectManager->get('\Cynoinfotech\StorePickup\Model\ResourceModel\StorePickupOrder');
				$obj->SavePickupOrder($data);
				$this->sessionStorage->unsData($this->helper->getStorepickupAttributesSessionKey());
			}			
		}
		
		return $order;
    }
    
}
