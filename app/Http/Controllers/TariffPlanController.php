<?php

namespace App\Http\Controllers;

use App\Models\TariffPlan;
use Illuminate\Http\Request;

class TariffPlanController extends Controller
{
    public function index()
    {
        $plans = TariffPlan::orderBy('amount')->paginate(20);
        return view('tariff-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('tariff-plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'amount'        => 'required|numeric|min:0',
            'count_lessons' => 'required|integer|min:1',
            'status'        => 'required|in:active,inactive',
        ]);

        TariffPlan::create($data);

        return redirect()->route('tariff-plans.index')
            ->with('success', 'Тарифний план створено.');
    }

    public function edit(TariffPlan $tariffPlan)
    {
        return view('tariff-plans.edit', compact('tariffPlan'));
    }

    public function update(Request $request, TariffPlan $tariffPlan)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'amount'        => 'required|numeric|min:0',
            'count_lessons' => 'required|integer|min:1',
            'status'        => 'required|in:active,inactive',
        ]);

        $tariffPlan->update($data);

        return redirect()->route('tariff-plans.index')
            ->with('success', 'Тарифний план оновлено.');
    }

    public function destroy(TariffPlan $tariffPlan)
    {
        $tariffPlan->delete();
        return redirect()->route('tariff-plans.index')
            ->with('success', 'Тарифний план видалено.');
    }
}
