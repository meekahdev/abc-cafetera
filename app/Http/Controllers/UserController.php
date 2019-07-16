<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Sentinel;
use App\Http\Requests;
use App\Order;
use App\Deliver;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->AuthUser = Auth()->user();
    }

    public function view_profile(Request $request)
    {
        return view('user.profile');
    }

    //to view the customer products
    public function view_my_purcases(Request $request)
    {
        if(isset($request->type) && $request->type == 'json')
        {
            $orders = Order::where('order_by', '=', $this->AuthUser->id)->get();


            return response()->json([ 'data' => $orders]);
        }
        
        return view('customer.index');
    }

    public function cancel_purchase(Request $request)
    {

            $order = Order::where('id', '=', $request->id)->first();
            $order->status = -1;
            $order->save();


        
        return redirect('/customer/product/view');
    }

    public function view_cheff_orders(Request $request)
    {

        if(isset($request->type) && $request->type == 'json')
        {
            $orders = Order::whereIn('status', [0,1,2])->get();


            return response()->json([ 'data' => $orders]);
        }

        return view('chef.index');
    }

    public function change_status(Request $request)
    {

        $order = Order::find($request->id);

        if(isset($request->prepration_time) && $request->prepration_time != '')
        {
            $order->status = 1;
            $order->preparation_time = $request->prepration_time;
        }

        else if(isset($request->deliver) && $request->deliver != '')
        {
            $order->status = 3;

            $deliver = Deliver::where('order_id', '=', $request->id)->first();
            $deliver->deliverd_by =  $this->AuthUser->id;
            $deliver->save();
        }

        else if(isset($request->delivered_finish) && $request->delivered_finish != '')
        {
            $order->status = 4;
        }

        else
        {
            $order->status = 2;
        }
        $order->updated_by = Auth()->user()->id;
        $order->save();

        if(isset($request->deliver) && $request->deliver != '')
        {
            return redirect('/deliver-boy/product/view');
        }

        if(isset($request->delivered_finish) && $request->delivered_finish == 'yes')
        {
            return redirect('/deliver-boy/my-product/view');
        }

        if(isset($request->delivered_finish) && $request->delivered_finish == 'receiptent')
        {
            return redirect('/receiptent/product/view');
        }


        

        return redirect('/chef/product/view');
    }

    //delivery boy function
    public function view_deliver_orders(Request $request)
    {

        if(isset($request->type) && $request->type == 'json')
        {
            $orders = Order::join('delivery_master', 'delivery_master.order_id',  '=', 'order_master.id')
                            ->Leftjoin('users', 'users.id',  '=', 'delivery_master.deliverd_by')
                            ->whereIn('status', [2,3])
                            ->where('order_master.type', '=' , 2)
                            //->where('delivery_master.deliverd_by', '!=' ,$this->AuthUser->id)
                            ->select('order_master.*', 'delivery_master.deliverd_by', 'users.name' )->get();

            return response()->json([ 'data' => $orders]);
        }

        return view('deliver-boy.index');
    }

    public function view_my_deliver_orders(Request $request)
    {

        if(isset($request->type) && $request->type == 'json')
        {
            $orders = Order::join('delivery_master', 'delivery_master.order_id',  '=', 'order_master.id')
                            ->Leftjoin('users', 'users.id',  '=', 'delivery_master.deliverd_by')
                            ->whereIn('status', [3,4])
                            ->where('order_master.type', '=' , 2)
                            ->where('delivery_master.deliverd_by', '=' ,$this->AuthUser->id)
                            ->select('order_master.*', 'delivery_master.deliverd_by' )->get();

            return response()->json([ 'data' => $orders]);
        }

        return view('deliver-boy.my-orders');
    }

    public function view_ready_orders(Request $request)
    {

        if(isset($request->type) && $request->type == 'json')
        {
            $orders = Order::join('delivery_master', 'delivery_master.order_id',  '=', 'order_master.id')
                            ->Leftjoin('users', 'users.id',  '=', 'delivery_master.deliverd_by')
                            ->whereIn('status', [2])
                            ->where('order_master.type', '=' , 1)
                            ->select('order_master.*', 'delivery_master.deliverd_by' )->get();

            return response()->json([ 'data' => $orders]);
        }

        return view('receiptent.index');
    }

    public function view_admin_orders(Request $request)
    {

        if(isset($request->type) && $request->type == 'json')
        {
            $orders = Order::join('delivery_master', 'delivery_master.order_id',  '=', 'order_master.id')
                            ->Leftjoin('users', 'users.id',  '=', 'delivery_master.deliverd_by')
                            ->select('order_master.*', 'delivery_master.deliverd_by' )->get();

            return response()->json([ 'data' => $orders]);
        }

        return view('admin.index');
    }

    



    

    
}
