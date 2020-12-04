<?php
namespace Tender\TenderDelivery\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddPickupTimeToOrderObserver implements ObserverInterface
{
    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $deliveryTime = $quote->getDeliveryDatetime();
        if (!$deliveryTime) {
            return $this;
        }
        //Set fee data to order
        $order = $observer->getOrder();
        $order->setData('delivery_datetime', $deliveryTime);

        return $this;
    }
}
