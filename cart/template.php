<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("ui.fonts.ruble");

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @var string $templateName
 * @var CMain $APPLICATION
 * @var CBitrixBasketComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $giftParameters
 */

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

foreach ($arResult['ITEMS']['AnDelCanBuy'] as $key => $item) {
    $arResult['ITEMS']['AnDelCanBuy'][$key]['AVAILABLE'] = $item['AVAILABLE_QUANTITY'] <= 0 ? 'Под заказ' : 'В наличии';
}

if (check_bitrix_sessid() && $request->isPost()) {
    echo json_encode($arResult);
    return;
}

$obName = md5(rand(0, rand(0, 50)));

$componentId = 'basket-root_' . $obName;

$item = $arResult['ITEMS']['AnDelCanBuy'][0];

?>

<div class="container cart">
    <div class="heading--center text-animation animated cart-headicvvcvcng">
        корзина
    </div>

    <div id="<?= $componentId ?>" class="basket">

        <div class="send-order" data-role="modal">
            <div class="send-order-form" data-role="modal-content">
                <div class="modal-close">
                    <span></span>
                </div>
                <div class="send-order-form__logo">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/img/logo.png" alt="">
                </div>
                <div class="send-order-form-title">Связаться по заказу</div>
                <label for="" class="send-order-form__field">
                    <span>Куда вам позвонить?</span>
                    <input type="text" data-role="phone">
                </label>

                <button class="send-order-btn" id="send-order">Отправить данные</button>

                <div class="get-discount" id="get-discount">получить дополнительную скидку</div>
            </div>
        </div>

        <div class="basket-preloader">
            <span>
                Загрузка...
            </span>
        </div>

        <div class="basket-success-modal" data-role="modal">
            <div class="basket-success-modal-form" data-role="modal-content">
                <div class="modal-close">
                    <span></span>
                </div>

                <div class="basket-success-modal-content">
                    <div class="basket-success-modal-content__left">
                        <div class="basket-success-modal-content-title">
                            Спасибо!
                        </div>

                        <div class="basket-success-modal-content-text">
                            Ваши контактные данные
                            успешно отправлены
                        </div>

                        <a href="/" class="basket-success-modal-content-link">
                            на главную
                        </a>
                    </div>

                    <div class="basket-success-modal-content__right">
                        <img src="<?= $templateFolder ?>/img/success.jpg" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="basket-error-modal" data-role="modal">
            <div class="basket-error-modal__text" data-role="modal-content"></div>
        </div>

        <div class="basket-items-list" id="basket-item-table">
        </div>
        <div class="total-node-wrap">
            <div class="total-node">
                <div class="total-node-content">
                    <div class="total-node__counts">
                        <span class="total-node__counts-title">Количество товаров:</span>
                        <span class="total-node__counts-value">0</span>
                    </div>
                    <div class="total-node__sum">
                        <span class="total-node__sum-title">Итого:</span>
                        <span class="total-node__sum-value">0 ₽</span>
                    </div>
                    <div class="total-node__contact-me">перейти к оплате</div>
                </div>

                <div class="total-node__accept">
                    Нажимая на кнопку Связаться со мной, вы соглашаетесь на обработку <a href="#">персональных
                        данных</a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php

$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'sale.basket.basket');
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.basket.basket');
$messages = Loc::loadLanguageFile(__FILE__);
?>

<script>
    $(document).ready(() => {
        let MainCart_<?= $obName ?> = new MainCart({
            result: <?=CUtil::PhpToJSObject($arResult, false, false, true)?>,
            params: <?=CUtil::PhpToJSObject($arParams)?>,
            template: '<?=CUtil::JSEscape($signedTemplate)?>',
            signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
            siteId: '<?=CUtil::JSEscape($component->getSiteId())?>',
            siteTemplateId: '<?=CUtil::JSEscape($component->getSiteTemplateId())?>',
            templateFolder: '<?=CUtil::JSEscape($templateFolder)?>',
            componentId: '<?= $componentId ?>'
        });
    });
</script>