<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

/**
 * Starter questions for /faq.
 *
 * These are the things travellers actually ask before a Tanzanian safari or
 * Kilimanjaro climb. The facts that are general — visa on arrival, yellow
 * fever rules, when the migration crosses — are accurate at the time of
 * writing; anything specific to how TWINS AFRICAN operates is deliberately
 * vague and needs replacing with your own terms. All editable under
 * Site -> FAQ.
 */
class FaqSeeder extends Seeder
{
    public function run(): void
    {
        if (Faq::query()->exists()) {
            $this->command?->info('  faqs already has data - skipping.');

            return;
        }

        foreach ($this->questions() as $index => $faq) {
            Faq::create($faq + ['sort_order' => $index + 1]);
        }
    }

    /** @return array<int, array<string, string>> */
    private function questions(): array
    {
        return [
            // --- Before you book -------------------------------------------
            [
                'category' => 'Before you book',
                'question' => 'When is the best time to go on safari in Tanzania?',
                'answer' => "There is no closed season — the parks are good all year, they are just "
                    . "good in different ways.\n\nJune to October is the dry season: animals gather at "
                    . "shrinking water sources, the grass is short and sightings come easily. It is also "
                    . "the busiest and most expensive time.\n\nJanuary and February are calving season in "
                    . "the southern Serengeti, which brings the predators in close. The green season from "
                    . "March to May is quiet and much cheaper, with dramatic skies — but afternoon rain "
                    . "and some camps closed.",
            ],
            [
                'category' => 'Before you book',
                'question' => 'How many days do I need for a safari?',
                'answer' => "Three days is enough for a taste of the northern circuit — Tarangire and "
                    . "Ngorongoro, say. Five to seven days lets you add the Serengeti without spending "
                    . "the whole trip driving.\n\nTen days or more means you can slow down: stay two "
                    . "nights in each place, sit with a sighting rather than rushing to the next one. "
                    . "Most people who have been before come back and ask for fewer parks and more time "
                    . "in each.",
            ],
            [
                'category' => 'Before you book',
                'question' => 'Can you plan a trip just for us?',
                'answer' => 'Yes — that is how we work. Tell us roughly how long you have, what you want '
                    . 'to see and the kind of camps you like, and we will build a route around it. '
                    . 'The packages on this site are starting points, not a fixed menu.',
            ],

            // --- Planning your trip ----------------------------------------
            [
                'category' => 'Planning your trip',
                'question' => 'Do I need a visa to enter Tanzania?',
                'answer' => "Most visitors do. Citizens of the UK, the US, Canada, Australia and most of "
                    . "Europe can get a single-entry tourist visa on arrival, or apply online in advance "
                    . "through the Tanzanian immigration e-visa service.\n\nUS citizens are normally "
                    . "issued a multiple-entry visa at a higher fee. Requirements change, so check the "
                    . "official immigration site close to your travel date rather than relying on this "
                    . "page.",
            ],
            [
                'category' => 'Planning your trip',
                'question' => 'Which airport should I fly into?',
                'answer' => 'Kilimanjaro International (JRO) is the one you want for the northern '
                    . 'circuit and for Kilimanjaro climbs — it is about an hour from Arusha. Dar es '
                    . 'Salaam (DAR) suits the southern parks, and Zanzibar (ZNZ) has direct flights '
                    . 'from several European and Gulf cities.',
            ],
            [
                'category' => 'Planning your trip',
                'question' => 'What should I pack?',
                'answer' => "Neutral colours — khaki, olive, brown. Avoid bright white, black and dark "
                    . "blue: dark blue attracts tsetse flies.\n\nBring layers. Mornings on the crater rim "
                    . "are genuinely cold and afternoons are hot. A wide-brimmed hat, sunglasses, high-factor "
                    . "sun cream, insect repellent and any personal medication. Binoculars are worth more "
                    . "than a long camera lens if you can only bring one.\n\nSoft duffel bags rather than "
                    . "hard suitcases, especially if your trip includes a light aircraft transfer — they "
                    . "have strict luggage limits, usually 15 kg.",
            ],

            // --- On safari --------------------------------------------------
            [
                'category' => 'On safari',
                'question' => 'Will I see the Big Five?',
                'answer' => "Often, but nobody can promise it — these are wild animals in very large "
                    . "parks.\n\nLion, elephant, buffalo and leopard are all realistic on a northern "
                    . "circuit trip. Rhino are the hard one: Ngorongoro Crater is the most reliable place "
                    . "in Tanzania to see black rhino, though usually at a distance. Anyone who guarantees "
                    . "you the full five is telling you what you want to hear.",
            ],
            [
                'category' => 'On safari',
                'question' => 'Where is the wildebeest migration right now?',
                'answer' => "It is a continuous loop rather than a single event, so it depends on the "
                    . "month.\n\nRoughly: December to March the herds are in the southern Serengeti and "
                    . "Ndutu for calving. April and May they move north-west. The Grumeti river crossings "
                    . "come around June, and the more famous Mara river crossings between July and "
                    . "September. From October they drift back south.\n\nRain shifts this by weeks in "
                    . "either direction every year. Ask us when you book and we will tell you where they "
                    . "are likely to be for your dates.",
            ],
            [
                'category' => 'On safari',
                'question' => 'What is a typical day on safari?',
                'answer' => "An early start — coffee around 6am and out at first light, which is when the "
                    . "animals are most active. A picnic breakfast or brunch in the park.\n\nMost days "
                    . "you are back at camp through the hottest hours, then out again mid-afternoon until "
                    . "sunset. On full-day drives you stay out with a packed lunch. Nothing is fixed: if "
                    . "there is something worth staying with, you stay with it.",
            ],
            [
                'category' => 'On safari',
                'question' => 'Is the food suitable if I have dietary requirements?',
                'answer' => 'Yes, with notice. Vegetarian, vegan, halal, gluten-free and allergy '
                    . 'requirements are all workable if you tell us when you book rather than on the '
                    . 'day — camps order supplies days in advance and some are a long way from a shop.',
            ],

            // --- Kilimanjaro -------------------------------------------------
            [
                'category' => 'Kilimanjaro',
                'question' => 'Do I need climbing experience for Kilimanjaro?',
                'answer' => "No technical climbing is involved — it is a long walk at altitude, not a "
                    . "climb. No ropes, no ice axes on the standard routes.\n\nWhat it does need is "
                    . "endurance: five to nine days of walking, several hours a day, ending with a summit "
                    . "night that starts around midnight. Train by hill walking with a daypack for a few "
                    . "months beforehand and you will enjoy it far more.",
            ],
            [
                'category' => 'Kilimanjaro',
                'question' => 'Which Kilimanjaro route has the best success rate?',
                'answer' => "The longer routes, because they give you more time to acclimatise. Lemosho "
                    . "over eight days and the northern circuit over nine have the highest summit "
                    . "rates.\n\nMachame over seven days is a good balance of cost and success. Marangu "
                    . "over five days is the cheapest and has the lowest success rate — it simply does "
                    . "not allow enough time to adjust. Adding a day is the single best thing you can do "
                    . "for your chances.",
            ],
            [
                'category' => 'Kilimanjaro',
                'question' => 'What happens if I get altitude sickness?',
                'answer' => "Your guides check oxygen saturation and pulse twice daily and watch for "
                    . "symptoms throughout.\n\nMild headaches and breathlessness are common and usually "
                    . "manageable. If symptoms are serious the only real treatment is to descend, and "
                    . "your guide's decision on that is final — it is not a judgement on you, and it is "
                    . "not negotiable at 5,000 metres.",
            ],

            // --- Health & safety ---------------------------------------------
            [
                'category' => 'Health & safety',
                'question' => 'What vaccinations do I need?',
                'answer' => "A yellow fever certificate is required if you are arriving from, or have "
                    . "transited through, a country where yellow fever is present — this includes Kenya "
                    . "and Ethiopia, so it catches a lot of people on connecting flights.\n\nHepatitis A, "
                    . "typhoid and tetanus are commonly recommended. Malaria prophylaxis is advised for "
                    . "most of the country.\n\nWe are not medical professionals: see a travel clinic six "
                    . "to eight weeks before you fly.",
            ],
            [
                'category' => 'Health & safety',
                'question' => 'Is Tanzania safe for tourists?',
                'answer' => 'Tanzania is one of the more stable countries in the region and tourism is '
                    . 'a major part of the economy. Normal precautions apply in cities — watch your '
                    . 'belongings, use registered taxis, avoid displaying valuables. On safari you are '
                    . 'with a guide throughout. Follow their instructions around animals and in camp '
                    . 'at night, and there is very little to worry about.',
            ],
            [
                'category' => 'Health & safety',
                'question' => 'Should I take out travel insurance?',
                'answer' => 'Yes, and we consider it essential rather than optional. Make sure it covers '
                    . 'medical evacuation, and if you are climbing Kilimanjaro check that it covers you '
                    . 'to at least 6,000 metres — many standard policies stop well below the summit.',
            ],

            // --- Payments & cancellation -------------------------------------
            [
                'category' => 'Payments & cancellation',
                'question' => 'How do I pay, and what deposit is required?',
                'answer' => 'We normally take a deposit to confirm your booking and hold camp space, '
                    . 'with the balance due before you travel. Bank transfer and card are both '
                    . 'accepted. The exact deposit and payment dates are set out in the quote we send '
                    . 'you, so everything is agreed in writing before any money changes hands.',
            ],
            [
                'category' => 'Payments & cancellation',
                'question' => 'What is your cancellation policy?',
                'answer' => 'What we can refund depends on how close to departure you cancel and on the '
                    . 'terms of the camps and park permits already booked in your name — some are '
                    . 'non-refundable once issued. The full terms are in your quote. This is the main '
                    . 'reason we recommend travel insurance that covers cancellation.',
            ],
            [
                'category' => 'Payments & cancellation',
                'question' => 'Are park fees included in the price?',
                'answer' => 'Park and conservation fees are a large part of the cost of any Tanzanian '
                    . 'safari, so we state clearly on every package what is included and what is not. '
                    . 'Check the "What is included" list on the package page, and ask us if anything '
                    . 'is unclear before you book.',
            ],
            [
                'category' => 'Payments & cancellation',
                'question' => 'How much should I tip?',
                'answer' => "Tipping is customary and makes up a meaningful part of what guides and "
                    . "camp staff earn, but it is discretionary.\n\nAs a rough guide, safari guides are "
                    . "commonly tipped per vehicle per day rather than per person, and camp staff through "
                    . "a shared box. Kilimanjaro crews are tipped as a team at the end of the climb, and "
                    . "the amounts are larger because the crew is large. We will give you current "
                    . "suggested ranges in your final documents.",
            ],
        ];
    }
}
