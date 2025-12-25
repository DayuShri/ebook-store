<?php

namespace App\Modules\Payment\Services;

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use App\Modules\Payment\Models\Payment;
use App\Modules\Payment\Services\WalletService; // Import Service temanmu
use App\Modules\Library\Services\LibraryService;
use App\Modules\Order\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentService
{
    protected $config;
    protected $walletService;
    protected $libraryService;

    public function __construct(WalletService $walletService, LibraryService $libraryService) // Inject WalletService & LibraryService
    {
        $this->config = Configuration::getDefaultConfiguration();
        $this->config->setApiKey(config('services.xendit.key'));
        $this->walletService = $walletService;
        $this->libraryService = $libraryService;
    }

    public function createTopUp($amount)
    {
        $apiInstance = new InvoiceApi(null, $this->config);
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
            $result = $apiInstance->createInvoice($create_invoice_request);

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

            $payment->checkout_link = $result['invoice_url']; 

            return $payment;
        } catch (\Xendit\XenditSdkException $e) {
            // Bungkus dengan json_encode()
            throw new \Exception("Xendit Error: " . json_encode($e->getFullError()));
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
            // Ambil saldo wallet user saat ini
            $wallet = DB::table('wallets')->where('user_id', $userId)->first();

            if (!$wallet || $wallet->balance < $amount) {
                throw new \Exception("Saldo Wallet tidak mencukupi untuk melakukan pembayaran.");
            }

            // Potong saldo melalui WalletService
            $this->walletService->deductBalance(
                $userId, 
                (float) $amount, 
                $orderId, 
                "Pembelian Buku Order #" . $orderId
            );

            // Update status di tabel orders menjadi 'paid'
            DB::table('orders')->where('id', $orderId)->update([
                'status' => 'paid',
                'updated_at' => now(),
            ]);

            // Grant library access to all books in the order
            try {
                $orderItems = DB::table('order_items')
                    ->where('order_id', $orderId)
                    ->get();

                // Use direct service injection for backend-to-backend communication
                $libraryService = app(\App\Modules\Library\Services\LibraryService::class);

                foreach ($orderItems as $item) {
                    try {
                        $libraryService->grant($userId, $item->book_id, $orderId);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to grant library access for item', [
                            'user_id' => $userId,
                            'book_id' => $item->book_id,
                            'order_id' => $orderId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Exception granting library access after payment', [
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the payment if library grant fails
                // The fallback in CartController will handle it
            }

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