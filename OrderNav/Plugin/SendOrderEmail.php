<?php

namespace Kitchen365\OrderNav\Plugin;

use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order;

class SendOrderEmail extends OrderSender
{
    /**
     * @param OrderSender $subject
     * @param \Closure $proceed
     * @param Order $order
     * @param bool $forceSyncMode
     * @return bool
     */
    public function aroundSend(
        OrderSender $subject,
        \Closure $proceed,
        $order,
        $forceSyncMode = false
    ) {
        // if (!$order->getIsFromApi()) {
        //     return false;
        // }

        $this->identityContainer->setStore($order->getStore());
        $order->setSendEmail($this->identityContainer->isEnabled());

        if (!$subject->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            if ($this->checkAndSendEmail($order, $forceSyncMode)) {
                $order->setEmailSent(true);
                $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $order->setEmailSent(null);
            $this->orderResource->saveAttribute($order, 'email_sent');
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }

    /**
     * Checks and sends the order email.
     *
     * @param OrderSender $subject
     * @param Order $order
     * @param bool $forceSyncMode
     * @return bool
     */
    protected function checkAndSendEmail(Order $order, $forceSyncMode)
    {
        $this->identityContainer->setStore($order->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($order);

        $sender = $this->getSender();

        try {
            if ($forceSyncMode) {
                $sender->send();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        if ($this->identityContainer->getCopyMethod() == 'copy') {
            try {
                $sender->sendCopyTo();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        return true;
    }
}
