<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @fonts

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="mb-16">
            <div class="bg-linear-120 bg-no-repeat py-16 from-purple-500 to-purple-900 text-center font-josefin-sans text-yellow-200 px-8">
                <p class="uppercase font-bold text-2xl sm:text-4xl mb-3">It&apos;s time to</p>
                <p class="uppercase font-black text-5xl sm:text-8xl">dance&nbsp;your ass&nbsp;off</p>
                <p class="font-black text-2xl mt-6 sm:mt-2">celebrating&nbsp;Wendy&apos;s&nbsp;59&frac12; and&nbsp;Tate&apos;s&nbsp;29&frac12;</p>
                <div class="mt-2 font-sans">
                    <a href="/rsvp" class="inline-block mt-4 text-2xl sm:text-4xl px-6 py-3 bg-yellow-200 text-purple-950 font-bold rounded-lg shadow-lg shadow-purple-950/40 transition-shadow hover:bg-yellow-300 hover:shadow-xl">RSVP by July 13, 2026</a>
                </div>
            </div>
            <div class="bg-yellow-300 p-4 text-center text-purple-950">
                <p class="font-josefin-sans text-4xl font-black mt-1">Friday, August 28, 2026</p>
                <p class="font-josefin-sans text-2xl font-semibold">Dinner, Drinks, &amp; Dancing &bull; 6:30 PM until we get kicked out &bull; Saint Paul, MN</p>
            </div>
            <div id="logistics-and-faq" class="mx-8 pt-4 sm:mx-16">
                <h2 class="text-center">Logistics &amp; FAQ</h2>
                <h3>I&apos;m in. Where do I RSVP?</h3>
                <p><a href="/rsvp" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">Right here.</a> Enter the email address or phone number associated with your invitation to get started. If you  need to make any changes to your RSVP, just come back here and fill out a new form.</p>

                <h3>When are RSVPs due?</h3>
                <p>We need your response by <strong>5:00 PM CT on Monday, July 13, 2026</strong>. Thanks in advance for submitting your RSVP in a timely fashion!</p>

                <h3>What&apos;s the dress code?</h3>
                <p>Dress to impress and be ready to rip the dance floor! <em>Rumor has it, at least one person is planning to wear her wedding dress...</em></p>
                <p>There will be awards for the best dressed, so bring your A-game!</p>

                <h3>What's the schedule for the weekend?</h3>
                <p><strong>Friday, August 28, 2026</strong></p>
                <ul>
                    <li>6:30 PM: Doors</li>
                    <li>7:00 PM: Dinner</li>
                    <li>Around 7:45 PM: Dance floor opens</li>
                    <li>9:30 PM: Awards for best dressed (and similarly silly categories)</li>
                    <li>11:00 PM: Like a Prayer</li>
                    <li>11:15 PM: Dance floor closes</li>
                </ul>
                <p><strong>Saturday, August 29, 2026</strong></p>
                <ul>
                    <li>Saturday morning: Optional group outing at the Minnesota State Fair! We'll take you through a few of our favorite spots, and then you'll be free to explore on your own. Meet at the State Fair at 10:00 AM (specific location TBA) &mdash; you'll want to leave your hotel around 9-9:30 to arrive on time.</li>
                    <li>3:30 PM: Pool party at Wendy's! (Not the restaurant, obviously.)</li>
                    <li>5:00 PM: We'll fire up the grill for a casual dinner.</li>
                </ul>

                <h3>Wait, you&apos;re celebrating your HALF birthdays?</h3>
                <p>Yes, you did read it correctly! We thought that celebrating our half birthdays, and therefore having the party in the summer, would be way more fun than waiting six months to celebrate our 30th and 60th birthdays in the middle of winter. Also, the State Fair is happening at the same time as our party, and you definitely don&apos;t want to miss that.</p>

                <h3>Do we need to bring anything?</h3>
                <p>No — just bring yourself and your dance shoes! Please do not bring gifts — your presence is enough of a gift to us, especially if you&apos;re traveling for the party.</p>

                <h3>What&apos;s the food and drink situation?</h3>
                <p>We'll have Indian food (served buffet style) and a full, open bar. More details are on your RSVP form, along with a space to let us know if you have any specific dietary needs we need to accommodate.</p>

                <h3>Are plus-ones allowed?</h3>
                <p>Generally speaking, anyone who would be a plus-one should be named on your invitation. There are a very limited number of additional spaces &mdash; if that applies to you, you'll see a space on your RSVP to name your extra guest.</p>
                <p>Please do not bring extra people along with you, unless they play for the Minnesota Lynx.</p>

                <h3>Are kids allowed?</h3>
                <p>This is an adults only event, sorry!</p>

                <h3>I&apos;m a local, what do I need to know?</h3>
                <ul>
                    <li>We strongly encourage everyone to use Uber, Lyft, or Metro Transit to get to and from the venue.</li>
                    <li>If you must drive, parking passes will be available for a lot that's half a block away.</li>
                    <li>You're welcome to join in with our State Fair and pool gatherings on Saturday too!</li>
                </ul>

                <h3>I&apos;m traveling in for the party, what do I need to know?</h3>
                <ul>
                <li>You&apos;ll want to book flights to MSP (Minneapolis&ndash;Saint Paul International). We are a hub for Delta and Sun Country <em>(now part of Allegiant)</em>, and we receive service from most other major airlines and some international carriers. We recommend arriving Friday morning/early afternoon, and departing on either Saturday afternoon or Sunday.</li>
                <li>You can, but do not necessarily have to, rent a car for your trip. If you do rent a car, make sure your rental is associated with the correct airport terminal for your airline (you can double check which terminal you need <a href="https://www.mspairport.com/flights-and-airlines" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">here</a>, or with your airline). Additionally, please note that we strongly encourage everyone, especially out-of-town guests, to use Uber, Lyft, or Metro Transit to get to and from the venue. Parking passes at the venue are available if you absolutely must drive.</li>
                <li>We have a list of recommended hotels below &mdash; there are no specific group blocks, so you're free to book any of them (or, if you have your own preference, you're welcome to book something else).</li>
                <li>If you have access to NRSA or ZED/ID90, we strongly recommend against using those and instead would advise that you use regular confirmed space. If you want to press your luck, contact Tate for specifics.</li>
                </ul>

                <h3>Where should I stay?</h3>
                <p>We don't have any group blocks, so you're free to pick any hotel you'd like. Here are some options we'd recommend:</p>
                <ul>
                    <li>Looking for old-school Saint Paul vibes? Check out <a href="https://www.saintpaulhotel.com" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">The Saint Paul Hotel</a>.</li>
                    <li>B&B more your style? <a href="https://newvictorianbb.com" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">The New Victorian B&B</a> is in a great neighborhood with lots of coffee shops and restaurants nearby.</li>
                    <li>Interested in something more unconventional? Check out <a href="https://www.celestestpaul.com" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">Celeste</a> &mdash; an old convent with an eccentric bar.</li>
                    <li>If convenience to the airport and Mall of America is important, there are <a href="https://www.choicehotels.com/minnesota/bloomington/radisson-blu-hotels/mn292" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">Radisson Blu</a> and <a href="https://www.marriott.com/en-us/hotels/mspjw-jw-marriott-minneapolis-mall-of-america/overview/" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">JW Marriott</a> options attached to MOA &mdash; a quick Uber or bus ride away from the venue.</li>
                    <li>Finally, if you're a Hyatt loyalist like us, the closest option is <a href="https://www.hyatt.com/hyatt-place/en-US/mspzs-hyatt-place-st-paul-downtown" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">Hyatt Place St. Paul Downtown</a>.</li>
                </ul>

                <h3>Is it true that the State Fair is happening during the party?</h3>
                <p>Yes, the Minnesota State Fair is happening at the same time as our party. If you&apos;ve never been, we highly recommend checking it out. More information is on the State Fair website, <a href="https://www.mnstatefair.org" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">mnstatefair.org</a>.</p>
                <p>If you want to go to the fair during our party weekend:</p>
                <ul>
                <li><a href="https://www.etix.com/ticket/p/67380207/2026-gate-admission-ticket-saint-paul-minnesota-state-fair" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">Buy tickets online ahead of time</a> &mdash; you&apos;ll save money compared to day-of gate prices.</li>
                <li>Make sure you have at least a half day to enjoy the fair &mdash; our outings typically last about 3-4 hours.</li>
                <li>We recommend avoiding the fair after about 4pm Friday (as you&apos;d miss the party) or Saturday (as it can get really crowded on weekend afternoons and evenings). The best time to go is anytime Thursday, or in the morning on Friday, Saturday, or Sunday.</li>
                <li>Take public transit. <strong>Do not park at the fair or take Uber/Lyft unless you know what you&apos;re doing.</strong> The State Fair has an excellent network of park and ride buses, and Metro Transit is very easy to use. Everything you need to know is on the State Fair website: <a href="https://www.mnstatefair.org/get-here/" class="text-purple-600 hover:text-purple-700 font-black" target="_blank">https://www.mnstatefair.org/get-here/</a></li>
                <li>There is a space on your RSVP form to indicate interest in a group State Fair outing. We&apos;ll send out more information about this in August, depending on how the numbers and logistics shake out. You&apos;re welcome to explore the fair on your own, even if we don&apos;t have a group outing!</li>
                <li>If you do choose to go to the fair on Friday, don&apos;t go too crazy on the fair food and beers &mdash; leave room for food and drinks at our party!</li>
                </ul>

                <h3>Is Like a Prayer on the playlist?</h3>
                <p>Yes, with Carleton rules. (Okay, maybe not FULL Carleton rules...)</p>
                <p>If you have no idea what this means, don&apos;t worry &mdash; it will become obvious at the party.</p>

                <h3>Help, my question wasn&apos;t answered here!</h3>
                <p>Just text or email either Tate or Wendy, and we&apos;ll be happy to help!</p>
            </div>
            </main>
    </body>
</html>
