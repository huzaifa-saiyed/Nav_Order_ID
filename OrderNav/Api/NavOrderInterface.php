<?php

namespace Kitchen365\OrderNav\Api;


interface NavOrderInterface
{
    /**
     * @param int $id
     * @param string $nav_order_id
     * @return string
     */
    public function putNavOrderId($id, $nav_order_id);

}