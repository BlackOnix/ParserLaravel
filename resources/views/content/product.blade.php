@extends('layout')

@push('title')
    <title>Парсер. Главная</title>
@endpush

@push('scripts')

@endpush

@section('content')
    <div class="col-12 tac mb-2 mt-2">
        <h1>{{$product->name}}</h1>
    </div>
    <div class="col-12 table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>Наименование показателя</th>
                <th>Содержание питательных веществ в 100 граммах продукта</th>
            </tr>
            </thead>
            <tbody>
            @foreach(json_decode($product->data) as $i => $d)
                <tr>
                    <td>{{$i}}</td>
                    <td>{{$d}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop