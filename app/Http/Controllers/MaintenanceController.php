<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;
use App\Models\Maintenance;

class MaintenanceController extends Controller
{
    // ---------- LIST ----------
    // GET /maintenance
    public function index(Request $request)
    {
        $vehicleId = $request->query('vehicle_id');

        $records = Maintenance::with('vehicle')
            ->when($vehicleId, fn($q) => $q->where('vehicle_id', $vehicleId))
            ->latest()
            ->paginate(10);

        return view('maintenance.index', ['records' => $records]);
    }

    // ---------- CREATE FORM ----------
    // GET /maintenance/create
    public function create()
    {
        $vehicles = Vehicle::where('availability_status', Vehicle::AVAILABLE)
            ->orderBy('id')->get();

        return view('maintenance.create', compact('vehicles'));
    }

    // ---------- STORE ----------
    // POST /maintenance
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'       => 'required|exists:vehicles,id',
            'maintenance_type' => 'required|string|max:255',
            'service_date'     => 'required|date|after_or_equal:today',
            'cost'             => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if ($vehicle->availability_status !== Vehicle::AVAILABLE) {
            return back()->withErrors(['vehicle_id' => 'This vehicle is not available to schedule maintenance.'])
                        ->withInput();
        }

        $alreadyScheduled = Maintenance::where('vehicle_id', $vehicle->id)
        ->where('status', 'Scheduled')
        ->exists();

        if ($alreadyScheduled) {
            return back()->withErrors(['vehicle_id' => 'This vehicle already has a scheduled maintenance.'])
                        ->withInput();
        }

        DB::transaction(function () use ($request, $vehicle) {
            // State Pattern: Available -> Under Maintenance
            $vehicle->getState()->markAsUnderMaintenance();

            Maintenance::create([
                'vehicle_id'       => $vehicle->id,
                // 'admin_id'         => auth('admin')->id() ?? null,
                'maintenance_type' => $request->maintenance_type,
                'service_date'     => $request->service_date,
                'cost'             => $request->cost,
                'notes'            => $request->notes,
                'status'           => 'Scheduled',
            ]);
        });

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance scheduled');
    }

    // ---------- EDIT FORM ----------
    // GET /maintenance/{maintenance}/edit
    public function edit(Maintenance $maintenance)
    {
        $vehicles = Vehicle::orderBy('id')->get();
        return view('maintenance.edit', compact('maintenance', 'vehicles'));
    }

    // ---------- UPDATE ----------
    // PUT /maintenance/{maintenance}
    public function update(Request $request, Maintenance $maintenance)
    {
        $request->validate([
            'maintenance_type' => 'required|string|max:255',
            'service_date'     => 'required|date',
            'status'           => 'required|in:Scheduled,Completed,Cancelled',
            'cost'             => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        $vehicle = $maintenance->vehicle;
        $fromStatus = $maintenance->status;
        $toStatus   = $request->status;

        DB::transaction(function () use ($request, $maintenance, $vehicle, $fromStatus, $toStatus) {
            $maintenance->fill([
                'maintenance_type' => $request->maintenance_type,
                'service_date'     => $request->service_date,
                'status'           => $toStatus,
                'cost'             => $request->cost,
                'notes'            => $request->notes,
            ]);

            // completed_at handling
            if ($fromStatus !== 'Completed' && $toStatus === 'Completed') {
                $maintenance->completed_at = now();
            } elseif ($fromStatus === 'Completed' && $toStatus !== 'Completed') {
                $maintenance->completed_at = null; // optional: clear if moved away from Completed
            }

            $maintenance->save();

            // State transitions
            if (in_array($toStatus, ['Completed', 'Cancelled'])) {
                if ($vehicle->availability_status === Vehicle::UNDER_MAINTENANCE) {
                    $vehicle->getState()->markAsAvailable();
                }
            } elseif ($toStatus === 'Scheduled') {
                if ($vehicle->availability_status !== Vehicle::UNDER_MAINTENANCE) {
                    $vehicle->getState()->markAsUnderMaintenance();
                }
            }
        });

        return back()->with('ok', 'Maintenance updated');
    }

    // ---------- DELETE ----------
    // DELETE /maintenance/{maintenance}
    public function destroy(Maintenance $maintenance)
    {
        $vehicle = $maintenance->vehicle;
        $wasScheduled = $maintenance->status === 'Scheduled';

        $maintenance->delete();

        // If that was the only scheduled maintenance, release the vehicle
        if ($wasScheduled) {
            $stillScheduled = Maintenance::where('vehicle_id', $vehicle->id)
                ->where('status', 'Scheduled')->exists();

            if (!$stillScheduled && $vehicle->availability_status === Vehicle::UNDER_MAINTENANCE) {
                $vehicle->getState()->markAsAvailable();
            }
        }

        return redirect()->route('maintenance.index')->with('ok', 'Maintenance deleted');
    }
}