<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Helpers\JwtAuth;
use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('api.auth',['except'=>['index','show']]);
    }
    public function index(){
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id){
        $category = Category::find($id);

        if(is_object($category)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ];
        }
        else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoria no existe'
            ];
        }
        return response()->json($data,$data['code']);
    }

    public function store(Request $request){
        // Recoger los datos por post
        $json = $request->input('json',null);

        // Validar los datos
        if(!empty($json)){
            $params_array = json_decode($json,true);
            $validate = Validator::make($params_array,[
                'name' => 'required',

            ]);

            // Guardar la categoria
            if($validate->fails()){
                $data = [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'No se ha guardado la categoria'
                ];
            }
            else{
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        }
        else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria'
            ];
        }
        // Devolver el resultado
        return response()->json($data,$data['code']);
    }

    public function update($id,Request $request){
        // Recoger datos por post
        $json = $request->input('json',null);
        if(!empty($json)){
            $params_array = json_decode($json,true);
            // Validar los datos
            $validate = Validator::make($params_array,[
                'name' => 'required'
            ]);
            unset($params_array['id']);
            unset($params_array['create_at']);

            $category = Category::where('id',$id)->update($params_array);

            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $params_array
            ];
            // if($validate->fails()){
            //     $data = [
            //         'code' => 404,
            //         'status' => 'error',
            //         'message' => 'Formatos incorrectos.'
            //     ]
            // }
        }
        else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoria.'
            ];
        }

        return response()->json($data,$data['code']);


        // Quitar lo que no quiero actualizar

        // Actualizar el registro
    }
}
