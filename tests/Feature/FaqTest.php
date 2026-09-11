<?php

namespace Tests\Feature;

use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Resources\Faqs\Pages\EditFaq;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Models\Faq;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The FAQ page needs its page record, the same as /about does.
        Page::create([
            'slug' => Page::FAQ,
            'title' => 'Frequently Asked Questions',
            'heading' => 'Questions travellers ask us',
            'intro' => 'Visas, vaccinations and packing.',
            'sections' => [],
            'is_published' => true,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function faq(array $overrides = []): Faq
    {
        return Faq::create(array_merge([
            'question' => 'Do I need a visa?',
            'answer' => 'Most visitors do.',
            'category' => 'Planning your trip',
            'is_published' => true,
        ], $overrides));
    }

    public function test_the_faq_page_shows_questions_and_answers(): void
    {
        $this->faq();

        $this->get('/faq')
            ->assertOk()
            ->assertSee('Questions travellers ask us')
            ->assertSee('Do I need a visa?')
            ->assertSee('Most visitors do.');
    }

    public function test_questions_are_grouped_under_their_heading(): void
    {
        $this->faq(['question' => 'Visa?', 'category' => 'Planning your trip']);
        $this->faq(['question' => 'Big Five?', 'category' => 'On safari']);

        $this->get('/faq')
            ->assertOk()
            ->assertSee('Planning your trip')
            ->assertSee('On safari');
    }

    public function test_a_question_with_no_category_falls_under_general(): void
    {
        $this->faq(['category' => null]);
        $this->faq(['question' => 'Another', 'category' => 'On safari']);

        $this->get('/faq')->assertOk()->assertSee(Faq::GENERAL);
    }

    public function test_a_single_group_gets_no_redundant_heading(): void
    {
        // One "General" title over the whole page says nothing worth saying.
        $this->faq(['category' => null]);

        $this->get('/faq')->assertOk()->assertDontSee(Faq::GENERAL);
    }

    public function test_group_order_follows_the_first_question_in_each(): void
    {
        // Dragging a question to the top should carry its section with it.
        $this->faq(['question' => 'Second group first Q', 'category' => 'On safari', 'sort_order' => 2]);
        $this->faq(['question' => 'First group first Q', 'category' => 'Before you book', 'sort_order' => 1]);

        $html = $this->get('/faq')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'On safari'), strpos($html, 'Before you book'));
    }

    public function test_a_hidden_question_is_left_off_the_page(): void
    {
        $this->faq(['question' => 'Public question']);
        $this->faq(['question' => 'Draft question', 'is_published' => false]);

        $this->get('/faq')
            ->assertOk()
            ->assertSee('Public question')
            ->assertDontSee('Draft question');
    }

    public function test_answers_split_into_paragraphs(): void
    {
        $this->faq(['answer' => "First para.\n\nSecond para."]);

        $html = $this->get('/faq')->assertOk()->getContent();

        $this->assertStringContainsString('<p>First para.</p>', $html);
        $this->assertStringContainsString('<p>Second para.</p>', $html);
    }

    public function test_the_page_works_with_no_questions_yet(): void
    {
        $this->get('/faq')->assertOk()->assertSee('Still not sure?');
    }

    public function test_the_page_publishes_faq_structured_data(): void
    {
        // This is most of why an FAQ page earns its keep: Google renders these
        // as expandable questions directly in the results.
        $this->faq(['question' => 'Do I need a visa?', 'answer' => 'Most visitors do.']);

        $html = $this->get('/faq')->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        $schemas = array_map(fn (string $json) => json_decode(trim($json), true), $matches[1]);
        $faqSchema = collect($schemas)->firstWhere('@type', 'FAQPage');

        $this->assertNotNull($faqSchema, 'No FAQPage schema on the page.');
        $this->assertSame('Do I need a visa?', $faqSchema['mainEntity'][0]['name']);
        $this->assertSame('Most visitors do.', $faqSchema['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function test_hidden_questions_stay_out_of_the_structured_data(): void
    {
        // Marking up an answer Google can show but the page does not would be
        // cloaking, and is a manual-action risk.
        $this->faq(['question' => 'Public question']);
        $this->faq(['question' => 'Draft question', 'is_published' => false]);

        $html = $this->get('/faq')->assertOk()->getContent();

        preg_match('#<script type="application/ld\+json">(?:(?!</script>).)*?FAQPage.*?</script>#s', $html, $m);

        $this->assertStringNotContainsString('Draft question', $m[0]);
    }

    public function test_no_schema_is_emitted_when_there_are_no_questions(): void
    {
        $this->get('/faq')->assertOk()->assertDontSee('FAQPage');
    }

    public function test_an_unpublished_faq_page_404s(): void
    {
        Page::where('slug', Page::FAQ)->update(['is_published' => false]);

        $this->get('/faq')->assertNotFound();
    }

    public function test_the_faq_page_is_in_the_sitemap(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('faq'), false);
    }

    // --- Admin -----------------------------------------------------------

    public function test_an_admin_can_add_a_question(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateFaq::class)
            ->fillForm([
                'question' => 'How much should I tip?',
                'answer' => 'It is customary but discretionary.',
                'category' => 'Payments & cancellation',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('faqs', ['question' => 'How much should I tip?']);

        $this->get('/faq')->assertOk()->assertSee('How much should I tip?');
    }

    public function test_a_new_question_joins_the_end_of_the_list(): void
    {
        $this->faq(['sort_order' => 7]);

        Livewire::actingAs($this->admin())
            ->test(CreateFaq::class)
            ->fillForm(['question' => 'Newest', 'answer' => 'Answer.'])
            ->call('create');

        $this->assertSame(8, Faq::where('question', 'Newest')->value('sort_order'));
    }

    public function test_an_admin_can_edit_an_answer(): void
    {
        $faq = $this->faq(['answer' => 'Old answer.']);

        Livewire::actingAs($this->admin())
            ->test(EditFaq::class, ['record' => $faq->getKey()])
            ->fillForm(['answer' => 'Corrected answer.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Corrected answer.', $faq->refresh()->answer);

        $this->get('/faq')->assertOk()->assertSee('Corrected answer.');
    }

    public function test_an_admin_can_hide_a_question_without_deleting_it(): void
    {
        $faq = $this->faq(['question' => 'Under review']);

        Livewire::actingAs($this->admin())
            ->test(ListFaqs::class)
            ->callTableAction('toggle_published', $faq)
            ->assertHasNoTableActionErrors();

        $this->assertFalse($faq->refresh()->is_published);
        $this->assertDatabaseHas('faqs', ['question' => 'Under review']);

        $this->get('/faq')->assertDontSee('Under review');
    }

    public function test_questions_can_be_hidden_in_bulk(): void
    {
        $one = $this->faq(['question' => 'One']);
        $two = $this->faq(['question' => 'Two']);

        Livewire::actingAs($this->admin())
            ->test(ListFaqs::class)
            ->callTableBulkAction('bulk_hide', [$one, $two]);

        $this->assertFalse($one->refresh()->is_published);
        $this->assertFalse($two->refresh()->is_published);
    }

    public function test_the_question_is_required(): void
    {
        // The answer is not: a question from the website arrives without one.
        // It becomes required the moment someone ticks "publish".
        Livewire::actingAs($this->admin())
            ->test(CreateFaq::class)
            ->fillForm(['question' => '', 'answer' => ''])
            ->call('create')
            ->assertHasFormErrors(['question' => 'required']);
    }

    public function test_the_resource_needs_an_admin(): void
    {
        $this->get('/admin/faqs')->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin/faqs')
            ->assertForbidden();

        $this->actingAs($this->admin())->get('/admin/faqs')->assertOk();
    }

    // --- Visitors asking a question ---------------------------------------

    public function test_the_faq_page_offers_the_ask_form(): void
    {
        $this->get('/faq')
            ->assertOk()
            ->assertSee('data-open-faq', false)
            ->assertSee('id="faq-dialog"', false)
            ->assertSee(route('faq.ask'), false);
    }

    public function test_a_visitor_can_submit_a_question(): void
    {
        $this->post('/faq/ask', [
            'question' => 'How far in advance should I book a Kilimanjaro climb?',
            'asked_by_name' => 'Amina',
            'asked_by_email' => 'amina@example.com',
        ])->assertRedirect(route('faq') . '#ask');

        $faq = Faq::latest('id')->first();

        $this->assertSame('How far in advance should I book a Kilimanjaro climb?', $faq->question);
        $this->assertSame('Amina', $faq->asked_by_name);
        $this->assertNotNull($faq->asked_at);
        $this->assertTrue($faq->wasAskedByAVisitor());
    }

    public function test_a_submitted_question_arrives_unanswered_and_unpublished(): void
    {
        // It must not reach the page before a person has written a reply.
        $this->post('/faq/ask', ['question' => 'Is there wifi at the camps?']);

        $faq = Faq::latest('id')->first();

        $this->assertFalse($faq->isAnswered());
        $this->assertFalse($faq->is_published);

        $this->get('/faq')->assertOk()->assertDontSee('Is there wifi at the camps?');
    }

    public function test_name_and_email_are_optional(): void
    {
        $this->post('/faq/ask', ['question' => 'What is the luggage limit on the light aircraft?'])
            ->assertRedirect(route('faq') . '#ask');

        $faq = Faq::latest('id')->first();

        $this->assertNull($faq->asked_by_name);
        $this->assertNull($faq->asked_by_email);
    }

    public function test_a_blank_or_tiny_question_is_rejected(): void
    {
        $this->post('/faq/ask', ['question' => ''])->assertSessionHasErrors('question', null, 'faq');
        $this->post('/faq/ask', ['question' => 'Hi?'])->assertSessionHasErrors('question', null, 'faq');

        $this->assertSame(0, Faq::fromVisitors()->count());
    }

    public function test_a_bad_email_is_rejected(): void
    {
        $this->post('/faq/ask', [
            'question' => 'Do you arrange airport transfers from JRO?',
            'asked_by_email' => 'not-an-address',
        ])->assertSessionHasErrors('asked_by_email', null, 'faq');
    }

    public function test_the_honeypot_turns_bots_away(): void
    {
        $this->post('/faq/ask', [
            'question' => 'Buy cheap watches at my website today please',
            'website' => 'http://spam.example.com',
        ])->assertSessionHasErrors('website', null, 'faq');

        $this->assertSame(0, Faq::fromVisitors()->count());
    }

    public function test_a_submitted_question_joins_the_end_of_the_list(): void
    {
        $this->faq(['sort_order' => 12]);

        $this->post('/faq/ask', ['question' => 'Are the vehicles fully enclosed?']);

        $this->assertSame(13, Faq::latest('id')->first()->sort_order);
    }

    public function test_the_page_confirms_the_question_was_received(): void
    {
        $this->followingRedirects()
            ->post('/faq/ask', ['question' => 'Can you cater for a vegan diet on safari?'])
            ->assertOk()
            ->assertSee('your question is with us');
    }

    // --- Answering in the admin panel --------------------------------------

    public function test_the_sidebar_badge_counts_unanswered_questions(): void
    {
        $resource = \App\Filament\Resources\Faqs\FaqResource::class;

        $this->assertNull($resource::getNavigationBadge());

        $this->post('/faq/ask', ['question' => 'How cold does it get on the crater rim?']);

        $this->assertSame('1', $resource::getNavigationBadge());
    }

    public function test_an_admin_can_answer_and_publish_in_one_step(): void
    {
        $this->post('/faq/ask', ['question' => 'How cold does it get on the crater rim?']);
        $asked = Faq::latest('id')->first();

        Livewire::actingAs($this->admin())
            ->test(ListFaqs::class)
            ->callTableAction('answer', $asked, [
                'answer' => 'Near freezing before dawn. Bring a proper fleece.',
                'category' => 'On safari',
                'is_published' => true,
            ])
            ->assertHasNoTableActionErrors();

        $asked->refresh();

        $this->assertTrue($asked->isAnswered());
        $this->assertTrue($asked->is_published);
        $this->assertSame('On safari', $asked->category);

        $this->get('/faq')
            ->assertOk()
            ->assertSee('How cold does it get on the crater rim?')
            ->assertSee('Near freezing before dawn. Bring a proper fleece.');
    }

    public function test_an_admin_can_answer_without_publishing_yet(): void
    {
        $this->post('/faq/ask', ['question' => 'Do you take payment in Tanzanian shillings?']);
        $asked = Faq::latest('id')->first();

        Livewire::actingAs($this->admin())
            ->test(ListFaqs::class)
            ->callTableAction('answer', $asked, [
                'answer' => 'Card and bank transfer only.',
                'is_published' => false,
            ]);

        $asked->refresh();

        $this->assertTrue($asked->isAnswered());
        $this->assertFalse($asked->is_published);

        $this->get('/faq')->assertDontSee('Do you take payment in Tanzanian shillings?');
    }

    public function test_the_answer_action_requires_an_answer(): void
    {
        $this->post('/faq/ask', ['question' => 'Is travel insurance included in the price?']);
        $asked = Faq::latest('id')->first();

        Livewire::actingAs($this->admin())
            ->test(ListFaqs::class)
            ->callTableAction('answer', $asked, ['answer' => ''])
            ->assertHasTableActionErrors(['answer']);

        $this->assertFalse($asked->fresh()->isAnswered());
    }

    public function test_the_answer_action_disappears_once_answered(): void
    {
        Livewire::actingAs($this->admin())
            ->test(ListFaqs::class)
            ->assertTableActionHidden('answer', $this->faq());
    }

    public function test_an_unanswered_question_cannot_be_published_by_a_stray_click(): void
    {
        // Publishing a blank answer would put an empty row on the page.
        $this->post('/faq/ask', ['question' => 'Can we charge camera batteries in the vehicles?']);

        Livewire::actingAs($this->admin())
            ->test(ListFaqs::class)
            ->assertTableActionHidden('toggle_published', Faq::latest('id')->first());
    }

    public function test_an_unanswered_question_never_reaches_the_page_even_if_published(): void
    {
        // Belt and braces: grouped() filters on the answer, not just the flag.
        $this->faq(['question' => 'Orphan question', 'answer' => null, 'is_published' => true]);

        $this->get('/faq')->assertOk()->assertDontSee('Orphan question');
    }

    public function test_an_admin_can_still_write_a_question_from_scratch(): void
    {
        // The original behaviour, unchanged by the visitor queue.
        Livewire::actingAs($this->admin())
            ->test(CreateFaq::class)
            ->fillForm([
                'question' => 'Do you offer single supplements?',
                'answer' => 'Yes, on request.',
                'category' => 'Before you book',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $faq = Faq::where('question', 'Do you offer single supplements?')->first();

        $this->assertFalse($faq->wasAskedByAVisitor());
        $this->assertTrue($faq->isAnswered());
    }

    public function test_publishing_from_the_edit_form_requires_an_answer(): void
    {
        $this->post('/faq/ask', ['question' => 'How many people share a vehicle?']);
        $asked = Faq::latest('id')->first();

        Livewire::actingAs($this->admin())
            ->test(EditFaq::class, ['record' => $asked->getKey()])
            ->fillForm(['answer' => '', 'is_published' => true])
            ->call('save')
            ->assertHasFormErrors(['answer']);
    }

    public function test_an_unanswered_question_can_be_saved_without_an_answer(): void
    {
        // The record has to be editable before anyone has replied to it.
        $this->post('/faq/ask', ['question' => 'Are tips included?']);
        $asked = Faq::latest('id')->first();

        Livewire::actingAs($this->admin())
            ->test(EditFaq::class, ['record' => $asked->getKey()])
            ->fillForm(['category' => 'Payments & cancellation'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Payments & cancellation', $asked->fresh()->category);
    }

}
