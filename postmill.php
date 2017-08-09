<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

define('PAYMILL_API_HOST', 'https://api.paymill.com/v2/');
define('PAYMILL_API_KEY', 'eef839be547455576bcd8d5bc44ed3cb');
set_include_path(
        implode(PATH_SEPARATOR, array(
            realpath(realpath(dirname(__FILE__)) . '/lib'),
            get_include_path())
        )
);

$data = json_decode(file_get_contents('php://input'));

if (isset($data->paymillToken)) 
{
    require_once "Services/Paymill/Transactions.php";
    require_once "Services/Paymill/Clients.php";
    require_once "Services/Paymill/Payments.php";

    $transactionsObject = new Services_Paymill_Transactions(PAYMILL_API_KEY, PAYMILL_API_HOST);
    $clientsObject = new Services_Paymill_Clients(PAYMILL_API_KEY, PAYMILL_API_HOST);
    $paymentsObject = new Services_Paymill_Payments(PAYMILL_API_KEY, PAYMILL_API_HOST);

    $clientsParam = array(
        'email' => $data->email,
        'description' => $data->cardholder
    );
    $client = $clientsObject->create($clientsParam);

    $paymentsParam = array(
        'token' => $data->paymillToken,
        'client' => $client['id']
    );
    $payment = $paymentsObject->create($paymentsParam);

    $transactionsParam = array(
        'payment' => $payment['id'],
        'amount' => $data->amount_int,
        'currency' => $data->currency,
        'description' => 'Transaction of ' . $data->cardholder
    );
    $result = $transactionsObject->create($transactionsParam);
    
    $response = new StdClass();
    if ($result['error'])
    {
        $response->status = 'error';
        $response->message = $result['error'];
    }
    else
    {
        $response->id = $result->id;
        $response->status = $result->status;
    }

    echo json_encode($response);
}
?>