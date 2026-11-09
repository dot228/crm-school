<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\TariffPlan;
use App\Services\LessonBalanceService;
use App\Services\WayForPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WayForPayController extends Controller
{
    public function __construct(
        private WayForPayService $wfp,
        private LessonBalanceService $balance
    ) {}

    public function initiate(Request $request)
    {
        $request->validate([
            'enrollment_id'  => 'required|exists:enrollments,id',
            'tariff_plan_id' => 'required|exists:tariff_plans,id',
        ]);

        $enrollment = Enrollment::where('id', $request->enrollment_id)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        $tariff      = TariffPlan::where('id', $request->tariff_plan_id)->where('status', 'active')->firstOrFail();
        $orderRef    = 'ORDER-' . auth()->id() . '-' . time();
        $productName = $tariff->name;

        $payment = Payment::create([
            'student_id'     => auth()->id(),
            'enrollment_id'  => $enrollment->id,
            'tariff_plan_id' => $tariff->id,
            'lessons_count'  => $tariff->count_lessons,
            'amount'         => $tariff->amount,
            'status'         => 'pending',
            'method'         => 'online',
            'payment_date'   => now()->toDateString(),
            'notes'          => "WayForPay: {$productName} ({$tariff->count_lessons} уроків) | ref:{$orderRef}",
        ]);

        $form = $this->wfp->buildForm([
            'order_reference' => $orderRef,
            'order_date'      => $payment->created_at->timestamp,
            'amount'          => $tariff->amount,
            'product_name'    => $productName,
            'product_count'   => 1,
            'product_price'   => $tariff->amount,
        ]);

        return view('wayforpay.redirect', compact('form'));
    }

    public function initiateMoneyTopup(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
        ]);

        if (!$this->wfp->isConfigured()) {
            return redirect()->route('student.payments')
                ->with('error', 'Онлайн-оплата не налаштована. Оберіть інший метод.');
        }

        $amount   = (float) $request->amount;
        $orderRef = 'MONEY-' . auth()->id() . '-' . time();

        $payment = Payment::create([
            'student_id'   => auth()->id(),
            'payment_type' => 'money',
            'amount'       => $amount,
            'status'       => 'pending',
            'method'       => 'online',
            'payment_date' => now()->toDateString(),
            'notes'        => "WayForPay: поповнення балансу | ref:{$orderRef}",
        ]);

        $form = $this->wfp->buildForm([
            'order_reference' => $orderRef,
            'order_date'      => $payment->created_at->timestamp,
            'amount'          => $amount,
            'product_name'    => 'Поповнення грошового балансу',
            'product_count'   => 1,
            'product_price'   => $amount,
        ]);

        return view('wayforpay.redirect', compact('form'));
    }

    public function callback(Request $request)
    {
        $data = $request->all();
        Log::info('WayForPay callback', $data);

        if (!$this->wfp->isConfigured()) {
            return response()->json(['error' => 'not configured'], 400);
        }

        if (!$this->wfp->verifyCallback($data)) {
            Log::warning('WayForPay invalid signature', $data);
            return response()->json(['error' => 'invalid signature'], 400);
        }

        $orderRef = $data['orderReference'] ?? null;
        $status   = $data['transactionStatus'] ?? null;

        $payment = Payment::where('notes', 'like', "%ref:{$orderRef}%")->first();

        if ($payment && $status === 'Approved' && $payment->status !== 'paid') {
            $payment->update(['status' => 'paid']);
            $this->balance->creditFromPayment($payment->fresh(['tariffPlan', 'enrollment']));
        }

        $time = now()->timestamp;
        return response()->json(
            $this->wfp->buildCallbackResponse($orderRef, 'accept', (string) $time)
        );
    }
}
