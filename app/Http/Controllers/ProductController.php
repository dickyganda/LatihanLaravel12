<?php

namespace App\Http\Controllers;

//import model product
use App\Models\Product; 

//import return type View
use Illuminate\View\View;

//import return type redirectResponse
use Illuminate\Http\RedirectResponse;

//import Http Request
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index() : View
    {
        //get all products
        // $products = Product::whereNull('deleted_at');

        $products = Product::latest()->paginate(10);

        //render view with products
        return view ('products.index', compact('products'));
    }

    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        //validate form
        $request->validate([
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric'
        ]);

        //upload image
        $image = $request->file('image');
        $image->storeAs('products', $image->hashName());

        //create product
        Product::create([
            'image'         => $image->hashName(),
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->stock
        ]);

        //redirect to index
        return redirect('/products/index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function show($id)
    {
        //get product by ID
        $product = Product::findOrFail($id);

        //render view with product
        return view('/products/show', compact('product'));
    }

    public function edit($id)
    {
        //get product by ID
        $product = Product::findOrFail($id);

        //render view with product
        return view('/products/edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        //validate form
        $request->validate([
            'image'         => 'image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric'
        ]);

        //get product by ID
        $product = Product::findOrFail($id);

        //check if image is uploaded
        if ($request->hasFile('image')) {

						//delete old image
            Storage::delete('products/'.$product->image);

            //upload new image
            $image = $request->file('image');
            $image->storeAs('products', $image->hashName());

            //update product with new image
            $product->update([
                'image'         => $image->hashName(),
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock
            ]);

        } else {

            //update product without image
            $product->update([
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock
            ]);
        }

        //redirect to index
        return redirect('/products/index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    // public function destroy($id)
    // {
    //     //get product by ID
    //     $product = Product::findOrFail($id);

    //         //update product with new image
    //         $product->update([
    //             'deleted_at'    => Carbon::now(),
    //         ]);

    //     //redirect to index
    //     return redirect('/products/index')->with(['success' => 'Data Berhasil Diubah!']);
    // }

    public function destroy($id)
    {
        //get product by ID
        $product = Product::findOrFail($id);

        //delete image
        Storage::delete('products/'. $product->image);

        //delete product
        $product->delete();

        // $post = Product::find($id);  
        // $post->delete(); // This sets the deleted_at column, instead of removing the record

        //redirect to index
        // return redirect('/product/index')->with(['success' => 'Data Berhasil Dihapus!']);
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}