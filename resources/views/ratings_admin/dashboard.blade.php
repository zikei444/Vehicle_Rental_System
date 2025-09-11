@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Admin Dashboard - Vehicle Ratings</h2>
        <a href="{{ route('ratings_admin.index') }}" class="btn btn-primary btn-sm">Manage Ratings</a>
    </div>

    <!-- Chart Container -->
    <div style="height: 250px; width: 100%; margin-bottom: 50px;">
        <canvas id="ratingsChart"></canvas>
    </div>

    <!-- Vehicle Ratings Table -->
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Average Rating</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $vehicle)
                @if($vehicle)
                <tr>
                    <td>{{ $vehicle->brand ?? 'Unknown' }} {{ $vehicle->model ?? '' }}</td>
                    <td>{{ number_format($vehicle->average_rating ?? 0, 2) }} ⭐</td>
                    <td>
                        <a href="{{ route('ratings_admin.details', $vehicle->id) }}" class="btn btn-info btn-sm">Details</a>
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
<div class="container my-5">
    <h2>Notifications Center</h2>
    <p>New ratings will appear as toast notifications at the bottom right.</p>
</div>

<!-- Toast container
<div aria-live="polite" aria-atomic="true" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="toast-container"></div>
</div> -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('ratingsChart').getContext('2d');

const labels = @json($vehicles->map(fn($v) => $v->brand . ' ' . $v->model));
const dataPoints = @json($vehicles->pluck('average_rating'));

const data = {
    labels: labels,
    datasets: [{
        label: 'Average Rating',
        data: dataPoints,
        backgroundColor: 'rgba(54, 162, 235, 0.6)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

const config = {
    type: 'bar',
    data: data,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 5
            },
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 45,
                    minRotation: 0
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.parsed.y} ⭐`;
                    }
                }
            }
        }
    }
};

new Chart(ctx, config);
</script>


<!-- <script>
// 显示 toast
function showToast(message, url = '#') {
    const toastContainer = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-bg-primary border-0 show';
    toast.role = 'alert';
    toast.ariaLive = 'assertive';
    toast.ariaAtomic = 'true';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"></button>
        </div>
    `;
    toast.querySelector('.btn-close').addEventListener('click', () => toast.remove());
    toast.addEventListener('click', () => {
        if (url) window.location.href = url;
    });

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 5000); // 5 秒后消失
}

// 每 5 秒轮询新通知
setInterval(() => {
    fetch('{{ route("admin.notifications.unread") }}')
        .then(res => res.json())
        .then(data => {
            data.forEach(n => showToast(n.message, n.url));
        });
}, 5000);
</script> -->
@endsection
