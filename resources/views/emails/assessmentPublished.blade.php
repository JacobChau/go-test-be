<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Test Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Your Assessment Results are Ready!</h1>

    <p>Hello {{ $attempt->user->name }},</p>

    <p>We are pleased to inform you that your assessment results for <strong>{{ $attempt->assessment->name }}</strong> are now available. The assessment was completed on {{ $attempt->updated_at->format('F j, Y') }}.</p>

    @if($showDetailsButton)
        <p>Please visit our website to view your detailed results and feedback.</p>
        <a href="{{ $url }}" class="button">View My Result</a>
    @else
        <p>Please check your assessment results on our platform for a summary of your performance.</p>
    @endif

    <p>If you wish to discuss your results or need additional resources for further learning, please don't hesitate to contact us. We're here to support your learning journey!</p>
</div>
</body>
</html>
