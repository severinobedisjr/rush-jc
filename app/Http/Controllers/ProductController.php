<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator; 
use Symfony\Component\HttpFoundation\StreamedResponse; // Import the correct class
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Spatie\Permission\Models\Role;
use App\Models\User;

class ProductController extends Controller
{
    /**
     * Instantiate a new ProductController instance.
     */
    public function __construct()
    {
       $this->middleware('auth');
       $this->middleware('permission:create-product|edit-product|delete-product', ['only' => ['index','show']]);
       $this->middleware('permission:create-product', ['only' => ['create','store']]);
       $this->middleware('permission:edit-product', ['only' => ['edit','update']]);
       $this->middleware('permission:delete-product', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = Auth::user();

        if ($user->hasRole('Subject Adviser')) {
            // If the user is a Subject Adviser, fetch all products
            // where the product's `user_id` matches students related to the adviser
            $userId = Auth::id();
            $products = Product::where('subject_adviser', $userId)
                    ->latest()
                    ->paginate(3);

               
        } else {
            // If the user is not a Subject Adviser, fetch only their own products
           

                    $products = Product::latest()
                    ->paginate(3);
        }

        return view('products.index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $products = DB::table('products')->paginate(10);
        return view('products.create', [
            'roles' => Role::pluck('name')->all(),
            'subjectAdvisers' => User::role('Subject Adviser')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the input
        $validator = Validator::make($request->all(), [
            'research_title' => 'required|max:255',
            'abstract' => 'required',
            'keyword' => 'required',
            'authors.*' => 'required|string|max:255',  // Validate each author string
            'pdf_file' => 'required|file|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Return JSON with errors and 422 status
        }


      
        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pdfs', $filename, 'public');
            $validatedData = $validator->validated(); // Get validated data
            $validatedData['pdf_path'] = $path;
        }

        // 3. Create the Research model instance and save it to the database
        $research = new Product();
        $research->research_title = $validatedData['research_title'];
        $research->abstract = $validatedData['abstract'];
        $research->keyword = $validatedData['keyword'];
        $research->authors = $request->input('authors');
        $research->pdf_path = $validatedData['pdf_path'];
        $research->subject_adviser = $validatedData['subject_adviser'];


        $research->save();

        // 4. Return a JSON response with success message
        return response()->json(['message' => 'Research paper added successfully!'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function viewPdf(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if (!Storage::disk('public')->exists($product->pdf_path)) {
            abort(404, 'File not found.');
        }

        $path = Storage::path('public/' . $product->pdf_path); // Using Storage::path
        $mimeType = Storage::mimeType('public/' . $product->pdf_path); // Get the mime type


        $stream = function () use ($path) {
            readfile($path);
        };


        return new StreamedResponse($stream, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updates(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->all());
        return redirect()->back()
                ->withSuccess('Product is updated successfully.');
    }

    public function update(Request $request, Product $product)
    {
        // 1. Validate the input
        $validator = Validator::make($request->all(), [
            'research_title' => 'required|max:255',
            'abstract' => 'required',
            'keyword' => 'required',
            'authors' => 'required|array|max:5',
            'authors.*' => 'required|string|max:255',
            'pdf_file' => 'nullable|file|mimes:pdf|max:2048', // Adjust max size as needed. Now nullable, so not required
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Always return JSON
        }

        // 2. Handle the file upload
        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pdfs', $filename, 'public'); // Specify the folder
            $validatedData = $validator->validated();
            $validatedData['pdf_path'] = $path;
        }

        // 3. Update the Research model instance and save it to the database
        $product->research_title = $request->input('research_title'); // Using the correct request data
        $product->abstract = $request->input('abstract'); // Using the correct request data
        $product->keyword = $request->input('keyword'); // Using the correct request data
        $product->authors = $request->input('authors');  // Directly use the authors array
       // $research->pdf_path = $validatedData['pdf_path'];

        // 4. Return to a success page or display a success message

        $product->save();

        return response()->json(['message' => 'Research paper updated successfully!'], 200);

        //If error:
        /* try{
            $product->save();
            return response()->json(['message' => 'Research paper updated successfully!'], 200);
        }
        catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 422); // Always return JSON
        } */

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return response()->json(['success' => true, 'message' => 'Research deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting research: ' . $e->getMessage()], 500);
        }
    }


    
}