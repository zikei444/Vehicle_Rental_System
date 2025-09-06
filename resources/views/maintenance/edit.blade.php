@extends('layouts.app')

@section('content')
    <h1>Edit Maintenance #{{ $maintenance->id }}</h1>

    @if (session('ok'))
        <div style="color:green">{{ session('ok') }}</div>
    @endif

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $e)
                    <li style="color:red">{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p>Vehicle: #{{ $maintenance->vehicle_id }}</p>

    <form method="post" action="{{ route('maintenance.update', $maintenance) }}">
        @csrf
        @method('put')

        <div>
            <label for="status">Status</label>
            <select id="status" name="status" required>
                @foreach (['Scheduled','Completed','Cancelled'] as $s)
                    <option value="{{ $s }}" {{ old('status', $maintenance->status) === $s ? 'selected' : '' }}>
                        {{ $s }}
                    </option>
                @endforeach
            </select>
            @error('status') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="cost">Cost</label>
            <input id="cost" type="number" step="0.01" min="0" name="cost"
                   value="{{ old('cost', $maintenance->cost) }}">
            @error('cost') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <div>
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes">{{ old('notes', $maintenance->notes) }}</textarea>
            @error('notes') <div style="color:red">{{ $message }}</div> @enderror
        </div>

        <button type="submit">Update</button>
        <a href="{{ route('maintenance.index') }}">Back</a>
    </form>
@endsection