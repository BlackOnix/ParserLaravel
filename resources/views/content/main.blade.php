@extends('layout')

@push('title')
    <title>Парсер. Главная</title>
@endpush

@push('scripts')
    <script>
        $(function(){
            $("body").on('click', '.product-action', function(){
                let $this = $(this);
                let action = $this.data('action');
                let cat_id = $this.data('id');
                if(action === 'open'){
                    if($("#td_"+cat_id+" .products-list").find('*').length == 0){
                        $this.button('loading');
                        $.get('/api/products/'+cat_id, function(data){
                            $this.button('reset');
                            if(data.success){
                                console.log(data['data'][0]);
                                let res = data['data'];
                                for(let i = 0; i < res.length; i++){
                                    $("#td_"+cat_id+" .products-list").prepend('<div><a href="/product/'+res[i]['id']+'">'+res[i]['name']+'</a></div>');
                                }
                                $("#td_"+cat_id+" .product-action").removeClass('hide');
                                $this.addClass('hide');
                                $("#td_"+cat_id+" .products-list").show();
                            }else{
                                console.log('Произошла ошибка при получении списка продуктов.')
                            }
                        }).fail(function() {
                            $this.button('reset');
                            alert('Произошла ошибка. попробуйте еще раз')
                        });
                    }else{
                        $("#td_"+cat_id+" .products-list").show();
                        $("#td_"+cat_id+" .product-action").removeClass('hide');
                        $this.addClass('hide');
                    }
                }else{
                    $("#td_"+cat_id+" .products-list").hide();
                    $("#td_"+cat_id+" .product-action").removeClass('hide');
                    $this.addClass('hide');
                }
            })
        });
    </script>
@endpush

@section('content')
    <div class="col-12 tac mb-2 mt-2">
        <h1>Список продуктов</h1>
    </div>
    <div class="col-12 table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Категории</th>
                    <th>Продукция</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cats as $c)
                    <tr>
                        <td><a href="http://pbprog.ru{{$c->link}}" target="_blank">{{$c->name}} <i class="fas fa-external-link-alt"></i></a></td>
                        <td id="td_{{$c->ind_id}}">
                            <a href="#" class="product-action" data-action="open" data-id="{{$c->ind_id}}">Расскрыть список продукции <i class="fas fa-caret-down"></i></a>
                            <a href="#" class="product-action hide" data-action="close" data-id="{{$c->ind_id}}">Закрыть список продукции <i class="fas fa-caret-up"></i></a>
                            <div class="products-list" style="display: none;">

                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@stop