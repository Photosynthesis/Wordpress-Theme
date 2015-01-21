#!/usr/bin/python
"""Create a CSV containing all data from a communities specified in a file."""

import csv
import getpass

import MySQLdb
import MySQLdb.cursors


# MySQL Login Details
MYSQL_USER = "root"
MYSQL_PASS = getpass.getpass("MySQL Password: ")
MYSQL_DB = "fic_wp"
MYSQL_HOST = "localhost"

WP_PREFIX = "3uOgy46w_"

# Formidable Details
FORM_ID = 2
NAME_FIELD_ID = 9


def main():
    """Read, pull & export community data."""
    cursor = get_database_cursor()
    community_ids = get_community_ids_to_export(cursor)
    data = [pull_community_data(cursor, community_name, community_id)
            for (community_name, community_id) in community_ids]
    export_to_csv(data)


def get_database_cursor():
    """Prompt for a password, create a connection and return a cursor."""
    connection = MySQLdb.connect(host=MYSQL_HOST, user=MYSQL_USER,
                                 passwd=MYSQL_PASS, db=MYSQL_DB,
                                 use_unicode=True, charset='utf8')
    return connection.cursor(MySQLdb.cursors.DictCursor)


def get_community_ids_to_export(cursor):
    """Retrieve a list of ids for the communities to export.

    A list is expected to be stored in a file named
    `communities_export_data.csv`. It should contain the name of 1 Community
    per line.

    """
    with open('./communities_export_data.csv') as input_file:
        community_names = input_file.readlines()
    ids = [get_id_from_name(name.strip(), cursor) for name in community_names]
    return [community_id for community_id in ids if community_id is not None]


def get_id_from_name(community_name, cursor):
    """Retrieve a Community's id given it's name."""
    name_query = """
        SELECT items.id as item_id
        FROM {0}frm_items as items
        INNER JOIN (
          SELECT ID as post_id
          FROM {0}posts
          WHERE post_title='{1}'
            AND post_type="directory")
          AS posts
          ON posts.post_id=items.post_id
    """.format(WP_PREFIX, community_name.replace("'", "\\'"), NAME_FIELD_ID)
    cursor.execute(name_query)
    result = cursor.fetchone()
    if result:
        return (community_name, int(result['item_id']))
    return None


def pull_community_data(cursor, community_name, community_id):
    """Pull the data for a single community."""
    data_query = """
        SELECT fields.name as field_name,
               metas.meta_value as field_value
        FROM {0}frm_item_metas as metas
        INNER JOIN (
          SELECT id, name
          FROM {0}frm_fields)
          AS fields
          ON metas.field_id=fields.id
        WHERE metas.item_id={1}
    """.format(WP_PREFIX, community_id)
    cursor.execute(data_query)
    result = cursor.fetchall()
    data = {'Community Name': community_name}
    for row in result:
        data[row['field_name']] = row['field_value']
    return {k: v.encode('utf8') for k, v in data.items()}


def export_to_csv(data):
    """Save the escaped data to an Excel CSV file."""
    keys = set("Community Name")
    _ = [keys.add(key) for community in data for key in community.keys()]
    with open('communities_for_research.csv', 'w') as output_file:
        output_file.write(u'\ufeff'.encode('utf8'))  # Required for Excel UTF-8
        dict_writer = csv.DictWriter(output_file, keys)
        dict_writer.writeheader()
        dict_writer.writerows(data)


if __name__ == '__main__':
    main()
