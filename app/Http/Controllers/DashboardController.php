<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $loans = Loan::with('installments')->get();
        // $loans = Loan::with('installments')->paginate(20);

        $chartLabels = [];
        $chartData = [];

        // Example: sum of paid installments per day
        $payments = \DB::table('installments')
            ->selectRaw('DATE(due_date) as date, SUM(amount) as total_paid')
            ->where('status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($payments as $p) {
            $chartLabels[] = $p->date;
            $chartData[] = $p->total_paid;
        }

        return view('dashboard', compact('loans', 'chartLabels', 'chartData'));
    }
}
