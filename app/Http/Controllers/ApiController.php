<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use App\Models\Cats;
use Symfony\Component\DomCrawler\Crawler;

class ApiController extends Controller
{
    /**
     * Парсинг категорий
     *
     * @return string
     */

    public function categoryParser(){
        // Ссылка на страницу для парсинга
        $link = 'http://pbprog.ru/databases/foodmeals/';
        // Получаем html страницу ссылки
        $html = $this->curl_get($link, true);
        // Создаем экземпляр парсера и добавляем html код
        $crawler = new Crawler(null, $link);
        $crawler->addHtmlContent($html);

        // Достаем ссылки со страницы и начинаем перебор
        $cats = $crawler->filter('.main-column a')->each(function (Crawler $node, $i) {
            $link  = $node->attr('href');
            // Берем только необходимые нам ссылки, исходя из регулярки
            if(preg_match('/\/databases\/foodmeals\/[0-9]/', $link)){
                //Достаем ID категории
                preg_match('/[0-9]+/', $link, $matches);
                $ind_id = $matches[0];
                $title = $node->text();
                return compact('link', 'title', 'ind_id');
            }
        });

        // Убираем из массива пустые элементы
        $cats = array_filter($cats, function($element) {
            return !empty($element);
        });

        //dd($cats);

        // Перебираем полученный массив и заносим в БД
        foreach($cats as $c){
            Cats::firstOrCreate(['name' => $c['title'], 'ind_id' => $c['ind_id'], 'link' => $c['link']]);
        }

        return 'Finish';

    }

    /**
     * Парсинг продуктов
     *
     * @return bool|string
     */

    public function productsParser(){
        ini_set('max_execution_time', 0);

        // Домен для ссылок
        $domain = 'http://pbprog.ru';
        // Достаем из бд все категории
        $cats = Cats::all();
        // Если категории не спарсены, то прерываем
        if(!$cats) return false;
        // Перебор категорий
        foreach($cats as $c){
            // Преобразуем ссылку
            $link = $domain.$c->link;
            // Получаем html страницу ссылки
            $html = $this->curl_get($link, true);
            // Создаем экземпляр парсера
            $crawler = new Crawler(null, $link);
            $crawler->addHtmlContent($html);

            // Достаем все ссылки в ul li и перебираем
            $products = $crawler->filter('.main-column ul li a')->each(function (Crawler $node, $i) {
                $link  = $node->attr('href');
                $title = $node->text();
                return compact('link', 'title');
            });

            // Убираем пустые элементы. Закомментировал т.к. считаю что из-за этого может тормозить выполнение
            /*$products = array_filter($products, function($element) {
                return !empty($element);
            });*/

            foreach($products as $p){
                // Проверяем элемент на пустоту
                if(!empty($p)) {
                    // Ищем продукт в бд
                    $f = Products::where('name', $p['title'])->first();
                    // Если продукта нету в бд, то работаем
                    if (!$f) {
                        // Получаем требуемую страницу
                        $html = $this->curl_get($domain . $p['link'], true);
                        // Создаем экземпляр парсера
                        $crawler = new Crawler(null, $link);
                        $crawler->addHtmlContent($html);

                        // Достаем таблицу и перебираем
                        $table = $crawler->filter('.main-column #wt-table')->filter('tr')->each(function ($tr, $i) {
                            return $tr->filter('td')->each(function ($td, $i) {
                                return trim($td->text());
                            });
                        });

                        // Убираем пустые элементы. Закомментировал т.к. считаю что из-за этого может тормозить выполнение
                        /*$table = array_filter($table, function ($element) {
                            return !empty($element);
                        });*/

                        // Преобразуем массив в вид 'Наименование' => 'Содержание питательных элементов'
                        $nt = array();
                        foreach ($table as $t) {
                            // Проверяем на пустоту
                            if(!empty($t[1])){
                                $nt[$t[0]] = $t[1];
                            }
                        }

                        // Добавляем продукт в базу
                        Products::firstOrCreate(['cat_id' => $c->ind_id, 'name' => $p['title'], 'data' => json_encode($nt), 'link' => $p['link']]);
                    }
                }
            }
        }

        return 'Finish';

    }

    /**
     * Очищаем таблицы Категорий и Продуктов
     *
     * @return string
     */

    public function truncate(){
        Cats::truncate();
        Products::truncate();

        return 'Finish';
    }

    /**
     * API для получения продуктов в категории
     *
     * @param $cat_id - ID категории
     * @return mixed
     */

    public function getProducts($cat_id){
        $products = Products::where('cat_id', $cat_id)->get();
        if($products){
            return response()->json(['success' => true, 'data' => $products], 200);
        }else{
            return response()->json(['success' => false, 'error' => 'Products error'], 401);
        }

    }

    /**
     * CURL с преобразованием кодировки windows-1251 в utf-8
     *
     * @param $link - Ссылка на требуемый HTML
     * @return string
     */

    private function curl_get($link){
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
