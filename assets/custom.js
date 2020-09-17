function logout($isAdmin = false) {
    sessionStorage.clear();
    localStorage.clear();

    window.location.href = $isAdmin ? '/inte2/home/logout.php' : 'logout.php';
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
    form.method = 'post';
    form.action = 'checkout_preprocess.php';

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

function generateCryptoKeys(isChanging = false) {
    if (!isChanging || (isChanging && confirm('You are changing your public-private keys pair. Continue?')))
        $.ajax({
            url: "http://localhost:81/inte2/admin/keys_generator.php",
            method: 'GET',
            success: function(response) {
                let json_response = JSON.parse(response);

                if (json_response.status === 'success') handleCryptoKeysResponse(json_response.privateKey);
                else alert('Error: We encountered an issue while generating your keys. Please try again.');
            },
            error: function () {
                alert('Error: We were unable to communicate with server at the moment. Please try again.');
            }
        });
}

function handleCryptoKeysResponse(privateKey) {
    $('#generate-keys-btn').remove();
    $('#crypto-keys-warning').remove();

    download('private.pem', privateKey);

    $('#crypto-keys-col').html('' +
        '<div id="crypto-keys-show">' +
            '<p class="subtitle text-success">' +
                'Your public key has been saved to database successfully.<br/>' +
                'Your private key has been downloaded to your local computer storage.<br/>' +
                'Please save it securely. If you loose it, you have to generate another public-private keys pair.<br/>' +
                'Please click the below button to close this message.' +
            '</p>' +
            '<div class="btn btn-warning" style="width: 20%" onclick="removeCryptoKeys()">Okay</div>' +
        '</div>');
}

function removeCryptoKeys() {
    $('#crypto-keys-show').remove();
    $('#crypto-keys-col').html('' +
        '<div class="alert alert-warning" id="crypto-keys-warning">' +
            '<h5><i class="fas fa-exclamation-triangle"></i>&nbsp;Caution! Make sure you are not being watched.</h5>' +
            '<p class="subtitle"><i class="fas fa-hand-point-right"></i>&nbsp;Are you in a public place, and/or using a public wifi network?</p>' +
            '<p class="subtitle"><i class="fas fa-hand-point-right"></i>&nbsp;Is there any camera around you, including CCTVs, video recorders and mobile phones?</p>' +
            '<p class="subtitle"><i class="fas fa-hand-point-right"></i>&nbsp;Is there any other people around you?</p>' +
            '<p class="subtitle">' +
                'If none of the above is true, then it would be safe to "Change" your keys.' +
            '</p>' +
        '</div>' +
        '<div class="btn btn-primary" style="width: 20%" id="generate-keys-btn" onclick="generateCryptoKeys(true)">Change</div>');
}

function download(filename, text) {
    const pom = document.createElement('a');
    pom.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    pom.setAttribute('download', filename);

    if (document.createEvent) {
        const event = document.createEvent('MouseEvents');
        event.initEvent('click', true, true);
        pom.dispatchEvent(event);
    }
    else {
        pom.click();
    }
}

function verifyCheque(chequeId) {
    $.ajax({
        url: "http://localhost:81/inte2/bank/verify_signature.php?chequeId=" + chequeId,
        method: 'GET',
        success: function(response) {
            if (response === 'error') alert ('Error: Database connection issue. Please try again.');

            if (response === 'failed') {
                $('#cheque-alert').html('Verification failed. This cheque was tampered.');
                $('#cheque-alert').removeClass('alert-success').addClass('alert-danger');
            }

            if (response === 'success') {
                $('#cheque-alert').removeClass('alert-danger').addClass('alert-success');
                $('#cheque-alert').html('Verification success. You can approve this cheque.');
                $('#approve-btn').removeClass('disabled');
            }
        },
        error: function () {
            alert('Error: We were unable to communicate with server at the moment. Please try again.');
        }
    });
}

function approveCheque(chequeId) {
    $.ajax({
        url: "http://localhost:81/inte2/bank/approve_cheque.php?chequeId=" + chequeId,
        method: 'GET',
        success: function(response) {
            if (response === 'error') alert ('Error: Database connection issue. Please try again.');

            if (response === 'failed') {
                $('#cheque-alert').html('An error occurred while updating database. Please try again');
                $('#cheque-alert').removeClass('alert-success').addClass('alert-danger');
            }

            if (response === 'success') {
                $('#cheque-alert').removeClass('alert-danger').addClass('alert-success');
                $('#cheque-alert').html('The cheque has been successfully approved.');
                $('#approve-btn').remove();
                $('#verify-btn').remove();
            }
        },
        error: function () {
            alert('Error: We were unable to communicate with server at the moment. Please try again.');
        }
    });
}






//These functions were intended to validate Bank Card details for user but no longer required
function validateCardName() {
    let name = $('#card-name').val();

    if (name.length === 0) {
        $('#name-error').remove();
        $('#pay-button').addClass('disabled');
    }
    else {
        let matched = /^[a-zA-Z ]+$/.test(name);

        if (!matched) {
            if (!$('#name-error').length) {
                let nameError = document.createElement('p');
                nameError.id = 'name-error';
                nameError.innerHTML = 'Name on card can only contains alphabetical letters.';

                $('#card-error').append(nameError);
                $('#pay-button').addClass('disabled');
            }
            else return false;
        }
        else {
            $('#name-error').remove();
            return true;
        }
    }

    return false;
}

function validateCardNumber() {
    let num = $('#card-number').val();

    if (num.length === 0) {
        $('#num-error').remove();
        $('#pay-button').addClass('disabled');
    }
    else {
        let matched = /^[0-9 ]+$/.test(num);

        if (!matched) {
            if (!$('#num-error').length) {
                let numError = document.createElement('p');
                numError.id = 'num-error';
                numError.innerHTML = 'Card Number only contains digits separated by spaces.';

                $('#card-error').append(numError);
                $('#pay-button').addClass('disabled');
            }
            else return false;
        }
        else {
            let deSpaced = num.replace(/ /g,'');

            if (deSpaced.length < 16) {
                $('#num-error').remove();
                $('#pay-button').addClass('disabled');
            }
            else if (deSpaced.length > 16) {
                if (!$('#num-error').length) {
                    $('#num-error').remove();

                    let numError = document.createElement('p');
                    numError.id = 'num-error';
                    numError.innerHTML = 'Card Number is too long and invalid.';

                    $('#card-error').append(numError);
                    $('#pay-button').addClass('disabled');
                }
                else return false;
            }
            else if (deSpaced.length === 16) {
                $('#num-error').remove();
                return true;
            }
        }
    }

    return false;
}

function validateExpiryDate() {
    let ex = $('#card-expiry').val();

    if (ex.length === 0) {
        $('#ex-error').remove();
        $('#pay-button').addClass('disabled');
    }
    else {
        let exError = document.createElement('p');
        exError.id = 'ex-error';

        if (ex.length !== 7) {
            if (!$('#ex-error').length) {
                exError.innerHTML = 'Expiry date format: mm/yyyy';

                $('#card-error').append(exError);
                $('#pay-button').addClass('disabled');
            }
            else return false;
        }
        else if (ex.length === 7) {
            let matched = /^([0-9]{2})\/([0-9]{4})$/.test(ex);

            if (!matched) {
                if (!$('#ex-error').length) {
                    exError.innerHTML = 'Expiry date format: mm/yyyy';

                    $('#card-error').append(exError);
                    $('#pay-button').addClass('disabled');
                }
                else return false;
            }
            else {
                let year = parseInt(ex.split('/')[1]);

                if (year < (new Date()).getFullYear()) {
                    exError.innerHTML = 'Expiry date value ' + year + ' is invalid.';

                    $('#ex-error').remove();
                    $('#card-error').append(exError);
                    $('#pay-button').addClass('disabled');
                }
                else {
                    let month = parseInt(ex.split('/')[0]);

                    if (year === (new Date()).getFullYear()) {
                        if (month < (new Date()).getMonth()) {
                            exError.innerHTML = 'Expiry date value ' + (month < 10 ? '0' + month : month) + ' is invalid.';

                            $('#ex-error').remove();
                            $('#card-error').append(exError);
                            $('#pay-button').addClass('disabled');
                        }
                        else {
                            $('#ex-error').remove();
                            return true;
                        }
                    }
                    else {
                        $('#ex-error').remove();
                        return true;
                    }
                }
            }
        }
    }

    return false;
}

function validateCvv() {
    let cvv = $('#card-cvv').val();

    if (cvv.length === 0) {
        $('#cvv-error').remove();
        $('#pay-button').addClass('disabled');
    }
    else {
        let matched = /^[0-9]{3}$/.test(cvv);
        if (!matched) {
            if (!$('#cvv-error').length) {
                let cvvError = document.createElement('p');
                cvvError.id = 'cvv-error';
                cvvError.innerHTML = 'CVV must contains 3 digits.';

                $('#card-error').append(cvvError);
                $('#pay-button').addClass('disabled');
            }
            else return false;
        }
        else {
            $('#cvv-error').remove();
            return true;
        }
    }

    return false;
}

function prettify() {
    let num = $('#card-number').val();

    let foo = num.split(" ").join("");
    if (foo.length > 0) foo = foo.match(new RegExp('.{1,4}', 'g')).join(" ");

    $('#card-number').val(foo);
}

function validateCardForm() {
    let nameValid = validateCardName();
    let numValid = validateCardNumber();
    let exValid = validateExpiryDate();
    let cvvValid = validateCvv();

    if (nameValid && numValid && exValid && cvvValid) $('#pay-button').removeClass('disabled');
    else $('#pay-button').addClass('disabled');
}