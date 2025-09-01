@extends('layouts.app')

@section('content')
<h1>Select a Vehicle</h1>

<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Type</th>
            <th>Brand</th>
            <th>Model</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($vehicles as $v)
            <tr>
                <td>{{ $v['type'] }}</td>
                <td>{{ $v['brand'] }}</td>
                <td>{{ $v['model'] }}</td>
                <td>{{ $v['rental_price'] }}</td>
                <td>{{ $v['availability_status'] }}</td>
                <td>
                    <!-- this part link to reservation section (after use select a vahicle) -->
                    @if($v['availability_status'] === 'available')
                        <a href="{{ url('vehicles/select/' . $v['id']) }}">Choose</a>
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection