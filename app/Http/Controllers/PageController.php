<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use App\Models\TeamMember;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.show', [
            'page' => $this->page(Page::ABOUT),
        ]);
    }

    public function team()
    {
        return view('pages.team', [
            'page' => $this->page(Page::TEAM),
            'members' => TeamMember::published()->ordered()->get(),
        ]);
    }

    public function faq()
    {
        return view('pages.faq', [
            'page' => $this->page(Page::FAQ),
            'groups' => Faq::grouped(),
        ]);
    }

    /**
     * The route exists whether or not anyone has filled the page in, so an
     * unpublished or missing record 404s rather than rendering an empty shell.
     */
    private function page(string $slug): Page
    {
        return Page::published()->where('slug', $slug)->first()
            ?? throw new NotFoundHttpException("No published page for [{$slug}].");
    }
}
