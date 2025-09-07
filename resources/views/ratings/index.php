<!DOCTYPE html>
<html>
<head>
    <title>车辆评分</title>
</head>
<body>
    <h1>车辆评分列表</h1>

    @if(isset($error))
        <p style="color:red;">错误：{{ $error }}</p>
    @endif

    @if($ratings && count($ratings) > 0)
        <ul>
            @foreach($ratings as $rating)
                <li>
                    用户ID: {{ $rating['customer_id'] ?? $rating->customer_id }},
                    分数: {{ $rating['rating'] ?? $rating->rating }},
                    评论: {{ $rating['feedback'] ?? $rating->feedback ?? '无' }}
                </li>
            @endforeach
        </ul>
    @else
        <p>暂无评分。</p>
    @endif
</body>
</html>
