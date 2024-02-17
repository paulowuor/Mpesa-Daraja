<?php
if (isset($_POST['submit'])) {
    ob_start(); // Start output buffering to capture the response
    date_default_timezone_set('Africa/Nairobi');

    # access token
    $consumerKey = 'MA4Xs4sTODePPTJ9soxp4kgPg7r7z3RQ'; // Fill with your app Consumer Key
    $consumerSecret = 'SpScAstUdrBJxShB'; // Fill with your app Secret

    # define the variables
    # provide the following details, this part is found on your test credentials on the developer account
    $BusinessShortCode = '174379'; // Change this code and passkey to your credentials
    $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';

    // Validate and format phone number
    $PartyA = '254' . substr($_POST['phone'], 1); // Ensure the correct format
    $PartyA = preg_replace('/[^0-9]/', '', $PartyA); // Remove non-numeric characters

    // Rest of your code remains unchanged...

    $AccountReference = '2255';
    $TransactionDesc = 'Test Payment';
    $Amount = $_POST['amount'];

    # Get the timestamp, format YYYYmmddhms -> 20181004151020
    $Timestamp = date('YmdHis');

    # Get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
    $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);

    # header for access token
    $headers = ['Content-Type:application/json; charset=utf8'];

    # M-PESA endpoint urls
    $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    # callback url
    $CallBackURL = 'https://morning-basin-87523.herokuapp.com/callback_url.php';  // Put the link to your live project here

    $curl = curl_init($access_token_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $result = json_decode($result);
    $access_token = $result->access_token;
    curl_close($curl);

    # header for stk push
    $stkheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];

    # initiating the transaction
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $initiate_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header

    $curl_post_data = array(
        // Fill in the request parameters with valid values
        'BusinessShortCode' => $BusinessShortCode,
        'Password' => $Password,
        'Timestamp' => $Timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $Amount,
        'PartyA' => $PartyA,
        'PartyB' => $BusinessShortCode,
        'PhoneNumber' => $PartyA,
        'CallBackURL' => $CallBackURL,
        'AccountReference' => $AccountReference,
        'TransactionDesc' => $TransactionDesc
    );

    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    $curl_response = curl_exec($curl);

    if ($curl_response === false) {
        // Handle cURL request failure
        echo "An error occurred while processing the request.";
    } else {
        // Stop capturing the output
        ob_end_clean();

        // Print only the success message
        $response_array = json_decode($curl_response, true);

        if (isset($response_array['ResponseCode']) && $response_array['ResponseCode'] == '0') {
            echo "Success. Request accepted for processing";
        } else {
            // Handle errors or display a generic message if needed
            echo "An error occurred.";
        }
    }
}
?>
