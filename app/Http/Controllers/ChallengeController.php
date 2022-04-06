<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeProfile;
use App\Models\Region;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $challenges = Challenge::all();

        return view('challenges.index', compact('challenges'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(ChallengeProfile $challenge)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Almacenar datos en la BD (sin modelos)
        DB::table('challenges_profiles')->insert([
            'profiles_id' => Auth::user()->id,
            'challenges_id' => $request->challenges_profiles_id,
            'done' => 1,
        ]);

        return redirect()->action('App\Http\Controllers\ChallengeController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Challenge  $Challenge
     * @return \Illuminate\Http\Response
     */
    public function show(Challenge $challenge)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Challenge  $Challenge
     * @return \Illuminate\Http\Response
     */
    public function edit(Challenge $challenge)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Challenge $Challenge
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Challenge $challenge)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Challenge $Challenge
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Challenge $challenge)
    {
        $find = ChallengeProfile::where('challenges_id', $request->challenges_profiles_id)->where('profiles_id', Auth::user()->id)->delete();

        return redirect()->action('App\Http\Controllers\ChallengeController@index');
    }
}
