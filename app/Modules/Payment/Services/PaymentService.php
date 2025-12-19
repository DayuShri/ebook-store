<?php

namespace App\Modules\Payment\Services;

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Services\WalletService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client; // Tambahkan ini untuk bypass SSL

class PaymentService
{
    protected $config;
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;

        // Setup Konfigurasi Xendit
        $this->config = Configuration::getDefaultConfiguration();
        $this->config->setApiKey(config('services.xendit.key'));
    }

    public function createTopUp($amount)
    {
        // --- BYPASS SSL (KHUSUS LOCALHOST) ---
        // Ini mengatasi error "cURL error 60: SSL certificate problem"
        $client = new Client(['verify' => false]);
        $apiInstance = new InvoiceApi($client, $this->config);
        
        $user = Auth::user();
        $amount = (float) $amount;
        $externalId = 'TOPUP-' . Str::random(10);
        
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
            // Eksekusi API ke Xendit
            $result = $apiInstance->createInvoice($create_invoice_request);

            // --- PERBAIKAN UTAMA DI SINI ---
            // Di v7, $result adalah OBJECT, bukan Array.
            // Gunakan tanda panah (->) untuk mengambil datanya.

            $payment = Payment::create([
                'id' => Str::uuid(),
                'payment_number' => 'PAY-' . strtoupper(Str::random(8)),
                'order_id' => $this->createRealOrder($user->id),
                'user_id' => $user->id,
                'amount' => $amount,
                'payment_method' => 'qris',
                'payment_gateway_ref' => $result->getId(), // Gunakan getter atau ->id
                'status' => 'pending',
            ]);

            // Ambil invoice_url dari object result
            $payment->checkout_link = $result->getInvoiceUrl(); 

            return $payment;

        } catch (\Xendit\XenditSdkException $e) {
            // Error handling diperbaiki agar tidak crash saat konversi ke string
            throw new \Exception("Xendit Error: " . $e->getMessage());
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
        // Pastikan ada ID
        if (!isset($data['id'])) { return null; }

        $payment = Payment::where('payment_gateway_ref', $data['id'])->first();
    
        if (!$payment) { return null; }

        // Mencegah double topup
        if ($payment->status === 'success') { return $payment; }
    
        if (in_array($data['status'], ['SETTLED', 'PAID'])) {
            $payment->update(['status' => 'success']);
    
            $this->walletService->addBalance(
                $payment->user_id, 
                $payment->amount, 
                $payment->id, 
                "Top Up via Xendit (Ref: " . $payment->payment_number . ")"
            );
        }
    
        return $payment;
    }

    public function processOrderPayment($orderId, $amount, $userId)
    {
        return DB::transaction(function () use ($orderId, $amount, $userId) {
            $wallet = DB::table('wallets')->where('user_id', $userId)->first();

            if (!$wallet || $wallet->balance < $amount) {
                throw new \Exception("Saldo Wallet tidak mencukupi.");
            }

            $this->walletService->deductBalance(
                $userId, 
                (float) $amount, 
                $orderId, 
                "Pembelian Buku Order #" . $orderId
            );

            DB::table('orders')->where('id', $orderId)->update([
                'status' => 'paid',
                'updated_at' => now(),
            ]);

            return Payment::create([
                'id' => Str::uuid(), 
                'payment_number' => 'PAY-ORD-' . strtoupper(Str::random(8)),
                'order_id' => $orderId,
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => 'wallet',
                'status' => 'success',
                'paid_at' => now(),
            ]);
        });
    }
}