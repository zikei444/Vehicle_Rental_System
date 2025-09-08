<!DOCTYPE html>
<html>
<head>
    <title>管理员 - 评论管理</title>
</head>
<body>
    <h1>所有用户评论</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if($ratings->count() > 0)
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>车辆</th>
                    <th>用户</th>
                    <th>评分</th>
                    <th>评论</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratings as $rating)
                    <tr>
                        <td>{{ $rating->id }}</td>
                        <td>{{ $rating->customer_id ?? 'N/A' }}</td>
                        <td>{{ $rating->vehicle_id ?? 'N/A' }}</td>
                        <td>{{ $rating->rating }}</td>
                        <td>{{ $rating->feedback ?? '无' }}</td>
                        <td>{{ $rating->status }}</td>
                        <td>
                            @if($rating->status === 'pending')
                                <form method="POST" action="{{ route('ratings_admin.approve', $rating->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit">批准</button>
                                </form>
                                <form method="POST" action="{{ route('ratings_admin.reject', $rating->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit">拒绝</button>
                                </form>
                            @else
                                <em>已处理</em>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>暂无评论。</p>
    @endif
</body>
</html>
