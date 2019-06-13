<?php

namespace App\Http\Controllers;
use App\Category;
use App\Post;

use Illuminate\Http\Request;

class PruebasController extends Controller
{
    public function index(){
        $titulo = 'Animales';
        $animales = ['Perro','Gato','Tigre'];

        return view('pruebas.index',[
            'animales' => $animales,
            'titulo' => $titulo
        ]);
    }

    public function testOrm(){
        $posts = Post::all();
        foreach($posts as $post){
            echo "<h1>".$post->title."</h1>";
            echo "<span style='color:gray;'>{$post->user->name} - {$post->category->name}</span>";
            echo "<p>".$post->content."</p>";
            echo "<hr>";
        }

        $categories = Category::all();

        foreach($categories as $category){
            echo "<h1>".$category->name."</h1>";
        }
    }
}
