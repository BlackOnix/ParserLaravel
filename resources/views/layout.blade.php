<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @stack('title')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ladda-bootstrap/0.9.4/ladda-themeless.min.css" />

    <style>
        .hide {
            display: none;
        }
        .tac {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <a href="/" class="btn">Главная</a>
                <div class="float-right">
                    <button class="btn btn-success ladda-button" data-style="expand-left" id="cat_parsing">Парсинг категорий</button>
                    <button class="btn btn-success ladda-button" data-style="expand-left" id="products_parsing">Парсинг продуктов</button>
                    <button class="btn btn-danger ladda-button" data-style="expand-left" id="truncate">Очистить категории и продукты</button>
                </div>
            </div>
            @yield('content')
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ladda-bootstrap/0.9.4/spin.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ladda-bootstrap/0.9.4/ladda.min.js"></script>
    <script>
        $(function(){
            $("#cat_parsing").click(function(){
                let l = Ladda.create(this);
                l.start();
                $.get('/api/parsing/cat', function(){
                    l.stop();
                    setTimeout(window.location.reload(), 1000);
                }).fail(function() {
                    l.stop();
                    alert('Произошла ошибка. попробуйте еще раз')
                });
            });

            $("#products_parsing").click(function(){
                let l = Ladda.create(this);
                l.start();
                $.get('/api/parsing/products', function(data){
                    l.stop();
                    setTimeout(window.location.reload(), 1000);
                }).fail(function() {
                    l.stop();
                    alert('Произошла ошибка. попробуйте еще раз')
                });
            });

            $("#truncate").click(function(){
                let l = Ladda.create(this);
                l.start();
                $.get('/api/truncate', function(data){
                    l.stop();
                    setTimeout(window.location.href = '/', 1000);
                }).fail(function() {
                    l.stop();
                    alert('Произошла ошибка. попробуйте еще раз')
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>