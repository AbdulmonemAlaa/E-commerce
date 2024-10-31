<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index(){
        return view('admin.index');
    }

    public function brands(){
        $brands = Brand::orderBy('id', 'desc')->Paginate(10);
        return view('admin.brands',['brands'=>$brands]);
    }

    public function add_brand(){
        return view('admin.add_brand');
    }

    public function store_brand(Request $request){
        // Step 1: Validate the incoming request data
        $request->validate([
            'name' => 'required',  // 'name' is required.
            'slug' => 'required|unique:brands,slug', // 'slug' is required and must be unique in the 'brands' table.
            'image' => 'required'  // 'image' must be in png, jpg, or jpeg format and no larger than 2MB.
        ]);

        // Step 2: Create a new Brand instance
        $brand = new Brand();

        // Step 3: Assign the 'name' from the request to the 'name' field of the Brand
        $brand->name = $request->name;

        // Step 4: Generate a URL-friendly slug based on the name and assign it to 'slug'
        $brand->slug = Str::slug($request->name);

        // Step 5: Get the uploaded image file from the request
        $image = $request->file('image');

        // Step 6: Get the file extension of the uploaded image
        $file_extension = $image->extension();

        // Step 7: Create a unique file name using the current timestamp and file extension
        $file_name = Carbon::now()->timestamp . " " . $file_extension;

        // Step 8: Generate brand thumbnail image using the provided method
        $this->GenerateBrandThumbailsImage($image, $file_name);

        // Step 9: Set the 'image' field of the brand to the generated file name
        $brand->image = $file_name;

        // Step 10: Save the Brand to the database
        $brand->save();

        // Step 11: Redirect the user to the 'admin.brands' route with a success message
        return redirect()->route('admin.brands')->with('status', 'Brand has been added successfully!');
    }

    public function GenerateBrandThumbailsImage($image, $imageName)
    {
        // Step 1: Define the destination path where the image will be saved
        $destinationPath = public_path('uploads/brands');

        // Step 2: Read the image file from the provided path using the Image library
        $img = Image::read($image->path());

        // Step 3: Cover the image to a 124x124 size, focusing on the top area
        $img->cover(124, 124, "top");

        // Step 4: Resize the image to 124x124 pixels while maintaining the aspect ratio
        $img->resize(124, 124, function ($constraint){
            $constraint->aspectRatio();
        });

        // Step 5: Save the resized image to the destination path with the provided file name
        $img->save($destinationPath . '/' . $imageName);
    }

    public function edit_brand($id){
        $brand = Brand::findOrFail($id);
        return view('admin.edit_brand',['brand'=>$brand]);
    }
    public function update_brand(Request $request, $id){
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $id,
            'image' => 'nullable',
        ]);

        $brand = Brand::findOrFail($id);
        $brand->name = $request->name;
        $brand->slug = $request->slug;

        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/brands/' . $brand->image))){
                File::delete(public_path('uploads/brands/' . $brand->image));
            }
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;

            $this->GenerateBrandThumbailsImage($image, $file_name);

            $brand->image = $file_name;
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand has been edited successfully!');
    }
    public function delete_brand($id){
        $brand = Brand::findOrFail($id);
        if(File::exists(public_path('uploads/brands/' . $brand->image))){
            File::delete(public_path('uploads/brands/' . $brand->image));
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Brand has been deleted successfully!');
    }
}
