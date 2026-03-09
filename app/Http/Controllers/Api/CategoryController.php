<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->paginate(15);
        return CategoryResource::collection($categories);
    }

    public function show($id)
    {
        $category = Category::with('products')->findOrFail($id);
        return new CategoryResource($category);
    }

    public function store(CategoryStoreRequest $request)
    {
        $data = $request->validated();
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $category = Category::create($data);
        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function update(CategoryUpdateRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validated();
        if (array_key_exists('name', $data) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $category->update($data);
        return new CategoryResource($category->fresh('products'));
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
