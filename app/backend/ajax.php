<?php
ini_set('max_execution_time', '3600');
ini_set('display_errors', 1);
header('Access-Control-Allow-Origin: *');
include("../../_/global.php");
if(isset($_POST['do'])) {
	$do = $_POST['do'];
	foreach($_POST as $k => $v) {
		$post[$k] = $v;
	}
	if(isset($post['token'])) $token = $post['token'];
    if($do == "get.brand") {
        $check = mysqli_query($con, "SELECT hu.virtuemart_category_id,hu.category_name FROM h207m_virtuemart_categories cat LEFT JOIN h207m_virtuemart_categories_hu_hu hu ON cat.virtuemart_category_id=hu.virtuemart_category_id WHERE cat.category_parent_id=1");
        while($f = mysqli_fetch_assoc($check)) {
            $ret[] = $f;
        }
    } else if($do == "get.brand-type") {
        $check = mysqli_query($con, "SELECT hu.virtuemart_category_id,hu.category_name FROM h207m_virtuemart_categories cat LEFT JOIN h207m_virtuemart_categories_hu_hu hu ON cat.virtuemart_category_id=hu.virtuemart_category_id WHERE cat.category_parent_id=".$post['brand']);
        while($f = mysqli_fetch_assoc($check)) {
            $exist_prods_q = mysqli_query($con, "SELECT virtuemart_product_id FROM h207m_virtuemart_product_categories WHERE virtuemart_category_id='".$f['virtuemart_category_id']."'");
            if(mysqli_num_rows($exist_prods_q) < 1) continue;
            $f['category_name'] = preg_replace('/(.*?)\s\((.*?)\)/', "$1", $f['category_name']);
            $ret[] = $f;
        }
    } else if($do == "get.brand-subtype") {
        $check = mysqli_query($con, "SELECT * FROM h207m_virtuemart_categories_hu_hu WHERE category_name LIKE '".$post['categName']." (%)%'");
        if(mysqli_num_rows($check) < 1) {
            $select = "SELECT * FROM h207m_virtuemart_product_categories WHERE virtuemart_category_id='".$post['categId']."'";
            $check = mysqli_query($con, $select);
            while($f = mysqli_fetch_assoc($check)) {
                $prod_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_products_hu_hu WHERE virtuemart_product_id='".$f['virtuemart_product_id']."'");
                $prod = mysqli_fetch_assoc($prod_q);
                $price_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_product_prices WHERE virtuemart_product_id='".$f['virtuemart_product_id']."'");
                $price = mysqli_fetch_assoc($price_q);
                $prod['price'] = $price['product_price'];
                $prod['price'] = setPrice($prod['price'], $prod['virtuemart_product_id']);
                $prod_media_q = mysqli_query($con, "SELECT virtuemart_media_id FROM h207m_virtuemart_product_medias WHERE virtuemart_product_id='".$f['virtuemart_product_id']."'");
                $prod_media = mysqli_fetch_assoc($prod_media_q);
                $media_q = mysqli_query($con, "SELECT file_url FROM h207m_virtuemart_medias WHERE virtuemart_media_id='".$prod_media['virtuemart_media_id']."'");
                $prod['img'] = mysqli_fetch_assoc($media_q)['file_url'];
                $prices[$prod['price']] = $prod;
            }
            ksort($prices);
            if(count($prices) == 2) $median = 1;
            else if(count($prices) == 1) $median = 1;
            else $median = (count($prices) / 2) - 1;
            $n = 1;
            foreach($prices as $k => $v) {
                if($n == round($median)) {
                    $v['product_desc'] = str_replace(array("\\r", "\\n"), "", $v['product_desc']);
                    $ret = $v;
                    break;
                }
                $n++;
            }
            $ret['msg'] = 'hasnt_subtype';
        } else {
            while($f = mysqli_fetch_assoc($check)) {
                $exist_prods_q = mysqli_query($con, "SELECT virtuemart_product_id FROM h207m_virtuemart_product_categories WHERE virtuemart_category_id='".$f['virtuemart_category_id']."'");
                if(mysqli_num_rows($exist_prods_q) < 1) continue;    
                $ret['subtypes'][] = $f;
            }
            $ret['msg'] = 'has_subtype';
        }
    } else if($do == "get.product") {
        $select = "SELECT hu_hu.* 
                    FROM h207m_virtuemart_products_hu_hu AS hu_hu
                    JOIN h207m_virtuemart_products AS products ON hu_hu.virtuemart_product_id = products.virtuemart_product_id
                    JOIN h207m_virtuemart_product_categories AS categories ON products.virtuemart_product_id = categories.virtuemart_product_id
                    WHERE categories.virtuemart_category_id = ".$post['category'];
        $check = mysqli_query($con, $select);
        if(mysqli_num_rows($check) < 1) {
            $ret['msg'] = 'hasnt_product';
        } else {
            while($f = mysqli_fetch_assoc($check)) {
                $price_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_product_prices WHERE virtuemart_product_id='".$f['virtuemart_product_id']."'");
                $price = mysqli_fetch_assoc($price_q);
                $f['price'] = $price['product_price'];
                $f['price'] = setPrice($f['price'], $f['virtuemart_product_id']);
                $prices[$f['price']] = $f;
            }
            ksort($prices);
            if(count($prices) == 2) $median = 1;
            else if(count($prices) == 1) $median = 1;
            else $median = (count($prices) / 2) - 1;
            $n = 1;
            foreach($prices as $k => $v) {
                if($n == round($median)) {
                    $v['product_desc'] = str_replace(array("\\r", "\\n"), "", $v['product_desc']);
                    $ret = $v;
                    break;
                }
                $n++;
            }
        }
    } else if($do == "get.items") {
        $items = json_decode($post['items'], true);
        $items = implode(",", array_keys($items));
        $check = mysqli_query($con, "SELECT hu_hu.virtuemart_product_id,hu_hu.product_name 
                    FROM h207m_virtuemart_products_hu_hu AS hu_hu
                    JOIN h207m_virtuemart_products AS products ON hu_hu.virtuemart_product_id=products.virtuemart_product_id  WHERE products.virtuemart_product_id IN (".$items.")");
        if(mysqli_num_rows($check) > 0) {
            while($f = mysqli_fetch_assoc($check)) {
                $price_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_product_prices WHERE virtuemart_product_id='".$f['virtuemart_product_id']."'");
                $price = mysqli_fetch_assoc($price_q);
                $prod_media_q = mysqli_query($con, "SELECT virtuemart_media_id FROM h207m_virtuemart_product_medias WHERE virtuemart_product_id='".$f['virtuemart_product_id']."'");
                $prod_media = mysqli_fetch_assoc($prod_media_q);
                $media_q = mysqli_query($con, "SELECT file_url FROM h207m_virtuemart_medias WHERE virtuemart_media_id='".$prod_media['virtuemart_media_id']."'");
                $f['img'] = mysqli_fetch_assoc($media_q)['file_url'];
                $f['price'] = intval($price['product_price']);
                $f['price'] = setPrice($f['price'], $f['virtuemart_product_id']);
                $ret[] = $f; 
            }
        }
    } else if($do == "get.shipment-cost") {
        $check = mysqli_query($con, "SELECT virtuemart_shipmentmethod_id, shipment_params FROM h207m_virtuemart_shipmentmethods WHERE virtuemart_shipmentmethod_id IN (2,3,6)");
        if(mysqli_num_rows($check) > 0) {
            while($f = mysqli_fetch_assoc($check)) {
                $f['shipment_params'] = str_replace('\"', '"', $f['shipment_params']);
                $_ex = explode('|' ,$f['shipment_params']);
                foreach($_ex as $k => $v) {
                    if(!isset(explode("=", $v)[1])) continue;
                    $ex[explode("=", $v)[0]] = trim(explode("=", $v)[1], '"');
                }
                $f['cost'] = intval($ex['shipment_cost']);
                unset($f['shipment_params']);
                $ret[] = $f;
            }
        } else {
            $ret['msg'] = "not match";
        }
    } else if($do == "get.payment-cost") {
        $check = mysqli_query($con, "SELECT virtuemart_paymentmethod_id, payment_params FROM h207m_virtuemart_paymentmethods WHERE virtuemart_paymentmethod_id IN (4,6)");
        if(mysqli_num_rows($check) > 0) {
            while($f = mysqli_fetch_assoc($check)) {
                $f['payment_params'] = str_replace('\"', '"', $f['payment_params']);
                $_ex = explode('|' ,$f['payment_params']);
                foreach($_ex as $k => $v) {
                    if(!isset(explode("=", $v)[1])) continue;
                    $ex[explode("=", $v)[0]] = trim(explode("=", $v)[1], '"');
                }
                $f['cost'] = intval($ex['cost_per_transaction']);
                unset($f['payment_params']);
                $ret[] = $f;
            }
        } else {
            $ret['msg'] = "not match";
        }
    } else if($do == "send.order") {
        $email = $post['shippingData']['email'] ?? '';
        if(filter_var($email, FILTER_VALIDATE_EMAIL) && checkdnsrr(substr($email, strrpos($email, '@') + 1), 'MX')) {
            $orderNumber = 'MA';
            $orderNumber .= keyGenerate();
            $customerNumber = 'nonreg_';
            $customerNumber = mb_substr($post['invoiceData']['lastname'], 0, 3);
            $customerNumber = mb_substr($post['invoiceData']['surname'], 0, 2);
            $customerNumber = date("Ymd_H:i:s_")."MA";
            $totalshits = 0;
            $products = array();
            $getitemsid = mysqli_query($con, "SELECT * FROM h207m_virtuemart_product_prices WHERE virtuemart_product_id IN ('".implode(",",array_keys($post['cart']))."')");
            while($getitemsprice = mysqli_fetch_assoc($getitemsid)) {
                $totalshits = $totalshits + ($getitemsprice['product_price'] * $post['cart'][$getitemsprice['virtuemart_product_id']]);
                $products[] = $getitemsprice;
            }
            $order = "INSERT INTO h207m_virtuemart_orders SET ";
            $order .= "`order_number`='".$orderNumber."',";
            $order .= "`customer_number`='".$customerNumber."',";
            $order .= "`order_pass`='p_".keyGenerate(8)."',";
            $order .= "`order_total`='".$totalshits."',";
            $order .= "`order_salesPrice`='".$totalshits."',";
            $order .= "`order_subtotal`='".$totalshits."',";
            $order .= "`order_currency`='64',";
            $order .= "`user_currency_rate`='1',";
            $order .= "`payment_currency_id`='64',";
            $order .= "`virtuemart_paymentmethod_id`='".$post['shippingData']['paymethodid']."',";
            $order .= "`virtuemart_shipmentmethod_id`='".$post['shippingData']['shipmethodid']."',";
            $order .= "`delivery_date`='Ugyanaz, mint a számlázási dátum',";
            $order .= "`ip_address`='".$_SERVER["REMOTE_ADDR"]."',";
            $order .= "`paid`='0',";
            $order .= "`created_on`='".date("Y-m-d H:i:s")."',";
            $order .= "`modified_on`='".date("Y-m-d H:i:s")."'";
            $order_q  = mysqli_query($con, $order);
            $order_id = mysqli_insert_id($con);
            
            $histories = "INSERT INTO h207m_virtuemart_order_histories SET ";
            $histories .="`virtuemart_order_id`='".$order_id."',";
            $histories .="`order_status_code`='U',";
            $histories .="`paid`='0',";
            $histories .="`created_on`='".date("Y-m-d H:i:s")."',";
            $histories .="`modified_on`='".date("Y-m-d H:i:s")."'";
            $histories_q = mysqli_query($con, $histories);
            $n = 0;
            foreach($post['cart'] as $k => $v) {
                $product_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_products WHERE virtuemart_product_id='".$k."'");
                $product = mysqli_fetch_assoc($product_q);
                $hu_hu_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_products_hu_hu WHERE virtuemart_product_id='".$k."'");
                $hu = mysqli_fetch_assoc($hu_hu_q);
                $price_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_product_prices WHERE virtuemart_product_id='".$k."'");
                $price = mysqli_fetch_assoc($price_q);
                $items = "INSERT INTO h207m_virtuemart_order_items SET ";
                $items .= "`virtuemart_order_id`='".$order_id."',";
                $items .= "`virtuemart_product_id`='".$k."',";
                $items .= "`order_item_sku`='".$product['product_sku']."',";
                $items .= "`order_item_name`='".$hu['product_name']."',";
                $items .= "`product_quantity`='".$v."',";
                $items .= "`product_item_price`='".$price['product_price']."',";
                $items .= "`product_priceWithoutTax`='".($price['product_price']*$v)."',";
                $items .= "`product_discountedPriceWithoutTax`='".($price['product_price']*$v)."',";
                $items .= "`product_final_price`='".($price['product_price']*$v)."',";
                $items .= "`product_subtotal_with_tax`='".($price['product_price']*$v)."',";
                $items .= "`order_status`='U',";
                $items .= "`created_on`='".date("Y-m-d H:i:s")."',";
                $items .= "`modified_on`='".date("Y-m-d H:i:s")."'";
                $item_q = mysqli_query($con, $items);
                $stripe_items[$n] = [
                    "price_data" => [
                        'product_data' => [
                            "name" => $hu['product_name']
                        ],
                        "unit_amount" => intval($price['product_price']) * 100,
                        "currency" => "huf"
                    ],
                    "quantity" => $v
                ];
                $n++;
            }
            $invDatas = "INSERT INTO h207m_virtuemart_order_userinfos SET ";
            $invDatas .= "`virtuemart_order_id`='".$order_id."',";
            $invDatas .= "`virtuemart_user_id`=0,";
            $invDatas .= "`address_type`='BT',";
            $invDatas .= "`last_name`='".$post['invoiceData']['lastname']."',";
            $invDatas .= "`first_name`='".$post['invoiceData']['surname']."',";
            $invDatas .= "`phone_1`='".$post['invoiceData']['phone']."',";
            $invDatas .= "`address_1`='".$post['invoiceData']['address']."',";
            $invDatas .= "`city`='".$post['invoiceData']['city']."',";
            $invDatas .= "`zip`='".$post['invoiceData']['zipcode']."',";
            $invDatas .= "`email`='".$post['invoiceData']['email']."',";
            $invDatas .= "`tos`='1',";
            $invDatas .= "`created_on`='".date("Y-m-d H:i:s")."',";
            $invDatas .= "`modified_on`='".date("Y-m-d H:i:s")."',";
            $invDatas .= "`Adszm`='".$post['invoiceData']['taxnum']."'";
            $invDatas_q = mysqli_query($con, $invDatas);
            if(!$invDatas_q) die(mysqli_error($con));
            $shipDatas = "INSERT INTO h207m_virtuemart_order_userinfos SET ";
            $shipDatas .= "`virtuemart_order_id`='".$order_id."',";
            $shipDatas .= "`virtuemart_user_id`=0,";
            $shipDatas .= "`address_type`='ST',";
            $shipDatas .= "`last_name`='".$post['shippingData']['lastname']."',";
            $shipDatas .= "`first_name`='".$post['shippingData']['surname']."',";
            $shipDatas .= "`phone_1`='".$post['shippingData']['phone']."',";
            $shipDatas .= "`address_1`='".$post['shippingData']['address']."',";
            $shipDatas .= "`city`='".$post['shippingData']['city']."',";
            $shipDatas .= "`zip`='".$post['shippingData']['zipcode']."',";
            $shipDatas .= "`email`='".$post['shippingData']['email']."',";
            $shipDatas .= "`tos`='1',";
            $shipDatas .= "`created_on`='".date("Y-m-d H:i:s")."',";
            $shipDatas .= "`modified_on`='".date("Y-m-d H:i:s")."',";
            $shipDatas .= "`Adszm`='".$post['shippingData']['taxnum']."'";
            $shipDatas_q = mysqli_query($con, $shipDatas);
            $ret['msg'] = 'ok';
            if($post['shippingData']['paymethodid'] == 6) {
                include("stripe.php");
            }
        } else {
            $ret['msg'] = "email_error";
        }
    }
}
if(isset($ret)) echo json_encode($ret, JSON_UNESCAPED_UNICODE);

function keyGenerate($length = 6) {
    $orderNumber = '';
    for($i = 1; $i <= 6; $i++) {
        if(rand(0,1) == 0) {
            $orderNumber .= array_rand(range('A','Z'));
        } else {
            $orderNumber .= array_rand(range('0','9'));
        }
    }
    return $orderNumber;
}
function setPrice($def_price,$prod_id) {
    global $con;
    $get_manufacture_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_product_manufacturers WHERE virtuemart_product_id='".$prod_id."'");
    $get_manufacture = mysqli_fetch_assoc($get_manufacture_q);
    $discount = 1;
    switch($get_manufacture['virtuemart_manufacturer_id']) {
        case 1: $discount = 0.92; break;
        case 2: $discount = 0.92; break;
        case 3: $discount = 0.90; break;
        case 4: $discount = 0.92; break;
        case 5: $discount = 0.93; break;
        case 6: $discount = 0.90; break;
        case 7: $discount = 0.92; break;
        case 8: $discount = 0.94; break;
    }
    $price = $def_price * $discount;
    $price = $price * 1.13;
    return $price;
}