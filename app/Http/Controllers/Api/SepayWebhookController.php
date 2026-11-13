<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommerceService;
use Illuminate\Http\Request;

class SepayWebhookController extends Controller
{
    public function __invoke(Request $request, CommerceService $service)
    {
        $key = config('commerce.webhook_key');
        abort_unless(is_string($key) && $key !== '' && hash_equals('Apikey '.$key, (string) $request->header('Authorization')), 401);
        $data = $request->validate([
            'id' => 'required|integer|min:1', 'accountNumber' => 'required|string|max:100',
            'transferType' => 'required|in:in,out', 'transferAmount' => 'required|integer|min:1|max:9000000000000000',
            'code' => 'nullable|string|max:100', 'content' => 'nullable|string|max:10000',
            'gateway' => 'nullable|string|max:100', 'subAccount' => 'nullable|string|max:100',
            'transactionDate' => 'nullable|string|max:100', 'referenceCode' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:10000',
        ]);
        $service->receive($data);

        return response()->json(['success' => true], 201);
    }
}
