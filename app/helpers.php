<?php

use Illuminate\Support\Facades\Cache;
use Razorpay\Api\Api;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Contracts\Message as ContractsMessage;
use Illuminate\Support\Facades\Http;

if (!function_exists('getCartQuantities')) {
    function getCartQuantities()
    {
        $userId  = auth('api')->id() ?? session()->getId();
        $cartKey = "cart_{$userId}";
        $cart    = Cache::get($cartKey, []);

        return collect($cart)->mapWithKeys(fn($item) => [
            $item['variant_id'] => $item['quantity']
        ]);
    }
}

    if (!function_exists('intValue')) {
        function intValue($value)
        {
            return intval($value);
        }
    }

if (!function_exists('showDate')) {
    function showDate($date, $format = 'd/m/Y h:i:s A')
    {
        return \Carbon\Carbon::parse($date)->format($format);
    }
}



if (!function_exists('razorPay')) {
    function razorPay()
    {
        return new class() {
            protected $api;

            public function __construct()
            {
                $this->api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
            }

            public function createOrder($amount, $currency = 'INR')
            {
                try {
                    $order = $this->api->order->create([
                        'amount' => $amount * 100,
                        'currency' => $currency,
                        'receipt' => 'order_' . uniqid(),
                        'payment_capture' => 1,
                    ]);

                    return $order;
                } catch (Exception $e) {
                    return ['error' => $e->getMessage()];
                }
            }
        };
    }
}


if (!function_exists('milkPackTypes')) {
    function milkPackTypes()
    {
        return [
            '500ml' => '500 ml',
            '1ltr'  => '1 Ltr',
            '2ltr'  => '2 Ltr',
        ];
    }
}

if (!function_exists('nettySms')) {
    /**
     * Initialize the Twilio Client and provide a fluent API.
     *
     * @return object
     */
    function nettySms()
    {
        return new class() {

            /**
             * Message to Send to the User
             * @var
             */
            protected $message;



            /**
             * Recipient of the Message
             * @var
             */
            protected $to;



            /**
             * function to set the message
             * @param \App\Contracts\Message $message
             * @return __anonymous
             */
            public function send(ContractsMessage $message)
            {
                $this->message = $message;
                return $this;
            }





            /**
             * Send The Message
             * @param string $recipient
             * @throws \Exception
             * @return \Illuminate\Http\Client\Response
             */
            public function to(string ...$recipient)
            {


                try {

                    /**
                     * Validatew the phone number
                     */
                    $validator = \Illuminate\Support\Facades\Validator::make(["phone" => $recipient], [
                        'phone' => [
                            'required'
                        ],
                    ]);

                    if ($validator->fails())
                        throw new \Exception("Phone Number is Required to Send Message");


                    # Recipient of the Message
                    $this->to = $recipient;

                    # Using HTTP Client to Send the Message
                    $payload = [
                        "Account" => [
                            "APIKey" => "hdx3ivaMM0uJ2cMgPWmfBw",
                            "SenderId" => "PASKUD",
                            "Channel" => "Trans",
                            "DCS" => 0,
                            "FlashSms" => 0,
                            "Route" => 4,
                            "PeId" => "1701178591033234713"
                        ],
                        "Messages" => $this->people($this->to)
                    ];
                     Http::post(
                        'https://retailsms.nettyfish.com/api/mt/SendSMS',
                        $payload
                    );

                } catch (\Throwable $e) {

                    throw new \Exception($e->getMessage());
                }
            }

            protected function people(array $numbers)
            {
                return collect($numbers)->map(function ($number) {
                    return [
                        "Number" => str($number),
                        "dlttemplateid" => $this->message->template(),
                        "Text" => $this->message->message()
                    ];
                });
            }
        };
    }
}



