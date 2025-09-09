@extends('layouts.app')

@section('content')
<h2>All Ratings</h2>

<div id="alert-container"></div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Vehicle</th>
            <th>Rating</th>
            <th>Feedback</th>
            <th>Status</th>
            <th>Admin Reply</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ratings as $rating)
        <tr id="rating-{{ $rating->id }}">
            <td>{{ $rating->customer->username }}</td>
            <td>{{ $rating->vehicle->brand }} {{ $rating->vehicle->model }}</td>
            <td>{{ $rating->rating }} ⭐</td>
            <td>{{ $rating->feedback }}</td>
            <td class="status">{{ ucfirst($rating->status) }}</td>
            <td class="reply">
                @if($rating->admin_reply)
                    {{ $rating->admin_reply }}
                @else
                    <input type="text" class="form-control form-control-sm reply-input" 
                           placeholder="Reply to user" data-id="{{ $rating->id }}">
                    <button class="btn btn-primary btn-sm mt-1 reply-btn" data-id="{{ $rating->id }}">Send</button>
                @endif
            </td>
            <td class="actions">
                @if($rating->status === 'pending')
                    <button class="btn btn-success btn-sm approve-btn" data-id="{{ $rating->id }}">Approve</button>
                    <button class="btn btn-danger btn-sm reject-btn" data-id="{{ $rating->id }}">Reject</button>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){

    function showAlert(message, type='success') {
        $('#alert-container').html(`<div class="alert alert-${type}">${message}</div>`);
        setTimeout(()=>$('#alert-container').html(''), 3000);
    }

    // Approve
    $('.approve-btn').click(function(){
        const id = $(this).data('id');
        $.post(`/admin/ratings/${id}/approve`, {_token:'{{ csrf_token() }}', status:'approved'}, function(){
            $(`#rating-${id} .status`).text('Approved');
            $(`#rating-${id} .actions`).html('');
            showAlert('Rating approved!');
        });
    });

    // Reject
    $('.reject-btn').click(function(){
        const id = $(this).data('id');
        $.post(`/admin/ratings/${id}/approve`, {_token:'{{ csrf_token() }}', status:'rejected'}, function(){
            $(`#rating-${id} .status`).text('Rejected');
            $(`#rating-${id} .actions`).html('');
            showAlert('Rating rejected!');
        });
    });

    // Reply
    $('.reply-btn').click(function(){
        const id = $(this).data('id');
        const input = $(`#rating-${id} .reply-input`);
        const reply = input.val().trim();
        if(!reply) return;

        $.post(`/admin/ratings/${id}/reply`, {_token:'{{ csrf_token() }}', reply: reply}, function(){
            // 显示回复
            $(`#rating-${id} .reply`).text(reply);
            // 禁用输入框和按钮，防止重复回复
            input.prop('disabled', true);
            $(`#rating-${id} .reply-btn`).prop('disabled', true);
            showAlert('Reply sent!');
        });
    });

    // 页面加载时检查已回复的 rating
    $('.reply-input').each(function(){
        const id = $(this).data('id');
        if($(this).val().trim() !== '') {
            $(this).prop('disabled', true);
            $(`#rating-${id} .reply-btn`).prop('disabled', true);
        }
    });

});
</script>
@endsection
