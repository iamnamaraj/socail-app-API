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
            <td>
                <p>Hello, <strong>{{ $body['name'] }}</strong></p> <br>
                <p>we're receive a request to change  your password.</p> <br>
                code: <strong>{{ $body['code'] }}</strong>
            </td>
        </tr>
    </table>

</body>
</html>