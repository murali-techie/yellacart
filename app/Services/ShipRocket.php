<?php


namespace App\Services;


use App\Order;
use App\OrderDetail;
use App\ProductStock;
use App\ShiprocketDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShipRocket
{
    private $token;
    /**
     * @var mixed
     */
    private $shippingInfo;
    /**
     * @var OrderDetail
     */
    private $orderDetail;
    /**
     * @var mixed
     */
    private $order;
    /**
     * @var mixed
     */
    private $product;
    private $productStock;

    public function __construct(OrderDetail $orderDetail)
    {
        $this->token = \Seshac\Shiprocket\Shiprocket::getToken();
        $this->orderDetail = $orderDetail;
        $this->order = $this->orderDetail->order;
        $this->shippingInfo = json_decode($this->order->shipping_address);
        $this->product = $this->orderDetail->product;
        $this->productStock = $this->product->stocks()->where('variant', $this->orderDetail->variation)->first();
    }

    public function createOrder()
    {
        $response = \Seshac\Shiprocket\Shiprocket::order($this->token)->create($this->getOrderDetails());
            if (isset($response['order_id'])) {
                $this->orderDetail->shiprocket()->save(
                new ShiprocketDetail(
                    [
                        "shiprocket_order_id" => $response['order_id'],
                        "shipment_id" => $response['shipment_id'],
                        "status" => $response['status'],
                        "status_code" => $response['status_code'],
                        "onboarding_completed_now" => $response['onboarding_completed_now'],
                        "awb_code" => $response['awb_code'],
                        "courier_company_id" => $response['courier_company_id'],
                        "courier_name" => $response['courier_name'],
                    ]
                )
            );
            } else {
            Log::error('Error from Shiprocket', $response->toArray());
        }
    }

    public function getOrderDetails()
    {
        return [
            "order_id" => $this->orderDetail->id,
            "order_date" => now()->format('Y-m-d'),
            "pickup_location" => 'Primary',
            "channel_id" => 1950441,
            "comment" => "",
            "billing_customer_name" => $this->shippingInfo->name,
            "billing_last_name" => "",
            "billing_address" => $this->shippingInfo->address,
            "billing_address_2" => "",
            "billing_city" => $this->shippingInfo->city,
            "billing_pincode" => $this->shippingInfo->postal_code,
            "billing_state" => $this->shippingInfo->name,
            "billing_country" => $this->shippingInfo->country,
            "billing_email" => $this->shippingInfo->email,
            "billing_phone" => $this->shippingInfo->phone,
            "shipping_is_billing" => true,
            "shipping_customer_name" => "",
            "shipping_last_name" => "",
            "shipping_address" => "",
            "shipping_address_2" => "",
            "shipping_city" => "",
            "shipping_pincode" => "",
            "shipping_country" => "",
            "shipping_state" => "",
            "shipping_email" => "",
            "shipping_phone" => "",
            "order_items" => [
                [
                    "name" => $this->product->name,
                    "sku" => $this->productStock->sku,
                    "units" => $this->orderDetail->quantity,
                    "selling_price" => $this->orderDetail->price,
                    "tax" => $this->orderDetail->tax
                ]

            ],
            "payment_method" => $this->order->payment_type === 'cash_on_delivery' ? 'COD' : 'Prepaid',
            "shipping_charges" => $this->orderDetail->shipping_cost,
            "giftwrap_charges" => 0,
            "transaction_charges" => 0,
            "total_discount" => $this->order->coupon_discount,
            "sub_total" => $this->orderDetail->price + $this->orderDetail->tax,
            "length" => $this->productStock->length,
            "breadth" => $this->productStock->breadth,
            "height" => $this->productStock->height,
            "weight" => $this->productStock->weight
        ];
    }
}
