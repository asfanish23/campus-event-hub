<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Test callback endpoint - for sandbox/development testing only
 */
class PaymentTestController extends Controller
{
    /**
     * Simulate a successful payment callback from ToyyibPay (for testing)
     * GET /payment/test/success/{payment_id}
     */
    public function simulateSuccess($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        
        Log::info('TEST: Simulating successful payment callback', [
            'payment_id' => $paymentId,
            'bill_code' => $payment->bill_code,
        ]);

        // Mark as paid
        $payment->markAsPaid();
        $payment->update([
            'transaction_time' => now(),
            'payment_reference' => 'TEST-' . uniqid(),
            'callback_response' => [
                'status' => 1,
                'billcode' => $payment->bill_code,
                'refno' => 'TEST-REF-' . time(),
                'test_mode' => true,
            ],
        ]);

        Log::info('TEST: Payment marked as paid', ['payment_id' => $paymentId]);

        return redirect(URL::signedRoute('payment.return', ['payment_id' => $paymentId]))
            ->with('success', 'Test payment completed successfully!');
    }

    /**
     * Simulate a failed payment callback from ToyyibPay (for testing)
     * GET /payment/test/failed/{payment_id}
     */
    public function simulateFailure($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        
        Log::info('TEST: Simulating failed payment callback', [
            'payment_id' => $paymentId,
            'bill_code' => $payment->bill_code,
        ]);

        // Mark as failed
        $payment->markAsFailed();
        $payment->update([
            'callback_response' => [
                'status' => 3,
                'billcode' => $payment->bill_code,
                'test_mode' => true,
                'reason' => 'Test failure'
            ],
        ]);

        Log::info('TEST: Payment marked as failed', ['payment_id' => $paymentId]);

        return redirect(URL::signedRoute('payment.return', ['payment_id' => $paymentId]))
            ->with('error', 'Test payment failed!');
    }
}
