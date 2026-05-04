<?php

namespace App\Http\Controllers\UserPanel\Tools;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LicenseVerificationController extends Controller
{
    public function index()
    {
        return theme_view('userpanel.tools.license-verification');
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back()->withInput();
        }

        $purchase = Purchase::where('seller_id', authUser()->id)
            ->where('code', $request->purchase_code)
            ->active()
            ->with('product')
            ->first();

        if (!$purchase) {
            toastr()->error(translate('Invalid purchase code'));
            return back();
        }

        toastr()->success(translate('Purchase code is valid'));
        return back()->with('purchase', $purchase)
            ->withInput();
    }
}


















