<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:customers,email'],
        'password' => ['required', 'min:6', 'confirmed'],
    ], [
        'name.required' => 'Vui lòng nhập họ tên',
        'email.required' => 'Vui lòng nhập email',
        'email.email' => 'Email không đúng định dạng',
        'email.unique' => 'Email đã được sử dụng',
        'password.required' => 'Vui lòng nhập mật khẩu',
        'password.min' => 'Mật khẩu tối thiểu 6 ký tự',
        'password.confirmed' => 'Xác nhận mật khẩu không khớp',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $validator->errors(),
        ], 422);
    }

    $customer = Customer::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'is_active' => true,
    ]);

    $token = $customer->createToken('customer-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Đăng ký thành công',
        'token' => $token,
        'customer' => $customer,
    ], 201);
}

    public function login(Request $request)
    {
       
        $zaloToken = $request->access_token;

        // Call Zalo API lấy profile

        $proof = hash_hmac(
            'sha256',
            $zaloToken,
            config('services.zalo.app_secret')
        );
        $profile = Http::get(
            'https://graph.zalo.me/v2.0/me',
            [
                'access_token' => $zaloToken,
                'appsecret_proof' => $proof,
            ]
        )->json();

        $customer = Customer::firstOrCreate(
            ['zalo_id' => $profile['id']],
            [
                'name' => $profile['name'] ?? null,
                'role' => "CUSTOMER"
            ]
        );

        $token = $customer->createToken('miniapp')->plainTextToken;

        return response()->json([
            'token' => $token,
            'customer' => $customer,
        ]);
    }

    public function info(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user,
        ]);
    }
}
