<?php

namespace App\Http\Controllers;

use App\Http\Repository\Task\BaseInterface;
use App\Http\Repository\Task\DBFLPASSInterface;
use Illuminate\Http\Request;
use ReflectionClass;
use stdClass;

use function PHPUnit\Framework\isEmpty;

class ModalController extends Controller
{

    private $globalRepository;
    private $dbflpassRepository;

    public function __construct(BaseInterface $globalRepository, DBFLPASSInterface $dbflpassRepository)
    {
        $this->globalRepository = $globalRepository;
        $this->dbflpassRepository = $dbflpassRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getModal(Request $request)
    {
        try{
            if(request()->ajax()){
                $resource = $request->resource;
                // dd($request->all());
                // view('')
                if(view()->exists($resource)){
                    // dd($request->resource);
                    $model = $request->model; 
                    $fnData = $request->fnData; 
                    $modalId = $request->modalId; 
                    $modalTitle = $request->modalTitle ?? '';
                    $modalWidth = $request->modalWidth; 
                    $modalParams = (object) $request->modalParams ?? [];
                    $formId = $request->formId; 
                    $plugins = $request->plugins ?? [];
                    $checkPermission = $request->checkPermission ?? false;
                    $codeAccess = $request->codeAccess ?? '';
                    $access = $request->access ?? '';
                    // dd($codeAccess, $access);
                    if($checkPermission){
                        $this->checkUserPermission($codeAccess, $access);
                    }

                    $url = $request->url;
                    $res = new stdClass();
                    $formMethod = NULL;
                    if($fnData !== NULL){
                        $formMethod = $request->fnData['params'][0] == NULL ? NULL : 'PUT';
                        if($fnData['class'] === '\\ModalController'){
                            $res = call_user_func_array(array(__NAMESPACE__ . $fnData['class'], $fnData['function']), $fnData['params']);
                        }else{
                            $class = __NAMESPACE__ . $fnData['class'];
                            $constructor = app()->make($class);
                            // dd($fnData['params']);
                            $res = call_user_func_array(array($constructor, $fnData['function']), $fnData['params']);
                        }
                    }else if($model !== NULL){
                        $model = 'App\Models\\' . $model;
                    }
                    // return view($resource, compact('modalId', 'formId', 'modalWidth', 'res', 'url', 'formMethod'));
                    // dd($modalParams);
                    $modal = view($resource, compact('modalId', 'modalTitle', 'formId', 'modalWidth', 'res', 'url', 'formMethod', 'modalParams'))->render();
                    $data = [
                        'modalId' => $modalId,
                        'formId' => $formId,
                        'view' => $modal,
                        'plugins' => $plugins,
                        'res' => $res
                    ];

                    return response()->json($data);
                }
            }
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()]);
        }
        return response()->json([
            'message' => 'Halaman tidak ditemukan'
        ], 500);
    }

    public function checkUserPermission($code, $access){
        $getAccess = auth()->user()->getPermissionsName($code);
        if($getAccess == NULL){
            return abort(403, 'Anda tidak memiliki akses untuk mengakses halaman ini');
        }

        if(in_array($access, $getAccess)){
            return true;
        }

        return abort(403, 'Anda tidak memiliki akses untuk mengakses halaman ini');
    }

    private function getUserPermission($userId)
    {
        return $this->globalRepository->queryModel('dbflpass')->with('departemen:KDDEP,NMDEP', 'jabatan:KODEJAB,NamaJab', 'karyawan:keyNIK,NIK,NAMA')->where('USERID', $userId)->firstornew();
    }

    private function getAllMenu($userId)
    {
        return $this->dbflpassRepository->getAllMenu($userId);
    }

}
