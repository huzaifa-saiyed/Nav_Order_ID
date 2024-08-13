<?php

namespace Kitchen365\OrderNav\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

class EmailCron
{
    protected $orderCollectionFactory;
    protected $orderSender;
    protected $logger;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        OrderSender $orderSender,
        LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderSender = $orderSender;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            echo('Cron is Start!! ');
            $orderCollection = $this->orderCollectionFactory->create()
                ->addFieldToFilter('nav_order_id', ['null' => true]);

            foreach ($orderCollection as $order) {
                if ($this->orderSender->send($order, true)) {
                    $order->setIsFromApi(1)->save();
                    echo('Order email sent successfully for Order ID: ' . $order->getIncrementId() . '<br>');
                    $this->logger->info('Order email sent successfully for Order ID: ' . $order->getIncrementId());
                } else {
                    echo('Order email sent successfully for Order ID: ' . $order->getIncrementId() . '<br>');
                    $this->logger->error('Failed to send order email for Order ID: ' . $order->getIncrementId());
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error in SendOrderEmails cron: ' . $e->getMessage());
        }
    }
}
