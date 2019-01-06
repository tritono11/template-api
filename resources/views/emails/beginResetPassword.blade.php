<!DOCTYPE html>
<html>
<head>
    <title>E' stato richiesto un cambio password</title>
</head>
 
<body>
    <h2>Welcome to the site {{$name}}</h2>
    <br/>
    Your registered email-id is {{$email}} , Please click on the below link to rest your password account
    <br/>
    <a href="{{url('user/reset', $verification_code)}}">Verify Email</a>
</body>
 
</html>