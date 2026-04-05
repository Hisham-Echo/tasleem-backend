<?php
// app/Http/Controllers/Api/CategoryController.php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends BaseController
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $categories = $query->paginate($request->get('per_page', 15));

        return $this->sendPaginated(
            $categories,
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $data = $request->all();
        
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('categories', 'public');
        }

        $category = Category::create($data);

        return $this->sendResponse(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    public function show($id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return $this->sendError('Category not found');
        }

        return $this->sendResponse(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->sendError('Category not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $id . ',category_id',
            'photo' => 'nullable|image|max:2048',
            'status' => 'sometimes|in:1,0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $data = $request->all();
        
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('categories', 'public');
        }

        $category->update($data);

        return $this->sendResponse(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->sendError('Category not found');
        }

        if ($category->products()->count() > 0) {
            return $this->sendError('Cannot delete category with associated products');
        }

        $category->delete();

        return $this->sendResponse(null, 'Category deleted successfully');
    }
}