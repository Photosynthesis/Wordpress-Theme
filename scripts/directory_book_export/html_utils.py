"""HTML utility functions & classes."""
from html.parser import HTMLParser
import html.entities


def html_to_text(html):
    """Convert HTML Entities & Remove Tags from HTML."""
    parser = HTMLTextExtractor()
    parser.feed(html)
    return parser.get_text()


class HTMLTextExtractor(HTMLParser):
    def __init__(self):
        HTMLParser.__init__(self)
        self.result = []

    def handle_data(self, data):
        self.result.append(data)

    def handle_charref(self, number):
        character_code = (int(number[1:], 16)
                          if number[0] in ('x', 'X') else int(number))
        self.result.append(chr(character_code))

    def handle_entityref(self, name):
        character_code = html.entities.name2codepoint[name]
        self.result.append(chr(character_code))

    def get_text(self):
        return ''.join(self.result)
