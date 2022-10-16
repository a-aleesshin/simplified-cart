$(function () {
    if (window.MainCart)
        return;

    window.MainCart = function (props) {
        /**
         * $arResult из php
         * @type {object}
         */
        this.result = props.result;
        /**
         * $arParams из php
         * @type {object}
         */
        this.params = props.params;
        /**
         * Название шаблона
         * @type {string}
         */
        this.template = props.template;
        /**
         * Параметры компонента в строке
         * @type {string}
         */
        this.signedParamsString = props.signedParamsString;
        /**
         * ID сайта
         * @type {string}
         */
        this.siteId = props.siteId;
        /**
         * ID шаблона сайта
         * @type {string}
         */
        this.siteTemplateId = props.siteTemplateId;
        /**
         * Путь к шаблону
         * @type {string}
         */
        this.templateFolder = props.templateFolder;
        /**
         * id DOM контейнера компонента
         * @type {string}
         */
        this.componentId = props.componentId;


        this.init();
    }

    window.MainCart.prototype = {
        async init() {

            /**
             * jQuery элемент контейнера компоненте
             * @type {*|jQuery|HTMLElement}
             */
            this.componentContainer = $(`#${this.componentId}`);
            /**
             * jQuery элемент таблицы товаров
             * @type {*|jQuery|HTMLElement}
             */
            this.basketItemsContainer = $('#basket-item-table');
            /**
             * jQuery элемент блока с общей информацией о корзине
             * @type {*|jQuery|HTMLElement}
             */
            this.totalNode = $('.total-node');

            /**
             * jQuery модалка для вывода ошибок
             * @type {*|jQuery|HTMLElement}
             */
            this.errorContainer = this.componentContainer.find('.basket-error-modal');
            /**
             * jQuery модалка успеха
             * @type {*|jQuery|HTMLElement}
             */
            this.successContainer = this.componentContainer.find('.basket-success-modal');
            /**
             * jQuery элемент прелоадера
             * @type {*|jQuery|HTMLElement}
             */
            this.preloader = this.componentContainer.find('.basket-preloader');
            /**
             * Выполняется ли загрузка в данный момент
             * @type {bool}
             */
            this.isLoading = false;

            /**
             * Шаблон вывода товара в корзине
             * @type {bool}
             */
            this.basketItemTemplate = await this.getBasketItemTemplate();

            if (!this.basketItemTemplate) {
                this.showError('Ошибка! Пожалуйста повторите попытку позже.');
                return;
            }

            this.componentContainer.find('[data-role="modal"]').click( function(e) {

                if (!$(this).hasClass('active'))
                    return;

                const modalContent = $(this).find('[data-role="modal-content"]');

                if (!modalContent.is(e.target) && modalContent.has(e.target).length === 0)
                    $(this).removeClass('active');
            });

            this.initSendOrderModal();
            this.initTotalNode();
            this.renderBasketItems(this.result.ITEMS.AnDelCanBuy);
        },

        /**
         * Инициализация модалки оформления заказа
         */
        initSendOrderModal() {

            this.sendOrderContainer = this.componentContainer.find('.send-order');

            this.sendOrderContainer.find('#send-order').click(() => this.sendOrder());

            this.subscribeForm = window.SubscribeFormObject;

            this.sendOrderContainer.find('#get-discount').click(() => this.sendOrder(true));

            this.sendOrderContainer.find('.modal-close').click(() => {
                this.sendOrderContainer.removeClass('active');
            });


        },

        /**
         * Показать модалку оформления заказа
         */
        showSendOrderModal() {

            if (!this.result.BASKET_ITEMS_COUNT) {
                this.showError('Ваша корзина пуста');
                return;
            }

            this.sendOrderContainer.addClass('active');

        },

        /**
         * Скрыть модалку оформления заказа
         */
        hideSendOrderModal() {

            this.sendOrderContainer.removeClass('active');

        },

        /**
         * Инициализация блока с общей информацией о корзине
         */
        initTotalNode() {

            this.totalNode.find('.total-node__contact-me').click(() => {
                this.showSendOrderModal()
            });

            $(document).scroll(() => this.moveTotalNode());

        },

        /**
         * Обновляет данные для фиксации блока с общей информацией о корзине
         */
        initFixTotalNode() {
            this.totalNodeHeight = this.totalNode.outerHeight();
            this.header = $('header');
            this.headerHeight = this.header.outerHeight();
            this.totalNodeStartPoint = this.totalNode.parents('.total-node-wrap').offset().top;
            this.totalNodeEndPoint = this.totalNodeStartPoint + this.componentContainer.outerHeight() - this.totalNodeHeight;
        },

        /**
         * Перемещает блок с общей информацией о корзине
         */
        moveTotalNode() {
            if(!this.header || !this.headerHeight)
                return false;
            const headerOffset = this.header.offset().top;
            const headerPos = this.headerHeight + headerOffset;
            if(this.totalNodeEndPoint < headerPos)
                return false;

            let newTotalNodePos = headerPos - this.totalNodeStartPoint;

            newTotalNodePos < 0 ? newTotalNodePos = 0 : false;

            this.totalNode.css('top', newTotalNodePos + 'px');
        },

        /**
         * Выводит данные в блок с общей информацией корзины
         */
        renderTotalNode() {
            const fullPrice = this.result.TOTAL_RENDER_DATA.PRICE_FORMATED;
            const basketItemsCount = this.result.BASKET_ITEMS_COUNT;

            this.totalNode.find('.total-node__sum-value').html(fullPrice);
            this.totalNode.find('.total-node__counts-value').html(basketItemsCount);

            setTimeout(() => this.initFixTotalNode(), 100);
        },

        /**
         * Перерисовывает корзину
         */
        reloadBasket() {

            this.showPreloader();
            $.ajax({
                url: this.params.AJAX_PATH,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'deferredLoad',
                    via_ajax: 'Y',
                    site_id: this.siteId,
                    site_template_id: this.siteTemplateId,
                    sessid: BX.bitrix_sessid(),
                    template: this.template,
                    signedParamsString: this.signedParamsString
                },
                success: data => {
                    this.result = Object.assign(this.result, data);
                    this.renderBasketItems(this.result.ITEMS.AnDelCanBuy);
                    this.hidePreloader();
                }
            });
        },

        /**
         * Вывод товаров в корзине
         * @param {array} basketItems - масиив товаров в корзине
         */
        renderBasketItems(basketItems) {

            this.basketItemsContainer.html('');

            if (basketItems.length === 0) {
                this.basketItemsContainer.append('<tr class="basket-empty"><td>Корзина пуста</td></tr>');
                this.renderTotalNode();
                return;
            }

            basketItems.forEach(basketItem => {
                let basketItemContainer = this.createBasketItemContainer(basketItem);
                this.basketItemsContainer.append(basketItemContainer);
                basketItem.ELEMENT = basketItemContainer;
            });

            this.renderTotalNode();

        },

        /**
         * Возвращает шаблон товара в корзине для вывода
         * @returns {Promise<string>} - сторка шаблона
         */
        async getBasketItemTemplate() {

            let basketItemTemplate = false;

            this.showPreloader();

            await $.ajax({
                url: this.templateFolder + '/js-templates/basket-item.php',
                method: 'GET',
                dataType: 'html',
                success: template => {
                    basketItemTemplate = template;
                    this.hidePreloader();
                    // console.log(template)
                },
            });

            return basketItemTemplate;
        },

        /**
         * Создает DOM элемент товара по шаблону из this.basketItemTemplate
         * @param {object} basketItem - объект товара в корзине (из массива this.result.ITEMS.AnDelCanBuy)
         * @returns {*|jQuery|HTMLElement} DOM элемент товара по шаблону this.basketItemTemplate
         */
        createBasketItemContainer(basketItem) {
            let basketItemContainer = this.basketItemTemplate;
            for (key in basketItem) {

                if (!basketItem[key]) {
                    basketItemContainer = basketItemContainer.replace(new RegExp(`if{{${key}}}:(.*)endif{{${key}}}`, 'g'), '');
                }

                basketItemContainer = basketItemContainer.replace(new RegExp(`(if{{${key}}}:)|(endif{{${key}}})`, 'g'), '');
                basketItemContainer = basketItemContainer.replace(`{{${key}}}`, basketItem[key]);

            }

            let regexp = /(if{{(.*)}}:(.*)endif{{(.*)}})|({{(.*)}})/g


            basketItemContainer = basketItemContainer.replace(regexp, '');

            basketItemContainer = $(basketItemContainer)



            basketItemContainer.find('.basket-item__remove').click(() => this.removeInBasket(basketItem));

            return basketItemContainer;
        },

        /**
         * @returns {array} - массив товаров в корзине
         */
        getBasketItems() {
            return this.result.ITEMS.AnDelCanBuy;
        },

        /**
         * Удалить товар из корзины
         * В случае успеха перерисовывает корзину, иначе выводит модалку с ошибкой
         * @param {object} basketItem - объект товара в корзине (из массива this.result.ITEMS.AnDelCanBuy)
         */
        removeInBasket(basketItem) {

            let basketItemIndex = this.result.ITEMS.AnDelCanBuy.indexOf(basketItem);

            if (basketItemIndex === -1)
                return false;

            this.showPreloader();

            $.ajax({
                url: this.templateFolder + '/deleteBasketItem.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: basketItem.PRODUCT_ID
                },
                success: data => {
                    if (!data.success) {
                        this.showError('Не удалось удалить товар. Пожалуйста повторите попытку позже.');
                        return false;
                    }

                    this.reloadBasket();
                    BX.onCustomEvent('OnBasketChange');
                }
            });

        },

        /**
         * Оформляет заказ
         * @param {bool} getDiscount - нужна ли пользователю дополнительная скидка
         */
        sendOrder(getDiscount = false) {

            if (this.result.BASKET_ITEMS_COUNT === 0)
                return;

            if (getDiscount && !window.SubscribeFormObject) {
                this.showError('Ошибка. Пожалуйста попробуйте перезагрузить страницу.');
                return;
            }

            const phone = this.sendOrderContainer.find('[data-role="phone"]').val();

            if(phone.replace(/[ ()-/_]/g, '').length < 11) {
                this.showError('Некорректный номер телефона');
                return;
            }

            this.showPreloader();
            $.ajax({
                url: this.templateFolder + '/ajax/sendOrger.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    phone: phone,
                    getDiscount: getDiscount,
                },
                success: data => {
                    if (!data.success) {

                        if (data.needSubscribe === true) {
                            this.subscribeForm.container.find('[data-role="phone"]').val(phone).attr('disabled', 'disabled');
                            this.subscribeForm.callback = isSuccess => {
                                if (isSuccess) this.sendOrder(true);
                            }
                            this.subscribeForm.container.show();
                            this.hidePreloader();
                            return false;
                        }

                        this.showError('Не удалось оформить заявку. Пожалуйста повторите попытку позже.');
                        this.hidePreloader();
                        return false;
                    }

                    if (!getDiscount)
                        this.showSuccess();

                    this.hideSendOrderModal();
                    this.reloadBasket();
                }
            });

        },

        /**
         * Вывод сообщения успеха
         */
        showSuccess() {
            this.successContainer.addClass('active');
        },

        /**
         * Вывод модалки с ошибкой
         * @param {string} message - текст ошибки
         */
        showError(message) {
            this.errorContainer.find('.basket-error-modal__text').html(message);
            this.errorContainer.addClass('active');
        },

        /**
         * Показать preloader
         */
        showPreloader() {
            this.isLoading = true;
            this.preloader.addClass('active');
        },

        /**
         * Скрыть preloader
         */
        hidePreloader() {
            this.isLoading = false;
            this.preloader.removeClass('active');
        }


    }

});