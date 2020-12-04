<?php
/**
 * @author Tender
 * @package Tender_TenderDelivery
 */
namespace Tender\TenderDelivery\Plugin\Checkout;

class ShippingInformationManagement
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

    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();
        if ($extAttributes instanceof \Magento\Checkout\Api\Data\ShippingInformationExtension) {
            $data = [];
            if ($extAttributes->getStorepickupShippingChecked()) {
                $data = [
                    'store_pickup'          => $extAttributes->getStorePickup(),
                    'calendar_inputField'   => $extAttributes->getCalendarInputField(),
                    'mobile_delivery_date'  => $extAttributes->getMobileDeliveryDate(),
                    'mobile_delivery_time'  => $extAttributes->getMobileDeliveryTime()
                ];
            }
            $this->tenderDeliveryHelper->setStorepickupDataToSession($data);
            $quote = $this->quoteRepository->getActive($cartId);
            if($extAttributes->getCalendarInputField()){
                $quote->setDeliveryDatetime($extAttributes->getCalendarInputField());
            }elseif($extAttributes->getMobileDeliveryDate() && $extAttributes->getMobileDeliveryTime()){
                $deliveryTime = $extAttributes->getMobileDeliveryDate().' '.$extAttributes->getMobileDeliveryTime();
                $quote->setDeliveryDatetime($deliveryTime);
            }else{
                $quote->setDeliveryDatetime(NULL);
            }
        }
        
        return $proceed($cartId, $addressInformation);
    }
}
