<?php

namespace App\Http\Controllers;

use App\Pages;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NewsController extends Controller
{

    public function index()
    {

        $canEdit = Auth::id() == 1;

        if (isset($_GET['delete']) && $canEdit) {
        	Pages::where('id', $_GET['delete'])->delete();
            return redirect('news');
        }

        return view('news', [
            'items' => Pages::where('type', 'news')->get(),
            'canEdit' => $canEdit,
            'addForm' => isset($_GET['add'])
        ]);
    }

    public function add(Request $request)
    {

        $all = $request->all();

        $this->validate($request, [
            'title' => 'required|min:10',
            'content' => 'required|min:10',
        ]);

        $object = new Pages();
    	$object->fill($all);
        $object->alias = Str::slug($all['title']);
        $object->type = 'news';
        $object->save();

        return redirect('/news');
    }
}