<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mail</title>
</head>
<body>
    <table>
        <tr>
            <td>{{ $subject }}</td>
        </tr>
        <tr>
            <td> hi Mr,<strong>{{ $data['name'] }}</strong></td> <tr>
            <td> {{ $data['body'] }}</td>
        </tr>
    </table>

</body>
</html>