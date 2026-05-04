<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $validator = $this->validateApiKey($request);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $seller = $this->getSeller($request->api_key);

        if ($seller) {
            $products = ProductResource::collection($seller->products);
            if ($products->count() > 1) {
                return response()->json([
                    'status' => translate('success'),
                    'products' => $products,
                ], 200);
            }
        }

        return response()->json([
            'status' => translate('error'),
            'msg' => translate('No products Found'),
        ], 404);
    }

    public function product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'api_key' => ['required', 'string'],
            'product_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $seller = $this->getSeller($request->api_key);

        if ($seller) {
            $product = $seller->products()->approved()->find($request->product_id);
            if ($product) {
                return response()->json([
                    'status' => translate('success'),
                    'product' => new productResource($product),
                ], 200);
            }
        }

        return response()->json([
            'status' => translate('error'),
            'msg' => translate('product Not Found'),
        ], 404);
    }

    private function validateApiKey(Request $request)
    {
        return Validator::make($request->all(), [
            'api_key' => ['required', 'string'],
        ]);
    }

    private function validationErrorResponse($validator)
    {
        return response()->json([
            'status' => translate('error'),
            'msg' => $validator->errors()->first(),
        ], 400);
    }

    private function getSeller($apiKey)
    {
        return User::where('api_key', $apiKey)
            ->seller()->with(['products' => function ($query) {
            return $query->orderbyDesc('id')->approved();
        }])->first();
    }
}


















