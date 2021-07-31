<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return $categories;
    }

    public function store(Request $request)
    {
        $category = new Category;
        $category->name = $request->name;     
        $category->save();
        return ["message" => "Category Created Successfully"];
    }

    public function show($id)
    {
        $category = Category::find($id);
        return $category;
    }

    public function update(Request $request, $id)
    {
        // dd($request->name);
        $category = Category::find($id);
        $category->name = $request->name;

        // if($request->hasFile('picture')) {
        //     $image = $request->file( 'picture' );
        //     $filename    = $image->getClientOriginalName();
        //     $image_resize = Images::make( $image->getRealPath() );
        //     $image_resize->resize( 300, 250 );
        //     $image_resize->save( public_path( 'images/' .$filename ) );
        //     $category->picture = 'images/' .$filename; 
        // }

        $category->update();
        return $category;
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        $category->delete();
        return ["message" => "Deleted Successfully"];
    }
}
