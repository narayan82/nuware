"""Regression checks for the approved single-PDF test fixture."""
from html.parser import HTMLParser
import unittest
from extract import extract, IMPORTS


class Structure(HTMLParser):
    def __init__(self):
        super().__init__()
        self.stack = []
        self.nested = 0

    def handle_starttag(self, tag, attrs):
        if tag == 'li':
            assert self.stack[-1] in ('ol', 'ul')
        if tag == 'ul' and self.stack and self.stack[-1] == 'li':
            self.nested += 1
        self.stack.append(tag)

    def handle_endtag(self, tag):
        assert self.stack.pop() == tag


class SampleTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.data = extract(IMPORTS / 'ai-automation.pdf')

    def test_title_and_filename_slug(self):
        self.assertEqual(self.data['title'], 'Enterprise-Scale AI Automation – From Vision to Impact')
        self.assertEqual(self.data['slug'], 'ai-automation')

    def test_sections_and_lists(self):
        self.assertEqual(self.data['block_counts'], {'h2': 7, 'p': 11, 'ul': 34, 'h3': 4, 'ol': 3})
        parser = Structure()
        parser.feed(self.data['content'])
        self.assertEqual(parser.stack, [])
        self.assertEqual(parser.nested, 3)

    def test_wrap_cleanup_and_artifacts(self):
        content = self.data['content']
        self.assertIn('processes end-to-end,', content)
        self.assertIn('self-learning environments', content)
        self.assertIn('data entry, and exception handling introduce costly mistakes.', content)
        self.assertNotIn('Images to be included', content)
        self.assertNotIn('●', content)
        self.assertNotIn('Case Study:', content)


if __name__ == '__main__':
    unittest.main()
