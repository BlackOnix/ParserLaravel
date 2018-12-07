<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use App\Models\Cats;
use Symfony\Component\DomCrawler\Crawler;

class ApiController extends Controller
{
    public function categoryParser(){
        $link = 'http://pbprog.ru/databases/foodmeals/';
        // Получаем html страницу ссылки
        $html = $this->curl_get($link, true);
        // Создаем экземпляр парсера
        $crawler = new Crawler(null, $link);
        $crawler->addHtmlContent($html);


        $cats = $crawler->filter('.main-column a')->each(function (Crawler $node, $i) {
            $link  = $node->attr('href');
            if(preg_match('/\/databases\/foodmeals\/[0-9]/', $link)){
                preg_match('/[0-9]+/', $link, $matches);
                $ind_id = $matches[0];
                $title = $node->text();
                return compact('link', 'title', 'ind_id');
            }
        });
        $cats = array_filter($cats, function($element) {
            return !empty($element);
        });

        //dd($cats);

        foreach($cats as $c){
            Cats::firstOrCreate(['name' => $c['title'], 'ind_id' => $c['ind_id'], 'link' => $c['link']]);
        }

        return 'Finish';

    }

    public function productsParser(){
        ini_set('max_execution_time', 0);

        $domain = 'http://pbprog.ru';
        $cats = Cats::all();
        if(!$cats) return false;
        foreach($cats as $c){
            $link = $domain.$c->link;
            // Получаем html страницу ссылки
            $html = $this->curl_get($link, true);
            // Создаем экземпляр парсера
            $crawler = new Crawler(null, $link);
            $crawler->addHtmlContent($html);

            $products = $crawler->filter('.main-column ul li a')->each(function (Crawler $node, $i) {
                $link  = $node->attr('href');
                $title = $node->text();
                return compact('link', 'title');
            });

            /*$products = array_filter($products, function($element) {
                return !empty($element);
            });*/

            foreach($products as $p){
                if(isset($p)) {
                    $f = Products::where('name', $p['title'])->first();
                    if (!$f) {
                        $html = $this->curl_get($domain . $p['link'], true);
                        // Создаем экземпляр парсера
                        $crawler = new Crawler(null, $link);
                        $crawler->addHtmlContent($html);

                        $table = $crawler->filter('.main-column #wt-table')->filter('tr')->each(function ($tr, $i) {
                            return $tr->filter('td')->each(function ($td, $i) {
                                return trim($td->text());
                            });
                        });

                        /*$table = array_filter($table, function ($element) {
                            return !empty($element);
                        });*/

                        $nt = array();
                        foreach ($table as $t) {
                            if(isset($t[1])){
                                $nt[$t[0]] = $t[1];
                            }
                        }

                        Products::firstOrCreate(['cat_id' => $c->ind_id, 'name' => $p['title'], 'data' => json_encode($nt), 'link' => $p['link']]);
                    }
                }
            }
        }

        return 'Finish';

    }

    public function truncate(){
        Cats::truncate();
        Products::truncate();

        return 'Finish';
    }

    public function getProducts($cat_id){
        $products = Products::where('cat_id', $cat_id)->get();
        if($products){
            return response()->json(['success' => true, 'data' => $products], 200);
        }else{
            return response()->json(['success' => false, 'error' => 'Products error'], 401);
        }

    }

    private function curl_get($link, $encode = false){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_ENCODING, "");

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MG (' . url('/') . ')');

        $result = curl_exec($ch);
        curl_close($ch);

        return iconv("windows-1251", "UTF-8", $result);
    }
}
