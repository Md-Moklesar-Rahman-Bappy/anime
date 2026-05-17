<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::latest()->get();
        return view('admin.genres.index', compact('genres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:genres']);
        $data['slug'] = Str::slug($data['name']);
        Genre::create($data);
        return redirect()->route('admin.genres.index')->with('success', 'Genre created.');
    }

    public function update(Request $request, Genre $genre)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:genres,name,' . $genre->id]);
        $data['slug'] = Str::slug($data['name']);
        $genre->update($data);
        return redirect()->route('admin.genres.index')->with('success', 'Genre updated.');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();
        return redirect()->route('admin.genres.index')->with('success', 'Genre deleted.');
    }
}
