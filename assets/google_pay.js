const baseRequest = {
    apiVersion: 2,
    apiVersionMinor: 0
};

const allowedCardNetworks = ["AMEX", "DISCOVER", "INTERAC", "JCB", "MASTERCARD", "VISA"];
const allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"];

const tokenizationSpecification = {
    type: 'PAYMENT_GATEWAY',
    parameters: {
        "gateway": "stripe",
        "stripe:version": "2020-08-27",
        "stripe:publishableKey": "pk_test_51HQDZND2FG7NncIEj68F5ie7Yc6VKR7y5r0aMkoaf3OD5CUIcqHBCYq3Wb2biu3D1jie5wjUKdsfwh3kdWG6flgJ00KdGXIjMp"
    }
};

const baseCardPaymentMethod = {
    type: 'CARD',
    parameters: {
        allowedAuthMethods: allowedCardAuthMethods,
        allowedCardNetworks: allowedCardNetworks
    }
};

const cardPaymentMethod = Object.assign(
    {},
    baseCardPaymentMethod,
    {
        tokenizationSpecification: tokenizationSpecification
    }
);

let paymentsClient = null;

function getGoogleIsReadyToPayRequest() {
    return Object.assign(
        {},
        baseRequest,
        {
            allowedPaymentMethods: [baseCardPaymentMethod]
        }
    );
}

function getGooglePaymentDataRequest() {
    const paymentDataRequest = Object.assign({}, baseRequest);
    paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
    paymentDataRequest.transactionInfo = getGoogleTransactionInfo();
    paymentDataRequest.merchantInfo = {
        merchantId: 'acct_1HQDZND2FG7NncIE',
        merchantName: 'INTE1070'
    };

    paymentDataRequest.callbackIntents = ["PAYMENT_AUTHORIZATION"];

    return paymentDataRequest;
}

function getGooglePaymentsClient() {
    if ( paymentsClient === null ) {
        paymentsClient = new google.payments.api.PaymentsClient({
            environment: 'TEST',
            paymentDataCallbacks: {
                onPaymentAuthorized: onPaymentAuthorized
            }
        });
    }
    return paymentsClient;
}

function onPaymentAuthorized(paymentData) {
    return new Promise(function(resolve, reject){
        processPayment(paymentData)
            .then(function() {
                resolve({ transactionState: 'SUCCESS' });
            })
            .catch(function() {
                resolve({
                    transactionState: 'ERROR',
                    error: {
                        intent: 'PAYMENT_AUTHORIZATION',
                        message: 'Insufficient funds, try again. Next attempt should work.',
                        reason: 'PAYMENT_DATA_INVALID'
                    }
                });
            });
    });
}

function onGooglePayLoaded() {
    const paymentsClient = getGooglePaymentsClient();
    paymentsClient.isReadyToPay(getGoogleIsReadyToPayRequest())
        .then(function(response) {
            if (response.result) {
                addGooglePayButton();
            }
        })
        .catch(function(err) {
            alert('Error occurred: We were unable to verify your card. Please try again.');
        });
}

function addGooglePayButton() {
    const paymentsClient = getGooglePaymentsClient();
    const button =
        paymentsClient.createButton({onClick: onGooglePaymentButtonClicked});
    document.getElementById('container').appendChild(button);
}

function onGooglePaymentButtonClicked() {
    const paymentDataRequest = getGooglePaymentDataRequest();
    paymentDataRequest.transactionInfo = getGoogleTransactionInfo();

    const paymentsClient = getGooglePaymentsClient();
    try {
        paymentsClient.loadPaymentData(paymentDataRequest);
    } catch ($e) {
        alert('You have canceled the payment. No charge has been made.');
    }
}

function processPayment(paymentData) {
    return new Promise(function(resolve, reject) {
        setTimeout(function() {
            let paymentToken = paymentData.paymentMethodData.tokenizationData.token;
            let json_token = JSON.parse(paymentToken);

            let form = document.createElement('form');
            form.action = 'capture.php';
            form.method = 'post';

            let tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.value = json_token.id;
            tokenInput.name = 'paymentToken';

            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();

            resolve();
        }, 100);
    });
}