<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VNPayController extends Controller
{
    public function createPayment(Request $request)
    {
        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url = config('services.vnpay.url');
        $vnp_Returnurl = config('services.vnpay.return_url');
        
        $vnp_TxnRef = date("YmdHis") . rand(100, 999); 
        $vnp_Amount = $request->input('amount', 100000) * 100; 
        
        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $request->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Salary advance for order: " . $vnp_TxnRef,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return response()->json([
            'status' => 'success',
            'payment_url' => $vnp_Url
        ]);
    }

    public function vnpayIpn(Request $request)
    {
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);
        ksort($inputData);
        
        $i = 0;
        $hashData = "";

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        if ($secureHash === $vnp_SecureHash) {
            if ($inputData['vnp_ResponseCode'] == '00' || $inputData['vnp_TransactionStatus'] == '00') {
                
                $orderId = $inputData['vnp_TxnRef'];
                Log::info("[VNPay] Payment successful for order: " . $orderId);
                
                return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
            } else {
                return response()->json(['RspCode' => '00', 'Message' => 'Transaction Failed']);
            }
        } else {
            Log::warning("[VNPay] Invalid IPN signature detected!");
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }
    }
}