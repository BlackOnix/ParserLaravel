<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Cats;
use App\Models\Products;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(){
        $res['cats'] = Cats::all();
        return view('content.main', $res);
    }

    public function product($id){
        $product = Products::find($id);
        if(!$product) return redirect('/');
        return view('content.product', compact('product'));

    }
}
