<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $all = Event::orderBy('id', 'DESC')->get();
        $hoy = Carbon::today();

        $bladers = Profile::orderBy('points_s2', 'DESC')->paginate(5);
        $stamina = Profile::where('user_id', 1)->first();
        $antiguos = $all->where("date", "<", Carbon::now());
        $nuevos = $all->where("date", ">=", Carbon::now());

        return view('inicio.index', compact('bladers', 'stamina', 'nuevos', 'antiguos'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function events()
    {
        $all = Event::all();
        $hoy = Carbon::today();

        $antiguos = $all->where("date", "<", Carbon::now());
        $nuevos = $all->where("date", ">=", Carbon::now());

        return view('inicio.events', compact('nuevos', 'antiguos'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function contact()
    {
        return view('inicio.contact');
    }

    public function sendMail(Request $request) {

        $this->validate($request, [
            'subject' => 'required',
            'message' => 'required'
        ]);

        $email = Auth::user()->email;


        Mail::send('contact_email',
             array(
                 'name' => Auth::user()->name,
                 'email' => $email,
                 'subject' => $request->get('asunto'),
                 'message' => $request->get('mensaje'),
             ), function($message) use ($request)
               {
                    $email = Auth::user()->email;
                    $message->from($email);
                    $message->to('sbbl.oficial@gmail.com');
               });

          return back()->with('success', 'Thank you for contact us!');

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rules()
    {
        return view('inicio.rules');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function privacy()
    {
        return view('inicio.privacy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function combo()
    {
        return view('inicio.combo');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\c  $c
     * @return \Illuminate\Http\Response
     */
    public function show(c $c)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\c  $c
     * @return \Illuminate\Http\Response
     */
    public function edit(c $c)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\c  $c
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, c $c)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\c  $c
     * @return \Illuminate\Http\Response
     */
    public function destroy(c $c)
    {
        //
    }
}
