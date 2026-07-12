import json

raw_file_path = "/Users/bryanpaul/Local Sites/astro-e3es/scratch/key_raw_quotes_batch1.json"
clean_file_path = "/Users/bryanpaul/Local Sites/astro-e3es/scratch/key_clean_quotes_batch1.json"

with open(raw_file_path, "r", encoding="utf-8") as f:
    raw_data = json.load(f)

# Mapping from (person_id, tuple of quote_ids) to the cleaned text
cleaned_mapping = {
    (11692, (2185,)): (
        "What an incredible difference."
    ),
    (1721, (2199, 2200)): (
        "Current projections indicate that our district will double in size over the next eight years, "
        "putting classroom space at a premium. We needed an additional school to handle this growth for "
        "the next eight to ten years, and potentially fifteen-plus years. We addressed those needs and "
        "secured new facilities."
    ),
    (1685, (2201, 2202, 2203, 2204, 2205, 2206, 2207, 2208, 2209)): (
        "We didn't have enough funding to meet the scope of our project without looking at a bond, but "
        "we were able to address energy management district-wide and add new units. We are very pleased "
        "with the energy management solution E3 brought to us. When they came out, we had some issues "
        "with other units, and they took care of them without any change orders. We also use E3 as a "
        "resource for architecture, reaching out to them for specifications they write for our campuses. "
        "The last time we did HVAC upgrades, we used a different company. They did not have boots on the "
        "ground like E3 does, making it much tougher to reach them and get them out here. With E3, the "
        "experience was quicker, easier, and much better. We didn't encounter a single problem throughout "
        "the process. It has been a great experience."
    ),
    (11694, (2211,)): (
        "The system is as intuitive and responsive as saying, 'OK Google.'"
    ),
    (1710, (2212, 2213, 2214, 2215, 2216, 2217, 2218, 2219, 2221, 2222, 2223, 2224, 2225)): (
        "Part of my role with Bryan ISD as the Director of Construction and Energy Management is to not "
        "only build safe and beautiful buildings, but to create a learning environment where students "
        "want to be. Our primary focus was LED lighting. People can see the impact of better quality "
        "lighting immediately, and we also gained a significant energy reduction. Working with the State "
        "Energy Conservation Office (SECO) allowed us to complete this seven-million-dollar energy "
        "efficiency project without a massive capital outlay. One of the main benefits of selecting E3 "
        "was their mechanical and lighting expertise, along with their extensive experience navigating "
        "SECO's application and reimbursement processes. Utilizing design-build made the most sense for "
        "this project, offering us the flexibility to make design adjustments without the administrative "
        "burden of traditional procurement methods. The construction process with E3 was simple and "
        "stress-free. All work was done after hours, on weekends, and during holidays, meaning there was "
        "zero impact on the school day. Teachers would leave in the afternoon and return the next morning "
        "to find their classroom completely updated to LED lighting, with no disruption to their routine."
    ),
    (1681, (2227, 2228, 2230, 2232, 2233, 2234, 2235, 2236, 2237)): (
        "The Caldwell ISD Board of Trustees voted unanimously to call for a bond election on November 6, "
        "2018. Developed with feedback from the community and CISD staff, this comprehensive program "
        "addresses school safety, academics, campus remodels, and essential equipment. The bond proposal "
        "includes renovations and additions to all CISD schools. District-wide safety projects include fire "
        "alarms, security cameras, secure entries, electrical upgrades, and ADA improvements. The Intermediate "
        "School proposal features a complete remodel and conversion to a seventh and eighth-grade campus, "
        "including a remodeled band hall, science labs, a weight room, and gym dressing rooms. The High School "
        "plan introduces new STEM learning spaces, the elimination of portable classrooms, and upgrades to "
        "the gym and agricultural facilities. Middle School projects include a complete remodel and conversion "
        "to a Pre-K through third-grade campus, a connector building to the cafeteria, and a new gym. The "
        "Elementary School plan converts the existing elementary school into a fourth through sixth-grade campus. "
        "Other considerations include replacing the high school's air conditioning and upgrading to LED lighting. "
        "There will be two propositions on the ballot. Early voting runs from October 22 through November 2, and "
        "Election Day is November 6. For more information, including polling locations, estimated tax impact, "
        "and ballot language, please visit our website."
    ),
    (1681, (2238, 2239, 2240, 2241, 2244, 2245, 2246, 2247, 2248, 2249, 2250)): (
        "Our system was over 30 years old, costing us a lot of money in repairs and failing to provide "
        "consistent temperatures throughout the building. Students were struggling in classrooms that "
        "were either hot and sweaty or freezing cold. The energy savings from a new system would pay "
        "for itself over a few years, so it made perfect sense to improve the lighting at the same time. "
        "From a maintenance standpoint, especially with our limited number of employees, this was a "
        "great decision. We didn't start with a predetermined idea of who we wanted to do the job; we "
        "just needed it done. E3 designed the system and ensured it was installed correctly. The board "
        "was comfortable with them, and it was a very easy process. The biggest challenge E3 faced was "
        "having the air conditioning running by the start of the school year, which they exceeded. They "
        "had to get everything buttoned up within a week, and they jumped through hoops to make sure we "
        "were ready for school. The most important thing we liked was their communication; they "
        "communicated regularly not just with me as the superintendent and director of operations, but "
        "daily with the principal responsible for the building. Early on, E3 brought in their engineers "
        "to meet with us, and we felt very confident in their design."
    ),
    (1683, (2242,)): (
        "We won't have to do any lighting maintenance for the next 20 years, other than maybe changing a bulb. "
        "There are no more ballasts and no more flickering bulbs."
    ),
    (11679, (2256, 2257)): (
        "My name is Nicole Berman, City Administrator for the City of Natalia. We are looking forward to having "
        "E3 help us address the issues at our wastewater treatment plant and other areas of our city, while "
        "helping us secure much-needed grant funding for the project."
    ),
    (11680, (2259, 2260, 2261, 2262, 2263, 2264)): (
        "In the heart of Timpson, Texas, a new chapter in water safety is beginning. The city has partnered "
        "with design-build contractor E3 to reduce TTHMs (chemical byproducts that can form during water treatment) "
        "and deliver cleaner, safer water to the community. At the core of this transformation is E3's "
        "leading-edge BiCARBUS technology, designed to establish microbial control in groundwater before harmful "
        "byproducts can form. Paired with a high-efficiency air stripper, the system will remove TTHMs and elevate "
        "overall water quality. This nearly $1 million investment also advances automated metering technology, "
        "giving the city detailed water consumption data and insights. Funded through a performance-based utility "
        "program, the project reflects a forward-thinking approach to infrastructure. E3 continues to redefine "
        "municipal water solutions—restoring systems, extending lifespans, and helping communities move forward "
        "with confidence. Cleaner water, smarter systems, stronger communities: E3 is built for what's next."
    ),
    (1681, (2265, 2266)): (
        "They communicated regularly, not just with me as the superintendent and the director of operations, "
        "but daily with the principal who was responsible for the building."
    ),
    (11681, (2268, 2270)): (
        "I love the family atmosphere here, where we get to see everyone and gather as a group to support one "
        "another. It is one of the wonderful things about E3, and it truly stands out. You just do not get this "
        "kind of experience with any other company."
    ),
    (1710, (2271, 2272, 2273, 2274)): (
        "When you encounter unforeseen issues with a competitive sealed proposal where contingencies are "
        "involved, once you hit that contingency limit, you have to go back to the school board and ask "
        "for more money. As someone managing projects for a school district, the last thing you want to "
        "do is ask for additional funding for a project. That is why the design-build model was so "
        "beneficial and positive for us."
    ),
    (1710, (2275, 2276, 2277, 2278, 2279, 2280, 2281)): (
        "Utilizing design-build for this project made the most sense for us compared to a competitive sealed "
        "proposal or going with a construction manager at risk. Design-build gave us the flexibility to design "
        "the project and make changes easily as things came up—whether it was a different lighting connection "
        "than originally planned, or one gym having a different setup than another. Unlike a competitive sealed "
        "proposal, where you have to dip into contingencies and process change orders once the bid is out, the "
        "design-build process gave us the flexibility to design and make alterations without the extensive "
        "paperwork and change orders required by other procurement methods."
    ),
    (1710, (2282, 2283, 2284, 2285, 2286, 2287, 2288, 2289)): (
        "An example of how design-build benefited us occurred in one of our high school gymnasiums. After we "
        "upgraded the gym lighting, we had multiple lights go out within a few weeks, which is unusual for LED "
        "lighting. E3's construction team, including Eric and Jeff, worked with the subcontractor and found that "
        "the issue was due to how they were wired and plugged in. They quickly developed a plan outside the original "
        "scope to replace the plugs and wiring. Rather than having us deal with recurring issues where we'd have "
        "to fix one light one week and another two weeks later, they decided after the third failure to go in and "
        "essentially rewire all of the fixtures to eliminate the problem permanently."
    ),
    (1710, (2290, 2291)): (
        "Once we understood the ins and outs of the design-build model, it was a very smooth and easy process. "
        "We will definitely look to use it again for future projects."
    ),
    (1681, (2292, 2293, 2294, 2295, 2296)): (
        "When we were evaluating different firms to make the best decision for Caldwell, we wanted to avoid "
        "the issues we had experienced in other buildings. A key priority was ensuring that whoever designed "
        "the system and whoever installed it fully understood each other and our specific needs. Very early in "
        "the process, E3 brought in their engineer to meet with us, ask questions, and walk our facilities. "
        "We felt very comfortable knowing the engineer and trusting his design. It has worked out great, and we "
        "are really excited about the results."
    ),
    (1710, (3019, 3020, 3021, 3022, 3023)): (
        "One of the key benefits of selecting E3 was their extensive experience with energy efficiency projects, "
        "specifically on the mechanical side and with LED lighting. In addition to their technical knowledge, "
        "their familiarity with the State Energy Conservation Office (SECO) was invaluable. They guided us "
        "through the application process, the construction paperwork, and the reimbursement channels. E3 was a "
        "huge asset to Bryan ISD in all of these areas."
    ),
    (11621, (3114, 3115, 3116, 3117)): (
        "Whether we needed to change a control that wasn't working, or address a struggling AC unit, the "
        "communication was excellent and made us feel very comfortable. From a business perspective, we "
        "got our money's worth, but we also got great customer service and follow-through. We've all dealt "
        "with companies that complete a project and are never heard from again. Knowing we can call E3 "
        "for guidance or support and that they will be there for us is invaluable."
    ),
    (11623, (3121, 3122)): (
        "When I started here in mid-July, I noticed it was extremely hot. I initially assumed the AC was "
        "just turned off for the summer, but I was soon informed that the air conditioning units were "
        "completely broken."
    ),
    (1714, (3123, 3124, 3125)): (
        "We were trying to fix the issues before the start of the school year, but we were in a very "
        "difficult situation. School was about to start and we had no working AC, which put us in a major "
        "bind. That is one of the key reasons I selected E3. Having worked with them before, I was confident "
        "in recommending them to the board."
    ),
    (11624, (3127, 3128, 3129, 3130, 3131, 3132)): (
        "Shifting classes around the campus would not have been beneficial to our students; they would "
        "have had to go outside, potentially getting lost or skipping classes, which would have made them "
        "hard to track. Now, everyone is in their own classroom, everyone is working, and we are back to "
        "teaching. With the design-build model, we didn't have to build everything from the ground up ourselves. "
        "E3 handled everything, so I never had to worry about change orders. We let them handle their business, "
        "they kept their end of the bargain, and it allowed us to focus on taking care of our kids."
    ),
    (11627, (3139, 3140, 3141, 3143, 3145, 3146, 3147, 3148, 3149, 3150, 3151, 3152)): (
        "In a hospital, there are all kinds of airborne pathogens, from COVID-19 to the flu and everything "
        "in between, so air quality is an absolute necessity. What I really liked about E3 was their "
        "comprehensive analysis of our entire system. They put together a complete solution rather than "
        "just adding random components here and there, addressing all of our issues—both the ones we knew "
        "about and those we didn't. The program E3 brought to the table allows the project to pay for "
        "itself over 20 years through energy savings, which was crucial because we could not have financed "
        "a project of this scale otherwise. When our aging system was about to fail, they managed to locate "
        "and secure one of the only compatible chillers in North America and had it installed within 30 "
        "days. Speed is critical when prices for labor and equipment are rising daily due to supply chain "
        "challenges. As an administrator, I appreciate only having to call one contact rather than managing "
        "multiple companies, which eliminates any finger-pointing because they handle everything. Not having "
        "to worry about change orders is a major benefit; once the scope was established, we knew exactly "
        "what we were getting. This project has upgraded our entire infrastructure to ensure our hospital "
        "can operate smoothly for the next 25 years."
    ),
    (11600, (3154, 3155, 3156)): (
        "We were highly impressed with E3 throughout this entire process. Their delivery method was "
        "well-planned and well-thought-out. Meeting the timeline was critical for us as we worked through the "
        "summer to prepare for the start of the school year, and their construction team met every schedule. "
        "Through our project with E3, they replaced over 200 HVAC units that were more than 20 years old, "
        "along with 13,000 light fixtures."
    ),
    (11601, (3158, 3159, 3161, 3162, 3163, 3164, 3165, 3166)): (
        "They upgraded our light fixtures and over 1,500 water fixtures. They even utilized helicopters "
        "to set the new HVAC units, which sped up the process dramatically. We also installed a synthetic "
        "turf field on our football field, which has been a big hit with our community, and we were able "
        "to integrate that into this project as well. E3 brought a tremendous amount of experience "
        "working with school districts, which was key during our selection process. Most importantly, "
        "this project has saved our district significant dollars that we can redirect into other areas "
        "of education. When comparing E3 to other companies we evaluated, the measurable savings "
        "component was the biggest factor. I wanted to make sure our public and school board could understand "
        "exactly where the savings were coming from and that they could be verified. At the end of the day, "
        "it comes down to performance, and they delivered exactly on what they promised."
    ),
}

clean_data = []
for item in raw_data:
    key = (item["person_id"], tuple(item["quote_ids"]))
    if key in cleaned_mapping:
        cleaned_text = cleaned_mapping[key]
    else:
        print(f"Warning: Key not found: {key}")
        cleaned_text = item["raw_text"]
    
    clean_item = {
        "video": item["video"],
        "person_id": item["person_id"],
        "person_name": item["person_name"],
        "quote_ids": item["quote_ids"],
        "cleaned_text": cleaned_text
    }
    clean_data.append(clean_item)

with open(clean_file_path, "w", encoding="utf-8") as f:
    json.dump(clean_data, f, indent=4, ensure_ascii=False)

print("SUCCESS")
