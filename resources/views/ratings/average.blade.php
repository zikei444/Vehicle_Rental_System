<!DOCTYPE html>
<html>
<head>
    <title>车辆平均评分</title>
</head>
<body>
    <h1>车辆平均评分</h1>

    @if(isset($error))
        <p style="color:red;">错误：{{ $error }}</p>
    @endif

    <p><strong>平均分:</strong> {{ $average['average_rating'] ?? 0 }}</p>
</body>
</html>
