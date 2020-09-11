function logout() {
    sessionStorage.clear();
    localStorage.clear();

    window.location.href = 'logout.php';
}

function addItemToCart(itemId) {
    let itemStock = $('#stock' + itemId).html().trim();
    let remaining = parseInt(itemStock.split(' ')[0]) - 1;

    if (remaining > 0) {
        $('#stock' + itemId).html(remaining + ' left');
        $('#no-item').hide();

        let itemPrice = parseInt($('#price' + itemId).html().slice(1));
        if ($('#item' + itemId).length) {
            let currentlyAdded = parseInt($('#item' + itemId + '-subtotal').html().split(' x ')[1]);
            $('#item' + itemId + '-subtotal').html('$' + itemPrice + ' x ' + (currentlyAdded + 1));
        }
        else {
            let newItem = '<div class="col-sm-12" id="item' + itemId + '">' +
                              '<p style="display: none;" id="item-id">' + itemId + '</p>' +
                              '<a class="text-danger" role="button" onclick="removeItemFromCart(' + itemId + ')">' +
                                    '<i class="fas fa-times"></i>' +
                              '</a>&nbsp;' +
                              '<b>' + $('#product' + itemId).html() + '</b>' +
                              '<span class="float-right" id="item' + itemId + '-subtotal">$' + itemPrice + ' x 1</span>' +
                          '</div>';

            $('#cart-items').append(newItem);
        }

        $('#checkout-button').removeClass('disabled');
        calculateTotal();
    }
    else {
        let checkoutBtn = $('#btn' + itemId);
        $('#stock' + itemId).html('Out of stock');
        checkoutBtn.removeClass('btn-primary').addClass('btn-secondary');
        checkoutBtn.addClass('disabled');
    }

    updateLocalStorageCart();
}

function removeItemFromCart(itemId) {
    let itemSubtotalLabel = $('#item' + itemId + '-subtotal').html();
    let quantity = parseInt(itemSubtotalLabel.split(' x ')[1]);

    if (quantity > 1)
        $('#item' + itemId + '-subtotal').html(itemSubtotalLabel.split(' x ')[0] + ' x ' + (quantity - 1));
    else {
        let cart = document.getElementById('cart-items');
        cart.removeChild(document.getElementById('item' + itemId));

        if (cart.getElementsByTagName('div').length === 0) {
            $('#no-item').show();
            $('#checkout-button').addClass('disabled');
        }
    }

    let itemStock = $('#stock' + itemId).html();
    if (itemStock === 'Out of stock') {
        $('#btn' + itemId).removeClass('disabled')
                          .removeClass('btn-secondary')
                          .addClass('btn-primary');

        $('#stock' + itemId).html('1 left');
    }
    else
        $('#stock' + itemId).html((parseInt(itemStock.split(' ')[0]) + 1) + ' left');

    calculateTotal();
    updateLocalStorageCart();
}

function calculateTotal() {
    let allItems = document.getElementById('cart-items')
                           .getElementsByTagName('div');

    let grandTotal = 0;
    for (let i = 0; i < allItems.length; i++) {
        let subTotalLabel = allItems[i].getElementsByTagName('span')[0].innerHTML.trim();
        let price = parseInt(subTotalLabel.split(' x ')[0].slice(1));
        let quantity = parseInt(subTotalLabel.split(' x ')[1]);

        grandTotal += price * quantity;
    }

    document.getElementById('total').innerHTML = '$' + grandTotal;
}

function updateLocalStorageCart() {
    let allItems = document.getElementById('cart-items')
                           .getElementsByTagName('div');

    let cart = localStorage.hasOwnProperty('SHOPPING_CART') ? JSON.parse(localStorage.getItem('SHOPPING_CART')) : [];
    for (let i = 0; i < allItems.length; i++) {
        let itemId = allItems[i].getElementsByTagName('p')[0].innerHTML;
        let quantity = allItems[i].getElementsByTagName('span')[0].innerHTML.split(' x ')[1];

        let entryIndex = -1;
        cart.forEach(item => { if (item.item === itemId) entryIndex = cart.indexOf(item); });

        if (entryIndex !== -1) cart.splice(entryIndex, 1);
        cart.push({item: itemId, qty: quantity});
    }

    localStorage.setItem('SHOPPING_CART', JSON.stringify(cart));
}

function gotoCart() {
    let cart = localStorage.getItem('SHOPPING_CART');
    let form = document.createElement('form');

    localStorage.removeItem('SHOPPING_CART');

    form.action = 'cart_review.php';
    form.method = 'post';

    let input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'cart';
    input.value = cart;

    form.appendChild(input);
    document.body.append(form);
    form.submit();
}

function selectPaymentMethod(method) {
    let paymentMethods = document.getElementById('payment-methods')
                                 .getElementsByTagName('a');

    for (let i = 0; i < paymentMethods.length; i++)
        if (paymentMethods[i].id === method)
            paymentMethods[i].classList.add('a-selected');
        else {
            paymentMethods[i].classList.remove('a-selected');
        }

    localStorage.setItem('PAYMENT_METHOD', method);
    $('#checkout-button').removeClass('disabled');
}

function checkout() {
    let paymentMethod = localStorage.getItem('PAYMENT_METHOD');
    localStorage.removeItem('PAYMENT_METHOD');

    let form = document.createElement('form');
    form.action = 'paypal/checkout.php';
    form.method = 'post';

    let methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = 'payment_method';
    methodInput.value = paymentMethod;

    form.appendChild(methodInput);
    document.body.append(form);
    form.submit();
}

function invokeCaptureRequestForOrder(orderId, authId) {
    let form = document.createElement('form');
    form.action = 'capture.php';
    form.method = 'post';

    let orderIdInput = document.createElement('input');
    orderIdInput.type = 'hidden';
    orderIdInput.name = 'orderId';
    orderIdInput.value = orderId;

    let authIdInput = document.createElement('input');
    authIdInput.type = 'hidden';
    authIdInput.name = 'authId';
    authIdInput.value = authId;

    form.appendChild(orderIdInput);
    form.appendChild(authIdInput);

    document.body.append(form);
    form.submit();
}