<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>INTE1070</title>

    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/custom.css" />
</head>
<body>
<div class="inte-header">
    <h2>INTE1070: Secure Electronic Commerce</h2>
</div>

<div class="container" style="margin-bottom: 80px">
    <h2 style="margin-top: 2rem;">Cryptocurrency Demo: Jaycoin</h2>
    <hr style="border: 1px solid #2e87e6; width: 35%;" />

    <div class="row">
        <h5><b>How to start servers for Jaycoin</b></h5>
        <ul style="margin-left: 40px;">
            <li>Install <code>node.js</code> for your user account.</li>
            <li>Extract the files in the jaycoin.zip archive and put them all in the same place.</li>
            <li>Open <code>Command Prompt</code> and navigate <code>cd</code> to the folder containing Jaycoin codes.</li>
            <li>Run these 2 commands in order: <code>npm install</code> then <code>npm start</code>.</li>
            <li>Done. You can then come back here and play the demo.</li>
        </ul>
    </div>

    <hr style="border: 1px solid #2e87e6; width: 35%;" />
    <div class="row">
        <div class="col-sm-4 demo-col" style="padding: 10px;">
            <div class="row">
                <div class="col-sm-12">
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="getBlockchain();">View Blockchain</button>

                    <script type="text/javascript">
                        function getBlockchain() {
                            $('#url-query').html('http://localhost:3001/blockchain');

                            $.ajax({
                                url: "http://localhost:3001/blockchain",
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'GET',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <input type="text" class="form-control" placeholder="Block hash" id="block-hash" />
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="getBlock();">View Block Detail</button>

                    <script type="text/javascript">
                        function getBlock() {
                            $('#url-query').html("http://localhost:3001/block/" + $('#block-hash').val());

                            if ($('#block-hash').val().length === 0) {
                                alert('The Block Hash is required.');
                                return;
                            }

                            $.ajax({
                                url: "http://localhost:3001/block/" + $('#block-hash').val(),
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'GET',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="getPublicKey();">Get Public Key</button>

                    <script type="text/javascript">
                        function getPublicKey() {
                            $('#url-query').html("http://localhost:3001/public-key");

                            $.ajax({
                                url: "http://localhost:3001/public-key",
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'GET',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="getBalance();">View Wallet Ballance</button>

                    <script type="text/javascript">
                        function getBalance() {
                            $('#url-query').html("http://localhost:3001/balance");

                            $.ajax({
                                url: "http://localhost:3001/balance",
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'GET',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="mineCoin();">Mine Jaycoin</button>

                    <script type="text/javascript">
                        function mineCoin() {
                            $('#url-query').html("http://localhost:3001/mine-last");

                            $.ajax({
                                url: "http://localhost:3001/mine-last",
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'POST',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <div class="instruction">Your Public Key is used to find your Wallet.</div>
                    <input type="text" class="form-control" placeholder="Public Key" id="public-key" />
                    <input type="number" step="1" class="form-control" placeholder="Amount" id="amount" />
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="processTransaction();">Process Transaction</button>

                    <script type="text/javascript">
                        function processTransaction() {
                            $('#url-query').html("http://localhost:3001/process-transaction" + "\n" +
                                JSON.stringify({ 'address': $('#public-key').val(), 'amount': parseInt($('#amount').val()) }));

                            if ($('#public-key').val().length === 0 || $('#amount').val() === 0) {
                                alert('The Public key and Amount are both required.');
                                return;
                            }

                            $.ajax({
                                url: "http://localhost:3001/process-transaction",
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'POST',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                data: JSON.stringify({ 'address': $('#public-key').val(), 'amount': parseInt($('#amount').val()) }),
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <div class="instruction">Use the button above to create a Transaction first.</div>
                    <input type="text" class="form-control" placeholder="Transaction Id" id="txid" />
                    <input type="number" step="1" class="form-control" placeholder="Amount" id="mine-amount" />
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="mineTransaction();">Mine Transaction</button>

                    <script type="text/javascript">
                        function mineTransaction() {
                            $('#url-query').html("http://localhost:3001/mine-transaction" + "\n" +
                                JSON.stringify({ 'address': $('#txid').val(), 'amount': parseInt($('#mine-amount').val()) }));

                            if ($('#txid').val().length === 0 || $('#mine-amount').val() === 0) {
                                alert('The Transaction Id and Amount are both required.');
                                return;
                            }

                            $.ajax({
                                url: "http://localhost:3001/mine-transaction",
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'POST',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                data: JSON.stringify({ 'address': $('#txid').val(), 'amount': parseInt($('#mine-amount').val()) }),
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <div class="instruction">View detail of a Transaction by its Id.</div>
                    <input type="text" class="form-control" placeholder="Transaction Id" id="transaction-id" />
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="getTransaction();">View Transaction Detail</button>

                    <script type="text/javascript">
                        function getTransaction() {
                            $('#url-query').html("http://localhost:3001/transaction/" + $('#transaction-id').val());

                            if ($('#transaction-id').val().length === 0) {
                                alert('The Transaction Id is required.');
                                return;
                            }

                            $.ajax({
                                url: "http://localhost:3001/transaction/" + $('#transaction-id').val(),
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'GET',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <div class="instruction">View information of a TxOut in your Wallet.</div>
                    <input type="text" class="form-control" placeholder="Address" id="address" />
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="getTxOuts();">View TxOut Detail</button>

                    <script type="text/javascript">
                        function getTxOuts() {
                            $('#url-query').html("http://localhost:3001/tx-outs/" + $('#address').val());

                            if ($('#address').val().length === 0) {
                                alert('The Transaction Id and Amount are both required.');
                                return;
                            }

                            $.ajax({
                                url: "http://localhost:3001/tx-outs/" + $('#address').val(),
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'GET',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <div class="instruction">View all available TxOuts in your Wallet.</div>
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="getWalletTxOuts();">View Wallet TxOuts</button>

                    <script type="text/javascript">
                        function getWalletTxOuts() {
                            $('#url-query').html("http://localhost:3001/wallet-available-tx-outs");

                            $.ajax({
                                url: "http://localhost:3001/wallet-available-tx-outs",
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'GET',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <div class="instruction">View all TxOuts.</div>
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="getAllTxOuts();">View All TxOuts</button>

                    <script type="text/javascript">
                        function getAllTxOuts() {
                            $('#url-query').html("http://localhost:3001/available-tx-outs");

                            $.ajax({
                                url: "http://localhost:3001/available-tx-outs",
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'GET',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>
                </div>

                <div class="col-sm-12" style="margin-top: 30px;">
                    <div class="instruction">View all TxOuts.</div>
                    <button class="btn btn-primary" style="margin-top: 10px;" onclick="getTransactionPool();">View Transaction Pool</button>

                    <script type="text/javascript">
                        function getTransactionPool() {
                            $('#url-query').html("http://localhost:3001/transaction-pool");

                            $.ajax({
                                url: "http://localhost:3001/transaction-pool",
                                crossOrigin: true,
                                contentType: 'application/json',
                                method: 'GET',
                                headers: { 'Access-Control-Allow-Origin':'*' },
                                success: function(response) {
                                    const json = JSON.stringify(response, null, 3);
                                    $('#json-response').html(json);
                                },
                                error: function () {
                                    alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                }
                            });
                        }
                    </script>

                    <div class="col-sm-12" style="margin-top: 30px;">
                        <div class="instruction">Enter Peer end point to connect.</div>
                        <input type="text" class="form-control" placeholder="ws://localhost:12345" id="peer" />
                        <button class="btn btn-primary" style="margin-top: 10px;" onclick="connectPeer();">Connect Peer</button>

                        <script type="text/javascript">
                            function connectPeer() {
                                $('#url-query').html("http://localhost:3001/connect-peer" + "\n" + JSON.stringify({ 'peer': $('#peer').val() }));

                                if ($('#peer').val().length === 0) {
                                    alert('The Peer\'s end point is required.');
                                    return;
                                }

                                $.ajax({
                                    url: "http://localhost:3001/connect-peer",
                                    crossOrigin: true,
                                    contentType: 'application/json',
                                    method: 'POST',
                                    headers: { 'Access-Control-Allow-Origin':'*' },
                                    data: JSON.stringify({ 'peer': $('#peer').val() }),
                                    success: function(response) {
                                        const json = JSON.stringify(response, null, 3);
                                        $('#json-response').html(json);
                                    },
                                    error: function () {
                                        alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                    }
                                });
                            }
                        </script>
                    </div>

                    <div class="col-sm-12" style="margin-top: 30px;">
                        <button class="btn btn-primary" style="margin-top: 10px;" onclick="getPeers();">View Peers</button>

                        <script type="text/javascript">
                            function getPeers() {
                                $('#url-query').html("http://localhost:3001/peers");

                                $.ajax({
                                    url: "http://localhost:3001/peers",
                                    crossOrigin: true,
                                    contentType: 'application/json',
                                    method: 'GET',
                                    headers: { 'Access-Control-Allow-Origin':'*' },
                                    success: function(response) {
                                        const json = JSON.stringify(response, null, 3);
                                        $('#json-response').html(json);
                                    },
                                    error: function () {
                                        alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                    }
                                });
                            }
                        </script>
                    </div>

                    <div class="col-sm-12" style="margin-top: 30px;">
                        <div class="instruction">Close your Wallet: Wallet data will be deleted.</div>
                        <button class="btn btn-primary" style="margin-top: 10px;" onclick="closeWallet();">Close Wallet</button>

                        <script type="text/javascript">
                            function closeWallet() {
                                $('#url-query').html("http://localhost:3001/close-wallet");

                                $.ajax({
                                    url: "http://localhost:3001/close-wallet",
                                    crossOrigin: true,
                                    contentType: 'application/json',
                                    method: 'GET',
                                    headers: { 'Access-Control-Allow-Origin':'*' },
                                    success: function(response) {
                                        $('#json-response').html(response);
                                    },
                                    error: function () {
                                        alert('Error: We were unable to communicate with server at the moment. Please try again.');
                                    }
                                });
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-8 demo-col">
            <div class="url-query" id="url-query"></div>
            <pre class="demo-display" id="json-response"></pre>
        </div>
    </div>
</div>

<div class="footer">S3493188 Le Kim Phuc Nguyen</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/js/fontawesome.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
<script src="../assets/custom.js"></script>
</body>
</html>
