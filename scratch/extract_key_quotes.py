import json

# Define the client testimonial and case study keywords
key_videos = [
    'Boyd ISD',
    'Bryan ISD',
    'Caldwell ISD',
    'Lake Worth',
    'Lessons in Learning',
    'Lessons In Learning',
    'Port Neches-Groves',
    'Goodall-Witcher Healthcare',
    'Granbury ISD',
    'Edcouch-Elsa',
    'Hardin-Jefferson',
    'Hardin Jefferson',
    'San Jacinto Community College',
    'Stockdale Lagoon Restoration',
    'Nano Gas - Stockdale',
    'Timpson, Texas',
    'City of Natalia',
    'Banquete',
    'DaVinci Award',
    'Highland Park ISD',
    'Pflugerville ISD'
]

with open('scratch/raw_merged_quotes.json', 'r') as f:
    groups = json.load(f)

key_groups = []
non_key_groups = []

for g in groups:
    is_key = False
    for kv in key_videos:
        if kv.lower() in g['video'].lower() or kv.lower() in g['raw_text'].lower():
            is_key = True
            break
            
    # Also include any group with a prominent superintendent/director name
    if g['person_name'] in ['Dr. Theresa Williams', 'Judd Marshall', 'Mike Lamb', 'Dr. Shannon Trejo', 'Dr. James Largent', 'Jerry Pickett', 'Paul Buckner', 'Andrew Peters', 'Effie Morris', 'Brandon Hopkins', 'Tom Woody', 'Brady Beavers', 'Elyssha Enriquez', 'Amelie Sanchez', 'Dr. Mike Gonzales', 'Jeff Bergeron', 'Heather Escalante', 'Nathan Goodlett']:
        is_key = True
        
    if is_key:
        key_groups.append(g)
    else:
        non_key_groups.append(g)

with open('scratch/key_raw_quotes.json', 'w') as f:
    json.dump(key_groups, f, indent=4)

with open('scratch/non_key_raw_quotes.json', 'w') as f:
    json.dump(non_key_groups, f, indent=4)

print(f"Extracted {len(key_groups)} key testimonial groups and {len(non_key_groups)} non-key groups.")
