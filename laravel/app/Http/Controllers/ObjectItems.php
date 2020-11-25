<?
namespace App\Http\Controllers;

use App\Objects;
use App\User;

class ObjectItems extends Controller
{

    public function items()
    {
        $where = ['status' => 'confirm'];
        if (@$_GET['id_nedv']) {
        	$where ['id_nedv'] = (int)$_GET['id_nedv'];
        }
        $items = Objects::orderBy('id', 'desc')->where($where)->get();
        return view('items', [
            'items' => $items
        ]);
    }

    public function item($id)
    {
        $item = Objects::find($id);
        $canEdit = User::canEdit();
        if (array_key_exists('delete', $_GET) && $canEdit) {
        	$item->status = 'trash';
            $item->save();
        }
        return view('item', [
            'item' => $item,
            'canEdit' =>$canEdit
        ]);
    }
}