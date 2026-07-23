import csv
from collections import defaultdict

quotes_path = '/Users/bryanpaul/Local Sites/e3es2026/app/public/wp-content/plugins/e3es-headless-helper/quotes.csv'

video_speakers = defaultdict(lambda: defaultdict(int))

with open(quotes_path, mode='r', encoding='utf-8') as f:
    reader = csv.reader(f)
    header = next(reader)
    for row in reader:
        if len(row) >= 5:
            speaker = row[1].strip()
            video = row[3].strip()
            video_speakers[video][speaker] += 1

print("Videos with multiple speakers:")
for video, speakers in video_speakers.items():
    if len(speakers) > 1:
        print(f"\n🎥 Video: {video}")
        for speaker, count in speakers.items():
            print(f"  - {speaker}: {count} quotes")
