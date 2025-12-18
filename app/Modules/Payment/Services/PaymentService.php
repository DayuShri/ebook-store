<?php

namespace App\Modules\Payment\Services;

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use App\Modules\Payment\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentService
{
    protected $config;

    public function __construct()
    {
        $this->config = Configuration::getDefaultConfiguration();
        $this->config->setApiKey(config('services.xendit.key'));
    }

    public function createTopUp($amount)
    {
        $apiInstance = new InvoiceApi(null, $this->config);
        $user = Auth::user(); 

        // Pastikan nominal adalah angka bulat (float)
        $amount = (float) $amount;
        $externalId = 'TOPUP-' . Str::random(10);
        
        // Perbaikan: Tambahkan fallback jika nama atau email user kosong agar Xendit tidak error
        $customerName = $user->name ?? 'Customer ' . $user->id;
        $customerEmail = $user->email ?? 'customer@example.com';

        $create_invoice_request = new CreateInvoiceRequest([
            'external_id' => $externalId,
            'description' => 'Top Up Saldo User: ' . $customerName,
            'amount' => $amount,
            'currency' => 'IDR',
            'customer' => [
                'given_names' => $customerName,
                'email' => $customerEmail,
            ],
            'success_redirect_url' => url('/payment/success'),
        ]);

        try {
            $result = $apiInstance->createInvoice($create_invoice_request);

            // Simpan ke database
            $payment = Payment::create([
                'id' => Str::uuid(), 
                'payment_number' => 'PAY-' . strtoupper(Str::random(8)),
                'order_id' => $this->createRealOrder($user->id), 
                'user_id' => $user->id, 
                'amount' => $amount,
                'payment_method' => 'qris',
                'payment_gateway_ref' => $result['id'], 
                'status' => 'pending',
            ]);

            // Tambahkan link invoice secara manual karena kolomnya tidak ada di database
            $payment->checkout_link = $result['invoice_url']; 

            return $payment;
        } catch (\Xendit\XenditSdkException $e) {
            // Menangkap error spesifik dari SDK Xendit untuk memudahkan debugging
            throw new \Exception("Xendit Error: " . $e->getFullError());
        } catch (\Exception $e) {
            throw new \Exception("Gagal menghubungi Xendit: " . $e->getMessage());
        }
    }

    private function createRealOrder($userId)
    {
        $orderId = Str::uuid();
        
        DB::table('orders')->insert([
            'id' => $orderId,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => $userId,
            'total_amount' => 0,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $orderId;
    }

    public function handleCallback($data)
    {
        $payment = Payment::where('payment_gateway_ref', $data['id'])->first();
    
        if (!$payment) {
            return null; 
        }
    
        // Pastikan pengecekan status mendukung format Xendit
        if (in_array($data['status'], ['SETTLED', 'PAID'])) {
            $payment->update(['status' => 'success']);
    
            $wallet = DB::table('wallets')->where('user_id', $payment->user_id)->first();
            
            if ($wallet) {
                DB::table('wallets')->where('user_id', $payment->user_id)
                    ->increment('balance', $payment->amount);
            }
        }
    
        return $payment;
    }
}