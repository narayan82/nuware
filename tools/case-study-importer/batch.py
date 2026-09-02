#!/usr/bin/env python3
"""Review/extract assets/pdfs, then import each via the duplicate-safe PHP importer."""
import argparse
from collections import Counter
import json
from pathlib import Path
import subprocess
from extract import ROOT, extract
from test_extract import Structure


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--php', required=True, help='PHP CLI executable')
    parser.add_argument('--mysql-socket', help='Local WordPress MySQL socket, if required')
    parser.add_argument('--publish', action='store_true', help='Publish; default is dry-run only')
    args = parser.parse_args()
    sources = sorted((ROOT / 'assets/pdfs').glob('*.pdf'))
    previews = ROOT / 'imports/case-study-preview'
    previews.mkdir(parents=True, exist_ok=True)
    report = []
    # Extract and validate every source before allowing any publication.
    for source in sources:
        try:
            data = extract(source)
            validator = Structure()
            validator.feed(data['content'])
            if validator.stack or not data['block_counts'].get('h2'):
                raise ValueError('Invalid HTML structure or missing main headings')
            (previews / f'{source.stem}.json').write_text(json.dumps(data, ensure_ascii=False, indent=2)+'\n')
            (previews / f'{source.stem}.html').write_text(data['content']+'\n')
            report.append({'source': source.name, 'title': data['title'], 'counts': data['block_counts']})
        except Exception as error:
            report.append({'source': source.name, 'result': 'extraction_error', 'error': str(error)})
    if not any(r.get('result') == 'extraction_error' for r in report):
        for row in report:
            command = [args.php]
            if args.mysql_socket:
                command += ['-d', f'mysqli.default_socket={args.mysql_socket}']
            command += [str(ROOT / 'tools/case-study-importer/import.php'), '--source-dir=assets/pdfs', f'--file={row["source"]}']
            if args.publish: command.append('--publish')
            result = subprocess.run(command, cwd=ROOT, capture_output=True, text=True)
            try:
                if result.returncode: raise ValueError(result.stderr or result.stdout)
                row.update(json.loads(result.stdout))
            except (ValueError, json.JSONDecodeError) as error:
                row.update(result='import_error', error=str(error))
            print(json.dumps(row, ensure_ascii=False), flush=True)
    else:
        print('Extraction failed; no posts were imported.')
    name = 'batch-published.json' if args.publish else 'batch-dry-run.json'
    (previews / name).write_text(json.dumps(report, ensure_ascii=False, indent=2)+'\n')
    print(json.dumps(dict(Counter(r.get('result', 'not_imported') for r in report))))
    if any(r.get('result', '').endswith('error') for r in report): raise SystemExit(1)


if __name__ == '__main__':
    main()
