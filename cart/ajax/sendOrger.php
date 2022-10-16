<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Context,
    Bitrix\Currency\CurrencyManager,
    Bitrix\Sale\Order,
    Bitrix\Sale\Basket,
    Bitrix\Sale\Delivery,
    Bitrix\Sale\Fuser;


global $USER;

$orderData = ['ORDER_LIST' => ''];

Bitrix\Main\Loader::includeModule("sale");
Bitrix\Main\Loader::includeModule("catalog");

// Допустим некоторые поля приходит в запросе
$request = Context::getCurrent()->getRequest();
$phone = $request["phone"];
$getDiscount = $request["getDiscount"];

$arSubscribes = CIBlockElement::GetList([], ['PROPERTY_PHONE' => $phone], false, false, ['ID']);

$userSubscribe = $arSubscribes->Fetch();

if (!$userSubscribe['ID']) {

    if ($getDiscount) {
        echo json_encode(['success' => false, 'needSubscribe' => true]);
        die();
    }
} else {
    $orderData['GET_DISCOUNT'] = "http://{$_SERVER['HTTP_HOST']}/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=6&type=subscribes&ID={$userSubscribe['ID']}";
}

if (empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Email не может быть пустым']);
    die();
}

$siteId = Context::getCurrent()->getSite();
$currencyCode = CurrencyManager::getBaseCurrency();

// Создаёт новый заказ
$order = Order::create($siteId, 1);
$order->setPersonTypeId(1);
$order->setField('CURRENCY', $currencyCode);

// Создаём корзину с одним товаром
$basket = Basket::loadItemsForFUser(Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());

$order->setBasket($basket);

foreach ($basket->getBasketItems() as $item) {
    $fields = $item->getFields();

    $orderData['ORDER_LIST'] .= $fields['NAME'] . ' № ' . $fields['PRODUCT_ID'] . " - " . $fields['PRICE'] . "₽ <br/>";
}

$orderData['ORDER_PRICE'] = $order->getBasePrice();

// Создаём одну отгрузку и устанавливаем способ доставки - "Без доставки" (он служебный)
$shipmentCollection = $order->getShipmentCollection();
$shipment = $shipmentCollection->createItem();
$service = Delivery\Services\Manager::getById(Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());
$shipment->setFields(array(
    'DELIVERY_ID' => $service['ID'],
    'DELIVERY_NAME' => $service['NAME'],
));
//$shipmentItemCollection = $shipment->getShipmentItemCollection();
//$shipmentItem = $shipmentItemCollection->createItem($item);
//$shipmentItem->setQuantity($item->getQuantity());

// Создаём оплату со способом #1
$paymentCollection = $order->getPaymentCollection();
$payment = $paymentCollection->createItem();
$paySystemService = Bitrix\Sale\PaySystem\Manager::getObjectById(1);
$payment->setFields(array(
    'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
    'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
));

// Устанавливаем свойства
$propertyCollection = $order->getPropertyCollection();
$phoneProp = $propertyCollection->getPhone();
$phoneProp->setValue($phone);

// Сохраняем
$order->doFinalAction(true);
$result = $order->save();
$orderId = $order->getId();

$orderData['ORDER_ID'] = $orderId;
$orderData['USER_PHONE'] = $phone;

\CEvent::Send('NEW_ORDER', 's1', $orderData);

echo json_encode(['success' => $result->isSuccess(), 'orderId' => $orderId]);