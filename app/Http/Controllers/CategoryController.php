<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\AliExpressService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected $aliexpressService;

    public function __construct(AliExpressService $aliexpressService)
    {
        $this->aliexpressService = $aliexpressService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parentId = $request->query('parent_id');

        $query = Category::withCount('products', 'children');

        if ($parentId) {
            // Show subcategories of a specific parent
            $query->where('parent_id', $parentId);
            $parentCategory = Category::find($parentId);
        } else {
            // Show only main categories (no parent)
            $query->whereNull('parent_id')
                  ->with(['children' => function($query) {
                      $query->select('id', 'name', 'parent_id')->orderBy('name');
                  }]);
            $parentCategory = null;
        }

        $categories = $query->orderBy('order')->paginate(20);

        return view('categories.index', compact('categories', 'parentCategory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'aliexpress_category_id' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('categories', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load(['products' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'aliexpress_category_id' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($category->photo) {
                Storage::disk('public')->delete($category->photo);
            }
            $validated['photo'] = $request->file('photo')->store('categories', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with products. Please reassign or delete products first.');
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return back()->with('error', 'Cannot delete category with subcategories. Please delete subcategories first.');
        }

        // Delete photo if exists
        if ($category->photo) {
            Storage::disk('public')->delete($category->photo);
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Fetch subcategories from AliExpress API
     */
    public function fetchSubcategories(Category $category)
    {
        try {
            if (!$category->aliexpress_category_id) {
                return back()->with('error', 'This category does not have an AliExpress category ID.');
            }

            // Fetch categories from AliExpress
            $allCategories = $this->aliexpressService->getChildCategories($category->aliexpress_category_id);

            Log::info('Fetched categories from API', [
                'parent_id' => $category->id,
                'aliexpress_id' => $category->aliexpress_category_id,
                'total_categories' => count($allCategories ?? [])
            ]);

            if (!$allCategories || empty($allCategories)) {
                return back()->with('warning', 'No categories found.');
            }

            // Filter only the direct children of this category
            // Convert to string for comparison since API returns integers
            $subcategories = array_filter($allCategories, function($cat) use ($category) {
                $parentId = $cat['parent_category_id'] ?? null;
                // Compare as strings to handle both string and int values
                return $parentId !== null && (string)$parentId === (string)$category->aliexpress_category_id;
            });

            // Re-index array after filtering
            $subcategories = array_values($subcategories);

            Log::info('Filtered subcategories', [
                'filtered_count' => count($subcategories),
                'looking_for_parent_id' => $category->aliexpress_category_id,
                'sample_parent_ids' => array_slice(array_map(function($c) {
                    return $c['parent_category_id'] ?? 'null';
                }, $allCategories), 0, 10)
            ]);

            // Show view even if no subcategories found, with all categories for debugging
            return view('categories.subcategories', [
                'category' => $category,
                'subcategories' => $subcategories,
                'allCategories' => $allCategories
            ]);

        } catch (\Exception $e) {
            Log::error('Fetch subcategories error', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to fetch subcategories: ' . $e->getMessage());
        }
    }

    /**
     * Save subcategories to database
     */
    public function saveSubcategories(Request $request, Category $category)
    {
        $request->validate([
            'subcategories' => 'required|array',
            'subcategories.*.id' => 'required|string',
            'subcategories.*.name' => 'required|string',
        ]);

        try {
            $saved = 0;
            $skipped = 0;

            foreach ($request->subcategories as $subcategoryData) {
                // Check if already exists
                $existing = Category::where('aliexpress_category_id', $subcategoryData['id'])->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Create subcategory
                Category::create([
                    'name' => $subcategoryData['name'],
                    'name_ar' => $subcategoryData['name_ar'] ?? null,
                    'slug' => Str::slug($subcategoryData['name']),
                    'aliexpress_category_id' => $subcategoryData['id'],
                    'parent_id' => $category->id,
                    'order' => $subcategoryData['order'] ?? 0,
                    'is_active' => true,
                ]);

                $saved++;
            }

            $message = "Successfully saved {$saved} subcategories.";
            if ($skipped > 0) {
                $message .= " {$skipped} already existed.";
            }

            return redirect()->route('categories.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Save subcategories error', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to save subcategories: ' . $e->getMessage());
        }
    }

    /**
     * Fetch category tree from AliExpress
     */
    public function fetchCategoryTree()
    {
        try {
            $allCategories = $this->aliexpressService->getCategoryTree();

            Log::info('Fetched all categories from API', [
                'total_categories' => count($allCategories ?? [])
            ]);

            if (!$allCategories || empty($allCategories)) {
                return back()->with('warning', 'No categories found.');
            }

            // Separate root categories (no parent_category_id) and subcategories
            $rootCategories = [];
            $childCategories = [];

            foreach ($allCategories as $category) {
                if (!isset($category['parent_category_id']) || empty($category['parent_category_id'])) {
                    $rootCategories[] = $category;
                } else {
                    $childCategories[] = $category;
                }
            }

            Log::info('Organized categories', [
                'root_count' => count($rootCategories),
                'children_count' => count($childCategories)
            ]);

            return view('categories.tree', [
                'categoryTree' => $rootCategories,
                'allCategories' => $allCategories,
                'rootCount' => count($rootCategories),
                'childCount' => count($childCategories)
            ]);

        } catch (\Exception $e) {
            Log::error('Fetch category tree error', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to fetch category tree: ' . $e->getMessage());
        }
    }

    /**
     * Save multiple categories from tree
     */
    public function saveCategoryTree(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|string',
            'categories.*.name' => 'required|string',
        ]);

        try {
            $saved = 0;
            $skipped = 0;

            foreach ($request->categories as $categoryData) {
                // Check if already exists
                $existing = Category::where('aliexpress_category_id', $categoryData['id'])->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Create category
                Category::create([
                    'name' => $categoryData['name'],
                    'name_ar' => $categoryData['name_ar'] ?? null,
                    'slug' => Str::slug($categoryData['name']),
                    'aliexpress_category_id' => $categoryData['id'],
                    'parent_id' => $categoryData['parent_id'] ?? null,
                    'order' => $categoryData['order'] ?? 0,
                    'is_active' => true,
                ]);

                $saved++;
            }

            $message = "Successfully saved {$saved} categories.";
            if ($skipped > 0) {
                $message .= " {$skipped} already existed.";
            }

            return redirect()->route('categories.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Save category tree error', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to save categories: ' . $e->getMessage());
        }
    }

    /**
     * Import ALL categories from AliExpress with parent-child relationships
     */
    public function importAllCategories()
    {
        try {
            // Fetch all categories from AliExpress
            $allCategories = $this->aliexpressService->getCategoryTree();

            if (!$allCategories || empty($allCategories)) {
                return back()->with('warning', 'No categories found to import.');
            }

            $saved = 0;
            $skipped = 0;
            $errors = 0;
            $categoryMap = []; // Map AliExpress IDs to our database IDs

            // First pass: Create all root categories (no parent)
            foreach ($allCategories as $category) {
                if (isset($category['parent_category_id']) && !empty($category['parent_category_id'])) {
                    continue; // Skip children for now
                }

                $aliexpressCategoryId = $category['category_id'] ?? null;
                $categoryName = $category['category_name'] ?? null;

                if (!$aliexpressCategoryId || !$categoryName) {
                    $errors++;
                    continue;
                }

                // Check if already exists
                $existing = Category::where('aliexpress_category_id', $aliexpressCategoryId)->first();

                if ($existing) {
                    $categoryMap[$aliexpressCategoryId] = $existing->id;
                    $skipped++;
                    continue;
                }

                // Create root category
                $newCategory = Category::create([
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'aliexpress_category_id' => $aliexpressCategoryId,
                    'parent_id' => null,
                    'order' => $saved,
                    'is_active' => true,
                ]);

                $categoryMap[$aliexpressCategoryId] = $newCategory->id;
                $saved++;
            }

            // Second pass: Create all child categories
            foreach ($allCategories as $category) {
                if (!isset($category['parent_category_id']) || empty($category['parent_category_id'])) {
                    continue; // Skip roots, already created
                }

                $aliexpressCategoryId = $category['category_id'] ?? null;
                $categoryName = $category['category_name'] ?? null;
                $parentAliexpressId = $category['parent_category_id'];

                if (!$aliexpressCategoryId || !$categoryName) {
                    $errors++;
                    continue;
                }

                // Check if already exists
                $existing = Category::where('aliexpress_category_id', $aliexpressCategoryId)->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Find parent category in our database
                $parentId = $categoryMap[$parentAliexpressId] ?? null;

                // If parent doesn't exist in our DB, create it first
                if (!$parentId) {
                    $parentCategory = Category::where('aliexpress_category_id', $parentAliexpressId)->first();
                    if (!$parentCategory) {
                        // Parent not in our system, skip this category or create orphan
                        Log::warning('Parent category not found', [
                            'child_id' => $aliexpressCategoryId,
                            'parent_id' => $parentAliexpressId
                        ]);
                        $errors++;
                        continue;
                    }
                    $parentId = $parentCategory->id;
                }

                // Create child category
                $newCategory = Category::create([
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'aliexpress_category_id' => $aliexpressCategoryId,
                    'parent_id' => $parentId,
                    'order' => $saved,
                    'is_active' => true,
                ]);

                $saved++;
            }

            $message = "Successfully imported {$saved} categories.";
            if ($skipped > 0) {
                $message .= " {$skipped} already existed.";
            }
            if ($errors > 0) {
                $message .= " {$errors} had errors.";
            }

            return redirect()->route('categories.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Import all categories error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to import categories: ' . $e->getMessage());
        }
    }

    /**
     * Toggle category active status
     */
    public function toggleStatus(Category $category)
    {
        try {
            $category->is_active = !$category->is_active;
            $category->save();

            $status = $category->is_active ? 'activated' : 'deactivated';

            return back()->with('success', "Category '{$category->name}' has been {$status}.");

        } catch (\Exception $e) {
            Log::error('Toggle category status error', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to toggle category status: ' . $e->getMessage());
        }
    }
}
