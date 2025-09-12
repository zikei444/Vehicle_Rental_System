<!-- STUDENT NAME: Kek Xin Ying
STUDENT ID: 23WMR14547 -->

@extends('layouts.app')

@section('content')
<div class="container my-5">
    <h2>为 {{ $vehicle->brand ?? '车辆' }} {{ $vehicle->model ?? '' }} 评分</h2>

    <div id="success-message" class="alert alert-success d-none"></div>
    <div id="error-message" class="alert alert-danger d-none"></div>

    <form id="rating-form">
        <input type="hidden" id="vehicle_id" value="{{ $vehicle->id }}">
        <input type="hidden" id="customer_id" value="{{ auth()->id() }}"> <!-- 登录用户 ID -->
        <div class="mb-3">
            <label for="rating">评分 (1-5)</label>
            <input type="number" id="rating" name="rating" min="1" max="5" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="feedback">评论（可选）</label>
            <textarea id="feedback" name="feedback" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">提交评分</button>
    </form>
</div>

<script>
document.getElementById('rating-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const vehicleId = document.getElementById('vehicle_id').value;
    const customerId = document.getElementById('customer_id').value;
    const rating = document.getElementById('rating').value;
    const feedback = document.getElementById('feedback').value;

    fetch(`/api/vehicles/${vehicleId}/ratings`, {  // 调整为 API 路由
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            // API 路由不需要 CSRF
        },
        body: JSON.stringify({
            customer_id: customerId,
            rating: rating,
            feedback: feedback
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            document.getElementById('error-message').textContent = data.error;
            document.getElementById('error-message').classList.remove('d-none');
            document.getElementById('success-message').classList.add('d-none');
        } else {
            document.getElementById('success-message').textContent = '评分提交成功！';
            document.getElementById('success-message').classList.remove('d-none');
            document.getElementById('error-message').classList.add('d-none');
            document.getElementById('rating').value = '';
            document.getElementById('feedback').value = '';
        }
    })
    .catch(err => {
        document.getElementById('error-message').textContent = '提交失败，请重试';
        document.getElementById('error-message').classList.remove('d-none');
        document.getElementById('success-message').classList.add('d-none');
    });
});
</script>
@endsection
