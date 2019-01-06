<!DOCTYPE html>

    <html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">    
        <title></title>
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    </head>
    <body>
        
        <div class="container">
            <div class="starter-template">
                <h1>Reset password</h1>
                <p class="lead">Descrizione</p>
                @if($success)
                <form action="../../user/reset_store" method="post">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">Password</span>
                        </div>
                        <input type="password" class="form-control" placeholder="Nuova password" aria-label="" aria-describedby="basic-addon1" name="password">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">Password</span>
                        </div>
                        <input type="password" class="form-control" placeholder="Conferma password" aria-label="" aria-describedby="basic-addon1" name="password_confirmation">
                    </div>
                    <input name="token" type="hidden" value="{{$token}}" />
                    
                    <button type="submit" class="btn btn-primary">Salva</button>
                </form>
                @else
                <p>Nessuna richiesta da processare</p>
                @endif
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    </body>
</html>
