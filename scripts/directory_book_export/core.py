#!/usr/bin/env python3
import phpserialize
import pymysql

from html_utils import html_to_text
import fields as F


DB_HOST = 'localhost'
DB_USER = 'root'
DB_NAME = 'fic_wp'
DB_PASSWORD = 'mypet99'
DB_PREFIX = '3uOgy46w_'


def export_listings(listings, filename):
    with open(filename, 'w', encoding='utf8') as export_file:
        export_file.write('<UNICODE-MAC>\n<vsn:11>\n')
        export_file.writelines(listings)


class Exporter(object):
    def __init__(self):
        """Connect to the database and obtain a cursor."""
        connection = pymysql.connect(
            host=DB_HOST, user=DB_USER, password=DB_PASSWORD, db=DB_NAME,
            charset='utf8', cursorclass=pymysql.cursors.DictCursor)
        self.cursor = connection.cursor()
        self.field_name_cache = {}

    def create_export(self, export_fields, export_filename):
        """Export the Communities Book data for the given fields.

        ``export_fields`` should be a list of field ids.

        """
        listings = self.get_listings_from_fields(export_fields)
        export_listings(listings, export_filename)

    def get_listings_from_fields(self, fields):
        items = self.get_export_items()
        return [self.export_listing(item, fields) for item in items]

    def get_export_items(self):
        """Get a list of eligible communities from the ``frm_items`` table."""
        items_query = """
            SELECT * FROM {prefix}frm_items as items
            RIGHT JOIN (SELECT * FROM {prefix}frm_item_metas
                        WHERE field_id = 219)
                    AS in_book ON in_book.item_id=items.id
            RIGHT JOIN (SELECT * FROM {prefix}posts
                        WHERE post_type='directory')
                    AS posts on posts.ID=items.post_id
            WHERE items.form_id = 2
              AND in_book.meta_value = "Yes"
              AND posts.post_status = "publish"
            ORDER BY post_title ASC
        """.format(prefix=DB_PREFIX)
        self.cursor.execute(items_query)
        return self.cursor.fetchall()

    def export_listing(self, item, fields):
        """Export the listing into a string."""
        output = []
        for field in fields:
            field_name = field['tag']
            field_value = self.get_item_field(item, field)
            if field_value == '':
                continue
            if field['label'] != '':
                field_value = '{} {}'.format(field['label'], field_value)
            output.append("<pstyle:{}>{}\n".format(field_name, field_value))
        return ''.join(output)

    def get_field_name(self, field):
        """Return the name of the given field."""
        if field in self.field_name_cache:
            return self.field_name_cache[field]
        name_query = """SELECT name FROM {prefix}frm_fields
                        WHERE id = {}""".format(field, prefix=DB_PREFIX)
        self.cursor.execute(name_query)
        result = self.cursor.fetchone()
        if result is None:
            raise Exception("Could not find name for field {}".format(field))
        name = result['name']
        self.field_name_cache[field] = name
        return name

    def get_item_field(self, item, field):
        """Return the Field's name and value."""
        if field['number'] == F.NAME['number']:
            return item['post_title']
        value_query = """
            SELECT meta_value FROM {prefix}frm_item_metas
            WHERE field_id={} AND item_id={}
        """.format(field['number'], item['id'], prefix=DB_PREFIX)
        self.cursor.execute(value_query)
        result = self.cursor.fetchone()
        if result is None:
            return ''
        meta_value = result['meta_value']
        try:
            value = ', '.join(phpserialize.dict_to_list(phpserialize.loads(
                str.encode(meta_value), decode_strings=True)))
        except ValueError:
            value = meta_value
        return clean_field_value(value)


def clean_field_value(field_value):
    plain_text = html_to_text(field_value.strip())
    needles_to_replacements = {
        '\r': '',
        '\n': '',
        '~': ',',
        '\t': ' ',
        '  ': ' ',
        '  ': ' ',
        '  ': ' ',
    }
    for (needle, replacement) in needles_to_replacements.items():
        plain_text = plain_text.replace(needle, replacement)
    return plain_text.strip()
