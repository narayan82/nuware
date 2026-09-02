"""Regression checks for the reviewed batch layouts; no WordPress writes."""
import unittest
from extract import ROOT, extract
from test_extract import Structure


class BatchTests(unittest.TestCase):
    def test_all_documents_have_valid_html_and_headings(self):
        files = sorted((ROOT / 'assets/pdfs').glob('*.pdf'))
        self.assertEqual(len(files), 53)
        for source in files:
            with self.subTest(source=source.name):
                data = extract(source)
                parser = Structure()
                parser.feed(data['content'])
                self.assertFalse(parser.stack)
                self.assertGreater(data['block_counts'].get('h2', 0), 0)
                self.assertNotIn('Case Study:', data['title'])
                self.assertNotIn('- ', data['title'])

    def test_complex_table_values(self):
        data = extract(ROOT / 'assets/pdfs/applications-l1l2-application-dba-support-nutrition-client.pdf')
        self.assertIn('<td>25</td><td>41</td><td>66</td>', data['content'])
        self.assertIn('<td>Application / Services</td>', data['content'])

    def test_lettered_lists_and_continued_tables(self):
        data = extract(ROOT / 'assets/pdfs/ai-data-sciences-nl.pdf')
        self.assertIn('list-style-type: lower-alpha', data['content'])
        self.assertEqual(data['content'].count('<table>'), 1)
        self.assertIn('Tokenization, Entity Recognition, Statistical Parsing', data['content'])

    def test_reference_appendices_are_excluded(self):
        for name in ('ai-scaling-agentic-solutions', 'fixed-income-model'):
            data = extract(ROOT / f'assets/pdfs/{name}.pdf')
            self.assertTrue(any('image-only appendix' in note for note in data['omitted']))


if __name__ == '__main__':
    unittest.main()
