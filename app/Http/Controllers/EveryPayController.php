<?php

namespace App\Http\Controllers;

use App\Models\AdditionalLanguage;
use App\Models\ItemPrice;
use App\Models\Items;
use App\Models\OptionPrice;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Shop;
use Illuminate\Http\Request;
use Everypay\Everypay;
use Everypay\Payment;
use Magarrent\LaravelCurrencyFormatter\Facades\Currency;

class EveryPayController extends Controller
{

    // Function for goto EveryPayCheckout
    public function gotoEveryPayCheckout($shop_slug)
    {
        $checkout_type = session()->get('checkout_type');
        $discount_per = session()->get('discount_per');
        $final_amount = 0.00;

        if(empty($checkout_type))
        {
            return redirect()->route('restaurant',$shop_slug)->with('error','UnAuthorized Request!');
        }

        // Get Cart Details
        $cart = session()->get('cart', []);

        if(count($cart) == 0)
        {
            return redirect()->route('restaurant',$shop_slug);
        }

        // Add Items
        foreach($cart as $cart_val)
        {
            $total_amount = $cart_val['total_amount'];
            $final_amount += $total_amount;
        }

        if($discount_per > 0)
        {
            $discount_amount = ($final_amount * $discount_per) / 100;
            $final_amount = $final_amount - $discount_amount;
        }

        $every_pay_data['shop_slug'] = $shop_slug;
        $every_pay_data['total_amount'] = $final_amount;
        $every_pay_data['total_amount_text'] = Currency::currency('EUR')->format($final_amount);
        return view('shop.every_pay_checkout',$every_pay_data);
    }


    // Function for Pay in EveryPay
    public function payWithEveryPay($shop_slug,Request $request)
    {
        $discount_per = session()->get('discount_per');
        $card_number = str_replace(' ','',$request->card_number);
        $card_holder = $request->card_holder;
        $card_cvc = $request->card_cvc;
        $total_amount = number_format($request->total_amount,0);
        $card_exp = explode(' / ',$request->card_expiry);

        // EveryPay Config
        $every_pay_config = getEveryPayConfig($shop_slug);
        $secret_key = (isset($every_pay_config['secret_key'])) ? $every_pay_config['secret_key'] : '';
        $test_mode = (isset($every_pay_config['mode'])) ? $every_pay_config['mode'] : 1;

        $evrypay = Everypay::setApiKey($secret_key);
        $evrypay = Everypay::$isTest = $test_mode;

        $params = array(
            'card_number'       => $card_number,
            'expiration_month'  => $card_exp[0],
            'expiration_year'   => $card_exp[1],
            'cvv'               => $card_cvc,
            'holder_name'       => $card_holder,
            'amount'            => $total_amount, # amount in cents for 10 EURO.
        );

        try
        {
            $payment = Payment::create($params);

            if(isset($payment->is_captured) && $payment->is_captured == 1)
            {
                $cart = session()->get('cart', []);

                // Shop Details
                $data['shop_details'] = Shop::where('shop_slug',$shop_slug)->first();

                // Shop ID
                $shop_id = isset($data['shop_details']->id) ? $data['shop_details']->id : '';

                $shop_settings = getClientSettings($shop_id);

                // Ip Address
                $user_ip = $request->ip();

                $final_amount = 0;
                $total_qty = 0;

                // Shop Currency
                $currency = (isset($shop_settings['default_currency']) && !empty($shop_settings['default_currency'])) ? $shop_settings['default_currency'] : 'EUR';

                // Primary Language Details
                $language_setting = clientLanguageSettings($shop_id);
                $primary_lang_id = isset($language_setting['primary_language']) ? $language_setting['primary_language'] : '';
                $data['primary_language_details'] = getLangDetails($primary_lang_id);

                // Get all Additional Language of Shop
                $data['additional_languages'] = AdditionalLanguage::with(['language'])->where('shop_id',$shop_id)->where('published',1)->get();

                // Current Languge Code
                $current_lang_code = (session()->has('locale')) ? session()->get('locale') : 'en';

                // Order Settings
                $order_settings = getOrderSettings($shop_id);

                // Keys
                $name_key = $current_lang_code."_name";
                $label_key = $current_lang_code."_label";

                $order_details = session()->get('order_details');

                $checkout_type = $order_details['checkout_type'];
                $payment_method = $order_details['payment_method'];

                if($checkout_type == 'takeaway')
                {
                    // New Order
                    $order = new Order();
                    $order->shop_id = $shop_id;
                    $order->ip_address = $user_ip;
                    $order->firstname =  $order_details['firstname'];
                    $order->lastname =  $order_details['lastname'];
                    $order->email =  $order_details['email'];
                    $order->phone =  $order_details['phone'];
                    $order->checkout_type = $checkout_type;
                    $order->payment_method = $payment_method;
                    $order->order_status = 'pending';
                    $order->estimated_time = (isset($order_settings['order_arrival_minutes']) && !empty($order_settings['order_arrival_minutes'])) ? $order_settings['order_arrival_minutes'] : '30';
                    $order->save();
                }
                elseif($checkout_type == 'table_service')
                {
                    // New Order
                    $order = new Order();
                    $order->shop_id = $shop_id;
                    $order->ip_address = $user_ip;
                    $order->checkout_type = $checkout_type;
                    $order->payment_method = $payment_method;
                    $order->order_status = 'pending';
                    $order->table = $order_details['table'];
                    $order->estimated_time = (isset($order_settings['order_arrival_minutes']) && !empty($order_settings['order_arrival_minutes'])) ? $order_settings['order_arrival_minutes'] : '30';
                    $order->save();
                }
                elseif($checkout_type == 'room_delivery')
                {
                    // New Order
                    $order = new Order();
                    $order->shop_id = $shop_id;
                    $order->ip_address = $user_ip;
                    $order->firstname = $order_details['firstname'];
                    $order->lastname = $order_details['lastname'];
                    $order->checkout_type = $checkout_type;
                    $order->payment_method = $payment_method;
                    $order->order_status = 'pending';
                    $order->room = $order_details['room'];
                    $order->delivery_time = (isset($order_details['delivery_time'])) ? $order_details['delivery_time'] : '';
                    $order->estimated_time = (isset($order_settings['order_arrival_minutes']) && !empty($order_settings['order_arrival_minutes'])) ? $order_settings['order_arrival_minutes'] : '30';
                    $order->save();
                }

                // Insert Order Items
                if($order->id)
                {
                    foreach($cart as $cart_val)
                    {
                        $otpions_arr = [];

                        // Item Details
                        $item_details = Items::where('id',$cart_val['item_id'])->first();
                        $item_name = (isset($item_details[$name_key])) ? $item_details[$name_key] : '';

                        //Price Details
                        $price_detail = ItemPrice::where('id',$cart_val['option_id'])->first();
                        $price_label = (isset($price_detail[$label_key])) ? $price_detail[$label_key] : '';
                        $item_price = (isset($price_detail['price'])) ? $price_detail['price'] : '';

                        if(!empty($price_label))
                        {
                            $otpions_arr[] = $price_label;
                        }


                        $total_amount = $cart_val['total_amount'];
                        $total_amount_text = $cart_val['total_amount_text'];
                        $categories_data = (isset($cart_val['categories_data']) && !empty($cart_val['categories_data'])) ? $cart_val['categories_data'] : [];

                        $final_amount += $total_amount;
                        $total_qty += $cart_val['quantity'];

                        if(count($categories_data) > 0)
                        {
                            foreach($categories_data as $option_id)
                            {
                                $my_opt = $option_id;

                                if(is_array($my_opt))
                                {
                                    if(count($my_opt) > 0)
                                    {
                                        foreach ($my_opt as $optid)
                                        {
                                            $opt_price_dt = OptionPrice::where('id',$optid)->first();$opt_price_name = (isset($opt_price_dt[$name_key])) ? $opt_price_dt[$name_key] : '';
                                            $otpions_arr[] = $opt_price_name;
                                        }
                                    }
                                }
                                else
                                {
                                    $opt_price_dt = OptionPrice::where('id',$my_opt)->first();
                                    $opt_price_name = (isset($opt_price_dt[$name_key])) ? $opt_price_dt[$name_key] : '';
                                    $otpions_arr[] = $opt_price_name;
                                }
                            }
                        }

                        // Order Items
                        $order_items = new OrderItems();
                        $order_items->shop_id = $shop_id;
                        $order_items->order_id = $order->id;
                        $order_items->item_id = $cart_val['item_id'];
                        $order_items->item_name = $item_name;
                        $order_items->item_price = $item_price;
                        $order_items->item_price_label = $price_label;
                        $order_items->item_qty = $cart_val['quantity'];
                        $order_items->sub_total = $total_amount;
                        $order_items->sub_total_text = $total_amount_text;
                        $order_items->item_price_label = $price_label;
                        $order_items->options = serialize($otpions_arr);
                        $order_items->save();
                    }

                    $update_order = Order::find($order->id);
                    if($discount_per > 0)
                    {
                        $discount_amount = ($final_amount * $discount_per) / 100;
                        $update_order->discount_per = $discount_per;
                        $update_order->discount_value = $final_amount - $discount_amount;
                    }
                    $update_order->order_total = $final_amount;
                    $update_order->order_total_text = Currency::currency($currency)->format($final_amount);
                    $update_order->total_qty = $total_qty;
                    $update_order->update();
                }

                session()->forget('cart');
                session()->forget('checkout_type');
                session()->forget('order_details');
                session()->forget('discount_per');
                session()->save();

                return redirect()->route('shop.checkout.success',[$shop_slug,encrypt($order->id)]);
            }
            else
            {
                return redirect()->route('everypay.checkout.view',$shop_slug)->with('error',$payment->error->message);
            }

        }
        catch (\Throwable $th)
        {
            return redirect()->route('restaurant', $shop_slug)->with('error','Payment Failed !');
        }

    }

}