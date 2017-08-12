<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Collective\Html\HtmlFacade as Html;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\Input;
use App\User;
use Exception;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function orderPost(Request $request)
    {
        $user = User::find(3);
        $input = $request->all();
        $token = $input['stripeToken'];

        try {
            $user->subscription($input['plane'])
                  ->create($token,['email' => $user->email]);
            return back()->with('success','Subscription is completed.');
        }
        catch (Exception $e) {
            return back()->with('success',$e->getMessage());
        }

    }

}
