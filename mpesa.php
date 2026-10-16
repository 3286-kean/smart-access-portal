<?php
require 'connect.php';

$data = file_get_contents('php://input');
$response = json_decode($data, true);

$transaction_code = $response['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
$status_code = $response['Body']['stkCallback']['ResultCode'];

$status = ($status_code == 0) ? "success" : "failed";

$stmt = $conn->prepare("UPDATE payments 
                        SET status=? 
                        WHERE reference=?");
$stmt->bind_param("ss", $status, $transaction_code);
$stmt->execute();

echo json_encode(["ResultCode"=>0,"ResultDesc"=>"Accepted"]);
?>