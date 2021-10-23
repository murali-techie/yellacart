<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PincodeController extends Controller
{
    public function verify(Request $request)
    {
        session()->put('pincode', $request->get('code'));

        return response()->json(['message' => 'success']);
    }
}