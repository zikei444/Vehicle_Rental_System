@extends('layouts.app')

@section('title', 'Admin - Schedule Maintenance')

@section('content')
<div class="container">
    <h1 class="bg-success text-white p-3 rounded">Schedule Maintenance</h1>

    <!-- Create form: posts to maintenance.store to create a new maintenance record -->
    <form method="post" action="{{ route('maintenance.store') }}">
        @csrf

        <div class="card p-3 shadow-sm">
            <div>
                <label for="vehicle_id" class="form-label">Vehicle</label>
                <select id="vehicle_id" name="vehicle_id" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $v)
                        <option
                        value="{{ $v->id }}"
                        data-brand="{{ $v->brand }}"
                        data-model="{{ $v->model }}"
                        data-year="{{ $v->year_of_manufacture }}"
                        data-reg="{{ $v->registration_number }}"
                        {{ old('vehicle_id') == $v->id ? 'selected' : '' }}
                        >
                        #{{ $v->id }} {{ $v->brand }} {{ $v->model }} ({{ $v->registration_number }})
                        </option>
                    @endforeach
                </select>

                <div class="row g-3 mt-2" id="vehicle-details" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label">Brand</label>
                        <input class="form-control" id="v-brand" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Model</label>
                        <input class="form-control" id="v-model" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Year</label>
                        <input class="form-control" id="v-year" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reg. Number</label>
                        <input class="form-control" id="v-reg" readonly>
                    </div>
                </div>
            </div>
            <br>
            <div>
                <label for="maintenance_type">Type</label>
                <input id="maintenance_type" name="maintenance_type" placeholder="Enter Maintenance Service Type" class="form-control mb-3" required>
            </div>

            <div>
                <label for="service_date">Service Date</label>
                <input id="service_date" type="date" name="service_date" class="form-control mb-3" required>
            </div>

            <div>
                <label for="cost">Cost (RM)</label>
                <input id="cost" type="number" step="any" min="1" name="cost" placeholder="RM 0.00" class="form-control mb-3" required>
            </div>

            <div>
                <label for="notes">Notes</label>
                <input id="notes" name="notes" placeholder="Add remarks for this maintenance (max 500 chars)" class="form-control mb-3">
            </div>
        </div>
        <br>
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">Back to All Maintenance Records</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
  const sel = document.getElementById('vehicle_id');
  const wrap = document.getElementById('vehicle-details');
  const brand = document.getElementById('v-brand');
  const model = document.getElementById('v-model');
  const year  = document.getElementById('v-year');
  const reg   = document.getElementById('v-reg');

  function fillFromOption(opt) {
    if (!opt || !opt.dataset || !opt.value) { wrap.style.display = 'none'; return; }
    brand.value = opt.dataset.brand || '';
    model.value = opt.dataset.model || '';
    year.value  = opt.dataset.year  || '';
    reg.value   = opt.dataset.reg   || '';
    wrap.style.display = 'block';
  }

  sel.addEventListener('change', () => fillFromOption(sel.selectedOptions[0]));
  fillFromOption(sel.selectedOptions[0]); // prefill on load
})();
</script>
@endpush
