<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Admin;
use App\Models\Devices;
use App\Models\Controller as DeviceController;

use App\Helpers\Response;

class AdminController extends Controller
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

        $admin = Admin::create([
            'name'     => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'address'  => $request->address,
            'phone'    => $request->phone,
            'token'    => $token,
            'expired_at' => $expiredAt,
        ]);

        return Response::success([
            'id'       => $admin->id,
            'name'     => $admin->name,
            'username' => $admin->username,
            'address'  => $admin->address,
            'phone'    => $admin->phone,
            'token'    => $admin->token,
            'expired_at' => $admin->expired_at,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return Response::badRequest('Invalid credentials');
        }

        $token = Str::random(60);
        $expiredAt = Carbon::tomorrow()->startOfDay();

        $admin->update([
            'token'      => $token,
            'expired_at' => $expiredAt,
        ]);

        return Response::success([
            'token'         => $token,
            'expired_at'    => $admin->expired_at,
        ]);
    }

    public function profile(Request $request)
    {
        $token = $request->bearerToken();

        $admin = Admin::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$admin) {
            return Response::badRequest('Invalid or expired token');
        }

        return Response::success([
            'id'            => $admin->id,
            'name'          => $admin->name,
            'username'      => $admin->username,
            'address'       => $admin->address,
            'phone'         => $admin->phone,
            'token'         => $admin->token,
            'expired_at'    => $admin->expired_at,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        $admin = Admin::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$admin) {
            return Response::badRequest('Invalid or expired token');
        }

        $admin->update([
            'token' => null,
            'expired_at' => null,
        ]);

        return Response::success('Logout successful');
    }

    public function deviceAll(Request $request)
    {
        $token = $request->bearerToken();

        $admin = Admin::where('token', $token)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$admin) {
            return Response::badRequest('Invalid or expired token');
        }

        $devices = Devices::with('controller')->get()->map(function ($device) {
            return [
                'id'             => $device->id,
                'serial_catalog' => $device->serial_catalog,
                'serial_number'  => $device->serial_number,
                'token'          => $device->token,
                'modified_by'    => $device->modified_by,
                'modified_at'    => $device->modified_at,
                'controller'     => $device->controller?->controller ?? [],
            ];
        });

        return Response::success($devices);
    }

    public function deviceRegister(Request $request)
    {
        try {
            $token = $request->bearerToken();

            $admin = Admin::where('token', $token)
                ->where('expired_at', '>', Carbon::now())
                ->first();

            $request->validate([
                'serial_catalog' => ['required', 'string', 'max:255'],
                'serial_number'  => ['required', 'string', 'max:255', 'unique:devices,serial_number'],
                'controller'     => ['nullable', 'array'],
            ]);

            $device = Devices::create([
                'serial_catalog' => $request->serial_catalog,
                'serial_number'  => $request->serial_number,
                'token'          => hash('sha256', Str::random(128)),
                'modified_by'    => $admin->name,
                'modified_at'    => Carbon::now(),
            ]);

            $deviceController =  DeviceController::create([
                'id_device'   => $device->id,
                'controller'  => $request->controller ?? [],
                'modified_at' => Carbon::now(),
            ]);

            return Response::success([
                'device' => [
                    'serial_catalog' => $device->serial_catalog,
                    'serial_number'  => $device->serial_number,
                    'token'          => $device->token,
                    'modified_by'    => $device->modified_by,
                    'modified_at'    => $device->modified_at,
                ],
                'controller'         => $deviceController->controller ?? [],
            ]);
        } catch (ValidationException $e) {
            return Response::badRequest($e->validator->errors()->first());
        }
    }

    public function deviceRemove(Request $request)
    {
        try {
            $request->validate([
                'token_device'    => ['required', 'string'],
                'serial_catalog'  => ['required', 'string', 'max:255'],
                'serial_number'   => ['required', 'string', 'max:255'],
            ]);

            $device = Devices::where('token', $request->token_device)
                ->where('serial_catalog', $request->serial_catalog)
                ->where('serial_number', $request->serial_number)
                ->first();

            if (!$device) {
                return Response::badRequest('Invalid token or device not found.');
            }

            $device->delete();

            return Response::success('Device removed successfully');
        } catch (ValidationException $e) {
            return Response::badRequest($e->validator->errors()->first());
        }
    }

    public function controlUpdate(Request $request)
    {
        try {
            $token = $request->bearerToken();

            $admin = Admin::where('token', $token)
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

            $deviceController = DeviceController::where('id_device', $device->id)->first();

            if ($deviceController) {
                $deviceController->update([
                    'controller'  => $request->controller ?? [],
                    'modified_at' => Carbon::now(),
                ]);
            } else {
                $deviceController = DeviceController::create([
                    'id_device'   => $device->id,
                    'controller'  => $request->controller ?? [],
                    'modified_at' => Carbon::now(),
                ]);

                return Response::success([
                    'device' => [
                        'serial_catalog' => $device->serial_catalog,
                        'serial_number'  => $device->serial_number,
                        'token'          => $device->token,
                        'modified_by'    => $device->modified_by,
                        'modified_at'    => $device->modified_at,
                    ],
                    'controller'         => $deviceController->controller ?? [],
                ]);
            }

            return Response::success([
                'serial_catalog' => $device->serial_catalog,
                'serial_number'  => $device->serial_number,
                'controller'     => $deviceController->controller ?? [],
            ]);
        } catch (ValidationException $e) {
            return Response::badRequest($e->validator->errors()->first());
        }
    }
}
