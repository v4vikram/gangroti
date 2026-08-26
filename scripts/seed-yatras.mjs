/**
 * One-off: merges the long-form fields (overview, itinerary, inclusions, FAQs)
 * into src/data/yatras.json. Kept in the repo so the seed content is
 * reproducible, but it is not part of the build.
 *
 *   node scripts/seed-yatras.mjs
 */
import { readFile, writeFile } from 'node:fs/promises';

const extra = {
  'kedarnath-yatra': {
    pickup: 'Haridwar',
    groupSize: 'Max 15',
    overview:
      'Kedarnath sits at 3,583 m in the Rudra Himalaya, reached by a 16 km walk from Gaurikund. This six-day itinerary spreads the climb either side of a night at Sonprayag, so you arrive at the temple acclimatised rather than exhausted.',
    highlights: [
      'Darshan at the twelfth Jyotirlinga',
      'Overnight at Kedarnath for the morning aarti',
      'Bhairavnath temple above the valley',
      'Optional helicopter transfer from Phata',
    ],
    itinerary: [
      { title: 'Haridwar to Guptkashi', text: 'Drive along the Alaknanda through Devprayag and Rudraprayag. Overnight at Guptkashi (1,319 m).' },
      { title: 'Guptkashi to Kedarnath', text: 'Early drive to Sonprayag, shuttle to Gaurikund, then the 16 km climb. Overnight in Kedarnath.' },
      { title: 'Kedarnath darshan', text: 'Morning aarti and darshan, then Bhairavnath temple. The rest of the day is free at altitude.' },
      { title: 'Descend to Guptkashi', text: 'Walk down to Gaurikund and drive back to Guptkashi.' },
      { title: 'Guptkashi to Rishikesh', text: 'Long drive down the valley with a stop at the Rudraprayag sangam.' },
      { title: 'Departure', text: 'Transfer to Haridwar railway station or Jolly Grant airport.' },
    ],
    inclusions: ['Accommodation on twin sharing', 'Breakfast and dinner daily', 'All transfers in a private vehicle', 'Certified guide throughout', 'Char Dham registration and e-pass', 'Oxygen cylinder and first-aid kit'],
    exclusions: ['Airfare and train tickets', 'Lunches and personal expenses', 'Pony, palki or helicopter charges', 'Travel insurance', 'Anything not listed under inclusions'],
    faqs: [
      { q: 'How long does the Kedarnath trek take?', a: 'Most people take 6 to 8 hours to cover the 16 km from Gaurikund, and 4 to 5 hours coming down. The path is paved stone throughout, so it is stamina rather than technique that decides your pace.' },
      { q: 'Can I take a helicopter instead of walking?', a: 'Yes. Helicopter services run from Phata, Sirsi and Guptkashi and cost roughly 7,500 to 8,500 rupees return. We book slots in advance, as they sell out weeks ahead in May and June.' },
      { q: 'What should I pack for Kedarnath?', a: 'Warm layers including a down jacket, a waterproof shell, broken-in walking shoes, a torch, sunscreen and any personal medication. Nights at the temple drop close to freezing even in June.' },
    ],
  },

  'badrinath-yatra': {
    pickup: 'Haridwar',
    groupSize: 'Max 15',
    overview:
      'Badrinath is the most accessible of the four dhams: the road runs all the way to the temple, so there is no trek involved. That makes this five-day itinerary a realistic option for elderly travellers and families with young children.',
    highlights: ['Darshan at Badrinath temple', 'Tapt Kund hot springs', 'Mana, the last village before Tibet', 'Vyas Gufa and Bheem Pul'],
    itinerary: [
      { title: 'Haridwar to Joshimath', text: 'Drive up the Alaknanda valley through Devprayag, Rudraprayag and Karnaprayag.' },
      { title: 'Joshimath to Badrinath', text: 'Short morning drive, afternoon darshan and a dip at Tapt Kund.' },
      { title: 'Mana village', text: 'Visit Mana, Vyas Gufa, Bheem Pul and Vasudhara falls, then return to Badrinath.' },
      { title: 'Badrinath to Rishikesh', text: 'Long descent with stops at the panch prayag confluences.' },
      { title: 'Departure', text: 'Transfer to Haridwar or Jolly Grant airport.' },
    ],
    inclusions: ['Accommodation on twin sharing', 'Breakfast and dinner daily', 'All transfers in a private vehicle', 'Certified guide throughout', 'Char Dham registration and e-pass', 'First-aid support'],
    exclusions: ['Airfare and train tickets', 'Lunches and personal expenses', 'Pooja and donation charges', 'Travel insurance', 'Anything not listed under inclusions'],
    faqs: [
      { q: 'Is there any trekking on the Badrinath yatra?', a: 'None. The road reaches the temple courtyard, and the walk from the car park to the temple is a few hundred metres on level ground.' },
      { q: 'Is Badrinath suitable for elderly travellers?', a: 'It is the easiest of the four dhams. The main consideration is altitude rather than walking: Badrinath sits at 3,133 m, so we build in a night at Joshimath to acclimatise.' },
    ],
  },

  'chopta-tungnath-yatra': {
    pickup: 'Rishikesh',
    groupSize: 'Max 15',
    overview:
      'Tungnath is the highest Shiva temple in the world at 3,680 m, and it is only a 3.5 km walk from Chopta. Push another 1.5 km to Chandrashila at 4,000 m and you get a 360 degree view of Nanda Devi, Trishul and Chaukhamba.',
    highlights: ['Highest Shiva temple in the world', 'Sunrise from Chandrashila summit', 'The Chopta meadows', 'Runs all year, including snow season'],
    itinerary: [
      { title: 'Rishikesh to Chopta', text: 'Drive through Devprayag and Ukhimath to the Chopta meadows. Evening at leisure.' },
      { title: 'Tungnath and Chandrashila', text: 'Pre-dawn start for Tungnath, then the summit push to Chandrashila for sunrise. Descend to Chopta.' },
      { title: 'Chopta to Rishikesh', text: 'Optional stop at Deoria Tal on the way down, arriving Rishikesh by evening.' },
    ],
    inclusions: ['Camp or guesthouse stay', 'Breakfast and dinner daily', 'Transfers from Rishikesh', 'Trek leader and support staff', 'Forest entry permits', 'First-aid kit and oximeter'],
    exclusions: ['Transport to Rishikesh', 'Lunches and personal expenses', 'Winter gear rental', 'Travel insurance', 'Anything not listed under inclusions'],
    faqs: [
      { q: 'Is the Tungnath trek difficult?', a: 'It is one of the gentlest Himalayan treks: 3.5 km on a paved path with a steady gradient, usually 2 to 3 hours up. The Chandrashila extension is steeper and can be icy in winter.' },
      { q: 'Can I do this trek in winter?', a: 'Yes, and many people come specifically for the snow. December to March needs microspikes and proper layers, both of which we arrange.' },
    ],
  },

  'har-ki-dun-trek': {
    pickup: 'Dehradun',
    groupSize: 'Max 15',
    overview:
      'Har Ki Dun is a wide hanging valley in the Govind National Park, reached through the old wooden villages of Osla and Seema. Seven days at a walking pace, with the Swargarohini massif standing at the head of the valley.',
    highlights: ['Ancient villages of Osla and Gangaad', 'Govind National Park forests', 'Swargarohini and Bandarpoonch views', 'Camping beside the Supin river'],
    itinerary: [
      { title: 'Dehradun to Sankri', text: 'Long drive through Mussoorie and Purola to the trailhead village of Sankri (1,950 m).' },
      { title: 'Sankri to Seema', text: 'Drive to Taluka, then walk 11 km along the Supin river to Seema.' },
      { title: 'Seema to Har Ki Dun', text: 'Climb 11 km through Osla and terraced fields into the valley itself.' },
      { title: 'Har Ki Dun exploration', text: 'Day walk towards the Jaundhar glacier and the base of Swargarohini.' },
      { title: 'Har Ki Dun to Seema', text: 'Retrace the route down the valley.' },
      { title: 'Seema to Sankri', text: 'Walk to Taluka and drive back to Sankri.' },
      { title: 'Sankri to Dehradun', text: 'Drive out, arriving Dehradun in the evening.' },
    ],
    inclusions: ['Camping and guesthouse stay', 'All meals on trek', 'Transfers from Dehradun', 'Trek leader, cook and porters', 'Forest permits and camping charges', 'First-aid kit, oxygen and oximeter'],
    exclusions: ['Transport to Dehradun', 'Personal trekking gear', 'Travel insurance', 'Porter for personal bags', 'Anything not listed under inclusions'],
    faqs: [
      { q: 'Do I need trekking experience for Har Ki Dun?', a: 'No, but you do need fitness. The days are long rather than technical, typically 6 to 7 hours of walking. We ask that you can jog 4 km in under 30 minutes before the trek.' },
      { q: 'When is Har Ki Dun open?', a: 'April to November. April and May bring the rhododendron bloom, September and October give the clearest views, and the valley holds snow into early spring.' },
    ],
  },

  'valley-of-flowers': {
    pickup: 'Haridwar',
    groupSize: 'Max 15',
    overview:
      'A UNESCO World Heritage site that only opens for the monsoon, when roughly 300 species of alpine flower come into bloom together. The itinerary pairs it with Hemkund Sahib at 4,632 m for anyone who wants the climb.',
    highlights: ['UNESCO World Heritage national park', 'Peak bloom in late July and August', 'Optional Hemkund Sahib at 4,632 m', 'Base at Ghangaria in the Bhyundar valley'],
    itinerary: [
      { title: 'Haridwar to Joshimath', text: 'Drive up the Alaknanda valley through the prayag confluences.' },
      { title: 'Joshimath to Ghangaria', text: 'Drive to Govindghat, then walk 9 km to Ghangaria (3,050 m).' },
      { title: 'Valley of Flowers', text: 'Day walk into the valley, returning to Ghangaria by evening.' },
      { title: 'Hemkund Sahib', text: 'Optional 6 km climb to the glacial lake and gurudwara at 4,632 m.' },
      { title: 'Ghangaria to Joshimath', text: 'Walk down to Govindghat and drive to Joshimath.' },
      { title: 'Return to Haridwar', text: 'Long drive down, arriving Haridwar by evening.' },
    ],
    inclusions: ['Accommodation on twin sharing', 'Breakfast and dinner daily', 'All transfers in a private vehicle', 'Certified guide throughout', 'National park entry permits', 'First-aid kit and oximeter'],
    exclusions: ['Airfare and train tickets', 'Lunches and personal expenses', 'Pony or porter charges', 'Travel insurance', 'Anything not listed under inclusions'],
    faqs: [
      { q: 'When is the best time to see the flowers?', a: 'Mid July to late August is peak bloom. The park opens on 1 June and closes on 4 October, but early June and late September have far fewer flowers.' },
      { q: 'How hard is the walk to Ghangaria?', a: 'It is 9 km with a steady climb from Govindghat, usually 5 to 6 hours. Ponies and porters are available at the trailhead if you would rather not carry a pack.' },
    ],
  },

  'char-dham-yatra': {
    pickup: 'Haridwar',
    groupSize: 'Max 15',
    overview:
      'All four dhams in a single circuit, in the traditional order: Yamunotri, Gangotri, Kedarnath and Badrinath. Eleven days with rest days built in, because compressing this into seven is how people end up ill at altitude.',
    highlights: ['All four dhams in the traditional order', 'Rest days built in for acclimatisation', 'Ganga aarti at Haridwar', 'Tapt Kund and Mana village'],
    itinerary: [
      { title: 'Arrive Haridwar', text: 'Check in and attend the evening Ganga aarti at Har Ki Pauri.' },
      { title: 'Haridwar to Barkot', text: 'Drive via Mussoorie to Barkot, the base for Yamunotri.' },
      { title: 'Yamunotri', text: 'Drive to Janki Chatti and walk 6 km to the temple and Surya Kund. Return to Barkot.' },
      { title: 'Barkot to Uttarkashi', text: 'Drive along the Bhagirathi to Uttarkashi, visiting Vishwanath temple.' },
      { title: 'Gangotri', text: 'Drive to Gangotri for darshan at the source temple, then back to Uttarkashi.' },
      { title: 'Uttarkashi to Guptkashi', text: 'Long transfer day across the valleys to Guptkashi.' },
      { title: 'Kedarnath', text: 'Drive to Sonprayag and climb 16 km to Kedarnath. Overnight at the temple.' },
      { title: 'Kedarnath to Guptkashi', text: 'Morning darshan, then descend and drive to Guptkashi.' },
      { title: 'Guptkashi to Badrinath', text: 'Drive via Joshimath to Badrinath for evening darshan.' },
      { title: 'Badrinath and Mana', text: 'Tapt Kund, Mana village and Vyas Gufa, then drive to Rudraprayag.' },
      { title: 'Return to Haridwar', text: 'Final drive down and transfer to station or airport.' },
    ],
    inclusions: ['Accommodation on twin sharing', 'Breakfast and dinner daily', 'All transfers in a private vehicle', 'Certified guide throughout', 'Char Dham registration and e-pass', 'Oxygen cylinder and first-aid kit'],
    exclusions: ['Airfare and train tickets', 'Lunches and personal expenses', 'Pony, palki or helicopter charges', 'Travel insurance', 'Anything not listed under inclusions'],
    faqs: [
      { q: 'What is the correct order for the Char Dham Yatra?', a: 'Traditionally Yamunotri first, then Gangotri, Kedarnath and Badrinath, moving west to east. Our itinerary follows that order.' },
      { q: 'How many days does the Char Dham Yatra need?', a: 'Eleven days is the comfortable minimum by road. Shorter itineraries exist, but they cut the acclimatisation days, which is where altitude sickness usually starts.' },
      { q: 'Is registration compulsory for all four dhams?', a: 'Yes. Every traveller needs a Uttarakhand Char Dham registration and e-pass, checked at barriers on each route. We file it for you once you send a photo ID.' },
    ],
  },
};

const yatras = JSON.parse(await readFile('src/data/yatras.json', 'utf8'));
for (const item of yatras) Object.assign(item, extra[item.slug] ?? {});

await writeFile('src/data/yatras.json', `${JSON.stringify(yatras, null, 2)}\n`);
console.log(`extended ${yatras.length} yatras\nfields: ${Object.keys(yatras[0]).join(', ')}`);
