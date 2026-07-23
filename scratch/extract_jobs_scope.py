import openpyxl
import json
import os

filepath = "/Users/bryanpaul/Dropbox/PaulDropbox/E3/docs/Copy of 10 Year Project History - redacted.xlsx"
wb = openpyxl.load_workbook(filepath, data_only=True)
sheet = wb['JOBS']

data = []
# Headers are on Row 2
# 'Project Title' is column 6 (F, index 5)
# 'General Scope of Work' is column 9 (I, index 8)

for row in sheet.iter_rows(min_row=3, values_only=True):
    if len(row) > 8:
        title = row[5]
        scope = row[8]
        if title:
            data.append({
                'title': str(title).strip(),
                'scope': str(scope).strip() if scope else ''
            })

with open('scratch/jobs_scope.json', 'w', encoding='utf-8') as f:
    json.dump(data, f, indent=2)

print(f"Extracted {len(data)} jobs.")
