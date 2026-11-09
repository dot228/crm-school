<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\LessonBalanceService;
use App\Services\PaymentService;
use App\Services\UserService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected UserService $userService,
        protected LessonBalanceService $balance
    ) {}

    public function index(Request $request)
    {
        $filters  = $request->only(['student_id', 'status', 'method']);
        $payments = $this->paymentService->getList($filters);
        return view('payments.index', compact('payments', 'filters'));
    }

    public function create()
    {
        $students    = $this->userService->getStudents();
        $enrollments = Enrollment::with(['student', 'group.course'])->get();
        $methods     = Payment::METHODS;
        $tariffPlans = \App\Models\TariffPlan::where('status', 'active')->get();
        return view('payments.create', compact('students', 'enrollments', 'methods', 'tariffPlans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'    => ['required', 'exists:users,id'],
            'payment_type'  => ['required', 'in:lessons,money'],
            'enrollment_id' => ['required_if:payment_type,lessons', 'nullable', 'exists:enrollments,id'],
            'tariff_plan_id'=> ['nullable', 'exists:tariff_plans,id'],
            'lessons_count' => ['nullable', 'integer', 'min:1'],
            'amount'        => ['required', 'numeric', 'min:0.01'],
            'status'        => ['required', 'in:pending,paid,cancelled,refunded'],
            'method'        => ['required', 'in:cash,card,bank_transfer,online'],
            'payment_date'  => ['required', 'date'],
            'notes'         => ['nullable', 'string'],
        ]);

        $payment = $this->paymentService->create($data);

        if ($payment->status === 'paid') {
            $this->balance->creditFromPayment($payment->load('tariffPlan'));
        }

        return redirect()->route('payments.index')->with('success', 'Платіж створено успішно.');
    }

    public function show(int $id)
    {
        $payment = $this->paymentService->findById($id);
        abort_if(!$payment, 404);
        return view('payments.show', compact('payment'));
    }

    public function edit(int $id)
    {
        $payment     = $this->paymentService->findById($id);
        abort_if(!$payment, 404);
        $enrollments = Enrollment::with(['student', 'group.course'])->get();
        $methods     = Payment::METHODS;
        return view('payments.edit', compact('payment', 'enrollments', 'methods'));
    }

    public function update(Request $request, int $id)
    {
        $payment = $this->paymentService->findById($id);
        abort_if(!$payment, 404);

        $wasPaid = $payment->status === 'paid';

        $data = $request->validate([
            'student_id'    => ['required', 'exists:users,id'],
            'payment_type'  => ['required', 'in:lessons,money'],
            'enrollment_id' => ['required_if:payment_type,lessons', 'nullable', 'exists:enrollments,id'],
            'tariff_plan_id'=> ['nullable', 'exists:tariff_plans,id'],
            'lessons_count' => ['nullable', 'integer', 'min:1'],
            'amount'        => ['required', 'numeric', 'min:0.01'],
            'status'        => ['required', 'in:pending,paid,cancelled,refunded'],
            'method'        => ['required', 'in:cash,card,bank_transfer,online'],
            'payment_date'  => ['required', 'date'],
            'notes'         => ['nullable', 'string'],
        ]);

        $this->paymentService->update($payment, $data);

        if (!$wasPaid && $data['status'] === 'paid') {
            $this->balance->creditFromPayment($payment->fresh(['tariffPlan']));
        }

        return redirect()->route('payments.index')->with('success', 'Платіж оновлено успішно.');
    }

    public function destroy(int $id)
    {
        $payment = $this->paymentService->findById($id);
        abort_if(!$payment, 404);
        $this->paymentService->delete($payment);
        return redirect()->route('payments.index')->with('success', 'Платіж видалено.');
    }
}
