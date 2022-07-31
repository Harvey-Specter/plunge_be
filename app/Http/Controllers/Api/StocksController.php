<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\Stock;
use App\Models\Category;

use App\Http\Resources\StockResource;
use App\Http\Requests\Api\StockRequest;
use App\Http\Queries\StockQuery;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StocksController extends Controller
{
    // public function index(Category $category)
    public function index($category_id, StockQuery $query,StockRequest $request)
    {
        //$stocks = $category->stocks()->paginate();
        //$where = array('category_id' => $category_id, 'price_id'=>$request->code);
        $data=array();
        $del=$request->del;

        Log::debug('del====='.$del );
        if($del=='1'){
            $userId=$request->userId;
            $stocks = DB::table('stocks')
            ->select(array('stocks.id','stocks.day','stocks.code','stocks.user_id','stocks.category_id','stocks.pattern','stocks.market','stocks.remark','stocks.created_at' ,'co_jp.name','co_jp.cate33' ,'stocks.score','co_jp.size' ,'categories.name as cateName' ))
            ->leftJoin('categories', 'stocks.category_id', '=', 'categories.id')
            ->leftJoin('co_jp', 'stocks.code', '=', 'co_jp.code')
            ->where('stocks.user_id', $userId)
            ->where('price_id', '=', 2)
            ->paginate($request->pageSize);
            // $data =parent::dataWithPage($stocks);
        }else{
            $stocks = DB::table('stocks')
            ->select(array('stocks.id','stocks.day','stocks.code','stocks.user_id','stocks.category_id','stocks.pattern','stocks.market','stocks.remark','stocks.created_at' ,'co_jp.name','co_jp.cate33' ,'stocks.score','co_jp.size' ))
            ->LeftJoin('co_jp', 'stocks.code', '=', 'co_jp.code')
            ->where('category_id', $category_id)
            ->where('price_id', '<>', 2)
            // ->orderBy('categories.id', 'desc')
            ->paginate($request->pageSize);
        }
        $data =parent::dataWithPage($stocks);
        return $data;
    }

    private function saveStockByReq(StockRequest $request , Stock $stock , $cateId)
    {
        Log::debug($request->code.'====='. $cateId );
        $stock->price_id = $request->price_id;
        $stock->day = now();
        $stock->code = $request->code;
        $stock->user()->associate($request->user());
        //$stock->category_id->$request->category_id;
        $stock->category_id=$cateId;
        $stock->pattern = $request->pattern;
        $stock->market = $request->market;
        $stock->remark = $request->remark;
        // $stock->created_at=now();
        // $stock->updated_at=now();
        $stock->save();
    }
    public function store(StockRequest $request,Category $category, Stock $stock)
    {
        $id = $request->id;
        $category_ids = $request->category_ids;
        // $stock->created_at=now();
        // $stock->updated_at=now();
        $ac = 0;
        // if(empty($id)){

        //     // $this->saveStockByReq($request,$stock,$request->category_id);
        //     for($i = 0; $i < count($category_ids); $i++) {

        //         $stock->id=null;
        //         if($category_ids[$i] != $request->category_id){
        //             $this->saveStockByReq($request,$stock,$category_ids[$i]);
        //         }

        //     }
        // }else{

            $where = array('user_id' => $request->user_id, 'code'=>$request->code);
            $deleted = DB::table('stocks')->where($where)->delete();
            $updateDatas=[];
            for($i = 0; $i < count($category_ids); $i++) {
                $row=[
                    'code' => $request->code,
                    'price_id' => 0,
                    'day' => now(), //  $request->day,
                    'user_id' => $request->user_id,
                    'pattern' => $request->pattern,
                    'category_id' => $category_ids[$i],
                    'market' => $request->market,
                    'remark' => $request->remark,
                    'score' => $request->score,
                    // 'created_at' => now(),
                    // 'updated_at' => now(),
                ];
                array_push($updateDatas,$row);
            }
            $ac = Stock::upsert($updateDatas, ['category_id', 'code'], ['pattern','market','remark']);
        // }
        return parent::success($ac);
    }
    public function destroy(Category $category, Stock $stock)
    {
        if ($stock->category_id != $category->id) {
            abort(404);
        }

        $this->authorize('destroy', $stock);
        $stock->delete();

        // return response(null, 204);
        return parent::success(204);
    }
    public function delStock(StockRequest $request, Stock $stock)
    {
        // $this->authorize('destroy', $stock);
        $ids=$request->ids;
        Log::debug("delStock========".$ids);
        $idsArray = explode(',',$ids);

        //$deleted = DB::table('stocks')->whereIn('id', $idsArray)->delete();
        // price_id =2 is delflag
        $affected = DB::table('stocks')
              ->whereIn('id', $idsArray)
              ->update(['price_id' => 2]);

        //Stock::destroy($idsArray);
        return parent::success(204);
    }
}
