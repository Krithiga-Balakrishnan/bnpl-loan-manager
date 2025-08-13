<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;

class DashboardController extends Controller
{
    //
    public function index()
    {
        // $loans = Loan::with('installments')->get();
        $loans = Loan::with('installments')->paginate(20);

        return view('dashboard', compact('loans'));
    }
}
