#!/usr/bin/env python
"""Utility functions for interfacing with the FIC Wordpress Database."""

import getpass

import pymysql


MYSQL_USER = "root"
MYSQL_PASS = getpass.getpass("MySQL Password: ")
MYSQL_DB = "fic_wp"
MYSQL_HOST = "localhost"

WP_PREFIX = "3uOgy46w_"

DIRECTORY_FORM_ID = 2


def get_cursor():
    """Retrieve the Database Connection Cursor."""
    connection = pymysql.connect(
        host=MYSQL_HOST, user=MYSQL_USER, passwd=MYSQL_PASS, db=MYSQL_DB,
        use_unicode=True, charset='utf8'
    )
    return connection.cursor(pymysql.cursors.DictCursor)


def get_communities(cursor):
    """Retrieve the Dictionaries representing Directory Listing Entries."""
    community_query = """
        SELECT items.id as id, items.updated_at as updated_at,
               posts.post_title as post_title, posts.post_status as post_status
        FROM {0}frm_items as items
        LEFT JOIN (SELECT * FROM {0}posts WHERE post_type="directory")
            AS posts on posts.ID=items.post_id
        WHERE items.form_id={1}
    """.format(WP_PREFIX, DIRECTORY_FORM_ID)
    cursor.execute(community_query)
    results = cursor.fetchall()
    listings = []
    for result in results:
        listings.append(add_community_metas(cursor, result))
    return listings


def add_community_metas(cursor, listing):
    """Add the listing's field values to the dictionary."""
    meta_query = """
        SELECT fields.name AS field_name, metas.meta_value AS field_value
        FROM {0}frm_item_metas as metas
        INNER JOIN (SELECT id, name FROM {0}frm_fields)
            AS fields ON metas.field_id=fields.id
        WHERE metas.item_id={1}
    """.format(WP_PREFIX, listing['id'])
    cursor.execute(meta_query)
    results = cursor.fetchall()
    field_name_replacements = [
        ("'", ""),
        ("(", ""),
        (")", ""),
        ("?", ""),
        (".", ""),
        (" ", "_")
    ]
    for meta in results:
        field_name = meta['field_name'].lower()
        for (search, replace) in field_name_replacements:
            field_name = field_name.replace(search, replace)
        listing[field_name] = meta['field_value']
    return listing
