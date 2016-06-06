<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use App\Models\Note;
use App\Http\Requests;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class NoteController extends Controller
{
	public function __construct()
	{
	    $this->middleware('auth', ['only' => ['store', 'create', 'update']]);
	}

	public function index(){
		return $this->show(Note::wherePublic()->first());
	}

	public function show(Note $note){
        return view('home')->with([
            'note' => $note,
            'readOnly' => !$note->belongsToCurrentUser()
        ]);
	}

    public function store(Request $request){
    	$this->validate($request, [
    		'problem' => 'required|string',
    		'solution' => 'required|string'
    	]);

    	$note = new Note;
	    $note->user_id = Auth::user()->id;
	    $note->problem = $request->problem;
	    $note->solution = $request->solution;
	    $note->save();

	    Session::flash('noteSaveSuccess', 'Your Note was created successfully!');
        return redirect('/');
    }

    public function create(){
    	return view('home')->with([
            'showCreate' => true,
            'note' => new Note
        ]);
    }

    public function update(Note $note, Request $request){
    	if(!$note->belongsToCurrentUser()){
    		Session::flash('noteSaveError', 'There was a problem with your request!');
        	return redirect()->back();
    	}

    	$this->validate($request, [
    		'problem' => 'required|string',
    		'solution' => 'required|string'
    	]);

    	$note->problem = $request->problem;
    	$note->solution = $request->solution;
    	$note->save();
    }

    public function notesDataTable(Note $note){
        $notesList = Note::select('id','problem', 'updated_at')
            ->where('user_id', $note->user_id)
            ->orderBy('updated_at', 'desc')
            ->get();
    	
    	return Datatables::of($notesList)
    	->setRowClass(function ($notesList) use($note){
    		if($notesList->id == $note->id){
    			return "active-row";
    		}
    	})
        ->editColumn('problem', function($note){
                return '<div class="notes-text-limit">'.$note->problem.'</div>';
            })
    	->make(true);
    }

    public function noteSettings(Request $request){
        if(Note::find($request->id)->belongsToCurrentUser()){
            Session::flash('noteSaveError', 'There was a problem with your request!');
            return redirect()->back();
        }

        $this->validate($request, [
            'privateNote' => 'in:on,off',
        ]);

        if($request->privateNote == "on"){
            $privateStatus = 1;
        }
        else{
            $privateStatus = 0;
        }

        $note = Note::find($request->id);
        $note->private = $privateStatus;
        $note->save();

        Session::flash('noteSaveSuccess', 'Your Note settings were saved successfully!');
        return redirect('/');
    }
}
