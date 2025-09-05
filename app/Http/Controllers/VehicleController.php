<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    /**
     * 显示所有车辆（返回 Blade 页面）
     */
    public function index()
    {
        $vehicles = Vehicle::all();
        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * 显示单个车辆详情（返回 Blade 页面）
     */
    public function show($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return redirect()->back()->with('error', 'Vehicle not found.');
        }

        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * 选择某辆车，跳转到预订流程
     */
    public function select($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return redirect()->back()->with('error', 'Vehicle not found.');
        }

        // 假设你有一个 reservation.process 路由
        return redirect()->route('reservation.process', ['vehicle_id' => $id]);
    }
}
