<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ResearchController extends Controller
{
    public function index() {

        return view('research.index');
    }

    public function search(Request $request)
{
    $query = $request->input('query');
    $page = $request->input('page', 1); // Default to 1 if not provided
    $perPage = 1; // Number of items per page

    $items = Product::search($query, function ($meilisearch, $query, $options) use ($request) {
        $options['attributesToSearchOn'] = ['research_title', 'authors', 'keyword', 'abstract'];

        // Advanced filtering
        $filter = [];

        // Always filter by status = 'approved'
        // $filter[] = "status = 'Approved'"; // Corrected to 'Approved'

        if ($request->filled('authors')) {
            $filter[] = "authors = '" . addslashes($request->input('authors')) . "'";
        }

        if ($request->filled('keyword')) {
            $keywords = array_map(fn($k) => "'".addslashes($k)."'", $request->input('keyword'));
            $filter[] = "keyword IN [" . implode(',', $keywords) . "]";
        }

        if ($request->filled('abstract')) {
            $filter[] = "abstract = '" . addslashes($request->input('abstract')) . "'";
        }

        if (!empty($filter)) {
            $options['filter'] = implode(' AND ', $filter); // Combine filters with AND
        }

        return $meilisearch->search($query, $options);
    })->get();

    return view('research.index', compact('items', 'page', 'perPage'));
}

    public function keywordAutocomplete(Request $request)
    {
        $query = $request->input('query');
    
        $products = Product::search($query, function ($meilisearch, $query, $options) {
            $options['attributesToSearchOn'] = ['keyword'];
            $options['limit'] = 50;  // Increased limit to get more potential keywords
            return $meilisearch->search($query, $options);
        })->get();
    
        $keywords = collect();
    
        foreach ($products as $product) {
            $keywordArray = json_decode($product->keyword, true); // Parse the JSON array
    
            if (is_array($keywordArray)) {
                foreach ($keywordArray as $keyword) {
                    $keywords->push($keyword); // Add each keyword to the collection
                }
            }
        }
    
        $uniqueKeywords = $keywords->unique()->values()->take(10); // Get unique keywords, limit to 10, and re-index
    
        return response()->json($uniqueKeywords);
    }
    
    public function approve(Request $request, Product $product)
    {
        $auth = Auth::user();

    if (!$auth) {
    return response()->json(['success' => false, 'message' => 'You are not authenticated.'], 401);
    }

     if (
    !$auth->hasRole('Department Chair') &&
    !$auth->hasRole('Subject Adviser') &&   !$auth->hasRole('Super Admin')
    ) {
    return response()->json(['success' => false, 'message' => 'You do not have permission to approve products.'], 403);
    }

    // Check is authorized first before taking and proceeding

    $newStatus = $product->status;

    if($auth->hasRole('Department Chair')) {
        $newStatus = 'Approved';
    } elseif($auth->hasRole('Subject Adviser')) {
        $newStatus = 'Approved by Subject Adviser';
    }
    elseif($auth->hasRole('Super Admin') || $auth->hasRole('Admin') ) {
        $newStatus = 'Approved';
    }

    $product->status = $newStatus;
    $product->save();

    return response()->json(['success' => true, 'newStatus' => $newStatus]);
}
    

}