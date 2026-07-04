<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VisitationSchedule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class VisitationScheduleController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prisoner_name' => 'required',
            'prisoner_birthday' => 'required',
            'prisoner_address' => 'required',
            'visitDate' => 'required|date',
            'visitTime' => ['required', 'date_format:H:i'],
            'count' => 'required|integer|min:1|max:3',
            // 'prisoner_id' => 'required|integer|exists:prisoners,id'
        ], [
            'count.integer' => 'count phải là số',
            'count.min' => 'count tối thiểu là 1',
            'count.max' => 'count tối đa là 3',
            'visitTime.date_format' => 'visitTime phải có định dạng HH:mm',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $visitDate = Carbon::parse($request->visitDate);
        $today = Carbon::today();

        $visitDateTime = Carbon::parse(
            $request->visitDate . ' ' . $request->visitTime,
            'Asia/Ho_Chi_Minh'
        );

        $now = Carbon::now('Asia/Ho_Chi_Minh');

        if ($visitDateTime->lte($now->copy()->addMinutes(30))) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng ký trước giờ thăm ít nhất 30 phút'
            ], 422);

        }

        // 2. Không quá 2 tháng kể từ hôm nay
        if ($visitDate->gt($today->copy()->addMonths(2))) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ được đăng ký lịch trong vòng 2 tháng tới'
            ], 422);
        }

        // 3. Không được là Chủ nhật
        // if ($visitDate->isSunday()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Không được đăng ký lịch thăm vào Chủ nhật'
        //     ], 422);
        // }

        // 5. Mỗi phạm nhân chỉ được gặp 1 lần trong tháng
        $exists = VisitationSchedule::whereHas('translations', function ($query) use ($request) {
                $query->where('locale', 'en')
                    ->where('title', $request->prisoner_name);
            })
            ->whereYear('visitDate', $visitDate->year)
            ->whereMonth('visitDate', $visitDate->month)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Phạm nhân này đã có lịch thăm trong tháng'
            ], 422);
        }

        // $bookedCount = VisitationSchedule::query()
        //     ->published()
        //     ->whereDate('visitDate', $request->visitDate)
        //     ->where('visitTime', $request->visitTime)
        //     ->count();

        // if ($bookedCount >= 9) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Khung giờ này đã đủ 9 lượt đăng ký, vui lòng chọn khung giờ khác.'
        //     ], 422);
        // }

        try {
            $schedule = VisitationSchedule::create([
                'visitDate' => $request->visitDate,
                'visitTime' => $request->visitTime,
                'visitEndTime' => $request->visitEndTime,
                'pt' => $request->pt,
                'visitGroup' => $request->visitGroup,
                'childVisitGroup' => $request->childVisitGroup,
                'identification' => $request->identification,
                'reason' => $request->reason,
                'count' => $request->count,
                'title' => $request->prisoner_name,
                'prisoner_name' => $request->prisoner_name,
                'prisoner_sex' => $request->prisoner_sex,
                'prisoner_birthday' => $request->prisoner_birthday,
                'prisoner_address' => $request->prisoner_address,
                'relatives' => $request->relatives,
                'customer_id' => $request->user()->id,
            ]);

            $token = Str::uuid()->toString();
            $schedule->update([
                'qr_token' => $token
            ]);

            $qrCode = (string) QrCode::format('svg')
                    ->size(300)
                    ->generate($token);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký lịch thăm thành công',
                'data' => $schedule,
                'qr_code' =>  'data:image/svg+xml;base64,' . base64_encode($qrCode)
            ], 201);

        } catch (\Exception $e) {

            \Log::error($e);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo lịch thăm' . $e
            ], 500);
        }
    }

    public function verify($token)
    {
        $schedule = VisitationSchedule::where('qr_token', $token)->first();

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'QR không hợp lệ'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }

     public function list(Request $request)
    {
        $schedules = VisitationSchedule::where('customer_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($schedules);
    }

     public function nearest(Request $request)
    {
        $schedule = VisitationSchedule::published()
            ->where('customer_id', $request->user()->id)
            ->whereDate('visitDate', '>=', Carbon::today())
            ->orderBy('visitDate', 'asc')
            ->get();

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Không có lịch thăm sắp tới.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy lịch thăm thành công.',
            'data' => $schedule,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $schedule = VisitationSchedule::findOrFail($id);

        $schedule->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công',
            'data' => $schedule,
        ]);
    }
}
