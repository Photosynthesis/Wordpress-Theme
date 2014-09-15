#!/usr/bin/python
"""
Create a CSV export containing Emails for Listing Editors & Contacts.

Email Addresses and Community Names are pulled from data in a Formidable Form.
Editor Emails are derived from the Author of the associated Wordpress Post while
Contact Emails are from a Contact Email field.

Simple validation and uniqueness checks are run on the list of email addreses.

The CSV file is written to ``export.csv`` in the current working directory and
has the following fields:

    email,community name,role

Where ``role`` is either ``contact`` or ``editor``.

Written For The FIC by Pavan Rikhi<pavan@ic.org> on 8/17/2014.

"""

import re
import getpass

import MySQLdb
import MySQLdb.cursors

# MySQL Login Details
MYSQL_USER = "root"
MYSQL_PASS = getpass.getpass("MySQL Password: ")
MYSQL_DB = "fic_wp"
MYSQL_HOST = "localhost"

# The Regular Expression Used to Validate Email Addresses
EMAIL_REGEX = re.compile(r'[^@]+@[^@]+\.[^@]+')

# The ID Number of the Formidable Form
FORM_ID = 2

# The ID Number of the Formidable Field Containg the Contact Person's Email.
CONTACT_EMAIL_FIELD_ID = 199


def main():
    """Retrieve the Emails of Listing Contacts and Generate a CSV File."""
    listing_rows = get_listing_contact_rows()
    unique_csv_lines = make_unique_csv_lines(listing_rows)
    write_csv_file(unique_csv_lines)


def get_listing_contact_rows():
    """Retrieve the Listing Contacts Query Result."""
    listing_contacts_query = """
        SELECT * FROM
                  (SELECT form_id, id, post_id, user_id
                   FROM 3uOgy46w_frm_items AS items
                   WHERE items.form_id={0}) AS items
        LEFT JOIN (SELECT meta_value AS contact_email, item_id
                   FROM 3uOgy46w_frm_item_metas
                   WHERE field_id={1})
             AS cemail_metas ON items.id=cemail_metas.item_id
        LEFT JOIN (SELECT post_title, post_author, ID FROM 3uOgy46w_posts)
             AS posts ON items.post_id=posts.ID
        LEFT JOIN (SELECT user_email, ID FROM 3uOgy46w_users)
            AS users ON users.ID=posts.post_author
        WHERE items.form_id={0}""".format(FORM_ID, CONTACT_EMAIL_FIELD_ID)
    cursor = get_cursor()
    cursor.execute(listing_contacts_query)
    listing_rows = cursor.fetchall()
    return listing_rows


def get_cursor():
    """Retrieve the Database Connection Cursor."""
    connection = MySQLdb.connect(host=MYSQL_HOST, user=MYSQL_USER,
                                 passwd=MYSQL_PASS, db=MYSQL_DB,
                                 use_unicode=True, charset='utf8')
    return connection.cursor(MySQLdb.cursors.DictCursor)


def make_unique_csv_lines(rows):
    """Make CSV Lines from Listing Rows."""
    return ensure_unique_emails(make_csv_line(row) for row in rows)


def make_csv_line(listing_row):
    """Create a CSV line of ``email,community name,role`` from a Listing row."""
    output = ""
    community_name = clean(listing_row["post_title"])

    contact_email = clean(listing_row["contact_email"])
    if is_valid_email(contact_email):
        output += u"{0},{1},contact\r\n".format(contact_email, community_name)

    editor_email = clean(listing_row["user_email"])
    if is_valid_email(editor_email):
        if editor_email != contact_email:
            output += u"{0},{1},editor\r\n".format(editor_email, community_name)
    return output


def clean(field_value):
    """Cleanup a Field, Removing Quotations or Additional Commas."""
    if field_value is None:
        return None
    return field_value.replace(',', '').replace("'", '').replace('"', '')


def is_valid_email(email_address):
    """Check if an E-Mail Address is Valid."""
    return (email_address is not None and
            ' ' not in email_address and
            EMAIL_REGEX.match(email_address) is not None)


def ensure_unique_emails(csv_lines):
    """Make sure each CSV line has a unique email address."""
    unique_csv_lines = list()
    used_emails = list()
    for line in csv_lines:
        if line is None:
            continue
        email = line.split(',')[0]
        if email not in used_emails:
            unique_csv_lines.append(line)
            used_emails.append(email)
    return unique_csv_lines


def write_csv_file(csv_lines):
    """Write the Lines to ``export.csv`` in the Current Working Directory."""
    with open('./export.csv', 'w') as output_file:
        output_file.writelines(s.encode('UTF-8') for s in csv_lines)

if __name__ == "__main__":
    main()
