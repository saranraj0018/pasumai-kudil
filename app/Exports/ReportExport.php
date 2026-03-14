<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        if ($this->type == 'grocery') {
            return collect($this->data)->map(function ($order) {
                if ($order->order->status == 1) {
                    $status = 'Ordered';
                } else if ($order->order->status == 2) {
                    $status = 'On Hold';
                } else if ($order->order->status == 3) {
                    $status = 'Shipped';
                } else if ($order->order->status == 4) {
                    $status = 'Delivered';
                } else if ($order->order->status == 5) {
                    $status = 'Cancelled';
                } else if ($order->order->status == 6) {
                    $status = 'Refunded';
                } else {
                    $status = '';
                }
                return [
                    'Order ID' => $order->order->order_id,
                    'User Name' => $order->order->user->name ?? '',
                    'Net Amount' => $order->net_amount ?? '',
                    'Shipping Amount' => $order->order->shipping_amount ?? '',
                    'GST Amount' => $order->order->gst_amount ?? '',
                    'Total Amount' => $order->order->gross_amount ?? '',
                    'Status' => $status ?? '',
                    'Order Date' => !empty($order->order->created_at)
                        ? Carbon::parse($order->order->created_at)->format('d-m-Y')
                        : '',
                    'Shipped Date' => !empty($order->order->shipped_at)
                        ? Carbon::parse($order->order->shipped_at)->format('d-m-Y')
                        : '',
                    'Delivered Date' =>  !empty($order->order->delivered_at)
                        ? Carbon::parse($order->order->delivered_at)->format('d-m-Y')
                        : '',
                    'Cancelled Date' => !empty($order->order->cancelled_at)
                        ? Carbon::parse($order->order->cancelled_at)->format('d-m-Y')
                        : '',
                    'Refunded Date' =>  !empty($order->order->refunded_at)
                        ? Carbon::parse($order->order->refunded_at)->format('d-m-Y')
                        : '',
                ];
            });
        }

        if ($this->type == 'milk') {
            return collect($this->data)->map(function ($delivery) {
                return [
                    'Subscription Plan Name' => $delivery->get_user_subscription->get_subscription->plan_name,
                    'User Name' => $delivery->get_user->name ?? '',
                    'Delivery Partner Name' => $delivery->get_user->name ?? '',
                    'Delivery Date' => !empty($delivery->delivery_date)
                        ? Carbon::parse($delivery->delivery_date)->format('d-m-Y')
                        : '',
                    'Delivery Status' => $delivery->delivery_status ?? '',
                    'Pack' => $delivery->pack ?? '',
                    'Quantity' => $delivery->quantity ?? '',
                    'Price' => $delivery->amount ?? '',
                ];
            });
        }
    }

    public function headings(): array
    {
        if ($this->type == 'grocery') {
            return [
                'Order ID',
                'User Name',
                'Net Amount',
                'Shipping Amount',
                'GST Amount',
                'Total Amount',
                'Status',
                'Order Date',
                'Shipped Date',
                'Delivered Date',
                'Cancelled Date',
                'Refunded Date'
            ];
        }

        if ($this->type == 'milk') {
            return [
                'Subscription Plan Name',
                'User Name',
                'Delivery Partner Name',
                'Delivery Date',
                'Delivery Status',
                'Pack',
                'Quantity',
                'Price',
            ];
        }
        return [];
    }
}
