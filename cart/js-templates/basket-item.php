<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/**
 * @var array $mobileColumns
 * @var array $arParams
 * @var string $templateFolder
 */
?>
<div class="basket-item">
    <div class="basket-item__detail-td">
        <a href="{{DETAIL_PAGE_URL}}" class="basket-item__detail">
            <div class="basket-item__detail-img">
                <img src="{{PREVIEW_PICTURE_SRC}}" alt="">
            </div>

            <div class="basket-item__detail-desc">
                <div class="basket-item__detail-name">{{NAME}}</div>
                <div class="basket-item__detail-article"><span>Модель: </span>{{PROPERTY_ARTNUMBER_VALUE}}</div>
                if{{PROPERTY_COLOR_VALUE}}:<div class="basket-item__detail-color"><span>Цвет: </span> {{PROPERTY_COLOR_VALUE}}</div>endif{{PROPERTY_COLOR_VALUE}}
                if{{PROPERTY_SIZE_VALUE}}:<div class="basket-item__detail-size"><span>Размер: </span>{{PROPERTY_SIZE_VALUE}}</div>endif{{PROPERTY_SIZE_VALUE}}
                <div class="basket-item__detail-availability availability">{{AVAILABLE}}</div>
                <div class="basket-item-block-properties">

                </div>
            </div>
        </a>

    </div>

    <div class="basket-item__price">
        <span>{{PRICE_FORMATED}}</span>
    </div>

    <div>
        <div class="basket-item__remove" data-id="{{ID}}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M13.3906 17.3601L10.6406 14.6101" stroke="#9C9C9C" stroke-width="1.5"
                      stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M13.3594 14.6399L10.6094 17.3899" stroke="#9C9C9C" stroke-width="1.5"
                      stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.80945 2L5.18945 5.63" stroke="#9C9C9C" stroke-width="1.5"
                      stroke-miterlimit="10"
                      stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15.1895 2L18.8095 5.63" stroke="#9C9C9C" stroke-width="1.5"
                      stroke-miterlimit="10"
                      stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 7.8501C2 6.0001 2.99 5.8501 4.22 5.8501H19.78C21.01 5.8501 22 6.0001 22 7.8501C22 10.0001 21.01 9.8501 19.78 9.8501H4.22C2.99 9.8501 2 10.0001 2 7.8501Z"
                      stroke="#9C9C9C" stroke-width="1.5"/>
                <path d="M3.5 10L4.91 18.64C5.23 20.58 6 22 8.86 22H14.89C18 22 18.46 20.64 18.82 18.76L20.5 10"
                      stroke="#9C9C9C" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </div>
    </div>
</div>