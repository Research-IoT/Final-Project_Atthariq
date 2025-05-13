<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Devices;
use App\Models\Data;

use App\Helpers\Response;

class DeviceController extends Controller
{
    public function info(Request $request)
    {
        $token = $request->bearerToken();

        $device = Devices::with('controller')->where('token', $token)->first();

        if (!$device) {
            return Response::badRequest('Invalid or expired token');
        }

        return Response::success([
            'device' => [
                'serial_catalog'    => $device->serial_catalog,
                'serial_number'     => $device->serial_number,
                'token'      => $device->token,
                'expired_at' => $device->expired_at,
            ],
            'controller'        => $device->controller->controller ?? [],
        ]);
    }

    public function sendData(Request $request)
    {
        $request->validate([
            'token_device' => ['required', 'string'],
            'data'         => ['required', 'array'],
        ]);

        $device = Devices::where('token', $request->token_device)->first();

        if (!$device) {
            return Response::badRequest('Device not found.');
        }

        $now = Carbon::now('Asia/Jakarta');

        $data = Data::create([
            'id_device' => $device->id,
            'data'      => $request->data,
            'year'      => $now->format('Y'),
            'month'     => $now->format('m'),
            'day'       => $now->format('d'),
            'time'      => $now,
        ]);

        return Response::success([
            'data' => $data,
            'time'    => $now->toDateTimeString(),
        ]);
    }

    public function allData(Request $request)
    {
        $request->validate([
            'token_device' => ['required', 'string'],
        ]);

        $device = Devices::where('token', $request->token_device)->first();

        if (!$device) {
            return Response::badRequest('Device not found.');
        }

        $data = Data::where('id_device', $device->id)->get();

        return Response::success($data);
    }

    public function latestData(Request $request)
    {
        $request->validate([
            'token_device' => ['required', 'string'],
        ]);

        $device = Devices::where('token', $request->token_device)->first();

        if (!$device) {
            return Response::badRequest('Device not found.');
        }

        $latestData = Data::where('id_device', $device->id)
            ->latest('time')
            ->first();

        if (!$latestData) {
            return Response::badRequest('No data found for this device.');
        }

        return Response::success($latestData);
    }

    public function dataDay(Request $request)
    {
        $request->validate([
            'token_device' => ['required', 'string'],
            'year'         => ['required', 'digits:4'],
            'month'        => ['required', 'digits_between:1,2'],
            'day'          => ['required', 'digits_between:1,2'],
        ]);

        $device = Devices::where('token', $request->token_device)->first();

        if (!$device) {
            return Response::badRequest('Device not found.');
        }

        $data = Data::where('id_device', $device->id)
            ->where('year', $request->year)
            ->where('month', str_pad($request->month, 2, '0', STR_PAD_LEFT))
            ->where('day', str_pad($request->day, 2, '0', STR_PAD_LEFT))
            ->orderBy('time', 'asc')
            ->get();

        if ($data->isEmpty()) {
            return Response::badRequest('No data found for the specified date.');
        }

        return Response::success($data);
    }

    public function dataWeek(Request $request)
    {
        $request->validate([
            'token_device' => ['required', 'string'],
            'year'         => ['required', 'digits:4'],
            'month'        => ['required', 'digits_between:1,2'],
            'day'          => ['required', 'digits_between:1,2'],
        ]);

        $device = Devices::where('token', $request->token_device)->first();

        if (!$device) {
            return Response::badRequest('Device not found.');
        }

        $dateString = sprintf('%04d-%02d-%02d', $request->year, $request->month, $request->day);

        $endDate = Carbon::parse($dateString)->endOfDay();
        $startDate = Carbon::parse($dateString)->subDays(6)->startOfDay();

        $data = Data::where('id_device', $device->id)
            ->whereBetween('time', [$startDate, $endDate])
            ->orderBy('time', 'asc')
            ->get();

        if ($data->isEmpty()) {
            return Response::badRequest('No data found for the specified week.');
        }

        return Response::success($data);
    }

    public function dataMonth(Request $request)
    {
        $request->validate([
            'token_device' => ['required', 'string'],
            'year'         => ['required', 'digits:4'],
            'month'        => ['required', 'digits_between:1,2'],
        ]);

        $device = Devices::where('token', $request->token_device)->first();

        if (!$device) {
            return Response::badRequest('Device not found.');
        }

        $data = Data::where('id_device', $device->id)
            ->where('year', $request->year)
            ->where('month', str_pad($request->month, 2, '0', STR_PAD_LEFT))
            ->orderBy('time', 'asc')
            ->get();

        if ($data->isEmpty()) {
            return Response::badRequest('No data found for the specified date.');
        }

        return Response::success($data);
    }

    public function dataYear(Request $request)
    {
        $request->validate([
            'token_device' => ['required', 'string'],
            'year'         => ['required', 'digits:4'],
        ]);

        $device = Devices::where('token', $request->token_device)->first();

        if (!$device) {
            return Response::badRequest('Device not found.');
        }

        $data = Data::where('id_device', $device->id)
            ->where('year', $request->year)
            ->orderBy('time', 'asc')
            ->get();

        if ($data->isEmpty()) {
            return Response::badRequest('No data found for the specified date.');
        }

        return Response::success($data);
    }
}
