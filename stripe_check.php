<?php
ini_set('display_errors', 1);
require '_/global.php';
require 'stripe/vendor/autoload.php';

\Stripe\Stripe::setApiKey("...");
try {
    $session = \Stripe\Checkout\Session::retrieve($_GET['i']);
    $order_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_orders WHERE virtuemart_order_id='".$session->metadata->order_id."'");
    $order = mysqli_fetch_assoc($order_q);
    if($session->payment_status == "paid") {
        $up = mysqli_query($con, "UPDATE h207m_virtuemart_orders SET order_status='C',paid='".$session->amount_total."',paid_on='".date("Y-m-d H:i:s")."' WHERE virtuemart_order_id='".$order['virtuemart_order_id']."'");
        $ins_f = "INSERT INTO h207m_virtuemart_order_histories SET ";
        $ins_f .= "`virtuemart_order_id`='".$order['virtuemart_order_id']."',";
        $ins_f .= "`order_status_code`='C',";
        $ins_f .= "`customer_notified`='1',";
        $ins_f .= "`comments`='By Stripe',";
        $ins_f .= "`paid`='".$session->amount_total."',";
        $ins_f .= "`created_on`='".date("Y-m-d H:i:s")."'";
        $ins = mysqli_query($con, $ins_f);
        $user_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_order_userinfos WHERE virtuemart_order_id='".$order['virtuemart_order_id']."'");
        $user = mysqli_fetch_assoc($user_q);
        $shipment_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_shipmentmethods_hu_hu WHERE virtuemart_shipmentmethod_id='".$order['virtuemart_shipmentmethod_id']."'");
        $shipment = mysqli_fetch_assoc($shipment_q);
        $payment_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_paymentmethods_hu_hu WHERE virtuemart_paymentmethod_id='".$order['virtuemart_paymentmethod_id']."'");
        $payment = mysqli_fetch_assoc($payment_q);
        $orderlink = 'https://vroomvroomtuning.hu/index.php?option=com_virtuemart&view=orders';
        $orderlink .= '&layout=details&order_number='.$order['order_number'].'&order_pass='.$order['order_pass'];
        include('phpmailer/class.phpmailer.php');
        include('phpmailer/class.smtp.php');
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0;
        $mail->Port = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = 'vroomvroomtuning.hu';
        $mail->SMTPAuth = true;
        $mail->Username = 'nevalaszolj@vroomvroomtuning.hu';
        $mail->Password = 'VlTr$cZcjSEH';
        $mail->setFrom('nevalaszolj@vroomvroomtuning.hu', 'VroomVroomTuning Webshop');
        $mail->addReplyTo("info@vroomvroomtuning.hu", "VroomVroomTuning Webshop");
        $mail->AltBody = '...';
        $mail->addAddress($user['email'], $user['first_name'].' '.$user['last_name']);
        $mail->Subject = 'Rendelési állapot változás';
        $text = '<!DOCTYPE html>
                <html lang="hu">
                    <head>
                        <meta charset="UTF-8">
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>VroomVroomTuning</title>
                        <style>
                            .information-table,.order-details .item,.process-bar{width:100%;overflow:hidden}body{font-family:Arial,sans-serif;background-color:#f4f4f4;margin:0;padding:0;font-size:14px}.email-container{max-width:600px;margin:0 auto;background-color:#fff;padding:10px 10px 20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.1)}.content,h1{padding:0 10px}.header{text-align:center}.header img{max-width:100%}.content{text-align:left;color:#333}.footer,h3,p{text-align:center}.content p{font-size:16px;line-height:1.6;margin-bottom:20px}.order-details{margin-top:10px}.order-details .item>div{padding:10px;border-bottom:1px solid #ddd;text-align:left;float:left}.order-details .item *{vertical-align:middle}.order-details .item .item-name{padding-left:10px;width:calc(100% - 64px - 110px);max-width:calc(100% - 64px - 110px);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.order-details .item .item-qty{width:64px;text-align:right}.order-details .item .item-fullprice{width:110px;text-align:right}.footer{padding:20px;font-size:14px;color:#777}.footer a{color:#f3740d;text-decoration:none}.footer a:hover{text-decoration:underline}@media screen and (max-width:600px){.email-container{width:100%;padding:10px}}h3,p{display:block}.process-bar{border:2px solid #eee;border-radius:4px;padding:20px}.process-bar .process{width:25%;float:left;font-size:14px;text-align:center;position:relative}.process-bar .process .circle{width:26px;height:26px;border-radius:50%;margin:0 auto 10px;border:2px solid #f3740d;background:#fff;z-index:2}.process-bar .process .circle.active{background:#f3740d}.process-bar .process b{display:block;margin-bottom:5px}.process-bar .process .line{width:calc(100% - 5px);z-index:0;height:0;border:1px dashed #f3740d;position:absolute;top:13px;left:calc(50% + 13px)}.information-table{border:1px solid #eee;padding:10px}.information-table .info{width:50%;float:left;font-size:14px;line-height:1.4}.information-table .info:first-child{border-right:1px solid #eee}*{box-sizing:border-box;position:relative}
                        </style>
                    </head>
                    <body>
                        <div class="email-container">
                            <div class="header">
                                <img src="https://vroomvroomtuning.hu/images/feliratget.jpg" />
                            </div>
                            <div class="content">
                                <h3>'.$user['last_name'].', a rendelésed összege kifizetésre került!</h3>
                                <p>Hamarosan elkezdjük összeállítani rendelésed, amely után egy külön e-mailben tájékoztatunk a további lépésekről!</p>
                                <div class="process-bar">
                                    <div class="process">
                                        <div class="circle active"></div>
                                        <div class="process-text">
                                            <b>Megrendelés</b>
                                        </div>
                                    </div>
                                    <div class="process">
                                        <div class="circle active"></div>
                                        <div class="process-text">
                                            <b>Fizetés</b>
                                        </div>
                                    </div>
                                    <div class="process">
                                        <div class="circle"></div>
                                        <div class="process-text">
                                            <b>Futár átadás</b>
                                        </div>
                                    </div>
                                    <div class="process">
                                        <div class="circle"></div>
                                        <div class="process-text">
                                            <b>Átadva</b>
                                        </div>
                                    </div>
                                </div>
                                <p style="text-align: left;">
                                    <b>Szállítási mód:</b> '.$shipment['shipment_name'].'<br />
                                    <b>Fizetési mód:</b> '.$payment['payment_name'].'<br />
                                </p>
                                <p>
                                    Rendelés száma: <a target="_blank" href="'.$orderlink.'">#'.$order['order_number'].'</a>
                                </p>
                                <div class="information-table">
                                    <div class="info">
                                        <b>Szállítási cím</b><br />
                                        '.$user['first_name'].' '.$user['last_name'].'<br />
                                        '.$user['zip'].', '.$user['city'].'<br />
                                        '.$user['address_1'].'<br />
                                        '.$user['address_2'].'
                                    </div>
                                </div><br />
                                <b style="font-size: 16px;">Megrendelt termékek</b>
                                <div class="order-details">';
                            $items_q = mysqli_query($con, "SELECT * FROM h207m_virtuemart_order_items WHERE virtuemart_order_id='".$order['virtuemart_order_id']."'");
                            $n=0;
                            $all_price = 0;
                            while($items = mysqli_fetch_assoc($items_q)) {
                                $all_price = $all_price + intval($items['product_subtotal_with_tax']);
                                $href = 'https://vroomvroomtuning.hu/index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$items['virtuemart_product_id'];
                                $text .= '<div class="item">
                                        <div class="item-name">
                                            <a href="'.$href.'" target="_blank">
                                                '.$items['order_item_name'].'
                                            </a>
                                        </div>
                                        <div class="item-qty">'.$items['product_quantity'].' db</div>
                                        <div class="item-fullprice">'.number_format($items['product_subtotal_with_tax'], 0, '', ' ').' Ft</div>
                                    </div>';
                                $n++;
                                $item_mpn = mysqli_query($con, "SELECT product_mpn FROM h207m_virtuemart_products WHERE virtuemart_product_id='".$items['virtuemart_product_id']."'");
                                $dropshipping[mysqli_fetch_assoc($item_mpn)['product_mpn']][] = $items['virtuemart_order_item_id'];
                            }
                            if(intval($order['order_shipment']) != 0) {
                                $text .= '<div class="item">
                                        <div class="item-name">
                                            Szállítási költség
                                        </div>
                                        <div class="item-qty">&nbsp;</div>
                                        <div class="item-fullprice">'.number_format($order['order_shipment'], 0, "", " ").' Ft</div>
                                    </div>';
                                $all_price = $all_price + intval($order['order_shipment']);
                                
                            }
                            if(intval($order['order_payment']) != 0) {
                                $text .= '<div class="item">
                                        <div class="item-name">
                                            Fizetési mód költsége
                                        </div>
                                        <div class="item-qty">&nbsp;</div>
                                        <div class="item-fullprice">'.number_format($order['order_payment'], 0, "", " ").' Ft</div>
                                    </div>';
                                $all_price = $all_price + intval($order['order_payment']);
                            }
                                $text .= '<div class="item">
                                        <div class="item-name" style="text-align: right;">
                                            <b>Összesen:</b>
                                        </div>
                                        <div class="item-qty">&nbsp;</div>
                                        <div class="item-fullprice">'.number_format($all_price, 0, "", " ").' Ft</div>
                                    </div>
                                </div>
                            </div>
                            <div class="footer">
                                <p>Köszönjük, hogy nálunk vásároltál!</p>
                                <p><a href="https://vroomvroomtuning.hu">Látogass vissza a webshopunkba</a></p>
                            </div>
                        </div>
                    </body>
                </html>';
        $mail->msgHTML($text, dirname(__FILE__));
        $mail->send();
        foreach($dropshipping as $k => $v) {
            foreach($v as $k2 => $v2) {
                $ins = mysqli_query($con, "INSERT INTO h207m_dropshipping_route (virtuemart_order_id, virtuemart_order_item_id, provider, create_date) VALUES ('".$order['virtuemart_order_id']."','".$v2."','".$k."','".date("Y-m-d H:i:s")."')");
            }
        }
        header("Location: /payment-success");
    } else {
        header("Location: /payment-fail");
    }
} catch (Exception $e) {
    // Hiba esetén loggolás
    echo 'Hiba történt: ' . $e->getMessage();
} 

