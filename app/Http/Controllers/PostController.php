<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use function GuzzleHttp\json_decode;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth',
        ['except'=>['index','show','getImage','getPostByCategory','getPostByUser']]);
    }

    public function index()
    {
        $posts = Post::all()
        ->load('category');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ],200);
    }

    public function show($id){
        $post = Post::find($id)
        ->load('category')
        ->load('user');



        if(is_object($post)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        }
        else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La entrada no existe'
            ];
        }
        return response()->json($data,$data['code']);
    }

    public function store(Request $request)
    {
        // Recoger datos por Post
        $json = $request->input('json',null);
        if(!empty($json)){
            $params = json_decode($json);
            $params_array = json_decode($json,true);
            //Conseguir usuario identificado
            $jwtAuth = new JwtAuth();
            $token = $request->header('Authorization',null);
            $user = $jwtAuth->checkToken($token,true);
            // Validar los datos
            $validate = Validator::make($params_array,[
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post, faltan datos'
                ];
            }
            else{
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }

            // Guardar el articulo
        }
        else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Datos ingresados son incorrectos'
            ];
        }
        // Devolver respuesta
        return response()->json($data,$data['code']);
    }

    public function update($id,Request $request)
    {
        // Recoger los datos por post
        $json = $request->input('json',null);
        if(!empty($json)){
            $params_array = json_decode($json,true);
            // Validar datos
            $validate = Validator::make($params_array,[
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);
            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post, faltan datos'
                ];
            }
            else{
                // Eliminar lo que no queremos actualizar
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['create_at']);
                unset($params_array['user']);

                // Conseguir el usuario identificado
                $user = $this->getIdentify($request);
                // Actualizar el registro en concreto
                $post = Post::where('id',$id)->where('user_id',$user->sub)->first();

                if(!empty($post) && is_object($post)){

                    $post->update($params_array);
                    $data = [
                        'code' => 200,
                        'status' => 'success',
                        'post' => $post,
                        'changes' => $params_array
                    ];
                }
                else{
                    $data = [
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'Usuario no autorizado'
                    ];
                }

            }
        }
        else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Datos ingresados son incorrectos'
            ];
        }
        // Devolver respuesta
        return response()->json($data,$data['code']);
    }

    public function destroy($id, Request $request)
    {
        // Conseguir usuario identificado
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization',null);
        $user = $jwtAuth->checkToken($token,true);

        // Comprobar si existe el registro
        $post = Post::where('id',$id)
                    ->where('user_id',$user->sub)
                    ->first();
        if(!empty($post)){
            // Borrarlo
            $post->delete();
            // Devolver respuesta
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        }
        else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'El objeto no existe'
            ];
        }
        return response()->json($data,$data['code']);
    }

    public function upload(Request $request){
        // Recoger la imagen de la petición
        $image = $request->file('file0');
        // Validar imagen
        $validate = Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar la imagen
        if(!$image || $validate->fails()){
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        }
        else{
            $image_name = time().$image->getClientOriginalName();

            \Storage::disk('images')->put($image_name,\File::get($image));
            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }
        // Devolver respuesta
        return response()->json($data,$data['code']);
    }

    public function getImage($filename){
        // Comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);
        if($isset){
            // Conseguir la imagen
            $file = \Storage::disk('images')->get($filename);

            // Devolver la imagen
            return new Response($file,200);
        }
        else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];

        }
        return response()->json($data,$data['code']);
    }

    public function getPostByCategory($id)
    {
        $posts = Post::where('category_id',$id)->get();

        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }

    public function getPostByUser($id)
    {
        $posts = Post::where('user_id',$id)->get();

        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ],200);
    }

    private function getIdentify($request){
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization',null);
        $user = $jwtAuth->checkToken($token,true);
        return $user;
    }
}
