<?php

namespace App\Http\Controllers;

use App\Product;
use App\Category;
use App\Sub_category;
use Illuminate\Http\Request;
use DB;
use Validator;
use Importer;
// use Intervention\Image\ImageManagerStatic as Images;
use Intervention\Image\Facades\Image as Images;

class ProductController extends Controller
{
    public function index()
    {
        $data = Product::with('category')->get();
        return $data;
    }

    public function create()
    {
        $categories = Category::all();
        $sub_categories = Sub_category::all();
        return view('products.create', compact('categories', 'sub_categories'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
           'name'      => 'required|max:200',
        ]);

        $product = new Product;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        // $product->sub_category_id = $request->sub_category_id;

        if($request->hasFile('picture')) {
            $image = $request->file( 'picture' );
            $filename    = $image->getClientOriginalName();
            $image_resize = Images::make( $image->getRealPath() );
            $image_resize->resize( 300, 250 );
            $image_resize->save( public_path( 'images/' .$filename ) );
            $product->picture = 'images/' .$filename; 
        }

        $product->save();
        return $product;
        // return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $data = $product->with('category')->first();
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::find($id);
        $categories = Category::all();
        $sub_categories = Sub_category::all();
        return view('products.edit', compact('product', 'categories', 'sub_categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
           'name'      => 'required|max:200',
        ]);

        $product = Product::find($id);       
        $product->name = $request->name;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->description = $request->description;

        if($request->hasFile('picture')) {
            $image = $request->file( 'picture' );
            $filename    = $image->getClientOriginalName();
            $image_resize = Images::make( $image->getRealPath() );
            $image_resize->resize( 300, 250 );
            $image_resize->save( public_path( 'images/' .$filename ) );
            $product->picture = 'images/' .$filename; 
        }

        $product->update();
        return $product;

        // return redirect()->route('products.index');
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();
        return ['message' => 'Deleted Successfully'];
        // return back();
    }


    // Search product
    public function search_product_view(Request $request)
    {   
        // dd($request);
        $query = $request->get('query');
        // dd($query);
        $data = DB::table('products')
                        ->where('name', 'like', '%'.$query.'%')
                        ->orWhere('category_id', 'like', '%'.$query.'%')
                        ->orderBy('id', 'desc')
                        ->get();
        // dd($data);   
        $categories = Category::all();
        $sub_categories = Sub_category::all();             
        // return view('search', compact('data', 'categories', 'sub_categories'));
        return view('search', compact('data', 'categories', 'sub_categories'));
    }

    public function import_file()
    {
        return view('excelTest');
    }

    public function import_excel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx, xls,csv'
        ]);

        if($validator->passes()){
            $file = $request->file('file');
            $date = date('Ymd_His');
            $fileName = $date.'_'.$file->getClientOriginalName();
            $uploadPath = public_path('upload/');
            $file->move($uploadPath, $fileName);

            $excel = Importer::make('Excel');
            $excel->load($uploadPath.$fileName);
            $collection = $excel->getCollection();

           

            if(sizeof($collection[1]) == 5){
                for($row = 1; $row < sizeof($collection); $row++){
                    try {
                        $product = new Product;
                        $product->id = $collection[$row][0];
                        $product->name = $collection[$row][1];
                        $product->description = $collection[$row][2];
                        $product->price = $collection[$row][3];
                        $product->quantity = $collection[$row][4];
                        $product->save();
                    } catch (Exception $e) {
                        return redirect()->back()->with(['errors' => $e->getMessage()]);
                    }
                }
            } else {
                return redirect()->back()->with(['errors' => [0 => 'Please provide valid data']]);
            }

        } else {
            return redirect()->back()->with(['errors' => $validator->errors()->all()]);
        }
    }
}
