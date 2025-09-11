@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>All Ratings</h2>
                <a href="{{ route('ratings_admin.dashboard') }}" class="btn btn-secondary mb-3">Back to Dashboard</a>
    </div>
<div id="alert-container"></div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Vehicle</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Status</th>
            <th>Admin Reply</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ratings as $rating)
        <tr id="rating-{{ $rating->id }}">
            <td>{{ $rating->customer->user->name ?? 'Unknown User' }}</td>
            <td>{{ $rating->vehicle->brand }} {{ $rating->vehicle->model }}</td>
            <td>{{ $rating->rating }} ⭐</td>
            <td>{{ $rating->feedback }}</td>
            <td class="status">{{ ucfirst($rating->status) }}</td>
            <td class="reply">
                @if($rating->adminreply)
                    {{ $rating->adminreply }}
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
                    @elseif($rating->status === 'approved')
                        <span>Approved</span>
                    @elseif($rating->status === 'rejected')
                        <span>Rejected</span>
                @endif
                <button class="btn btn-outline-danger btn-sm delete-btn" data-id="{{ $rating->id }}">Delete</button>
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
        // Instead of clearing actions, show a label
        $(`#rating-${id} .actions`).html('<span class="badge bg-success">Approved</span>');
            showAlert('Rating approved!');
        });
    });

    // Reject
    $('.reject-btn').click(function(){
        const id = $(this).data('id');
        $.post(`/admin/ratings/${id}/approve`, {_token:'{{ csrf_token() }}', status:'rejected'}, function(){
        $(`#rating-${id} .status`).text('Rejected');
        $(`#rating-${id} .actions`).html('<span class="badge bg-danger">Rejected</span>');
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
    // Delete
    $('.delete-btn').click(function(){
        if(!confirm('Are you sure you want to delete this rating?')) return;

        const id = $(this).data('id');
        $.ajax({
            url: `/admin/ratings/${id}`,
            type: 'DELETE',
            data: {_token: '{{ csrf_token() }}'},
            success: function(){
                $(`#rating-${id}`).remove();
                showAlert('Rating deleted!', 'danger');
            },
            error: function(){
                showAlert('Failed to delete rating', 'danger');
            }
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
