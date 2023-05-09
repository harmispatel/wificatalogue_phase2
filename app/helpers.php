<?php

    use App\Models\{AdminSettings, Category, CategoryProductTags,ClientSettings,Ingredient,ItemPrice, Items, Languages,LanguageSettings, OrderSetting, PaymentSettings, ShopBanner,Subscriptions,ThemeSettings,User,UserShop,UsersSubscriptions,Shop};
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;

    // Get Admin's Settings
    function getAdminSettings()
    {
        // Keys
        $keys = ([
            'favourite_client_limit',
            'copyright_text',
            'logo',
            'login_form_background',
            'default_light_theme_image',
            'default_dark_theme_image',
            'theme_main_screen_demo',
            'theme_category_screen_demo',
            'default_special_item_image',
            'contact_us_email',
            'contact_us_subject',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = AdminSettings::select('value')->where('key',$key)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Client's Settings
    function getClientSettings($shopID="")
    {

        if(!empty($shopID))
        {
            $shop_id = $shopID;
        }
        else
        {
            $shop_id = isset(Auth::user()->hasOneShop->shop['id']) ? Auth::user()->hasOneShop->shop['id'] : '';
        }

        // Keys
        $keys = ([
            'shop_view_header_logo',
            'shop_intro_icon',
            'intro_icon_status',
            'intro_icon_duration',
            'business_name',
            'default_currency',
            'business_telephone',
            'instagram_link',
            'pinterest_link',
            'twitter_link',
            'facebook_link',
            'foursquare_link',
            'tripadvisor_link',
            'homepage_intro',
            'map_url',
            'website_url',
            'shop_active_theme',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = ClientSettings::select('value')->where('shop_id',$shop_id)->where('key',$key)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Order Settings
    function getOrderSettings($shopID)
    {
        // Keys
        $keys = ([
            'delivery',
            'takeaway',
            'room_delivery',
            'table_service',
            'only_cart',
            'auto_order_approval',
            'scheduler_active',
            'min_amount_for_delivery',
            'discount_percentage',
            'order_arrival_minutes',
            'schedule_array',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = OrderSetting::select('value')->where('shop_id',$shopID)->where('key',$key)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Payment Settings
    function getPaymentSettings($shopID)
    {
        // Keys
        $keys = [
            'paypal',
            'paypal_mode',
            'paypal_public_key',
            'paypal_private_key',
            'every_pay',
            'everypay_mode',
            'every_pay_public_key',
            'every_pay_private_key',
        ];

        $settings = [];

        foreach($keys as $key)
        {
            $query = PaymentSettings::select('value')->where('shop_id',$shopID)->where('key',$key)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Client's LanguageSettings
    function clientLanguageSettings($shopID)
    {
        // Keys
        $keys = ([
            'primary_language',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = LanguageSettings::select('value')->where('key',$key)->where('shop_id',$shopID)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Package Permissions
    function getPackagePermission($subID)
    {
        $details = Subscriptions::where('id',$subID)->first();
        $permission = (isset($details['permissions']) && !empty($details['permissions'])) ? unserialize($details['permissions']) : '';
        return $permission;
    }


    // Get Subscription ID
    function getClientSubscriptionID($shop_id)
    {
        $user_shop = UserShop::where('shop_id',$shop_id)->first();
        $user_id = (isset($user_shop['user_id'])) ? $user_shop['user_id'] : '';
        $user_subscription = UsersSubscriptions::where('user_id',$user_id)->first();
        $subscription_id = (isset($user_subscription['subscription_id'])) ? $user_subscription['subscription_id'] : '';
        return $subscription_id;
    }


    // Get Theme Settings
    function themeSettings($themeID)
    {
        // Keys
        $keys = ([
            'header_color',
            'sticky_header',
            'language_bar_position',
            'logo_position',
            'search_box_position',
            'banner_position',
            'banner_type',
            'background_color',
            'font_color',
            'label_color',
            'social_media_icon_color',
            'categories_bar_color',
            'menu_bar_font_color',
            'category_title_and_description_color',
            'price_color',
            'item_box_shadow',
            'item_box_shadow_color',
            'item_box_shadow_thickness',
            'item_divider',
            'item_divider_color',
            'item_divider_thickness',
            'item_divider_type',
            'item_divider_position',
            'item_divider_font_color',
            'tag_font_color',
            'tag_label_color',
            'category_bar_type',
            'today_special_icon',
            'theme_preview_image',
            'search_box_icon_color',
        ]);

        $settings = [];

        foreach($keys as $key)
        {
            $query = ThemeSettings::select('value')->where('key',$key)->where('theme_id',$themeID)->first();
            $settings[$key] = isset($query->value) ? $query->value : '';
        }

        return $settings;
    }


    // Get Language Details
    function getLangDetails($langID)
    {
        $language = Languages::where('id',$langID)->first();
        return $language;
    }


    // Get Language Details by Code
    function getLangDetailsbyCode($langCode)
    {
        $language = Languages::where('code',$langCode)->first();
        return $language;
    }


    // Get Tags Product
    function getTagsProducts($tagID,$catID)
    {
        if(!empty($tagID) && !empty($catID))
        {
            // $items = CategoryProductTags::with(['product'])->where('tag_id',$tagID)->where('category_id',$catID)->get();
            $items = CategoryProductTags::join('items','items.id','category_product_tags.item_id')->where('tag_id',$tagID)->where('category_product_tags.category_id',$catID)->orderBy('items.order_key')->get();
        }
        else
        {
            $items = [];
        }
        return $items;
    }


    // Get Ingredients Details
    function getIngredientDetail($id)
    {
        $ingredient = Ingredient::where('id',$id)->first();
        return $ingredient;
    }


    // Get Banner Settings
    function getBanners($shopID)
    {
        $banners = ShopBanner::where('shop_id',$shopID)->where('key','shop_banner')->get();
        return $banners;
    }


    // Get Favourite Clients List
    function FavClients($limit)
    {
        $clients = User::with(['hasOneShop','hasOneSubscription'])->where('user_type',2)->where('is_fav',1)->limit($limit)->get();
        return $clients;
    }


    // Function for Hex to RGB
    function hexToRgb($hex)
    {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

        return $rgb;
    }


    // Function for Get Item Price
    function getItemPrice($itemID)
    {
        $prices = ItemPrice::where('item_id',$itemID)->get();
        return $prices;
    }


    // Function for Genrate random Token
    function genratetoken($length = 32)
    {
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $max = strlen($string) - 1;
        $token = '';

        for ($i = 0; $i < $length; $i++)
        {
            $token .= $string[mt_rand(0, $max)];
        }

        return $token;
    }


    // Check Schedule
    function checkCategorySchedule($catID,$shop_id)
    {
        $current_date = Carbon::now();
        $today = strtolower($current_date->format('l'));
        $current_time = strtotime($current_date->format('G:i'));
        $cat_details = Category::where('id',$catID)->where('shop_id',$shop_id)->first();
        $schedule = $cat_details['schedule'];
        if($schedule == 0)
        {
            return 1;
        }
        else
        {
            $schedule_type = (isset($cat_details['schedule_type']) && !empty($cat_details['schedule_type'])) ? $cat_details['schedule_type'] : 'time';

            if($schedule_type == 'time')
            {
                $schedule_arr = (isset($cat_details['schedule_value']) && !empty($cat_details['schedule_value'])) ? json_decode($cat_details['schedule_value'],true) : '';
                if(count($schedule_arr) > 0)
                {
                    $current_day = (isset($schedule_arr[$today])) ? $schedule_arr[$today] : '';
                    if(isset($current_day['enabled']) && $current_day['enabled'] == 1)
                    {
                        $time_schedule_arr = isset($current_day['timesSchedules']) ? $current_day['timesSchedules'] : [];

                        if(count($time_schedule_arr) > 0)
                        {
                            $count = 1;
                            $total_count = count($time_schedule_arr);
                            foreach($time_schedule_arr as $tsarr)
                            {
                                $start_time = strtotime($tsarr['startTime']);
                                $end_time = strtotime($tsarr['endTime']);

                                if($current_time > $start_time && $current_time < $end_time)
                                {
                                    return 1;
                                }
                                else
                                {
                                    if($count == $total_count)
                                    {
                                        return 0;
                                    }
                                }
                                $count ++;
                            }
                        }
                        else
                        {
                            return 0;
                        }
                    }
                    else
                    {
                        return 0;
                    }
                }
                else
                {
                    return 0;
                }
            }
            else
            {
                $start_date =  strtotime($cat_details['sch_start_date']);
                $end_date =  strtotime($cat_details['sch_end_date']);

                if(empty($start_date) || empty($end_date))
                {
                    return 1;
                }
                else
                {
                    $curr_date = strtotime($current_date);

                    if($curr_date > $start_date && $curr_date < $end_date)
                    {
                        return 1;
                    }
                    else
                    {
                        return 0;
                    }

                }

            }
        }
    }


    // Get total Quantity of Cart
    function getCartQuantity()
    {
        $cart = session()->get('cart', []);
        $total_quantity = 0;
        if(count($cart) > 0)
        {
            foreach($cart as $val)
            {
                $total_quantity += (isset($val['quantity'])) ? $val['quantity'] : 0;
            }
        }
        return $total_quantity;
    }


    // Get Item Details
    function itemDetails($itemID)
    {
        $item_details = Items::with(['category'])->where('id',$itemID)->first();
        return $item_details;
    }


    // Function for get client PayPal Config
    function getPayPalConfig($shop_slug)
    {
        $shop = Shop::where('shop_slug',$shop_slug)->first();
        $shop_id = isset($shop['id']) ? $shop['id'] : '';

        // Get Payment Settings
        $payment_settings = getPaymentSettings($shop_id);

        $paypal_config = [
            'client_id' => (isset($payment_settings['paypal_public_key'])) ? $payment_settings['paypal_public_key'] : '',
            'secret' => (isset($payment_settings['paypal_private_key'])) ? $payment_settings['paypal_private_key'] : '',
            'settings' => [
                'mode' => (isset($payment_settings['paypal_mode'])) ? $payment_settings['paypal_mode'] : '',
                'http.ConnectionTimeOut' => 30,
                'log.LogEnabled' => 1,
                'log.FileName' => storage_path() . '/logs/paypal.log',
                'log.LogLevel' => 'ERROR',
            ]
        ];
        return $paypal_config;
    }


    // Function for get client EveryPay Config
    function getEveryPayConfig($shop_slug)
    {
        $shop = Shop::where('shop_slug',$shop_slug)->first();
        $shop_id = isset($shop['id']) ? $shop['id'] : '';

        // Get Payment Settings
        $payment_settings = getPaymentSettings($shop_id);

        $every_pay_config = [
            'public_key' => (isset($payment_settings['every_pay_public_key'])) ? $payment_settings['every_pay_public_key'] : '',
            'secret_key' => (isset($payment_settings['every_pay_private_key'])) ? $payment_settings['every_pay_private_key'] : '',
            'mode' => (isset($payment_settings['everypay_mode'])) ? $payment_settings['everypay_mode'] : 1,
        ];
        return $every_pay_config;
    }

?>