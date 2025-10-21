<?php

namespace App\Http\Controllers;

use App\Services\AliExpressDropshippingService;
use App\Models\Category;
use Illuminate\Http\Request;

class AliExpressController extends Controller
{
    protected $aliexpress;

    public function __construct(AliExpressDropshippingService $aliexpress)
    {
        $this->aliexpress = $aliexpress;
    }

    /**
     * Show the product search page
     */
    public function index()
    {
        // Check dropshipping enrollment status
        $status = $this->aliexpress->checkDropshippingAccess();

        $categories = Category::active()->get();

        return view('aliexpress.index', [
            'categories' => $categories,
            'enrollment_status' => $status,
        ]);
    }

    /**
     * Search products via AJAX
     */
    public function search(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
        ]);

        $result = $this->aliexpress->searchProducts(
            $request->get('keyword', ''),
            [
                'page' => $request->get('page', 1),
                'limit' => 20,
            ]
        );

        return response()->json($result);
    }

    /**
     * Get product details
     */
    public function details($productId)
    {
        $result = $this->aliexpress->getProductDetails($productId);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Check dropshipping enrollment
     */
    public function checkEnrollment()
    {
        $status = $this->aliexpress->checkDropshippingAccess();
        return response()->json($status);
    }
}
