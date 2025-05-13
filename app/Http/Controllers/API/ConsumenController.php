<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Consumen;
use App\Models\Devices;
use App\Models\Mapping\ConsumenDevice;
use App\Models\Controller as DeviceController;

use App\Helpers\Response;

class ConsumenController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:consumen,username'],
            'password' => ['required', 'string', 'min:6'],
            'address'  => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
        ]);

        $token = Str::random(60);
        $expiredAt = Carbon::tomorrow()->startOfDay();

        $consumen = Consumen::create([
            'name'     => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'address'  => $request->address,
            'phone'    => $request->phone,
            'token'    => $token,
            'expired_at' => $expiredAt,
        ]);

        return Response::success([
            'id'       => $consumen->id,
            'name'     => $consumen->name,
            'username' => $consumen->username,
            'address'  => $consumen->address,
            'phone'    => $consumen->phone,
            'token'    => $consumen->token,
            'expired_at' => $consumen->expired_at,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $consumen = Consumen::where('username', $request->username)->first();

        if (!$consumen || !Hash::check($request->password, $consumen->password)) {
            return Response::badRequest('Invalid credentials');
        }

        $token = Str::random(60);
        $expiredAt = Carbon::tomorrow()->startOfDay();

        $consumen->update([
            'token'      => $token,
            'expired_at' => $expiredAt,
        ]);

        return Response::success([
            'token'         => $consumen->token,
            'expired_at'    => $consumen->expired_at,
        ]);
    }

    public function profile(Request $request)
    {
        $token = $request->bearerToken();

        $consumen = Consumen::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$consumen) {
            return Response::badRequest('Invalid or expired token');
        }

        return Response::success([
            'id'            => $consumen->id,
            'name'          => $consumen->name,
            'username'      => $consumen->username,
            'address'       => $consumen->address,
            'phone'         => $consumen->phone,
            'token'         => $consumen->token,
            'expired_at'    => $consumen->expired_at,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        $consumen = Consumen::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$consumen) {
            return Response::badRequest('Invalid or expired token');
        }

        $consumen->update([
            'token' => null,
            'expired_at' => null,
        ]);

        return Response::success('Logout successful');
    }

    public function deviceList(Request $request)
    {
        $token = $request->bearerToken();

        $consumen = Consumen::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->with('devices')
            ->first();

        $devices = $consumen->devices->map(function ($device) {
            return [
                'serial_catalog' => $device->serial_catalog,
                'serial_number' => $device->serial_number,
                'token' => $device->token,
                'controller' => $device->controller?->controller,
            ];
        });

        return Response::success($devices);
    }


    public function deviceAddBySerial(Request $request)
    {
        $request->validate([
            'serial_number' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->bearerToken();

        $consumen = Consumen::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$consumen) {
            return Response::badRequest('Invalid or expired token');
        }

        $device = Devices::where('serial_number', $request->serial_number)->first();

        if (!$device) {
            return Response::badRequest('Device not found');
        }

        $existing = ConsumenDevice::where('id_consumen', $consumen->id)
            ->where('id_device', $device->id)
            ->first();

        if ($existing) {
            return Response::badRequest('Device already linked to this consumen');
        }

        ConsumenDevice::create([
            'id_consumen' => $consumen->id,
            'id_device'   => $device->id,
            'added_at'    => Carbon::now(),
        ]);

        return Response::success([
            'device'  => [
                'serial_catalog' => $device->serial_catalog,
                'serial_number'  => $device->serial_number,
                'token'          => $device->token,
            ],
            'controller'     => $device->controller?->controller,
        ]);
    }

    public function deviceAddByToken(Request $request)
    {
        $tokenDevice = $request->query('tokenDevice');

        $token = $request->bearerToken();

        $consumen = Consumen::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$consumen) {
            return Response::badRequest('Invalid or expired token');
        }

        $device = Devices::where('token', $tokenDevice)->first();

        if (!$device) {
            return Response::badRequest('Device not found');
        }

        $existing = ConsumenDevice::where('id_consumen', $consumen->id)
            ->where('id_device', $device->id)
            ->first();

        if ($existing) {
            return Response::badRequest('Device already linked to this consumen');
        }

        ConsumenDevice::create([
            'id_consumen' => $consumen->id,
            'id_device'   => $device->id,
            'added_at'    => Carbon::now(),
        ]);

        return Response::success([
            'device'  => [
                'serial_catalog' => $device->serial_catalog,
                'serial_number'  => $device->serial_number,
                'token'          => $device->token,
            ],
            'controller'     => $device->controller?->controller,
        ]);
    }

    public function deviceRemove(Request $request)
    {
        $request->validate([
            'token_device'    => ['nullable', 'string', 'max:255'],
            'serial_number'  => ['nullable', 'string', 'max:255'],
        ]);

        if (!$request->tokenDevice && !$request->serial_number) {
            return Response::badRequest('Either tokenDevice or serial_number is required');
        }

        $token = $request->bearerToken();

        $consumen = Consumen::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$consumen) {
            return Response::badRequest('Invalid or expired token');
        }

        $device = Devices::when($request->tokenDevice, function ($query) use ($request) {
            return $query->where('token', $request->tokenDevice);
        })
            ->when($request->serial_number, function ($query) use ($request) {
                return $query->where('serial_number', $request->serial_number);
            })
            ->first();

        if (!$device) {
            return Response::badRequest('Device not found');
        }

        $deleted = ConsumenDevice::where('id_consumen', $consumen->id)
            ->where('id_device', $device->id)
            ->delete();

        if ($deleted === 0) {
            return Response::badRequest('Mapping not found or already deleted');
        }

        return Response::success('Device removed successfully');
    }


    public function controlInfo(Request $request)
    {
        $tokenDevice = $request->query('tokenDevice');

        $token = $request->bearerToken();

        $consumen = Consumen::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$consumen) {
            return Response::badRequest('Invalid or expired token');
        }

        $device = Devices::where('token', $tokenDevice)->first();

        if (!$device) {
            return Response::badRequest('Device not found');
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

    public function controlChange(Request $request)
    {
        try {
            $token = $request->bearerToken();

            $admin = Consumen::where('token', $token)
                ->where('expired_at', '>', Carbon::now())
                ->first();

            if (!$admin) {
                return Response::badRequest('Invalid or expired token');
            }

            $request->validate([
                'serial_catalog' => ['required', 'string', 'max:255'],
                'serial_number'  => ['required', 'string', 'max:255'],
                'token_device'   => ['required', 'string'],
                'controller'     => ['nullable', 'array'],
            ]);

            $device = Devices::where('token', $request->token_device)
                ->where('serial_catalog', $request->serial_catalog)
                ->where('serial_number', $request->serial_number)
                ->first();

            if (!$device) {
                return Response::badRequest('Device not found.');
            }

            $newController = $request->controller ?? [];

            $deviceController = DeviceController::where('id_device', $device->id)->first();

            if ($deviceController) {
                $oldController = $deviceController->controller ?? [];

                $oldKeys = array_keys($oldController);
                $newKeys = array_keys($newController);

                sort($oldKeys);
                sort($newKeys);

                if ($oldKeys !== $newKeys) {
                    return Response::badRequest('Controller structure mismatch. Update denied.');
                }

                $deviceController->update([
                    'controller'  => $newController,
                    'modified_at' => Carbon::now(),
                ]);
            } else {
                $deviceController = DeviceController::create([
                    'id_device'   => $device->id,
                    'controller'  => $newController,
                    'modified_at' => Carbon::now(),
                ]);
            }

            return Response::success([
                'device' => [
                    'serial_catalog' => $device->serial_catalog,
                    'serial_number'  => $device->serial_number,
                    'token'          => $device->token,
                    'modified_by'    => $device->modified_by,
                    'modified_at'    => $device->modified_at,
                ],
                'controller' => $deviceController->controller ?? [],
            ]);
        } catch (ValidationException $e) {
            return Response::badRequest($e->validator->errors()->first());
        }
    }
}
