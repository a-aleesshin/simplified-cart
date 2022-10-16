<?php require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

Bitrix\Main\Loader::includeModule("sale");
Bitrix\Main\Loader::includeModule("catalog");

use Bitrix\Main\Loader;
use Bitrix\Sale;

$id = $_POST['id'];

//проверка является ли это корзиной текущего пользователя

$ar = Sale\Internals\BasketTable::getList([
    'filter' => [
        "FUSER_ID" => CSaleBasket::GetBasketUserID(),
        "PRODUCT_ID" => $id
    ],
    'select' => [
        "ID"
    ],
    'limit' => 1
]) -> fetch();

if($id = $ar['ID']) echo json_encode(['success' => CSaleBasket::Delete($id)]);
else echo json_encode(['success' => false]);

