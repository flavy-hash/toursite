<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TourController extends Controller
{
    /**
     * Query keys the listing filters on. Each maps to a column and is matched
     * case-insensitively, so the nav's ?category=wildlife links line up with
     * the "Wildlife" stored on the package.
     */
    private const FILTERS = ['category', 'region', 'tier', 'difficulty'];

    /**
     * List every published package, narrowed by the query string.
     */
    public function index(Request $request): View
    {
        $query = Tour::query()->published()->ordered();

        foreach ($this->activeFilters($request) as $column => $value) {
            $query->whereRaw("LOWER({$column}) = ?", [mb_strtolower($value)]);
        }

        return view('tours.index', [
            'tours' => $query->get(),
            'active' => $this->activeFilters($request),
            'categories' => Tour::published()->distinct()->orderBy('category')->pluck('category')->all(),
            'total' => Tour::published()->count(),
        ]);
    }

    /**
     * Show a single package. Unpublished packages 404 for the public.
     */
    public function show(string $slug): View
    {
        $tour = Tour::published()->where('slug', $slug)->firstOrFail();

        return view('tours.show', [
            'tour' => $tour,
            'related' => $this->related($tour),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function activeFilters(Request $request): array
    {
        return collect(self::FILTERS)
            ->mapWithKeys(fn (string $key) => [$key => $request->query($key)])
            // Reject arrays (?category[]=x) and blanks before they reach SQL.
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->all();
    }

    /**
     * Up to three other packages, preferring ones in the same category.
     */
    private function related(Tour $tour)
    {
        return Tour::published()
            ->whereKeyNot($tour->getKey())
            ->orderByRaw('category = ? DESC', [$tour->category])
            ->ordered()
            ->limit(3)
            ->get();
    }
}
