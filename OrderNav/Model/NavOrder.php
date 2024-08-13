<?php 
namespace Kitchen365\OrderNav\Model;

use Kitchen365\OrderNav\Api\NavOrderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

class NavOrder implements NavOrderInterface
{
    private $orderFactory;
    private $orderSender;
    private $logger;

    public function __construct(
        OrderFactory $orderFactory, 
        OrderSender $orderSender,
        LoggerInterface $logger
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
    }

    /** 
     * @return string 
     */
    public function putNavOrderId($id, $nav_order_id)
    {
        try {
            $order = $this->orderFactory->create()->load($id);
            $order->setIsFromApi(1);
            $order->setNavOrderId($nav_order_id)->save();

            if ($this->orderSender->send($order, true)) {
                $response = ['success' => true, 'message' => 'Nav Order ID set and email sent successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Nav Order ID set but email failed to send'];
            }
            return $response;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
