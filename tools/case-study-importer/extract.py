#!/usr/bin/env python3
"""Extract ONE text PDF into reviewable HTML/JSON. Never publishes anything."""
import argparse
from collections import Counter
import hashlib
import html
import json
from pathlib import Path
import re
import unicodedata

import pdfplumber
from table_profiles import TABLES

ROOT = Path(__file__).resolve().parents[2]
IMPORTS = ROOT / 'imports' / 'case-studies'


def clean(text):
    text = unicodedata.normalize('NFKC', text)
    text = text.replace('\u00ad', '').replace('\u200b', '').replace('\ufeff', '')
    return re.sub(r'\s+', ' ', text).strip()


def join_wrapped(left, right):
    # Preserve compound words such as end-to-end and self-learning across lines.
    return left + ('' if left.endswith('-') else ' ') + right


def extract(source):
    lines = []
    skipped = []
    with pdfplumber.open(source) as pdf:
        pages = len(pdf.pages)
        for page_index, page in enumerate(pdf.pages):
            page_lines = page.extract_text_lines()
            if not page_lines:
                if source.stem in ('ai-scaling-agentic-solutions', 'fixed-income-model') and page_index == 4:
                    skipped.append(f'Page {page_index + 1}: reviewed image-only appendix (imagery deferred).')
                    continue
                raise ValueError(f'Page {page_index + 1} has no text; needs manual/OCR review.')
            profiles = [p for p in TABLES.get(source.stem, []) if p[0] == page_index + 1]
            for _, columns, rows, headers in profiles:
                cells = []
                for row_index, (top, bottom) in enumerate(zip(rows, rows[1:])):
                    values = []
                    for col_index, (left, right) in enumerate(zip(columns, columns[1:])):
                        if col_index == len(columns) - 2: right = page.width
                        chars = [c for c in page.chars if left <= (c['x0']+c['x1'])/2 < right and top <= c['top'] < bottom]
                        parts = (pdfplumber.utils.extract_text(chars) or '').splitlines()
                        value = ''
                        for part in parts:
                            value = join_wrapped(value, clean(part)).strip()
                        if len(columns) > 3:
                            if col_index >= 3: value = value.replace(' ', '')
                            value = value.replace('Applicatio n', 'Application')
                        value = re.sub(r'Case study - NOC and Helpdesk s(?:…|\.{3})', '', value).strip()
                        values.append(value)
                    if row_index == 0 and headers: values = headers
                    tag = 'th' if row_index == 0 and headers else 'td'
                    cells.append('<tr>' + ''.join(f'<{tag}>{html.escape(v, quote=False)}</{tag}>' for v in values) + '</tr>')
                table = '<figure class="wp-block-table"><table><tbody>' + ''.join(cells) + '</tbody></table></figure>'
                lines.append(dict(text='', size=0, x=columns[0], top=rows[0], bottom=rows[-1], page=page_index, table=table))
            for line in page_lines:
                if any(rows[0] <= line['top'] < rows[-1] for _, _, rows, _ in profiles):
                    continue
                text = clean(line['text'])
                if re.fullmatch(r'(?:Page\s+)?\d+(?:\s+of\s+\d+)?', text, re.I):
                    skipped.append(text)
                    continue
                if re.fullmatch(r'_+\s*Images(?: to be included in the case study\.?)?', text, re.I):
                    skipped.append(text)
                    continue
                if '\ufffd' in text or '(cid:' in text:
                    raise ValueError('Unresolved PDF character encoding; review before publishing.')
                sizes = Counter(round(c['size'], 1) for c in line['chars'] if c['text'].strip())
                lines.append(dict(text=text, size=sizes.most_common(1)[0][0],
                                  x=line['x0'], top=line['top'], bottom=line['bottom'], page=page_index,
                                  bold=sum('Bold' in c['fontname'] for c in line['chars']) / len(line['chars']) > 0.8))

    lines.sort(key=lambda l: (l['page'], l['top']))

    if not lines:
        raise ValueError('No usable PDF text.')
    body_size = Counter(l['size'] for l in lines).most_common(1)[0][0]
    title_size = max(l['size'] for l in lines if l['page'] == 0)
    if title_size <= body_size:
        raise ValueError('Cannot confidently identify title; manual review required.')
    title_lines = []
    while lines and lines[0]['page'] == 0 and lines[0]['size'] == title_size:
        title_lines.append(lines.pop(0)['text'])
    title_text = ''
    for part in title_lines: title_text = join_wrapped(title_text, part).strip()
    title = re.sub(r'^Case\s+Study\s*:\s*', '', title_text, flags=re.I).strip()
    if not title:
        raise ValueError('Missing PDF title.')

    blocks = []
    previous = None
    heading_sizes = sorted({l['size'] for l in lines if l['size'] > body_size * 1.1}, reverse=True)
    for line in lines:
        if 'table' in line:
            blocks.append(dict(kind='table', text='', x=line['x'], html=line['table']))
            previous = line
            continue
        text = line['text']
        bullet = re.match(r'^[●○•▪◦]\s*(.+)$', text)
        numbered = re.match(r'^(\d+)[.)]\s+(.+)$', text)
        lettered = re.match(r'^([a-z])[.)]\s+(.+)$', text)
        if heading_sizes and line['size'] == heading_sizes[0]:
            kind = 'h2'
        elif line['size'] > body_size * 1.1 or (line.get('bold') and line['x'] < 76 and not text.endswith(('.', ',', ';'))):
            kind = 'h3'
        elif bullet:
            kind, text = 'ul', bullet[1]
        elif numbered:
            kind, text = 'ol', numbered[2]
        elif lettered:
            kind, text = 'ol', lettered[2]
        else:
            kind = 'p'
        continuation = False
        if previous and blocks:
            last = blocks[-1]
            tight = line['page'] == previous['page'] and line['top'] - previous['bottom'] < body_size * 0.85
            across_page = line['page'] != previous['page'] and not re.search(r'[.!?:]$', last['text'])
            continuation = kind == 'p' and last['kind'] in ('p', 'ul', 'ol') and (tight or across_page)
            if last['kind'] in ('ul', 'ol') and line['x'] < last['x'] + 8:
                continuation = False
            if kind in ('h2', 'h3') and last['kind'] == kind and tight:
                continuation = True
            if continuation:
                last['text'] = join_wrapped(last['text'], text)
        if not continuation:
            blocks.append(dict(kind=kind, text=text, x=line['x'], number=int(numbered[1]) if numbered else ord(lettered[1])-96 if lettered else 1, alpha=bool(lettered)))
        previous = line

    # Lists nest by PDF indentation; numbered section headings remain h3 headings.
    output, stack = [], []
    def close_list():
        item = stack.pop()
        output.append('</li></' + item['kind'] + '>')
    for block in blocks:
        kind = block['kind']
        content = html.escape(block['text'], quote=False)
        if kind not in ('ul', 'ol'):
            while stack:
                close_list()
            output.append(block['html'] if kind == 'table' else f'<{kind}>{content}</{kind}>')
            continue
        while stack and block['x'] < stack[-1]['x'] - 5:
            close_list()
        if stack and abs(block['x'] - stack[-1]['x']) <= 5 and stack[-1]['kind'] != kind:
            close_list()
        if not stack or block['x'] > stack[-1]['x'] + 5:
            start = f' start="{block["number"]}"' if kind == 'ol' and block['number'] != 1 else ''
            style = ' type="a" style="list-style-type: lower-alpha"' if block.get('alpha') else ''
            output.append(f'<{kind}{start}{style}>')
            stack.append(block)
        else:
            output.append('</li>')
        value = f' value="{block["number"]}"' if kind == 'ol' else ''
        output.append(f'<li{value}>{content}')
    while stack:
        close_list()
    content = '\n'.join(output).replace('</tbody></table></figure>\n<figure class="wp-block-table"><table><tbody>', '')
    return dict(schema=1, source=source.name, sha256=hashlib.sha256(source.read_bytes()).hexdigest(),
                title=title, slug=source.stem, content=content, pages=pages,
                omitted=skipped, block_counts=dict(Counter(b['kind'] for b in blocks)))


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--file', required=True, help='One filename within imports/case-studies/')
    args = parser.parse_args()
    source = (IMPORTS / args.file).resolve()
    if Path(args.file).name != args.file or source.parent != IMPORTS.resolve() or source.suffix.lower() != '.pdf':
        parser.error('Specify one PDF filename, not a directory, glob or external path.')
    payload = extract(source)
    target = ROOT / 'imports' / 'case-study-preview'
    target.mkdir(parents=True, exist_ok=True)
    (target / f'{source.stem}.json').write_text(json.dumps(payload, ensure_ascii=False, indent=2) + '\n')
    (target / f'{source.stem}.html').write_text(payload['content'] + '\n')
    print(json.dumps({k: v for k, v in payload.items() if k != 'content'}, ensure_ascii=False, indent=2))


if __name__ == '__main__':
    main()
