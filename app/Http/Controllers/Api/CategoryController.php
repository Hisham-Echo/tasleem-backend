<?php
namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends BaseController
{
    public function index(Request $request)
    {
        // Return ALL active categories — no pagination for the dropdown
        $categories = Category::withCount('products')
            ->where('status', '1')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function show($id)
    {
        $category = Category::with('products')->find($id);
        if (!$category) return $this->sendError('Category not found', [], 404);
        return $this->sendResponse(new CategoryResource($category), 'Category retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255|unique:categories',
            'photo'  => 'nullable|image|max:2048',
            'status' => 'sometimes|in:1,0',
        ]);

        if ($validator->fails()) return $this->sendError('Validation Error', $validator->errors(), 422);

        $data = ['name' => $request->name, 'status' => $request->get('status', '1')];

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('categories', 'public');
        }

        $category = Category::create($data);
        return $this->sendResponse(new CategoryResource($category), 'Category created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) return $this->sendError('Category not found', [], 404);

        $validator = Validator::make($request->all(), [
            'name'   => 'sometimes|string|max:255|unique:categories,name,'.$id.',category_id',
            'status' => 'sometimes|in:1,0',
        ]);

        if ($validator->fails()) return $this->sendError('Validation Error', $validator->errors(), 422);

        $category->update($request->only(['name', 'status']));
        return $this->sendResponse(new CategoryResource($category), 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) return $this->sendError('Category not found', [], 404);
        if ($category->products()->count() > 0) return $this->sendError('Cannot delete a category that has products');
        $category->delete();
        return $this->sendResponse(null, 'Category deleted successfully');
    }
}
