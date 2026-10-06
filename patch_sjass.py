with open('generate_sjass.py', 'r', encoding='utf-8', errors='replace') as f:
    lines = f.readlines()

replacements = {
    89:  '    ("TM / IT",           "TM / IT",              "IT"),\n',
    102: '    ("Dinagdee",          "Dinagdee",             "Economics (SS)"),\n',
    103: '    ("TM / IT",           "TM / IT",              "IT"),\n',
    273: '          "ADEEMSA BARNOOTAA / SUBJECT SCORES",\n',
}

fixed = [replacements.get(i, line) for i, line in enumerate(lines)]

with open('generate_sjass.py', 'w', encoding='utf-8') as f:
    f.writelines(fixed)

print("Patched lines:", sorted(replacements.keys()))
