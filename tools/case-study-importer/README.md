# Case study PDF importer

The existing ACF-registered CPT is `case-studies` (renamed from `case-study`). No theme hooks or public endpoints
are added. Run these scripts from the theme directory using Python with
`pdfplumber` and a PHP CLI configured to connect to this WordPress installation.

1. Place **one selected PDF** in `imports/case-studies/`.
2. Extract and review:

   ```sh
   python3 tools/case-study-importer/extract.py --file=ai-automation.pdf
   python3 tools/case-study-importer/test_extract.py
   ```

   Review `imports/case-study-preview/ai-automation.html` and the JSON manifest.
3. Dry-run WordPress validation, then explicitly publish:

   ```sh
   php tools/case-study-importer/import.php --file=ai-automation.pdf
   php tools/case-study-importer/import.php --file=ai-automation.pdf --publish
   ```

On Local for Mac, use Local's PHP binary with `-d mysqli.default_socket=...`
pointing at this site's MySQL socket, or run in Local's Site Shell.

The title is the PDF's first large heading with `Case Study:` removed. The slug
is the filename without `.pdf`, normalized by WordPress (`ai-automation`). Posts
are created with `post_status => publish`. Existing posts are never overwritten.
Duplicate checks cover PDF SHA-256, source filename, title, and post slug,
including trashed posts. Re-running a published PDF reports `skipped_duplicate`.
A database option lock serializes runs; after a process crash, check that no
import is running before manually removing `_nuware_case_study_import_lock`.

Extraction uses font sizes, line gaps and indentation to preserve h2/h3 headings,
paragraphs, numbered sections, and nested lists. Wrapped lines and ligatures are
cleaned, retaining compound hyphens. Inline bold styling and embedded images are
not imported in this text-only pilot. The sample's trailing image-placement note
is omitted and logged in the JSON. Scanned/undecodable pages fail for manual
review; other PDF layouts require review before publishing.

## Approved assets/pdfs batch

The batch runner processes only PDFs directly inside `assets/pdfs/`. It extracts
and validates every file before importing any. Default is dry-run; `--publish`
explicitly publishes. Existing posts and featured images are never overwritten.

```sh
python3 tools/case-study-importer/batch.py --php /path/to/php --mysql-socket /path/to/mysqld.sock
python3 tools/case-study-importer/batch.py --php /path/to/php --mysql-socket /path/to/mysqld.sock --publish
```

Omit `--mysql-socket` if PHP already connects to the WordPress database. JSON/HTML
previews and `batch-dry-run.json` / `batch-published.json` reports are saved under
`imports/case-study-preview/`. Each report maps the source PDF to its post ID,
title, URL and published/skipped/error outcome.

The extractor now handles lettered sublists, font-ranked headings, wrapped titles,
and the reviewed borderless tables in `table_profiles.py`. Those table profiles
use source-specific PDF cell coordinates; re-review if source layouts change.
Image-only appendices in the two reviewed PDFs are explicitly omitted. No image
attachments or featured images are created. Three pairs in this batch contain
identical text under different filenames, so the second file in each pair is
skipped by the duplicate guard. Duplicate checks also include the legacy CPT slug.
